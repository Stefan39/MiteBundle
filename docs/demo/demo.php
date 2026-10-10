<?php

declare(strict_types=1);

// Demo with invented data: runs offline against a mocked mite API.
require __DIR__.'/../../vendor/autoload.php';

use Stejac\MiteBundle\Api\TimeEntriesApi;
use Stejac\MiteBundle\Dto\Filter\TimeEntryFilter;
use Stejac\MiteBundle\Enum\Period;
use Stejac\MiteBundle\Http\MiteClient;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

$entry = static fn (int $id, int $minutes, string $project, string $note): array => ['time_entry' => [
    'id' => $id, 'minutes' => $minutes, 'date_at' => '2026-10-09', 'note' => $note, 'billable' => true, 'locked' => false,
    'revenue' => $minutes / 60 * 9000, 'hourly_rate' => 9000, 'user_id' => 1, 'user_name' => 'Jane Doe',
    'project_id' => 1, 'project_name' => $project, 'customer_id' => 1, 'customer_name' => 'Acme Inc.',
    'service_id' => 1, 'service_name' => 'Development',
    'created_at' => '2026-10-09T09:00:00+02:00', 'updated_at' => '2026-10-09T09:00:00+02:00',
]];

$http = new MockHttpClient(new MockResponse(json_encode([
    $entry(1, 90, 'Website relaunch', 'Checkout flow'),
    $entry(2, 45, 'Website relaunch', 'Code review'),
    $entry(3, 120, 'Mobile app', 'API integration'),
], \JSON_THROW_ON_ERROR)));

$timeEntries = new TimeEntriesApi(new MiteClient('acme', 'demo-key', 'demo', $http));

$total = 0;
foreach ($timeEntries->list(new TimeEntryFilter(currentUser: true, at: Period::Today)) as $e) {
    printf("%-18s %-16s %5s h  %s\n", $e->projectName, $e->serviceName, number_format($e->minutes / 60, 2), $e->note);
    $total += $e->minutes;
}
printf("%'-60s\nToday: %s h\n", '', number_format($total / 60, 2));
