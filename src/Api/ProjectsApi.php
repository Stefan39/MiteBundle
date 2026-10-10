<?php

declare(strict_types=1);

namespace Stejac\MiteBundle\Api;

use Stejac\MiteBundle\Dto\Filter\ProjectFilter;
use Stejac\MiteBundle\Dto\Input\NewProject;
use Stejac\MiteBundle\Dto\Input\ProjectUpdate;
use Stejac\MiteBundle\Dto\Project;
use Stejac\MiteBundle\Http\MiteClient;
use Stejac\MiteBundle\Http\MiteResponse;
use Stejac\MiteBundle\Internal\Data;

final class ProjectsApi
{
    public function __construct(private readonly MiteClient $client)
    {
    }

    /**
     * @return list<Project>
     */
    public function list(?ProjectFilter $filter = null): array
    {
        return $this->map($this->client->get('/projects.json', ($filter ?? new ProjectFilter())->toQuery()));
    }

    /**
     * @return list<Project>
     */
    public function listArchived(?ProjectFilter $filter = null): array
    {
        return $this->map($this->client->get('/projects/archived.json', ($filter ?? new ProjectFilter())->toQuery()));
    }

    public function get(int $id): Project
    {
        return Project::fromArray(Data::unwrap($this->client->get(\sprintf('/projects/%d.json', $id))->data, 'project'));
    }

    public function create(NewProject $project): Project
    {
        return Project::fromArray(Data::unwrap($this->client->post('/projects.json', ['project' => $project->toArray()])->data, 'project'));
    }

    public function update(int $id, ProjectUpdate $update): void
    {
        $this->client->patch(\sprintf('/projects/%d.json', $id), ['project' => $update->toArray()]);
    }

    /**
     * Fails with a ValidationException if the project has time entries; archive it instead.
     */
    public function delete(int $id): void
    {
        $this->client->delete(\sprintf('/projects/%d.json', $id));
    }

    /**
     * @return list<Project>
     */
    private function map(MiteResponse $response): array
    {
        return array_map(Project::fromArray(...), Data::unwrapList($response->data, 'project'));
    }
}
