<?php

declare(strict_types=1);

namespace Stejac\MiteBundle\Dto;

use Stejac\MiteBundle\Internal\Data;

final readonly class User
{
    /**
     * @param string $role mite role, e.g. "owner", "admin", "coworker", "time_tracker"; kept as string because mite does not document a closed list
     */
    public function __construct(
        public int $id,
        public string $name,
        public string $email,
        public string $note,
        public bool $archived,
        public string $role,
        public string $language,
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
            Data::string($data, 'email'),
            Data::text($data, 'note'),
            Data::bool($data, 'archived'),
            Data::string($data, 'role'),
            Data::string($data, 'language'),
            Data::dateTime($data, 'created_at'),
            Data::dateTime($data, 'updated_at'),
        );
    }
}
