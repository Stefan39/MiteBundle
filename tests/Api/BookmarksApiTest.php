<?php

declare(strict_types=1);

namespace Stejac\MiteBundle\Tests\Api;

use Stejac\MiteBundle\Api\BookmarksApi;
use Stejac\MiteBundle\Exception\InvalidResponseException;
use Stejac\MiteBundle\Tests\ApiTestCase;
use Stejac\MiteBundle\Tests\Fixtures;
use Symfony\Component\HttpClient\Response\MockResponse;

final class BookmarksApiTest extends ApiTestCase
{
    public function testList(): void
    {
        $bookmarks = (new BookmarksApi($this->client(self::json([['bookmark' => Fixtures::bookmark()]]))))->list();

        self::assertSame('https://acme.mite.de/time_entries/bookmarks.json', $this->requestUrl());
        self::assertSame('Website this week', $bookmarks[0]->name);
        self::assertNull($bookmarks[0]->userId);
        self::assertSame(1, $bookmarks[0]->accountId);
    }

    public function testGet(): void
    {
        $bookmark = (new BookmarksApi($this->client(self::json(['bookmark' => array_merge(Fixtures::bookmark(), ['user_id' => 7])]))))->get(3);

        self::assertSame('https://acme.mite.de/time_entries/bookmarks/3.json', $this->requestUrl());
        self::assertSame(7, $bookmark->userId);
    }

    public function testFollowReturnsQueryOfRedirect(): void
    {
        $api = new BookmarksApi($this->client(new MockResponse('', [
            'http_code' => 302,
            'response_headers' => ['Location' => 'https://acme.mite.de/time_entries.json?project_id=11&at=this_week'],
        ])));

        self::assertSame('project_id=11&at=this_week', $api->follow(3));
        self::assertSame('https://acme.mite.de/time_entries/bookmarks/3/follow.json', $this->requestUrl());
    }

    public function testFollowWithoutRedirectRaises(): void
    {
        $this->expectException(InvalidResponseException::class);

        (new BookmarksApi($this->client(self::json([]))))->follow(3);
    }
}
