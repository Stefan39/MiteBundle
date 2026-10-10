<?php

declare(strict_types=1);

namespace Stejac\MiteBundle\Dto;

use Stejac\MiteBundle\Internal\Data;

final readonly class Service
{
    /**
     * @param int|null $hourlyRate cents
     */
    public function __construct(
        public int $id,
        public string $name,
        public string $note,
        public ?int $hourlyRate,
        public bool $archived,
        public bool $billable,
        public \DateTimeImmutable $createdAt,
        public \DateTimeImmutable $updatedAt,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            Data::int($data, 'id'),
            Data::string($data, 'name'),
            Data::text($data, 'note'),
            Data::nullableInt($data, 'hourly_rate'),
            Data::bool($data, 'archived'),
            Data::bool($data, 'billable'),
            Data::dateTime($data, 'created_at'),
            Data::dateTime($data, 'updated_at'),
        );
    }
}
