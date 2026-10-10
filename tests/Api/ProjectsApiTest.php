<?php

declare(strict_types=1);

namespace Stejac\MiteBundle\Tests\Api;

use Stejac\MiteBundle\Api\ProjectsApi;
use Stejac\MiteBundle\Dto\Filter\ProjectFilter;
use Stejac\MiteBundle\Dto\Input\NewProject;
use Stejac\MiteBundle\Dto\Input\ProjectUpdate;
use Stejac\MiteBundle\Enum\BudgetType;
use Stejac\MiteBundle\Exception\InvalidArgumentException;
use Stejac\MiteBundle\Tests\ApiTestCase;
use Stejac\MiteBundle\Tests\Fixtures;

final class ProjectsApiTest extends ApiTestCase
{
    public function testListWithCustomerFilter(): void
    {
        $projects = (new ProjectsApi($this->client(self::json([['project' => Fixtures::project()]]))))
            ->list(new ProjectFilter(name: 'web', customerIds: [31, 32], limit: 20, page: 2));

        self::assertSame('https://acme.mite.de/projects.json?name=web&customer_id=31,32&limit=20&page=2', $this->requestUrl());
        self::assertSame(BudgetType::Minutes, $projects[0]->budgetType);
        self::assertSame('Example Corp', $projects[0]->customerName);
    }

    public function testGetWithoutCustomer(): void
    {
        $project = (new ProjectsApi($this->client(self::json(['project' => array_merge(Fixtures::project(), ['customer_id' => null, 'hourly_rate' => null])]))))->get(11);

        self::assertSame('https://acme.mite.de/projects/11.json', $this->requestUrl());
        self::assertNull($project->customerId);
        self::assertNull($project->hourlyRate);
    }

    public function testListArchivedWithoutCustomerName(): void
    {
        $fixture = Fixtures::project();
        unset($fixture['customer_name']);
        $projects = (new ProjectsApi($this->client(self::json([['project' => $fixture]]))))->listArchived();

        self::assertSame('https://acme.mite.de/projects/archived.json', $this->requestUrl());
        self::assertNull($projects[0]->customerName);
    }

    public function testCreate(): void
    {
        (new ProjectsApi($this->client(self::json(['project' => Fixtures::project()], 201))))
            ->create(new NewProject('Website', customerId: 31, budget: 45000, budgetType: BudgetType::CentsPerMonth));

        self::assertSame(['project' => ['name' => 'Website', 'customer_id' => 31, 'budget' => 45000, 'budget_type' => 'cents_per_month']], $this->requestBody());
    }

    public function testUpdateAndDelete(): void
    {
        $api = new ProjectsApi($this->client(self::emptyResponse(), self::emptyResponse()));

        $api->update(11, ProjectUpdate::new()->withCustomerId(null)->withBudgetType(BudgetType::Cents)->withArchived(true));
        $api->delete(11);

        self::assertSame(['project' => ['customer_id' => null, 'budget_type' => 'cents', 'archived' => true]], $this->requestBody(0));
        self::assertSame('DELETE', $this->requestMethod(1));
        self::assertSame('https://acme.mite.de/projects/11.json', $this->requestUrl(1));
    }

    public function testInvalidPaginationIsRejected(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new ProjectFilter(limit: 0);
    }
}
