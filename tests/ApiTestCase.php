<?php

declare(strict_types=1);

namespace Stejac\MiteBundle\Tests;

use PHPUnit\Framework\TestCase;
use Stejac\MiteBundle\Http\MiteClient;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

abstract class ApiTestCase extends TestCase
{
    /** @var list<array{method: string, url: string, options: array<string, mixed>}> */
    protected array $requests = [];

    protected function client(MockResponse ...$responses): MiteClient
    {
        $queue = array_values($responses);
        $http = new MockHttpClient(function (string $method, string $url, array $options) use (&$queue): MockResponse {
            $this->requests[] = ['method' => $method, 'url' => $url, 'options' => $options];
            $next = array_shift($queue);
            if (null === $next) {
                throw new \LogicException(\sprintf('No mock response left for %s %s.', $method, $url));
            }

            return $next;
        });

        return new MiteClient('acme', 'secret-key', 'test-agent', $http);
    }

    /**
     * @param array<string, string> $headers
     */
    protected static function json(mixed $data, int $status = 200, array $headers = []): MockResponse
    {
        return new MockResponse(json_encode($data, \JSON_THROW_ON_ERROR), [
            'http_code' => $status,
            'response_headers' => array_merge(['content-type' => 'application/json'], $headers),
        ]);
    }

    protected static function emptyResponse(int $status = 200): MockResponse
    {
        return new MockResponse('', ['http_code' => $status]);
    }

    protected function requestBody(int $index = 0): mixed
    {
        $body = $this->requests[$index]['options']['body'] ?? '';

        return json_decode(\is_string($body) ? $body : '', true, 512, \JSON_THROW_ON_ERROR);
    }

    protected function requestUrl(int $index = 0): string
    {
        return urldecode($this->requests[$index]['url']);
    }

    protected function requestMethod(int $index = 0): string
    {
        return $this->requests[$index]['method'];
    }
}
