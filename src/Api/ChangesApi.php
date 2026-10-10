<?php

declare(strict_types=1);

namespace Stejac\MiteBundle\Api;

use Stejac\MiteBundle\Dto\Change;
use Stejac\MiteBundle\Dto\ChangesCursor;
use Stejac\MiteBundle\Dto\ChangeSet;
use Stejac\MiteBundle\Enum\ChangesResource;
use Stejac\MiteBundle\Exception\InvalidResponseException;
use Stejac\MiteBundle\Http\MiteClient;
use Stejac\MiteBundle\Internal\Data;

/**
 * Incremental sync via mite's changes feed. Not available to time trackers.
 */
final class ChangesApi
{
    private const LINK_HEADER = 'X-Changes-Link';

    public function __construct(private readonly MiteClient $client)
    {
    }

    /**
     * Returns a cursor for changes made from now on.
     */
    public function start(ChangesResource $resource): ChangesCursor
    {
        $link = $this->client->get(\sprintf('/%s.json', $resource->value), ['limit' => '1'])->header(self::LINK_HEADER);
        if (null === $link) {
            throw new InvalidResponseException(\sprintf('mite did not send an %s header; the user role may not allow reading changes.', self::LINK_HEADER));
        }

        return ChangesCursor::fromLink($link);
    }

    public function fetch(ChangesCursor $cursor): ChangeSet
    {
        $response = $this->client->get($cursor->path);
        $next = $response->header(self::LINK_HEADER);

        return new ChangeSet(
            array_map(Change::fromArray(...), Data::unwrapList($response->data, 'change')),
            null === $next ? null : ChangesCursor::fromLink($next),
        );
    }
}
