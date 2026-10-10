<?php

declare(strict_types=1);

namespace Stejac\MiteBundle\Tests\Api;

use Stejac\MiteBundle\Api\TimeEntriesApi;
use Stejac\MiteBundle\Dto\Filter\TimeEntryFilter;
use Stejac\MiteBundle\Dto\Input\NewTimeEntry;
use Stejac\MiteBundle\Dto\Input\TimeEntryUpdate;
use Stejac\MiteBundle\Enum\GroupBy;
use Stejac\MiteBundle\Enum\Period;
use Stejac\MiteBundle\Enum\SortDirection;
use Stejac\MiteBundle\Enum\TimeEntrySort;
use Stejac\MiteBundle\Exception\InvalidArgumentException;
use Stejac\MiteBundle\Exception\LockedException;
use Stejac\MiteBundle\Tests\ApiTestCase;
use Stejac\MiteBundle\Tests\Fixtures;

final class TimeEntriesApiTest extends ApiTestCase
{
    public function testListMapsAllFields(): void
    {
        $entries = (new TimeEntriesApi($this->client(self::json([['time_entry' => Fixtures::timeEntry()]]))))->list();

        self::assertSame('https://acme.mite.de/time_entries.json', $this->requestUrl());
        $entry = $entries[0];
        self::assertSame(90, $entry->minutes);
        self::assertSame('2026-10-09', $entry->dateAt->format('Y-m-d'));
        self::assertSame(12000.0, $entry->revenue);
        self::assertSame('Development', $entry->serviceName);
        self::assertNull($entry->tracking);
    }

    public function testFilterQuery(): void
    {
        (new TimeEntriesApi($this->client(self::json([]))))->list(new TimeEntryFilter(
            userIds: [7, 8],
            projectIds: [11],
            notes: ['review', 'meeting'],
            from: new \DateTimeImmutable('2026-10-01'),
            to: Period::Today,
            billable: true,
            locked: false,
            sort: TimeEntrySort::Minutes,
            direction: SortDirection::Asc,
            limit: 50,
            page: 1,
        ));

        self::assertSame(
            'https://acme.mite.de/time_entries.json?user_id=7,8&project_id=11&note[]=review&note[]=meeting&from=2026-10-01&to=today&billable=true&locked=false&sort=minutes&direction=asc&limit=50&page=1',
            $this->requestUrl(),
        );
    }

    public function testSingleNoteAndCurrentUser(): void
    {
        (new TimeEntriesApi($this->client(self::json([]))))->list(new TimeEntryFilter(currentUser: true, notes: ['review'], at: Period::ThisWeek, tracking: true));

        self::assertSame('https://acme.mite.de/time_entries.json?user_id=current&note=review&at=this_week&tracking=true', $this->requestUrl());
    }

    public function testCurrentUserAndUserIdsAreExclusive(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new TimeEntryFilter(userIds: [7], currentUser: true);
    }

    public function testTrackingObjectIsMapped(): void
    {
        $fixture = array_merge(Fixtures::timeEntry(), ['tracking' => ['since' => '2026-10-09T09:00:00+02:00', 'minutes' => 95], 'revenue' => null, 'project_id' => null, 'project_name' => null]);
        $entry = (new TimeEntriesApi($this->client(self::json(['time_entry' => $fixture]))))->get(101);

        self::assertSame('https://acme.mite.de/time_entries/101.json', $this->requestUrl());
        self::assertNotNull($entry->tracking);
        self::assertSame(95, $entry->tracking->minutes);
        self::assertNull($entry->revenue);
        self::assertNull($entry->projectId);
    }

    public function testGrouped(): void
    {
        $groups = (new TimeEntriesApi($this->client(self::json([['time_entry_group' => [
            'minutes' => 240, 'revenue' => 32000.0, 'user_id' => 7, 'user_name' => 'Jane Doe',
            'from' => '2026-10-01', 'to' => '2026-10-09', 'time_entries_params' => ['user_id' => 7],
        ]]]))))->grouped([GroupBy::User, GroupBy::Month], new TimeEntryFilter(at: Period::ThisMonth));

        self::assertSame('https://acme.mite.de/time_entries.json?at=this_month&group_by=user,month', $this->requestUrl());
        self::assertSame(240, $groups[0]->minutes);
        self::assertSame(['user_id' => 7, 'user_name' => 'Jane Doe'], $groups[0]->groupFields);
        self::assertSame(['user_id' => 7], $groups[0]->timeEntriesParams);
        self::assertSame('2026-10-01', $groups[0]->from?->format('Y-m-d'));
    }

    public function testDailyAliases(): void
    {
        $api = new TimeEntriesApi($this->client(self::json([]), self::json([['time_entry' => Fixtures::timeEntry()]])));

        $api->today();
        $entries = $api->forDay(new \DateTimeImmutable('2026-02-07'));

        self::assertSame('https://acme.mite.de/daily.json', $this->requestUrl(0));
        self::assertSame('https://acme.mite.de/daily/2026/2/7.json', $this->requestUrl(1));
        self::assertCount(1, $entries);
    }

    public function testCreate(): void
    {
        $entry = (new TimeEntriesApi($this->client(self::json(['time_entry' => Fixtures::timeEntry()], 201))))
            ->create(new NewTimeEntry(dateAt: new \DateTimeImmutable('2026-10-09'), minutes: 90, note: 'Code review', projectId: 11, serviceId: 21));

        self::assertSame(['time_entry' => ['date_at' => '2026-10-09', 'minutes' => 90, 'note' => 'Code review', 'project_id' => 11, 'service_id' => 21]], $this->requestBody());
        self::assertSame(101, $entry->id);
    }

    public function testUpdate(): void
    {
        (new TimeEntriesApi($this->client(self::emptyResponse())))
            ->update(101, TimeEntryUpdate::new()->withMinutes(120)->withServiceId(null)->withLocked(false)->withForce(true));

        self::assertSame('PATCH', $this->requestMethod());
        self::assertSame(['time_entry' => ['minutes' => 120, 'service_id' => null, 'locked' => false, 'force' => true]], $this->requestBody());
    }

    public function testDelete(): void
    {
        $api = new TimeEntriesApi($this->client(self::emptyResponse(), self::emptyResponse()));

        $api->delete(101);
        $api->delete(102, force: true);

        self::assertSame('', $this->requests[0]['options']['body'] ?? '');
        self::assertSame(['time_entry' => ['force' => true]], $this->requestBody(1));
    }

    public function testLockedEntry(): void
    {
        $this->expectException(LockedException::class);

        (new TimeEntriesApi($this->client(self::json(['error' => 'Time entry is locked'], 423))))->update(101, TimeEntryUpdate::new()->withMinutes(1));
    }
}
