<?php

declare(strict_types=1);

namespace Stejac\MiteBundle\Http;

use Stejac\MiteBundle\Exception\AuthenticationException;
use Stejac\MiteBundle\Exception\ForbiddenException;
use Stejac\MiteBundle\Exception\InvalidArgumentException;
use Stejac\MiteBundle\Exception\InvalidResponseException;
use Stejac\MiteBundle\Exception\LockedException;
use Stejac\MiteBundle\Exception\MiteException;
use Stejac\MiteBundle\Exception\NotFoundException;
use Stejac\MiteBundle\Exception\ServerException;
use Stejac\MiteBundle\Exception\ValidationException;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Low-level transport for the mite JSON API. Use the services in Stejac\MiteBundle\Api instead.
 */
final class MiteClient
{
    /** A single DNS label: the account sub-domain of <domain>.mite.de. */
    private const DOMAIN_PATTERN = '/^[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?$/i';

    private readonly HttpClientInterface $httpClient;

    public function __construct(
        string $domain,
        #[\SensitiveParameter] string $apiKey,
        string $userAgent = 'stejac/mite-bundle',
        ?HttpClientInterface $httpClient = null,
    ) {
        if (1 !== preg_match(self::DOMAIN_PATTERN, $domain)) {
            throw new InvalidArgumentException(\sprintf('Invalid mite domain "%s": expected only the account sub-domain, e.g. "acme" for acme.mite.de.', $domain));
        }
        if ('' === trim($apiKey)) {
            throw new InvalidArgumentException('The mite API key must not be empty.');
        }

        $this->httpClient = ($httpClient ?? HttpClient::create())->withOptions([
            'base_uri' => \sprintf('https://%s.mite.de/', $domain),
            'headers' => [
                'X-MiteApiKey' => $apiKey,
                'User-Agent' => $userAgent,
            ],
        ]);
    }

    /**
     * @param array<string, string|list<string>> $query
     */
    public function get(string $path, array $query = []): MiteResponse
    {
        return $this->request('GET', $path, $query);
    }

    /**
     * Sends a GET request and returns 3xx responses instead of following them.
     */
    public function getWithoutRedirect(string $path): MiteResponse
    {
        return $this->request('GET', $path, maxRedirects: 0);
    }

    /**
     * @param array<string, mixed> $body
     */
    public function post(string $path, array $body): MiteResponse
    {
        return $this->request('POST', $path, body: $body);
    }

    /**
     * @param array<string, mixed> $body
     */
    public function patch(string $path, array $body): MiteResponse
    {
        return $this->request('PATCH', $path, body: $body);
    }

    /**
     * @param array<string, mixed> $body
     */
    public function delete(string $path, array $body = []): MiteResponse
    {
        return $this->request('DELETE', $path, body: [] === $body ? null : $body);
    }

    /**
     * @param array<string, string|list<string>> $query
     * @param array<string, mixed>|null          $body
     */
    private function request(string $method, string $path, array $query = [], ?array $body = null, int $maxRedirects = 20): MiteResponse
    {
        $options = ['max_redirects' => $maxRedirects];
        if (null !== $body) {
            $options['json'] = $body;
        }

        try {
            $response = $this->httpClient->request($method, $path.self::encodeQuery($query), $options);
            $status = $response->getStatusCode();
            $headers = $response->getHeaders(false);
            $content = $response->getContent(false);
        } catch (TransportExceptionInterface $e) {
            throw new MiteException(\sprintf('Request to mite failed: %s', $e->getMessage()), 0, $e);
        }

        if ($status >= 400) {
            throw self::errorFor($status, $content);
        }

        return new MiteResponse($status, self::decode($content), $headers);
    }

    /**
     * Encodes lists as repeated "key[]=value" pairs, the format mite expects (e.g. note[]).
     *
     * @param array<string, string|list<string>> $query
     */
    private static function encodeQuery(array $query): string
    {
        $parts = [];
        foreach ($query as $key => $value) {
            foreach (\is_array($value) ? $value : [$value] as $item) {
                $parts[] = rawurlencode(\is_array($value) ? $key.'[]' : $key).'='.rawurlencode($item);
            }
        }

        return [] === $parts ? '' : '?'.implode('&', $parts);
    }

    private static function decode(string $content): mixed
    {
        if ('' === trim($content)) {
            return null;
        }

        try {
            return json_decode($content, true, 512, \JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            throw new InvalidResponseException('mite returned a body that is not valid JSON.', 0, $e);
        }
    }

    private static function errorFor(int $status, string $content): MiteException
    {
        $errors = self::errorMessages($content);
        $message = [] === $errors ? \sprintf('mite API responded with HTTP %d.', $status) : implode('; ', $errors);

        return match (true) {
            401 === $status => new AuthenticationException($message, $status),
            403 === $status => new ForbiddenException($message, $status),
            404 === $status => new NotFoundException($message, $status),
            422 === $status => new ValidationException($message, $status, $errors),
            423 === $status => new LockedException($message, $status),
            $status >= 500 => new ServerException($message, $status),
            default => new MiteException($message, $status),
        };
    }

    /**
     * @return list<string>
     */
    private static function errorMessages(string $content): array
    {
        try {
            $decoded = json_decode($content, true, 512, \JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            return [];
        }
        if (!\is_array($decoded)) {
            return [];
        }

        $raw = $decoded['error'] ?? $decoded['errors'] ?? null;
        $messages = \is_array($raw) ? $raw : [$raw];

        return array_values(array_filter(
            array_map(static fn (mixed $m): string => \is_string($m) ? $m : '', $messages),
            static fn (string $m): bool => '' !== $m,
        ));
    }
}
