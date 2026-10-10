<?php

declare(strict_types=1);

namespace Stejac\MiteBundle\Internal;

use Stejac\MiteBundle\Exception\InvalidResponseException;

/**
 * Strict, typed access to decoded mite JSON.
 *
 * @internal
 */
final class Data
{
    /**
     * @return array<string, mixed>
     */
    public static function object(mixed $value, string $context): array
    {
        if (!\is_array($value) || ([] !== $value && array_is_list($value))) {
            throw new InvalidResponseException(\sprintf('Expected a JSON object for "%s".', $context));
        }

        /** @var array<string, mixed> $object */
        $object = $value;

        return $object;
    }

    /**
     * @return list<mixed>
     */
    public static function list(mixed $value, string $context): array
    {
        if (!\is_array($value) || !array_is_list($value)) {
            throw new InvalidResponseException(\sprintf('Expected a JSON array for "%s".', $context));
        }

        return $value;
    }

    /**
     * Unwraps mite's resource envelope: {"service": {...}} -> {...}.
     *
     * @return array<string, mixed>
     */
    public static function unwrap(mixed $value, string $key): array
    {
        return self::object(self::object($value, $key)[$key] ?? null, $key);
    }

    /**
     * Unwraps a list of envelopes: [{"service": {...}}, ...] -> [{...}, ...].
     *
     * @return list<array<string, mixed>>
     */
    public static function unwrapList(mixed $value, string $key): array
    {
        return array_map(static fn (mixed $item): array => self::unwrap($item, $key), self::list($value, $key));
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function int(array $data, string $key): int
    {
        $value = $data[$key] ?? null;
        if (!\is_int($value)) {
            throw self::invalid($key, 'an integer');
        }

        return $value;
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function nullableInt(array $data, string $key): ?int
    {
        return null === ($data[$key] ?? null) ? null : self::int($data, $key);
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function string(array $data, string $key): string
    {
        $value = $data[$key] ?? null;
        if (!\is_string($value)) {
            throw self::invalid($key, 'a string');
        }

        return $value;
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function nullableString(array $data, string $key): ?string
    {
        return null === ($data[$key] ?? null) ? null : self::string($data, $key);
    }

    /**
     * Free text such as "note": null and missing become an empty string.
     *
     * @param array<string, mixed> $data
     */
    public static function text(array $data, string $key): string
    {
        return self::nullableString($data, $key) ?? '';
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function bool(array $data, string $key): bool
    {
        $value = $data[$key] ?? null;
        if (!\is_bool($value)) {
            throw self::invalid($key, 'a boolean');
        }

        return $value;
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function nullableFloat(array $data, string $key): ?float
    {
        $value = $data[$key] ?? null;
        if (null === $value) {
            return null;
        }
        if (!\is_int($value) && !\is_float($value)) {
            throw self::invalid($key, 'a number');
        }

        return (float) $value;
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function dateTime(array $data, string $key): \DateTimeImmutable
    {
        $value = self::string($data, $key);
        try {
            return new \DateTimeImmutable($value);
        } catch (\Exception) {
            throw self::invalid($key, 'an ISO 8601 date-time');
        }
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function date(array $data, string $key): \DateTimeImmutable
    {
        $date = \DateTimeImmutable::createFromFormat('!Y-m-d', self::string($data, $key));
        if (false === $date) {
            throw self::invalid($key, 'a date (YYYY-MM-DD)');
        }

        return $date;
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function nullableDate(array $data, string $key): ?\DateTimeImmutable
    {
        return null === ($data[$key] ?? null) ? null : self::date($data, $key);
    }

    /**
     * @template T of \BackedEnum
     *
     * @param array<string, mixed> $data
     * @param class-string<T>      $enum
     *
     * @return T
     */
    public static function enum(array $data, string $key, string $enum): \BackedEnum
    {
        $value = $data[$key] ?? null;
        $case = \is_string($value) ? $enum::tryFrom($value) : null;
        if (null === $case) {
            $allowed = implode(', ', array_map(static fn (\BackedEnum $c): string => (string) $c->value, $enum::cases()));
            throw self::invalid($key, 'one of '.$allowed);
        }

        return $case;
    }

    /**
     * @template T of \BackedEnum
     *
     * @param array<string, mixed> $data
     * @param class-string<T>      $enum
     *
     * @return T|null
     */
    public static function nullableEnum(array $data, string $key, string $enum): ?\BackedEnum
    {
        return null === ($data[$key] ?? null) ? null : self::enum($data, $key, $enum);
    }

    private static function invalid(string $key, string $expected): InvalidResponseException
    {
        return new InvalidResponseException(\sprintf('Expected field "%s" to be %s.', $key, $expected));
    }
}
