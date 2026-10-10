<?php

declare(strict_types=1);

namespace Stejac\MiteBundle\Api;

use Stejac\MiteBundle\Dto\Account;
use Stejac\MiteBundle\Dto\User;
use Stejac\MiteBundle\Http\MiteClient;
use Stejac\MiteBundle\Internal\Data;

final class AccountApi
{
    public function __construct(private readonly MiteClient $client)
    {
    }

    public function get(): Account
    {
        return Account::fromArray(Data::unwrap($this->client->get('/account.json')->data, 'account'));
    }

    /**
     * The user the API key belongs to.
     */
    public function myself(): User
    {
        return User::fromArray(Data::unwrap($this->client->get('/myself.json')->data, 'user'));
    }
}
