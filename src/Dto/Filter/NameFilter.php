<?php

declare(strict_types=1);

namespace Stejac\MiteBundle\Dto\Filter;

use Stejac\MiteBundle\Internal\Query;

/**
 * Filter for customers and services lists: partial, case-insensitive name match plus pagination.
 */
final readonly class NameFilter
{
    public function __construct(
        public ?string $name = null,
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
        return Query::compact(['name' => $this->name, 'limit' => $this->limit, 'page' => $this->page]);
    }
}
