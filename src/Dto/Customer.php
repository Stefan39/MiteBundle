<?php

declare(strict_types=1);

namespace Stejac\MiteBundle\Dto;

use Stejac\MiteBundle\Enum\ActiveHourlyRate;
use Stejac\MiteBundle\Internal\Data;

final readonly class Customer
{
    /**
     * @param int|null                   $hourlyRate            cents, used when $activeHourlyRate is HourlyRate
     * @param list<HourlyRatePerService> $hourlyRatesPerService used when $activeHourlyRate is HourlyRatesPerService
     */
    public function __construct(
        public int $id,
        public string $name,
        public string $note,
        public ?ActiveHourlyRate $activeHourlyRate,
        public ?int $hourlyRate,
        public bool $archived,
        public array $hourlyRatesPerService,
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
            Data::nullableEnum($data, 'active_hourly_rate', ActiveHourlyRate::class),
            Data::nullableInt($data, 'hourly_rate'),
            Data::bool($data, 'archived'),
            HourlyRatePerService::listFromArray($data, 'hourly_rates_per_service'),
            Data::dateTime($data, 'created_at'),
            Data::dateTime($data, 'updated_at'),
        );
    }
}
