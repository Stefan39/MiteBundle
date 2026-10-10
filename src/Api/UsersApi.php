<?php

declare(strict_types=1);

namespace Stejac\MiteBundle\Api;

use Stejac\MiteBundle\Dto\Filter\UserFilter;
use Stejac\MiteBundle\Dto\User;
use Stejac\MiteBundle\Http\MiteClient;
use Stejac\MiteBundle\Http\MiteResponse;
use Stejac\MiteBundle\Internal\Data;

/**
 * Read-only: mite does not allow creating or editing users through the API. Not available to time trackers.
 */
final class UsersApi
{
    public function __construct(private readonly MiteClient $client)
    {
    }

    /**
     * @return list<User>
     */
    public function list(?UserFilter $filter = null): array
    {
        return $this->map($this->client->get('/users.json', ($filter ?? new UserFilter())->toQuery()));
    }

    /**
     * @return list<User>
     */
    public function listArchived(?UserFilter $filter = null): array
    {
        return $this->map($this->client->get('/users/archived.json', ($filter ?? new UserFilter())->toQuery()));
    }

    public function get(int $id): User
    {
        return User::fromArray(Data::unwrap($this->client->get(\sprintf('/users/%d.json', $id))->data, 'user'));
    }

    /**
     * @return list<User>
     */
    private function map(MiteResponse $response): array
    {
        return array_map(User::fromArray(...), Data::unwrapList($response->data, 'user'));
    }
}
