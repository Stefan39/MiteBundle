<?php

declare(strict_types=1);

namespace Stejac\MiteBundle\Dto\Filter;

use Stejac\MiteBundle\Internal\Query;

final readonly class ProjectFilter
{
    /**
     * @param list<int> $customerIds
     */
    public function __construct(
        public ?string $name = null,
        public array $customerIds = [],
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
        return Query::compact([
            'name' => $this->name,
            'customer_id' => Query::ids($this->customerIds),
            'limit' => $this->limit,
            'page' => $this->page,
        ]);
    }
}
