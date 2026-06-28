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
