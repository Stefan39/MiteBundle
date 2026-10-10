<?php

declare(strict_types=1);

namespace Stejac\MiteBundle\Tests\Api;

use Stejac\MiteBundle\Api\TrackerApi;
use Stejac\MiteBundle\Tests\ApiTestCase;

final class TrackerApiTest extends ApiTestCase
{
    public function testShowWhenIdle(): void
    {
        $tracker = (new TrackerApi($this->client(self::json(['tracker' => new \stdClass()]))))->show();

        self::assertSame('https://acme.mite.de/tracker.json', $this->requestUrl());
        self::assertFalse($tracker->isRunning());
        self::assertNull($tracker->stoppedTimeEntry);
    }

    public function testShowWhenRunning(): void
    {
        $tracker = (new TrackerApi($this->client(self::json(['tracker' => ['tracking_time_entry' => ['id' => 101, 'minutes' => 247, 'since' => '2026-10-09T09:00:00+02:00']]]))))->show();

        self::assertTrue($tracker->isRunning());
        self::assertNotNull($tracker->trackingTimeEntry);
        self::assertSame(101, $tracker->trackingTimeEntry->id);
        self::assertSame(247, $tracker->trackingTimeEntry->minutes);
    }

    public function testStartStopsPreviousEntry(): void
    {
        $tracker = (new TrackerApi($this->client(self::json(['tracker' => [
            'tracking_time_entry' => ['id' => 102, 'minutes' => 0, 'since' => '2026-10-09T10:00:00+02:00'],
            'stopped_time_entry' => ['id' => 101, 'minutes' => 60],
        ]]))))->start(102);

        self::assertSame('PATCH', $this->requestMethod());
        self::assertSame('https://acme.mite.de/tracker/102.json', $this->requestUrl());
        self::assertSame(101, $tracker->stoppedTimeEntry?->id);
    }

    public function testStop(): void
    {
        $tracker = (new TrackerApi($this->client(self::json(['tracker' => ['stopped_time_entry' => ['id' => 102, 'minutes' => 15]]]))))->stop(102);

        self::assertSame('DELETE', $this->requestMethod());
        self::assertSame('https://acme.mite.de/tracker/102.json', $this->requestUrl());
        self::assertFalse($tracker->isRunning());
        self::assertSame(15, $tracker->stoppedTimeEntry?->minutes);
    }
}
