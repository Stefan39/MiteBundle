<?php

declare(strict_types=1);

namespace Stejac\MiteBundle\Dto\Filter;

use Stejac\MiteBundle\Enum\Period;
use Stejac\MiteBundle\Enum\SortDirection;
use Stejac\MiteBundle\Enum\TimeEntrySort;
use Stejac\MiteBundle\Exception\InvalidArgumentException;
use Stejac\MiteBundle\Internal\Query;

final readonly class TimeEntryFilter
{
    /**
     * @param list<int>    $userIds
     * @param bool         $currentUser only entries of the user the API key belongs to (user_id=current)
     * @param list<int>    $customerIds
     * @param list<int>    $projectIds
     * @param list<int>    $serviceIds
     * @param list<string> $notes       several notes are OR-combined
     */
    public function __construct(
        public array $userIds = [],
        public bool $currentUser = false,
        public array $customerIds = [],
        public array $projectIds = [],
        public array $serviceIds = [],
        public array $notes = [],
        public Period|\DateTimeInterface|null $at = null,
        public Period|\DateTimeInterface|null $from = null,
        public Period|\DateTimeInterface|null $to = null,
        public ?bool $billable = null,
        public ?bool $locked = null,
        public ?bool $tracking = null,
        public ?TimeEntrySort $sort = null,
        public ?SortDirection $direction = null,
        public ?int $limit = null,
        public ?int $page = null,
    ) {
        if ($currentUser && [] !== $userIds) {
            throw new InvalidArgumentException('Use either $userIds or $currentUser, not both.');
        }
        Query::assertPositive($limit, 'limit');
        Query::assertPositive($page, 'page');
    }

    /**
     * @return array<string, string|list<string>>
     */
    public function toQuery(): array
    {
        return Query::compact([
            'user_id' => $this->currentUser ? 'current' : Query::ids($this->userIds),
            'customer_id' => Query::ids($this->customerIds),
            'project_id' => Query::ids($this->projectIds),
            'service_id' => Query::ids($this->serviceIds),
            'note' => 1 === \count($this->notes) ? $this->notes[0] : $this->notes,
            'at' => Query::date($this->at),
            'from' => Query::date($this->from),
            'to' => Query::date($this->to),
            'billable' => $this->billable,
            'locked' => $this->locked,
            'tracking' => $this->tracking,
            'sort' => $this->sort?->value,
            'direction' => $this->direction?->value,
            'limit' => $this->limit,
            'page' => $this->page,
        ]);
    }
}
