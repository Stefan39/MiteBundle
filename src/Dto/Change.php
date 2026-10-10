<?php

declare(strict_types=1);

namespace Stejac\MiteBundle\Dto;

use Stejac\MiteBundle\Enum\ChangeAction;
use Stejac\MiteBundle\Internal\Data;

final readonly class Change
{
    /**
     * @param string                                  $recordType e.g. "project", "customer", "service", "time_entry"
     * @param Project|Customer|Service|TimeEntry|null $record     null for deleted records and unknown record types
     */
    public function __construct(
        public string $recordType,
        public ChangeAction $action,
        public int $recordId,
        public Project|Customer|Service|TimeEntry|null $record,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        $type = Data::string($data, 'record_type');
        $record = $data['record'] ?? null;

        return new self(
            $type,
            Data::enum($data, 'action', ChangeAction::class),
            Data::int($data, 'record_id'),
            null === $record ? null : self::record($type, $record),
        );
    }

    private static function record(string $type, mixed $record): Project|Customer|Service|TimeEntry|null
    {
        return match ($type) {
            'project' => Project::fromArray(Data::unwrap($record, 'project')),
            'customer' => Customer::fromArray(Data::unwrap($record, 'customer')),
            'service' => Service::fromArray(Data::unwrap($record, 'service')),
            'time_entry' => TimeEntry::fromArray(Data::unwrap($record, 'time_entry')),
            default => null,
        };
    }
}
