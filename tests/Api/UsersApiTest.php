<?php

declare(strict_types=1);

namespace Stejac\MiteBundle\Tests\Api;

use Stejac\MiteBundle\Api\UsersApi;
use Stejac\MiteBundle\Dto\Filter\UserFilter;
use Stejac\MiteBundle\Tests\ApiTestCase;
use Stejac\MiteBundle\Tests\Fixtures;

final class UsersApiTest extends ApiTestCase
{
    public function testListWithFilter(): void
    {
        $users = (new UsersApi($this->client(self::json([['user' => Fixtures::user()]]))))
            ->list(new UserFilter(name: 'jane', email: 'example', limit: 5, page: 1));

        self::assertSame('https://acme.mite.de/users.json?name=jane&email=example&limit=5&page=1', $this->requestUrl());
        self::assertCount(1, $users);
        self::assertSame('Jane Doe', $users[0]->name);
    }

    public function testListArchived(): void
    {
        (new UsersApi($this->client(self::json([]))))->listArchived();

        self::assertSame('https://acme.mite.de/users/archived.json', $this->requestUrl());
    }

    public function testGet(): void
    {
        $user = (new UsersApi($this->client(self::json(['user' => array_merge(Fixtures::user(), ['note' => null, 'role' => 'time_tracker'])]))))->get(7);

        self::assertSame('https://acme.mite.de/users/7.json', $this->requestUrl());
        self::assertSame('', $user->note);
        self::assertSame('time_tracker', $user->role);
    }
}
