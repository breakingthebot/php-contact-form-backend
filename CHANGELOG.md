# CHANGELOG.md
# Tracks project iterations and shipped changes.
# Connects to: README.md
# Created: 2026-06-28

# Changelog

## [0.1.0] - 2026-06-28
- Create the first iteration of a modular PHP contact form backend.
- Add request validation, persistence abstraction, mail abstraction, and structured logging.
- Add PHPUnit test scaffolding for validation and request handling behavior.
- Add GitHub Actions CI to run Composer install and PHPUnit on push and pull request.
- Add setup documentation, MIT license, and local environment template.

## [0.1.1] - 2026-06-28
- Fix Linux CI autoload failures by making Composer and the local fallback autoloader compatible with the repository's lowercase source folders.
- Fix test bootstrap and container autoload references after hardening the autoload layer.

## [0.2.0] - 2026-06-28
- Add configurable SMTP delivery through Symfony Mailer with DSN-based runtime configuration.
- Add a mail transport factory and message factory so email generation and transport selection stay modular.
- Keep PHP's native mail transport as a fallback when no SMTP DSN is configured.
- Add transport and message tests for the new mail delivery layer.

## [0.3.0] - 2026-06-28
- Add request abuse protection with origin checks, honeypot validation, and IP-based rate limiting.
- Add a dedicated request guard service so abuse controls stay at the HTTP boundary.
- Add tests for origin validation, honeypot validation, and file-backed rate limiting behavior.

## [0.4.0] - 2026-06-28
- Add a dedicated health-check endpoint for database and mail transport readiness.
- Add modular health checkers plus an aggregate health service for operational diagnostics.
- Add tests for database, mail, and aggregate health check behavior.

## [0.5.0] - 2026-06-28
- Replace the one-off schema file with versioned SQL migrations.
- Add a native migration CLI for checking status and applying pending migrations.
- Add tests for migration discovery and migration planning behavior.

## [0.6.0] - 2026-06-28
- Add an OpenAPI 3.1 specification for the public contact and health endpoints.
- Add example request and response payloads for API consumers.
- Add a lightweight contract test to keep the OpenAPI document aligned with the current routes.

## [0.7.0] - 2026-06-28
- Add endpoint-level integration tests for the public contact and health entrypoints.
- Add a request input reader abstraction and bootstrap container override seam to support entrypoint testing.
- Keep runtime behavior unchanged while making public HTTP flows testable in PHPUnit.
