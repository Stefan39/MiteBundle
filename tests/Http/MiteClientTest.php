<?php

declare(strict_types=1);

namespace Stejac\MiteBundle\Tests\Http;

use PHPUnit\Framework\Attributes\DataProvider;
use Stejac\MiteBundle\Exception\AuthenticationException;
use Stejac\MiteBundle\Exception\ForbiddenException;
use Stejac\MiteBundle\Exception\InvalidArgumentException;
use Stejac\MiteBundle\Exception\InvalidResponseException;
use Stejac\MiteBundle\Exception\LockedException;
use Stejac\MiteBundle\Exception\MiteException;
use Stejac\MiteBundle\Exception\NotFoundException;
use Stejac\MiteBundle\Exception\ServerException;
use Stejac\MiteBundle\Exception\ValidationException;
use Stejac\MiteBundle\Http\MiteClient;
use Stejac\MiteBundle\Tests\ApiTestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

final class MiteClientTest extends ApiTestCase
{
    public function testGetBuildsUrlAndSendsAuthHeaders(): void
    {
        $response = $this->client(self::json(['ok' => true]))->get('/account.json');

        self::assertSame('GET', $this->requestMethod());
        self::assertSame('https://acme.mite.de/account.json', $this->requestUrl());
        $headers = $this->requests[0]['options']['normalized_headers'] ?? [];
        self::assertIsArray($headers);
        self::assertSame(['X-MiteApiKey: secret-key'], $headers['x-miteapikey'] ?? null);
        self::assertSame(['User-Agent: test-agent'], $headers['user-agent'] ?? null);
        self::assertSame(['ok' => true], $response->data);
        self::assertSame(200, $response->status);
    }

    public function testQueryListsAreEncodedWithBrackets(): void
    {
        $this->client(self::json([]))->get('/time_entries.json', ['user_id' => '1,2', 'note' => ['foo', 'bar baz']]);

        self::assertSame('https://acme.mite.de/time_entries.json?user_id=1,2&note[]=foo&note[]=bar baz', $this->requestUrl());
    }

    public function testPostSendsJsonBody(): void
    {
        $this->client(self::json(['service' => ['id' => 1]], 201))->post('/services.json', ['service' => ['name' => 'Dev']]);

        self::assertSame('POST', $this->requestMethod());
        self::assertSame(['service' => ['name' => 'Dev']], $this->requestBody());
        $headers = $this->requests[0]['options']['normalized_headers'] ?? [];
        self::assertIsArray($headers);
        self::assertSame(['Content-Type: application/json'], $headers['content-type'] ?? null);
    }

    public function testEmptyBodyDecodesToNull(): void
    {
        $response = $this->client(self::emptyResponse())->patch('/services/1.json', ['service' => ['name' => 'x']]);

        self::assertNull($response->data);
    }

    public function testDeleteWithoutBodySendsNoBody(): void
    {
        $this->client(self::emptyResponse())->delete('/services/1.json');

        self::assertSame('DELETE', $this->requestMethod());
        self::assertSame('', $this->requests[0]['options']['body'] ?? '');
    }

    public function testNonJsonBodyRaisesInvalidResponse(): void
    {
        $this->expectException(InvalidResponseException::class);

        $this->client(new MockResponse('<html>oops</html>', ['http_code' => 200]))->get('/account.json');
    }

    public function testHeadersAreExposedLowerCased(): void
    {
        $response = $this->client(self::json([], 200, ['X-Changes-Link' => 'https://acme.mite.de/projects/changes/abc.json']))->get('/projects.json');

        self::assertSame('https://acme.mite.de/projects/changes/abc.json', $response->header('X-Changes-Link'));
        self::assertNull($response->header('X-Missing'));
    }

    public function testRedirectIsNotFollowedWhenRequested(): void
    {
        $response = $this->client(new MockResponse('', ['http_code' => 302, 'response_headers' => ['Location' => 'https://acme.mite.de/time_entries.json?at=today']]))
            ->getWithoutRedirect('/time_entries/bookmarks/3/follow.json');

        self::assertSame(302, $response->status);
        self::assertSame('https://acme.mite.de/time_entries.json?at=today', $response->header('location'));
    }

