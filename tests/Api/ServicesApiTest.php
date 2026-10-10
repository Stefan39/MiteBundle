<?php

declare(strict_types=1);

namespace Stejac\MiteBundle\Tests\Api;

use Stejac\MiteBundle\Api\ServicesApi;
use Stejac\MiteBundle\Dto\Filter\NameFilter;
use Stejac\MiteBundle\Dto\Input\NewService;
use Stejac\MiteBundle\Dto\Input\ServiceUpdate;
use Stejac\MiteBundle\Exception\InvalidResponseException;
use Stejac\MiteBundle\Exception\ValidationException;
use Stejac\MiteBundle\Tests\ApiTestCase;
use Stejac\MiteBundle\Tests\Fixtures;

final class ServicesApiTest extends ApiTestCase
{
    public function testList(): void
    {
        $services = (new ServicesApi($this->client(self::json([['service' => Fixtures::service()]]))))->list(new NameFilter('dev'));

        self::assertSame('https://acme.mite.de/services.json?name=dev', $this->requestUrl());
        self::assertSame(9000, $services[0]->hourlyRate);
        self::assertTrue($services[0]->billable);
    }

    public function testListArchived(): void
    {
        (new ServicesApi($this->client(self::json([]))))->listArchived();

        self::assertSame('https://acme.mite.de/services/archived.json', $this->requestUrl());
    }

    public function testGetWithNullRate(): void
    {
        $service = (new ServicesApi($this->client(self::json(['service' => array_merge(Fixtures::service(), ['hourly_rate' => null])]))))->get(21);

        self::assertSame('https://acme.mite.de/services/21.json', $this->requestUrl());
        self::assertNull($service->hourlyRate);
    }

    public function testCreateSendsOnlyGivenFields(): void
    {
        $service = (new ServicesApi($this->client(self::json(['service' => Fixtures::service()], 201))))
            ->create(new NewService('Development', hourlyRate: 9000));

        self::assertSame('POST', $this->requestMethod());
        self::assertSame('https://acme.mite.de/services.json', $this->requestUrl());
        self::assertSame(['service' => ['name' => 'Development', 'hourly_rate' => 9000]], $this->requestBody());
        self::assertSame(21, $service->id);
    }

    public function testUpdateWithEmptyResponseBody(): void
    {
        (new ServicesApi($this->client(self::emptyResponse())))
            ->update(21, ServiceUpdate::new()->withHourlyRate(null)->withArchived(true)->withUpdateHourlyRateOnTimeEntries(true));

        self::assertSame('PATCH', $this->requestMethod());
        self::assertSame('https://acme.mite.de/services/21.json', $this->requestUrl());
        self::assertSame(['service' => ['hourly_rate' => null, 'archived' => true, 'update_hourly_rate_on_time_entries' => true]], $this->requestBody());
    }

    public function testDelete(): void
    {
        (new ServicesApi($this->client(self::emptyResponse())))->delete(21);

        self::assertSame('DELETE', $this->requestMethod());
        self::assertSame('https://acme.mite.de/services/21.json', $this->requestUrl());
    }

    public function testDeleteWithTimeEntriesRaisesValidation(): void
    {
        $this->expectException(ValidationException::class);

        (new ServicesApi($this->client(self::json(['error' => 'This service cannot be deleted: There are associated time entries. Please archive instead.'], 422))))->delete(21);
    }

    public function testCreateWithEmptyBodyRaisesInvalidResponse(): void
    {
        $this->expectException(InvalidResponseException::class);

        (new ServicesApi($this->client(self::emptyResponse(201))))->create(new NewService('Development'));
    }
}
