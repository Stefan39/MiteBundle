# MiteBundle

> A Symfony bundle for the mite time tracking API: typed services and DTOs for every resource, configured with two env vars.

[![License: MIT](https://img.shields.io/badge/License-MIT-blue.svg)](LICENSE)
[![PHPStan](https://img.shields.io/badge/PHPStan-level%208-brightgreen)](phpstan.neon)

[Deutsche Version](README.de.md)

<p align="center">
  <img src="docs/demo/demo.gif" alt="Listing today's time entries with TimeEntriesApi (demo data)" width="720">
</p>

## Why this one?

There are framework-agnostic PHP clients for mite, such as `gamez/mite`. This bundle is built for Symfony instead: one config file, autowired services, Symfony's HttpClient, and read-only DTOs for every resource the mite API offers.

- Every mite resource: account, time entries (incl. grouped reports), tracker, bookmarks, customers, projects, services, users, changes feed.
- Read-only DTOs and enums instead of arrays; PHPStan level 8.
- One exception per mite error (401, 403, 404, 422, 423, 5xx), all catchable as `MiteExceptionInterface`.

## Quickstart

```bash
composer require stejac/mite-bundle
```

```php
use Stejac\MiteBundle\Api\TimeEntriesApi;
use Stejac\MiteBundle\Dto\Filter\TimeEntryFilter;
use Stejac\MiteBundle\Enum\Period;

final class WeeklyReport
{
    public function __construct(private TimeEntriesApi $timeEntries) {}

    public function minutesThisWeek(): int
    {
        $entries = $this->timeEntries->list(new TimeEntryFilter(currentUser: true, at: Period::ThisWeek));

        return array_sum(array_map(static fn ($e) => $e->minutes, $entries));
    }
}
```

## Configuration

Register the bundle (Symfony Flex does this for you) and add `config/packages/mite.yaml`:

```yaml
mite:
    domain: '%env(MITE_DOMAIN)%'
    api_key: '%env(MITE_API_KEY)%'
```

| Key | Default | Description |
| --- | --- | --- |
| `domain` | — (required) | Account sub-domain: `acme` for `acme.mite.de`. Only the bare name is accepted. |
| `api_key` | — (required) | API key from your mite user settings ("Allow API access"). Keep it in an env var. |
| `user_agent` | `stejac/mite-bundle` | Sent as `User-Agent`; mite asks for your app name and a contact. |
| `http_client` | `null` | Service id of your own `HttpClientInterface` (proxy, retries, tracing). Defaults to the framework's `http_client`. |

## Features

| Service | Methods | Status |
| --- | --- | --- |
| `AccountApi` | `get`, `myself` | stable |
| `TimeEntriesApi` | `list`, `grouped`, `today`, `forDay`, `get`, `create`, `update`, `delete` | stable |
| `TrackerApi` | `show`, `start`, `stop` | stable |
| `BookmarksApi` | `list`, `get`, `follow` | stable |
| `CustomersApi`, `ProjectsApi`, `ServicesApi` | `list`, `listArchived`, `get`, `create`, `update`, `delete` | stable |
| `UsersApi` | `list`, `listArchived`, `get` | stable |
| `ChangesApi` | `start`, `fetch` | stable |
| ETag / `If-None-Match` caching | — | planned |

### Writing data

```php
use Stejac\MiteBundle\Dto\Input\NewTimeEntry;
use Stejac\MiteBundle\Dto\Input\TimeEntryUpdate;

$entry = $timeEntries->create(new NewTimeEntry(minutes: 90, note: 'Code review', projectId: 11, serviceId: 21));
$timeEntries->update($entry->id, TimeEntryUpdate::new()->withMinutes(120));
```

Update DTOs only send what you set, so `withHourlyRate(null)` clears a rate while untouched fields stay as they are.

### Errors

```php
use Stejac\MiteBundle\Exception\LockedException;
use Stejac\MiteBundle\Exception\MiteExceptionInterface;

try {
    $timeEntries->delete($id);
} catch (LockedException) {
    // entry is locked; admins can pass force: true
} catch (MiteExceptionInterface $e) {
    // any other mite or transport error
}
```

### Incremental sync

```php
use Stejac\MiteBundle\Dto\ChangesCursor;
use Stejac\MiteBundle\Enum\ChangesResource;

$cursor = $changes->start(ChangesResource::Projects);   // persist $cursor->path
// later
$set = $changes->fetch(ChangesCursor::fromLink($storedPath));
foreach ($set->changes as $change) { /* $change->action, $change->record */ }
$storedPath = $set->next?->path;
```

mite keeps changes for 7 days and may deliver duplicates.

## Security

Read [SECURITY.md](SECURITY.md) before reporting a vulnerability. The API key is only sent as a header to `https://{domain}.mite.de`; `domain` must be a bare sub-domain, and changes-feed links are reduced to their path, so the key never leaves your mite host.

## Status

- Unit tests and static analysis run in CI on PHP 8.3, 8.4 and 8.5 with Symfony 7.4 and 8.
- An opt-in live suite (`composer test-live`, credentials in `.env.local`) runs against a real mite account; it covers every read endpoint, the changes feed, and creating, updating, timing and deleting a time entry.

## Contributing

Issues and pull requests are welcome — see [CONTRIBUTING.md](CONTRIBUTING.md).

## About

Built and maintained by [Stefan Jacomeit](https://www.jacomeit.de) — freelance PHP/Symfony developer with 25+ years of experience, building AI integrations for businesses.

Unofficial. Not affiliated with or endorsed by mite GmbH. mite is a product of mite GmbH.

## License

[MIT](LICENSE)
