<?php

declare(strict_types=1);

namespace Stejac\MiteBundle\Dto;

use Stejac\MiteBundle\Internal\Data;

/**
 * State of the current user's timer. Only one timer per user can run at a time.
 */
final readonly class Tracker
{
    public function __construct(
        public ?TrackedTimeEntry $trackingTimeEntry,
        public ?StoppedTimeEntry $stoppedTimeEntry,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        $tracking = $data['tracking_time_entry'] ?? null;
        $stopped = $data['stopped_time_entry'] ?? null;

        return new self(
            null === $tracking ? null : TrackedTimeEntry::fromArray(Data::object($tracking, 'tracking_time_entry')),
            null === $stopped ? null : StoppedTimeEntry::fromArray(Data::object($stopped, 'stopped_time_entry')),
        );
    }

    public function isRunning(): bool
    {
        return null !== $this->trackingTimeEntry;
    }
}