    public function testRedirectIsNeverFollowedSoTheKeyStaysOnTheMiteHost(): void
    {
        $client = $this->client(new MockResponse('', ['http_code' => 302, 'response_headers' => ['Location' => 'https://evil.example.com/collect']]));

        try {
            $client->get('/account.json');
            self::fail('Expected a MiteException for an unexpected redirect.');
        } catch (MiteException $e) {
            self::assertStringContainsString('redirect', $e->getMessage());
        }
        self::assertCount(1, $this->requests);
        self::assertSame(0, $this->requests[0]['options']['max_redirects'] ?? null);
    }

    #[DataProvider('foreignPaths')]
    public function testAbsoluteOrProtocolRelativePathIsRejectedBeforeAnyRequest(string $path): void
    {
        $client = $this->client();

        try {
            $client->get($path);
            self::fail('Expected an InvalidArgumentException.');
        } catch (InvalidArgumentException) {
        }
        self::assertSame([], $this->requests);
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function foreignPaths(): iterable
    {
        yield 'absolute url' => ['https://evil.example.com/x.json'];
        yield 'protocol relative' => ['//evil.example.com/x.json'];
        yield 'relative without slash' => ['account.json'];
    }

    /**
     * @param class-string<MiteException> $expected
     */
    #[DataProvider('errorStatuses')]
    public function testErrorStatusMapsToException(int $status, string $expected): void
    {
        try {
            $this->client(self::json(['error' => 'Something went wrong'], $status))->get('/account.json');
            self::fail('Expected an exception.');
        } catch (MiteException $e) {
            self::assertInstanceOf($expected, $e);
            self::assertSame('Something went wrong', $e->getMessage());
            self::assertSame($status, $e->getCode());
            self::assertStringNotContainsString('secret-key', $e->getMessage());
        }
    }

    /**
     * @return iterable<string, array{int, class-string<MiteException>}>
     */
    public static function errorStatuses(): iterable
    {
        yield '401' => [401, AuthenticationException::class];
        yield '403' => [403, ForbiddenException::class];
        yield '404' => [404, NotFoundException::class];
        yield '422' => [422, ValidationException::class];
        yield '423' => [423, LockedException::class];
        yield '500' => [500, ServerException::class];
        yield '503' => [503, ServerException::class];
        yield '409' => [409, MiteException::class];
    }

    public function testValidationErrorsListIsExposed(): void
    {
        try {
            $this->client(self::json(['errors' => ['Name is missing', 'Budget is invalid']], 422))->post('/projects.json', ['project' => []]);
            self::fail('Expected a ValidationException.');
        } catch (ValidationException $e) {
            self::assertSame(['Name is missing', 'Budget is invalid'], $e->getErrors());
            self::assertSame('Name is missing; Budget is invalid', $e->getMessage());
        }
    }

    public function testErrorWithoutBodyHasGenericMessage(): void
    {
        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessage('mite API responded with HTTP 404.');

        $this->client(self::emptyResponse(404))->get('/projects/9.json');
    }

    public function testTransportErrorIsWrapped(): void
    {
        $this->expectException(MiteException::class);

        $this->client(new MockResponse('', ['error' => 'Could not resolve host']))->get('/account.json');
    }

    #[DataProvider('invalidDomains')]
    public function testInvalidDomainIsRejectedBeforeAnyRequest(string $domain): void
    {
        $http = new MockHttpClient(static function (): MockResponse {
            throw new \LogicException('No request must be sent.');
        });

        $this->expectException(InvalidArgumentException::class);

        new MiteClient($domain, 'secret-key', 'test-agent', $http);
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function invalidDomains(): iterable
    {
        yield 'url' => ['https://acme.mite.de'];
        yield 'host' => ['acme.mite.de'];
        yield 'path' => ['evil.com/x'];
        yield 'empty' => [''];
        yield 'leading hyphen' => ['-acme'];
        yield 'at sign' => ['user@evil'];
    }

    public function testEmptyApiKeyIsRejected(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new MiteClient('acme', '  ', 'test-agent', new MockHttpClient());
    }
}
