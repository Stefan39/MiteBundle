# Changelog

All notable changes to this project are documented here.
The format follows [Keep a Changelog](https://keepachangelog.com/en/1.1.0/), versions follow [Semantic Versioning](https://semver.org/).

## [Unreleased]

## [1.0.0] - 2026-10-10

### Added
- Symfony bundle with `domain`, `api_key`, `user_agent` and `http_client` configuration.
- Typed services for every mite resource: account, time entries (incl. grouped reports and daily aliases), tracker, bookmarks, customers, projects, services, users and the changes feed.
- Read-only DTOs, enums and input DTOs with partial-update semantics.
- Exception per mite error status, all implementing `MiteExceptionInterface`.

### Planned
- ETag / `If-None-Match` caching.

[Unreleased]: https://github.com/Stefan39/MiteBundle/compare/v1.0.0...HEAD
[1.0.0]: https://github.com/Stefan39/MiteBundle/releases/tag/v1.0.0
