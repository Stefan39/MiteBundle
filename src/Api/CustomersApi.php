<?php

declare(strict_types=1);

namespace Stejac\MiteBundle\Api;

use Stejac\MiteBundle\Dto\Customer;
use Stejac\MiteBundle\Dto\Filter\NameFilter;
use Stejac\MiteBundle\Dto\Input\CustomerUpdate;
use Stejac\MiteBundle\Dto\Input\NewCustomer;
use Stejac\MiteBundle\Http\MiteClient;
use Stejac\MiteBundle\Http\MiteResponse;
use Stejac\MiteBundle\Internal\Data;

final class CustomersApi
{
    public function __construct(private readonly MiteClient $client)
    {
    }

    /**
     * @return list<Customer>
     */
    public function list(?NameFilter $filter = null): array
    {
        return $this->map($this->client->get('/customers.json', ($filter ?? new NameFilter())->toQuery()));
    }

    /**
     * @return list<Customer>
     */
    public function listArchived(?NameFilter $filter = null): array
    {
        return $this->map($this->client->get('/customers/archived.json', ($filter ?? new NameFilter())->toQuery()));
    }

    public function get(int $id): Customer
    {
        return Customer::fromArray(Data::unwrap($this->client->get(\sprintf('/customers/%d.json', $id))->data, 'customer'));
    }

    public function create(NewCustomer $customer): Customer
    {
        return Customer::fromArray(Data::unwrap($this->client->post('/customers.json', ['customer' => $customer->toArray()])->data, 'customer'));
    }

    public function update(int $id, CustomerUpdate $update): void
    {
        $this->client->patch(\sprintf('/customers/%d.json', $id), ['customer' => $update->toArray()]);
    }

    /**
     * Fails with a ValidationException if the customer has projects; archive it instead.
     */
    public function delete(int $id): void
    {
        $this->client->delete(\sprintf('/customers/%d.json', $id));
    }

    /**
     * @return list<Customer>
     */
    private function map(MiteResponse $response): array
    {
        return array_map(Customer::fromArray(...), Data::unwrapList($response->data, 'customer'));
    }
}
