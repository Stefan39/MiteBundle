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

    protected function tearDown(): void
    {
        unset($_SERVER['MITE_TEST_DOMAIN'], $_SERVER['MITE_TEST_KEY'], $_SERVER['MITE_TEST_BAD_DOMAIN']);
    }
}
