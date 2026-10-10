<?php

declare(strict_types=1);

namespace Stejac\MiteBundle\Dto\Input;

use Stejac\MiteBundle\Dto\HourlyRatePerService;
use Stejac\MiteBundle\Enum\ActiveHourlyRate;

final class CustomerUpdate extends AbstractUpdate
{
    public function withName(string $name): static
    {
        return $this->with('name', $name);
    }

    public function withNote(string $note): static
    {
        return $this->with('note', $note);
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
