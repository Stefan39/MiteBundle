<?php

declare(strict_types=1);

namespace Stejac\MiteBundle\Dto;

use Stejac\MiteBundle\Internal\Data;

final readonly class Account
{
    public function __construct(
        public int $id,
        public string $name,
        public string $title,
        public string $currency,
        public \DateTimeImmutable $createdAt,
        public \DateTimeImmutable $updatedAt,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            Data::int($data, 'id'),
            Data::string($data, 'name'),
            Data::string($data, 'title'),
            Data::string($data, 'currency'),
            Data::dateTime($data, 'created_at'),
            Data::dateTime($data, 'updated_at'),
        );
    }
}
