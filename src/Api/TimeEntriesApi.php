<?php

declare(strict_types=1);

namespace Stejac\MiteBundle\Api;

use Stejac\MiteBundle\Dto\Filter\TimeEntryFilter;
use Stejac\MiteBundle\Dto\Input\NewTimeEntry;
use Stejac\MiteBundle\Dto\Input\TimeEntryUpdate;
use Stejac\MiteBundle\Dto\TimeEntry;
use Stejac\MiteBundle\Dto\TimeEntryGroup;
use Stejac\MiteBundle\Enum\GroupBy;
use Stejac\MiteBundle\Http\MiteClient;
use Stejac\MiteBundle\Http\MiteResponse;
use Stejac\MiteBundle\Internal\Data;

final class TimeEntriesApi
{
    public function __construct(private readonly MiteClient $client)
    {
    }

    /**
     * @return list<TimeEntry>
     */
    public function list(?TimeEntryFilter $filter = null): array
    {
        return $this->map($this->client->get('/time_entries.json', ($filter ?? new TimeEntryFilter())->toQuery()));
    }

    /**
     * @param non-empty-list<GroupBy> $groupBy
     *
     * @return list<TimeEntryGroup>
     */
    public function grouped(array $groupBy, ?TimeEntryFilter $filter = null): array
    {
        $query = ($filter ?? new TimeEntryFilter())->toQuery();
        $query['group_by'] = implode(',', array_map(static fn (GroupBy $group): string => $group->value, $groupBy));

        return array_map(TimeEntryGroup::fromArray(...), Data::unwrapList($this->client->get('/time_entries.json', $query)->data, 'time_entry_group'));
    }

    /**
     * Entries of the current user for today.
     *
     * @return list<TimeEntry>
     */
    public function today(): array
    {
        return $this->map($this->client->get('/daily.json'));
    }

    /**
     * Entries of the current user for the given day.
     *
     * @return list<TimeEntry>
     */
    public function forDay(\DateTimeInterface $day): array
    {
        return $this->map($this->client->get(\sprintf('/daily/%s.json', $day->format('Y/n/j'))));
    }

    public function get(int $id): TimeEntry
    {
        return TimeEntry::fromArray(Data::unwrap($this->client->get(\sprintf('/time_entries/%d.json', $id))->data, 'time_entry'));
    }

    public function create(NewTimeEntry $entry): TimeEntry
    {
        return TimeEntry::fromArray(Data::unwrap($this->client->post('/time_entries.json', ['time_entry' => $entry->toArray()])->data, 'time_entry'));
    }

    /**
     * Fails with a LockedException for locked entries unless an admin sets withForce().
     */
    public function update(int $id, TimeEntryUpdate $update): void
    {
        $this->client->patch(\sprintf('/time_entries/%d.json', $id), ['time_entry' => $update->toArray()]);
    }

    /**
     * @param bool $force admins only: delete a locked entry
     */
    public function delete(int $id, bool $force = false): void
    {
        $this->client->delete(\sprintf('/time_entries/%d.json', $id), $force ? ['time_entry' => ['force' => true]] : []);
    }

    /**
     * @return list<TimeEntry>
     */
    private function map(MiteResponse $response): array
    {
        return array_map(TimeEntry::fromArray(...), Data::unwrapList($response->data, 'time_entry'));
    }
}
