<?php

declare(strict_types=1);

namespace Stejac\MiteBundle\Dto;

use Stejac\MiteBundle\Internal\Data;

/**
 * Hourly rate in cents for one service, used by customers and projects.
 */
final readonly class HourlyRatePerService
{
    public function __construct(
        public int $serviceId,
        public int $hourlyRate,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        if (isset($data['hourly_rate_per_service'])) {
            $data = Data::object($data['hourly_rate_per_service'], 'hourly_rate_per_service');
        }

        return new self(Data::int($data, 'service_id'), Data::int($data, 'hourly_rate'));
    }

    /**
     * @param array<string, mixed> $data
     *
     * @return list<self>
     */
    public static function listFromArray(array $data, string $key): array
    {
        return array_map(
            static fn (mixed $item): self => self::fromArray(Data::object($item, $key)),
            Data::list($data[$key] ?? [], $key),
        );
    }

    /**
     * @return array{service_id: int, hourly_rate: int}
     */
    public function toArray(): array
    {
        return ['service_id' => $this->serviceId, 'hourly_rate' => $this->hourlyRate];
    }
}
