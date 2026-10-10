<?php

declare(strict_types=1);

namespace Stejac\MiteBundle\Internal;

/**
 * @internal
 */
final class Payload
{
    /**
     * Drops null values so that mite applies its own defaults.
     *
     * @param array<string, mixed> $fields
     *
     * @return array<string, mixed>
     */
    public static function compact(array $fields): array
    {
        return array_filter($fields, static fn (mixed $value): bool => null !== $value);
    }
}
