<?php

declare(strict_types=1);

namespace Stejac\MiteBundle\Dto;

use Stejac\MiteBundle\Exception\InvalidResponseException;

/**
 * Position in a changes feed. Persist $path and restore it with fromLink() to continue later (mite keeps changes for 7 days).
 */
final readonly class ChangesCursor
{
    private const PATH_PATTERN = '#^/[a-z_]+/changes/[^/?\#]+\.json$#';

    private function __construct(public string $path)
    {
    }

    /**
     * Accepts the X-Changes-Link header value or a stored path. Only the path is kept, so the request always
     * goes to the configured account host.
     */
    public static function fromLink(string $link): self
    {
        $path = parse_url($link, \PHP_URL_PATH);
        if (!\is_string($path) || 1 !== preg_match(self::PATH_PATTERN, $path)) {
            throw new InvalidResponseException(\sprintf('Unexpected changes link "%s".', $link));
        }

        return new self($path);
    }
}
