<?php

declare(strict_types=1);

namespace Stejac\MiteBundle\Dto\Input;

use Stejac\MiteBundle\Internal\Payload;

final readonly class NewTimeEntry
{
    /**
     * @param \DateTimeInterface|null $dateAt defaults to today in mite
     * @param int|null                $userId admins only
     * @param bool|null               $locked admins only
     */
    public function __construct(
        public ?\DateTimeInterface $dateAt = null,
        public ?int $minutes = null,
        public ?string $note = null,
        public ?int $userId = null,
        public ?int $projectId = null,
        public ?int $serviceId = null,
        public ?bool $locked = null,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return Payload::compact([
            'date_at' => $this->dateAt?->format('Y-m-d'),
            'minutes' => $this->minutes,
            'note' => $this->note,
            'user_id' => $this->userId,
            'project_id' => $this->projectId,
            'service_id' => $this->serviceId,
            'locked' => $this->locked,
        ]);
    }
}
