<?php

declare(strict_types=1);

namespace Stejac\MiteBundle\Dto\Input;

use Stejac\MiteBundle\Dto\HourlyRatePerService;
use Stejac\MiteBundle\Enum\ActiveHourlyRate;
use Stejac\MiteBundle\Enum\BudgetType;

final class ProjectUpdate extends AbstractUpdate
{
    public function withName(string $name): static
    {
        return $this->with('name', $name);
    }

    public function withNote(string $note): static
    {
        return $this->with('note', $note);
    }

    public function withCustomerId(?int $customerId): static
    {
        return $this->with('customer_id', $customerId);
    }

    /**
     * @param int $budget minutes or cents, depending on the budget type
     */
    public function withBudget(int $budget): static
    {
        return $this->with('budget', $budget);
    }

    public function withBudgetType(BudgetType $budgetType): static
    {
        return $this->with('budget_type', $budgetType->value);
    }

    public function withActiveHourlyRate(?ActiveHourlyRate $activeHourlyRate): static
    {
        return $this->with('active_hourly_rate', $activeHourlyRate?->value);
    }

    /**
     * @param int|null $hourlyRate cents
     */
    public function withHourlyRate(?int $hourlyRate): static
    {
        return $this->with('hourly_rate', $hourlyRate);
    }

    /**
     * @param list<HourlyRatePerService> $rates
     */
    public function withHourlyRatesPerService(array $rates): static
    {
        return $this->with('hourly_rates_per_service', array_map(static fn (HourlyRatePerService $rate): array => $rate->toArray(), $rates));
    }

    public function withArchived(bool $archived): static
    {
        return $this->with('archived', $archived);
    }

    /**
     * Apply changed hourly rates to existing time entries, not only to new ones.
     */
    public function withUpdateHourlyRateOnTimeEntries(bool $update = true): static
    {
        return $this->with('update_hourly_rate_on_time_entries', $update);
    }
}
