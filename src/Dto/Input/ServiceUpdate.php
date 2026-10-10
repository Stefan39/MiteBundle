<?php

declare(strict_types=1);

namespace Stejac\MiteBundle\Dto\Input;

final class ServiceUpdate extends AbstractUpdate
{
    public function withName(string $name): static
    {
        return $this->with('name', $name);
    }

    public function withNote(string $note): static
    {
        return $this->with('note', $note);
    }

    /**
     * @param int|null $hourlyRate cents; null removes the rate
     */
    public function withHourlyRate(?int $hourlyRate): static
    {
        return $this->with('hourly_rate', $hourlyRate);
    }

    public function withBillable(bool $billable): static
    {
        return $this->with('billable', $billable);
    }

    public function withArchived(bool $archived): static
    {
        return $this->with('archived', $archived);
    }

    /**
     * Apply a changed hourly rate to existing time entries, not only to new ones.
     */
    public function withUpdateHourlyRateOnTimeEntries(bool $update = true): static
    {
        return $this->with('update_hourly_rate_on_time_entries', $update);
    }
}
