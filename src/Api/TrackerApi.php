<?php

declare(strict_types=1);

namespace Stejac\MiteBundle\Api;

use Stejac\MiteBundle\Dto\Tracker;
use Stejac\MiteBundle\Http\MiteClient;
use Stejac\MiteBundle\Http\MiteResponse;
use Stejac\MiteBundle\Internal\Data;

/**
 * The timer of the user the API key belongs to.
 */
final class TrackerApi
{
    public function __construct(private readonly MiteClient $client)
    {
    }

    public function show(): Tracker
    {
        return $this->map($this->client->get('/tracker.json'));
    }

    /**
     * Starts the timer on a time entry; a timer running on another entry is stopped.
     */
    public function start(int $timeEntryId): Tracker
    {
        return $this->map($this->client->patch(\sprintf('/tracker/%d.json', $timeEntryId), []));
    }

    /**
     * Stops the timer; safe to call when it is not running.
     */
    public function stop(int $timeEntryId): Tracker
    {
        return $this->map($this->client->delete(\sprintf('/tracker/%d.json', $timeEntryId)));
    }

    private function map(MiteResponse $response): Tracker
    {
        return Tracker::fromArray(Data::unwrap($response->data, 'tracker'));
    }
}
