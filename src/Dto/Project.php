<?php

declare(strict_types=1);

namespace Stejac\MiteBundle\Dto;

use Stejac\MiteBundle\Enum\ActiveHourlyRate;
use Stejac\MiteBundle\Enum\BudgetType;
use Stejac\MiteBundle\Internal\Data;

final readonly class Project
{
    /**
     * @param string|null                $customerName          only returned by some endpoints, e.g. a single GET
     * @param int                        $budget                minutes or cents, depending on $budgetType
     * @param int|null                   $hourlyRate            cents
     * @param list<HourlyRatePerService> $hourlyRatesPerService
     */
    public function __construct(
        public int $id,
        public string $name,
        public string $note,
        public ?int $customerId,
        public ?string $customerName,
        public int $budget,
        public BudgetType $budgetType,
        public ?int $hourlyRate,
        public bool $archived,
        public ?ActiveHourlyRate $activeHourlyRate,
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
            Data::nullableInt($data, 'customer_id'),
            Data::nullableString($data, 'customer_name'),
            Data::int($data, 'budget'),
            Data::enum($data, 'budget_type', BudgetType::class),
            Data::nullableInt($data, 'hourly_rate'),
            Data::bool($data, 'archived'),
            Data::nullableEnum($data, 'active_hourly_rate', ActiveHourlyRate::class),
            HourlyRatePerService::listFromArray($data, 'hourly_rates_per_service'),
            Data::dateTime($data, 'created_at'),
            Data::dateTime($data, 'updated_at'),
        );
    }
}
