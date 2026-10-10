<?php

declare(strict_types=1);

namespace Stejac\MiteBundle\Dto\Filter;

use Stejac\MiteBundle\Internal\Query;

/**
 * Filter for users lists: partial, case-insensitive name and email match plus pagination.
 */
final readonly class UserFilter
{
    public function __construct(
        public ?string $name = null,
        public ?string $email = null,
        public ?int $limit = null,
        public ?int $page = null,
    ) {
        Query::assertPositive($limit, 'limit');
        Query::assertPositive($page, 'page');
    }

    /**
     * @return array<string, string|list<string>>
     */
    public function toQuery(): array
    {
        return Query::compact(['name' => $this->name, 'email' => $this->email, 'limit' => $this->limit, 'page' => $this->page]);
    }
}
