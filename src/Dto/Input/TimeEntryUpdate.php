<?php

declare(strict_types=1);

namespace Stejac\MiteBundle\Dto\Input;

final class TimeEntryUpdate extends AbstractUpdate
{
    public function withDateAt(\DateTimeInterface $dateAt): static
    {
        return $this->with('date_at', $dateAt->format('Y-m-d'));
    }

    public function withMinutes(int $minutes): static
    {
        return $this->with('minutes', $minutes);
    }

    public function withNote(string $note): static
    {
        return $this->with('note', $note);
    }

    /**
     * Admins only.
     */
    public function withUserId(int $userId): static
    {
        return $this->with('user_id', $userId);
    }

    public function withProjectId(?int $projectId): static
    {
        return $this->with('project_id', $projectId);
    }

    public function withServiceId(?int $serviceId): static
    {
        return $this->with('service_id', $serviceId);
    }

    /**
     * Admins only.
     */
    public function withLocked(bool $locked): static
    {
        return $this->with('locked', $locked);
    }

    /**
     * Admins only: change a locked entry anyway.
     */
    public function withForce(bool $force = true): static
    {
        return $this->with('force', $force);
    }
}
