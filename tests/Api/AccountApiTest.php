<?php

declare(strict_types=1);

namespace Stejac\MiteBundle\Tests\Api;

use Stejac\MiteBundle\Api\AccountApi;
use Stejac\MiteBundle\Tests\ApiTestCase;
use Stejac\MiteBundle\Tests\Fixtures;

final class AccountApiTest extends ApiTestCase
{
    public function testGet(): void
    {
        $account = (new AccountApi($this->client(self::json(['account' => Fixtures::account()]))))->get();

        self::assertSame('https://acme.mite.de/account.json', $this->requestUrl());
        self::assertSame(1, $account->id);
        self::assertSame('Acme Inc.', $account->title);
        self::assertSame('EUR', $account->currency);
        self::assertSame(Fixtures::CREATED_AT, $account->createdAt->format(\DATE_ATOM));
    }

    public function testMyself(): void
    {
        $user = (new AccountApi($this->client(self::json(['user' => Fixtures::user()]))))->myself();

        self::assertSame('https://acme.mite.de/myself.json', $this->requestUrl());
        self::assertSame('jane@example.com', $user->email);
        self::assertSame('admin', $user->role);
    }
}
