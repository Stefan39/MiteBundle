<?php

declare(strict_types=1);

namespace Stejac\MiteBundle\Api;

use Stejac\MiteBundle\Dto\Filter\NameFilter;
use Stejac\MiteBundle\Dto\Input\NewService;
use Stejac\MiteBundle\Dto\Input\ServiceUpdate;
use Stejac\MiteBundle\Dto\Service;
use Stejac\MiteBundle\Http\MiteClient;
use Stejac\MiteBundle\Http\MiteResponse;
use Stejac\MiteBundle\Internal\Data;

final class ServicesApi
{
    public function __construct(private readonly MiteClient $client)
    {
    }

    /**
     * @return list<Service>
     */
    public function list(?NameFilter $filter = null): array
    {
        return $this->map($this->client->get('/services.json', ($filter ?? new NameFilter())->toQuery()));
    }

    /**
     * @return list<Service>
     */
    public function listArchived(?NameFilter $filter = null): array
    {
        return $this->map($this->client->get('/services/archived.json', ($filter ?? new NameFilter())->toQuery()));
    }

    public function get(int $id): Service
    {
        return Service::fromArray(Data::unwrap($this->client->get(\sprintf('/services/%d.json', $id))->data, 'service'));
    }

    public function create(NewService $service): Service
    {
        return Service::fromArray(Data::unwrap($this->client->post('/services.json', ['service' => $service->toArray()])->data, 'service'));
    }

    public function update(int $id, ServiceUpdate $update): void
    {
        $this->client->patch(\sprintf('/services/%d.json', $id), ['service' => $update->toArray()]);
    }

    /**
     * Fails with a ValidationException if the service has time entries; archive it instead.
     */
    public function delete(int $id): void
    {
        $this->client->delete(\sprintf('/services/%d.json', $id));
    }

    /**
     * @return list<Service>
     */
    private function map(MiteResponse $response): array
    {
        return array_map(Service::fromArray(...), Data::unwrapList($response->data, 'service'));
    }
}
