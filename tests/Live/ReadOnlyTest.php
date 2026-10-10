<?php

declare(strict_types=1);

namespace Stejac\MiteBundle\Tests\Live;

use Stejac\MiteBundle\Api\AccountApi;
use Stejac\MiteBundle\Api\BookmarksApi;
use Stejac\MiteBundle\Api\ChangesApi;
use Stejac\MiteBundle\Api\CustomersApi;
use Stejac\MiteBundle\Api\ProjectsApi;
use Stejac\MiteBundle\Api\ServicesApi;
use Stejac\MiteBundle\Api\TimeEntriesApi;
use Stejac\MiteBundle\Api\TrackerApi;
use Stejac\MiteBundle\Api\UsersApi;
use Stejac\MiteBundle\Dto\Filter\TimeEntryFilter;
use Stejac\MiteBundle\Enum\ChangesResource;
use Stejac\MiteBundle\Enum\GroupBy;
use Stejac\MiteBundle\Enum\Period;
use Stejac\MiteBundle\Exception\NotFoundException;

final class ReadOnlyTest extends LiveTestCase
{
    public function testEveryReadEndpointMaps(): void
    {
        $client = $this->liveClient();

        $myself = (new AccountApi($client))->myself();
        self::assertGreaterThan(0, $myself->id);
        self::assertNotSame('', (new AccountApi($client))->get()->name);

        $projects = (new ProjectsApi($client))->list();
        (new ProjectsApi($client))->listArchived();
        if ([] !== $projects) {
            (new ProjectsApi($client))->get($projects[0]->id);
        }
        (new CustomersApi($client))->list();
        (new CustomersApi($client))->listArchived();
        (new ServicesApi($client))->list();
        (new ServicesApi($client))->listArchived();
        (new BookmarksApi($client))->list();
        (new TrackerApi($client))->show();

        if ('time_tracker' !== $myself->role) {
            (new UsersApi($client))->list();
            (new UsersApi($client))->listArchived();
        }

        $entries = (new TimeEntriesApi($client))->list(new TimeEntryFilter(at: Period::LastMonth, limit: 20));
        (new TimeEntriesApi($client))->today();
        (new TimeEntriesApi($client))->grouped([GroupBy::Project, GroupBy::Month], new TimeEntryFilter(at: Period::ThisYear));
        if ([] !== $entries) {
            (new TimeEntriesApi($client))->get($entries[0]->id);
        }

        $this->addToAssertionCount(1);
    }

    public function testChangesFeedHeaderWorks(): void
    {
        $client = $this->liveClient();
        if ('time_tracker' === (new AccountApi($client))->myself()->role) {
            self::markTestSkipped('Time trackers cannot read changes.');
        }

        $changes = new ChangesApi($client);
        foreach (ChangesResource::cases() as $resource) {
            $set = $changes->fetch($changes->start($resource));
            self::assertNotNull($set->next, $resource->value.' did not return a follow-up cursor.');
        }
    }

    public function testUnknownIdRaisesNotFound(): void
    {
        $this->expectException(NotFoundException::class);

        (new ProjectsApi($this->liveClient()))->get(999999999);
    }
}
