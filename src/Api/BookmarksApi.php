<?php

declare(strict_types=1);

namespace Stejac\MiteBundle\Api;

use Stejac\MiteBundle\Dto\Bookmark;
use Stejac\MiteBundle\Exception\InvalidResponseException;
use Stejac\MiteBundle\Http\MiteClient;
use Stejac\MiteBundle\Internal\Data;

/**
 * Read-only: mite does not allow creating or editing bookmarks through the API.
 */
final class BookmarksApi
{
    public function __construct(private readonly MiteClient $client)
    {
    }

    /**
     * @return list<Bookmark>
     */
    public function list(): array
    {
        return array_map(Bookmark::fromArray(...), Data::unwrapList($this->client->get('/time_entries/bookmarks.json')->data, 'bookmark'));
    }

    public function get(int $id): Bookmark
    {
        return Bookmark::fromArray(Data::unwrap($this->client->get(\sprintf('/time_entries/bookmarks/%d.json', $id))->data, 'bookmark'));
    }

    /**
     * Returns the time-entries query string mite redirects to for this bookmark.
     */
    public function follow(int $id): string
    {
        $response = $this->client->getWithoutRedirect(\sprintf('/time_entries/bookmarks/%d/follow.json', $id));
        $location = $response->header('location');
        if (null === $location || $response->status < 300 || $response->status >= 400) {
            throw new InvalidResponseException(\sprintf('Expected a redirect for bookmark %d, got HTTP %d.', $id, $response->status));
        }

        return parse_url($location, \PHP_URL_QUERY) ?: '';
    }
}
