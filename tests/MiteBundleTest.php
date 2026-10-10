<?php

declare(strict_types=1);

namespace Stejac\MiteBundle\Tests;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Stejac\MiteBundle\Api\AccountApi;
use Stejac\MiteBundle\Exception\InvalidArgumentException;
use Stejac\MiteBundle\Tests\Kernel\AccountMockHttpClient;
use Stejac\MiteBundle\Tests\Kernel\TestKernel;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

final class MiteBundleTest extends TestCase
{
    /**
     * @return iterable<string, array{class-string}>
     */
    public static function apiClasses(): iterable
    {
        foreach (glob(\dirname(__DIR__).'/src/Api/*.php') ?: [] as $file) {
            /** @var class-string $class */
            $class = 'Stejac\\MiteBundle\\Api\\'.basename($file, '.php');
            yield basename($file, '.php') => [$class];
        }
    }

    /**
     * @param class-string $class
     */
    #[DataProvider('apiClasses')]
    public function testEveryApiServiceIsPublicAndAutowired(string $class): void
    {
        $kernel = new TestKernel(['domain' => 'acme', 'api_key' => 'secret-key']);
        $kernel->boot();

        self::assertInstanceOf($class, $kernel->getContainer()->get($class));
    }

    public function testEnvPlaceholdersAreResolvedAtRuntime(): void
    {
        $_SERVER['MITE_TEST_DOMAIN'] = 'acme';
        $_SERVER['MITE_TEST_KEY'] = 'secret-key';
        $kernel = new TestKernel(['domain' => '%env(MITE_TEST_DOMAIN)%', 'api_key' => '%env(MITE_TEST_KEY)%']);
        $kernel->boot();

        self::assertInstanceOf(AccountApi::class, $kernel->getContainer()->get(AccountApi::class));
    }

    public function testInvalidDomainFromEnvFailsWhenTheClientIsCreated(): void
    {
        $_SERVER['MITE_TEST_BAD_DOMAIN'] = 'evil.example.com';
        $kernel = new TestKernel(['domain' => '%env(MITE_TEST_BAD_DOMAIN)%', 'api_key' => 'secret-key']);
        $kernel->boot();

        $this->expectException(InvalidArgumentException::class);
        $kernel->getContainer()->get(AccountApi::class);
    }

    public function testMissingApiKeyIsAConfigError(): void
    {
        $this->expectException(InvalidConfigurationException::class);

        (new TestKernel(['domain' => 'acme']))->boot();
    }

    public function testCustomHttpClientServiceIsUsed(): void
    {
        $kernel = new TestKernel(
            ['domain' => 'acme', 'api_key' => 'secret-key', 'http_client' => 'app.mite_http'],
            ['app.mite_http' => AccountMockHttpClient::class],
        );
        $kernel->boot();

        $api = $kernel->getContainer()->get(AccountApi::class);
        self::assertInstanceOf(AccountApi::class, $api);
        self::assertSame('Acme Inc.', $api->get()->title);
    }

    private mixed $errorHandlerBefore = null;

    private mixed $exceptionHandlerBefore = null;

    protected function setUp(): void
    {
        $this->errorHandlerBefore = self::currentErrorHandler();
        $this->exceptionHandlerBefore = self::currentExceptionHandler();
    }

    protected function tearDown(): void
    {
        unset($_SERVER['MITE_TEST_DOMAIN'], $_SERVER['MITE_TEST_KEY'], $_SERVER['MITE_TEST_BAD_DOMAIN']);

        // FrameworkBundle 7.4 registers Symfony's ErrorHandler on boot; PHPUnit flags leftover handlers as risky.
        for ($i = 0; $i < 5 && self::currentExceptionHandler() !== $this->exceptionHandlerBefore; ++$i) {
            restore_exception_handler();
        }
        for ($i = 0; $i < 5 && self::currentErrorHandler() !== $this->errorHandlerBefore; ++$i) {
            restore_error_handler();
        }
    }

    private static function currentErrorHandler(): mixed
    {
        $handler = set_error_handler(static fn (): bool => false);
        restore_error_handler();

        return $handler;
    }

    private static function currentExceptionHandler(): mixed
    {
        $handler = set_exception_handler(static function (\Throwable $e): void {});
        restore_exception_handler();

        return $handler;
    }
}
