<?php

declare(strict_types=1);

namespace Stejac\MiteBundle\Dto;

use Stejac\MiteBundle\Internal\Data;

final readonly class TimeEntry
{
    /**
     * @param float|null    $revenue    cents
     * @param int           $hourlyRate cents
     * @param Tracking|null $tracking   set while a timer runs on this entry
     */
    public function __construct(
        public int $id,
        public int $minutes,
        public \DateTimeImmutable $dateAt,
        public string $note,
        public bool $billable,
        public bool $locked,
        public ?float $revenue,
        public int $hourlyRate,
        public int $userId,
        public string $userName,
        public ?int $projectId,
        public ?string $projectName,
        public ?int $customerId,
        public ?string $customerName,
        public ?int $serviceId,
        public ?string $serviceName,
        public \DateTimeImmutable $createdAt,
        public \DateTimeImmutable $updatedAt,
        public ?Tracking $tracking,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        $tracking = $data['tracking'] ?? null;

        return new self(
            Data::int($data, 'id'),
            Data::int($data, 'minutes'),
            Data::date($data, 'date_at'),
            Data::text($data, 'note'),
            Data::bool($data, 'billable'),
            Data::bool($data, 'locked'),
            Data::nullableFloat($data, 'revenue'),
            Data::int($data, 'hourly_rate'),
            Data::int($data, 'user_id'),
            Data::text($data, 'user_name'),
            Data::nullableInt($data, 'project_id'),
            Data::nullableString($data, 'project_name'),
            Data::nullableInt($data, 'customer_id'),
            Data::nullableString($data, 'customer_name'),
            Data::nullableInt($data, 'service_id'),
            Data::nullableString($data, 'service_name'),
            Data::dateTime($data, 'created_at'),
            Data::dateTime($data, 'updated_at'),
            null === $tracking ? null : Tracking::fromArray(Data::object($tracking, 'tracking')),
        );
    }
}
