<?php

declare(strict_types=1);

namespace Stejac\MiteBundle\Tests\Live;

use Stejac\MiteBundle\Api\TimeEntriesApi;
use Stejac\MiteBundle\Api\TrackerApi;
use Stejac\MiteBundle\Dto\Input\NewTimeEntry;
use Stejac\MiteBundle\Dto\Input\TimeEntryUpdate;
use Stejac\MiteBundle\Exception\NotFoundException;

final class WriteTest extends LiveTestCase
{
    public function testTimeEntryLifecycleAndTracker(): void
    {
        $client = $this->liveClient();
        if ('1' !== self::env('MITE_LIVE_WRITE')) {
            self::markTestSkipped('Set MITE_LIVE_WRITE=1 to run write tests.');
        }
        $entries = new TimeEntriesApi($client);
        $tracker = new TrackerApi($client);

        $entry = $entries->create(new NewTimeEntry(minutes: 1, note: 'mite-bundle live test'));
        try {
            self::assertSame('mite-bundle live test', $entries->get($entry->id)->note);

            $entries->update($entry->id, TimeEntryUpdate::new()->withMinutes(2)->withNote('mite-bundle live test (updated)'));
            self::assertSame(2, $entries->get($entry->id)->minutes);

            self::assertTrue($tracker->start($entry->id)->isRunning());
            self::assertNotNull($entries->get($entry->id)->tracking);
            self::assertSame($entry->id, $tracker->stop($entry->id)->stoppedTimeEntry?->id);
        } finally {
            $tracker->stop($entry->id);
            $entries->delete($entry->id);
        }

        $this->expectException(NotFoundException::class);
        $entries->get($entry->id);
    }
}
