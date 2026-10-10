<?php

declare(strict_types=1);

namespace Stejac\MiteBundle\Tests\Api;

use Stejac\MiteBundle\Api\CustomersApi;
use Stejac\MiteBundle\Dto\Filter\NameFilter;
use Stejac\MiteBundle\Dto\HourlyRatePerService;
use Stejac\MiteBundle\Dto\Input\CustomerUpdate;
use Stejac\MiteBundle\Dto\Input\NewCustomer;
use Stejac\MiteBundle\Enum\ActiveHourlyRate;
use Stejac\MiteBundle\Exception\InvalidResponseException;
use Stejac\MiteBundle\Tests\ApiTestCase;
use Stejac\MiteBundle\Tests\Fixtures;

final class CustomersApiTest extends ApiTestCase
{
    public function testList(): void
    {
        $customers = (new CustomersApi($this->client(self::json([['customer' => Fixtures::customer()]]))))->list(new NameFilter('example', 10));

        self::assertSame('https://acme.mite.de/customers.json?name=example&limit=10', $this->requestUrl());
        self::assertSame(ActiveHourlyRate::HourlyRatesPerService, $customers[0]->activeHourlyRate);
        self::assertNull($customers[0]->hourlyRate);
        self::assertSame(9500, $customers[0]->hourlyRatesPerService[0]->hourlyRate);
    }

    public function testListArchivedAndGet(): void
    {
        $api = new CustomersApi($this->client(self::json([]), self::json(['customer' => array_merge(Fixtures::customer(), ['active_hourly_rate' => null, 'hourly_rates_per_service' => []])])));

        $api->listArchived();
        $customer = $api->get(31);

        self::assertSame('https://acme.mite.de/customers/archived.json', $this->requestUrl(0));
        self::assertSame('https://acme.mite.de/customers/31.json', $this->requestUrl(1));
        self::assertNull($customer->activeHourlyRate);
        self::assertSame([], $customer->hourlyRatesPerService);
    }

    public function testCreate(): void
    {
        (new CustomersApi($this->client(self::json(['customer' => Fixtures::customer()], 201))))->create(new NewCustomer(
            'Example Corp',
            activeHourlyRate: ActiveHourlyRate::HourlyRatesPerService,
            hourlyRatesPerService: [new HourlyRatePerService(21, 9500)],
        ));

        self::assertSame(['customer' => [
            'name' => 'Example Corp',
            'active_hourly_rate' => 'hourly_rates_per_service',
            'hourly_rates_per_service' => [['service_id' => 21, 'hourly_rate' => 9500]],
        ]], $this->requestBody());
    }

    public function testUpdate(): void
    {
        (new CustomersApi($this->client(self::emptyResponse())))->update(31, CustomerUpdate::new()
            ->withActiveHourlyRate(ActiveHourlyRate::HourlyRate)
            ->withHourlyRate(8000)
            ->withHourlyRatesPerService([])
            ->withUpdateHourlyRateOnTimeEntries());

        self::assertSame('PATCH', $this->requestMethod());
        self::assertSame(['customer' => [
            'active_hourly_rate' => 'hourly_rate',
            'hourly_rate' => 8000,
            'hourly_rates_per_service' => [],
            'update_hourly_rate_on_time_entries' => true,
        ]], $this->requestBody());
    }

    public function testDelete(): void
    {
        (new CustomersApi($this->client(self::emptyResponse())))->delete(31);

        self::assertSame('DELETE', $this->requestMethod());
        self::assertSame('https://acme.mite.de/customers/31.json', $this->requestUrl());
    }

    public function testUnknownActiveHourlyRateRaisesInvalidResponse(): void
    {
        $this->expectException(InvalidResponseException::class);
        $this->expectExceptionMessage('"active_hourly_rate"');

        (new CustomersApi($this->client(self::json(['customer' => array_merge(Fixtures::customer(), ['active_hourly_rate' => 'per_minute'])]))))->get(31);
    }
}
