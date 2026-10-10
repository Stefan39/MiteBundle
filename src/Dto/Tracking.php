<?php

declare(strict_types=1);

namespace Stejac\MiteBundle\Dto;

use Stejac\MiteBundle\Internal\Data;

/**
 * A running timer on a time entry.
 */
final readonly class Tracking
{
    public function __construct(
        public \DateTimeImmutable $since,
        public int $minutes,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(Data::dateTime($data, 'since'), Data::int($data, 'minutes'));
    }
}
