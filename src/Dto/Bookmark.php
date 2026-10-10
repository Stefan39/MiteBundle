<?php

declare(strict_types=1);

namespace Stejac\MiteBundle\Dto;

use Stejac\MiteBundle\Internal\Data;

final readonly class Bookmark
{
    /**
     * @param string   $query  time-entries query string, e.g. "project_id=11&at=this_week"
     * @param int|null $userId null for team bookmarks
     */
    public function __construct(
        public int $id,
        public string $name,
        public string $query,
        public string $type,
        public int $accountId,
        public ?int $userId,
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
            Data::text($data, 'query'),
            Data::string($data, 'type'),
            Data::int($data, 'account_id'),
            Data::nullableInt($data, 'user_id'),
            Data::dateTime($data, 'created_at'),
            Data::dateTime($data, 'updated_at'),
        );
    }
}
