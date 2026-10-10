<?php

declare(strict_types=1);

namespace Stejac\MiteBundle\Dto\Input;

use Stejac\MiteBundle\Dto\HourlyRatePerService;
use Stejac\MiteBundle\Enum\ActiveHourlyRate;
use Stejac\MiteBundle\Enum\BudgetType;
use Stejac\MiteBundle\Internal\Payload;

final readonly class NewProject
{
    /**
     * @param int|null                        $budget                minutes or cents, depending on $budgetType
     * @param int|null                        $hourlyRate            cents
     * @param list<HourlyRatePerService>|null $hourlyRatesPerService
     */
    public function __construct(
        public string $name,
        public ?string $note = null,
        public ?int $customerId = null,
        public ?int $budget = null,
        public ?BudgetType $budgetType = null,
        public ?ActiveHourlyRate $activeHourlyRate = null,
        public ?int $hourlyRate = null,
        public ?array $hourlyRatesPerService = null,
        public ?bool $archived = null,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return Payload::compact([
            'name' => $this->name,
            'note' => $this->note,
            'customer_id' => $this->customerId,
            'budget' => $this->budget,
            'budget_type' => $this->budgetType?->value,
            'active_hourly_rate' => $this->activeHourlyRate?->value,
            'hourly_rate' => $this->hourlyRate,
            'hourly_rates_per_service' => null === $this->hourlyRatesPerService
                ? null
                : array_map(static fn (HourlyRatePerService $rate): array => $rate->toArray(), $this->hourlyRatesPerService),
            'archived' => $this->archived,
        ]);
    }
}
