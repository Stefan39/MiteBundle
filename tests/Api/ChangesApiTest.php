<?php

declare(strict_types=1);

namespace Stejac\MiteBundle\Tests\Api;

use Stejac\MiteBundle\Api\ChangesApi;
use Stejac\MiteBundle\Dto\ChangesCursor;
use Stejac\MiteBundle\Dto\Project;
use Stejac\MiteBundle\Enum\ChangeAction;
use Stejac\MiteBundle\Enum\ChangesResource;
use Stejac\MiteBundle\Exception\InvalidResponseException;
use Stejac\MiteBundle\Tests\ApiTestCase;
use Stejac\MiteBundle\Tests\Fixtures;

final class ChangesApiTest extends ApiTestCase
{
    public function testStartReadsCursorFromHeader(): void
    {
        $cursor = (new ChangesApi($this->client(self::json([], 200, ['X-Changes-Link' => 'https://acme.mite.de/projects/changes/abc123.json']))))
            ->start(ChangesResource::Projects);

        self::assertSame('https://acme.mite.de/projects.json?limit=1', $this->requestUrl());
        self::assertSame('/projects/changes/abc123.json', $cursor->path);
    }

    public function testStartWithoutHeaderRaises(): void
    {
        $this->expectException(InvalidResponseException::class);

        (new ChangesApi($this->client(self::json([]))))->start(ChangesResource::TimeEntries);
    }

    public function testFetchMapsChangesAndNextCursor(): void
    {
        $set = (new ChangesApi($this->client(self::json([
            ['change' => ['record_type' => 'project', 'action' => 'updated', 'record_id' => 11, 'record' => ['project' => Fixtures::project()]]],
            ['change' => ['record_type' => 'project', 'action' => 'deleted', 'record_id' => 12, 'record' => null]],
            ['change' => ['record_type' => 'invoice', 'action' => 'created', 'record_id' => 5, 'record' => ['invoice' => ['id' => 5]]]],
        ], 200, ['X-Changes-Link' => 'https://acme.mite.de/projects/changes/def456.json']))))
            ->fetch(ChangesCursor::fromLink('/projects/changes/abc123.json'));

        self::assertSame('https://acme.mite.de/projects/changes/abc123.json', $this->requestUrl());
        self::assertCount(3, $set->changes);
        self::assertSame(ChangeAction::Updated, $set->changes[0]->action);
        self::assertInstanceOf(Project::class, $set->changes[0]->record);
        self::assertNull($set->changes[1]->record);
        self::assertSame('invoice', $set->changes[2]->recordType);
        self::assertNull($set->changes[2]->record);
        self::assertSame('/projects/changes/def456.json', $set->next?->path);
    }

    public function testForeignHostInLinkIsIgnored(): void
    {
        $cursor = ChangesCursor::fromLink('https://evil.example.com/projects/changes/abc123.json');
        (new ChangesApi($this->client(self::json([]))))->fetch($cursor);

        self::assertSame('https://acme.mite.de/projects/changes/abc123.json', $this->requestUrl());
    }

    public function testUnexpectedLinkIsRejected(): void
    {
        $this->expectException(InvalidResponseException::class);

        ChangesCursor::fromLink('https://acme.mite.de/account.json');
    }

    public function testUnknownActionRaisesInvalidResponse(): void
    {
        $this->expectException(InvalidResponseException::class);

        (new ChangesApi($this->client(self::json([['change' => ['record_type' => 'project', 'action' => 'merged', 'record_id' => 1, 'record' => null]]]))))
            ->fetch(ChangesCursor::fromLink('/projects/changes/abc123.json'));
    }
}
