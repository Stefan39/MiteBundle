<?php

declare(strict_types=1);

namespace Stejac\MiteBundle\Dto;

use Stejac\MiteBundle\Internal\Data;

/**
 * One row of a grouped time entries report (group_by).
 */
final readonly class TimeEntryGroup
{
    private const KNOWN_KEYS = ['minutes', 'revenue', 'from', 'to', 'time_entries_params'];

    /**
     * @param float|null                 $revenue           cents
     * @param array<string, scalar|null> $groupFields       the grouping identifiers, e.g. ['user_id' => 7, 'user_name' => 'Jane Doe']
     * @param array<string, mixed>       $timeEntriesParams filter parameters that list this group's entries
     */
    public function __construct(
        public int $minutes,
        public ?float $revenue,
        public ?\DateTimeImmutable $from,
        public ?\DateTimeImmutable $to,
        public array $groupFields,
        public array $timeEntriesParams,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        $groupFields = [];
        foreach ($data as $key => $value) {
            if (!\in_array($key, self::KNOWN_KEYS, true) && (null === $value || \is_scalar($value))) {
                $groupFields[$key] = $value;
            }
        }

        return new self(
            Data::int($data, 'minutes'),
            Data::nullableFloat($data, 'revenue'),
            Data::nullableDate($data, 'from'),
            Data::nullableDate($data, 'to'),
            $groupFields,
            Data::object($data['time_entries_params'] ?? [], 'time_entries_params'),
        );
    }
}
