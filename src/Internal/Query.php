<?php

declare(strict_types=1);

namespace Stejac\MiteBundle\Internal;

use Stejac\MiteBundle\Enum\Period;
use Stejac\MiteBundle\Exception\InvalidArgumentException;

/**
 * @internal
 */
final class Query
{
    /**
     * @param array<string, string|int|bool|list<string>|null> $params
     *
     * @return array<string, string|list<string>>
     */
    public static function compact(array $params): array
    {
        $query = [];
        foreach ($params as $key => $value) {
            if (null === $value || [] === $value) {
                continue;
            }
            $query[$key] = match (true) {
                \is_bool($value) => $value ? 'true' : 'false',
                \is_int($value) => (string) $value,
                default => $value,
            };
        }

        return $query;
    }

    /**
     * @param list<int> $ids
     */
    public static function ids(array $ids): ?string
    {
        return [] === $ids ? null : implode(',', $ids);
    }

    public static function date(Period|\DateTimeInterface|null $value): ?string
    {
        return match (true) {
            null === $value => null,
            $value instanceof Period => $value->value,
            default => $value->format('Y-m-d'),
        };
    }

    public static function assertPositive(?int $value, string $name): void
    {
        if (null !== $value && $value < 1) {
            throw new InvalidArgumentException(\sprintf('"%s" must be at least 1, got %d.', $name, $value));
        }
    }
}
