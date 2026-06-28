# PHP Contact Form Backend

Receives contact form submissions, validates them, stores them in MySQL, and dispatches an email notification through a modular PHP backend.

## Stack
- PHP 8.2+
- Composer for autoloading and test dependencies
- Symfony Mailer for SMTP delivery
- PHPUnit for automated tests
- GitHub Actions for CI
- MySQL via PDO

## Setup
1. Install PHP 8.2 or newer with the `pdo_mysql` extension enabled.
2. Install Composer.
3. Copy `.env.example` to `.env` and fill in the required values.
4. Install dependencies:

```bash
composer install
```

5. Run database migrations:

```bash
php bin/migrate.php migrate
```

## Environment Variables
The application expects the variables defined in `.env.example`.

- `APP_ENV`
- `APP_DEBUG`
- `APP_URL`
- `ALLOWED_ORIGINS`
- `CONTACT_TO_EMAIL`
- `CONTACT_FROM_EMAIL`
- `CONTACT_FROM_NAME`
- `CONTACT_SUBJECT_PREFIX`
- `HONEYPOT_FIELD_NAME`
- `MAILER_DSN`
- `RATE_LIMIT_MAX_ATTEMPTS`
- `RATE_LIMIT_WINDOW_SECONDS`
- `DB_HOST`
- `DB_PORT`
- `DB_NAME`
- `DB_USER`
- `DB_PASSWORD`
- `DB_CHARSET`
- `LOG_PATH`

## Running Locally
Use PHP's built-in server from the project root:

```bash
php -S localhost:8000 -t public
```

Check operational readiness:

```bash
curl http://localhost:8000/health.php
```

Check migration status:

```bash
php bin/migrate.php status
```

Inspect the API contract:

```bash
Get-Content docs/openapi.yaml
```

Send a POST request to `/contact.php` with JSON:

```bash
curl -X POST http://localhost:8000/contact.php \
  -H "Content-Type: application/json" \
  -H "Origin: http://localhost:3000" \
  -d "{\"name\":\"Ada Lovelace\",\"email\":\"ada@example.com\",\"message\":\"Hello from the form.\"}"
```

For local SMTP testing, point `MAILER_DSN` at a mail catcher such as Mailpit:

```bash
MAILER_DSN=smtp://127.0.0.1:1025
```

## Deployed
Not deployed in iteration 1.

## Architecture Notes
This first iteration builds the backend in layers so the HTTP endpoint stays thin and the business rules are easy to test. The request comes into a single public entrypoint, gets normalized into a request model, passes through a validator, and then flows into a service that coordinates storage, email delivery, and logging. Storage and mail both sit behind interfaces so the real infrastructure can change later without rewriting the core request handling.

The result is a project that is easy to extend incrementally. Iteration 1 focuses on the contract and control flow: validate hostile input, return clear API responses, log failures with context, and keep MySQL and email integrations isolated behind atomic classes. Later iterations can harden SMTP delivery, add richer persistence features, and wire in CI without changing the public API shape.

The second iteration hardens the project for real deployment environments by making the autoload layer explicit about the repository's source layout. That matters because Windows tolerates path-case mismatches that Linux CI and many production hosts do not.

The third iteration upgrades the delivery path from a placeholder `mail()` implementation to a real SMTP-capable transport layer. Instead of hardwiring SMTP logic into the service, the application now builds the email message separately and chooses the transport at runtime based on configuration. That keeps the core submission flow stable while making production mail delivery explicit and testable.

The fourth iteration hardens the public endpoint against obvious automated abuse. The controller now runs a dedicated request guard before it hands work to the submission service. That guard checks whether the request origin is allowed, rejects bots that fill a hidden honeypot field, and rate-limits repeated requests from the same IP within a configurable time window.

The fifth iteration adds a separate health endpoint for operational visibility. Instead of mixing readiness checks into the contact submission path, the application now exposes a lightweight diagnostics route that verifies database connectivity and mail transport configuration through dedicated checkers and returns a structured JSON status report.

The sixth iteration adds native schema versioning. Instead of maintaining a single raw schema file, the project now keeps ordered SQL migrations and applies them through a small CLI entrypoint. That makes database changes explicit, repeatable, and easier to review alongside the code that depends on them.

The seventh iteration adds a first-class API contract. The repository now includes an OpenAPI document and concrete JSON examples for the public endpoints, so frontend or integration consumers can rely on a versioned spec instead of reverse-engineering controller behavior from the PHP code.

The eighth iteration adds public entrypoint integration tests. Instead of stopping at service-level unit tests, the suite now exercises the actual PHP entry files with controlled request inputs and container overrides, which gives better confidence that the route wiring and JSON responses behave as expected.

The follow-up patch for that iteration fixes a scope edge case in the bootstrap flow. The app now publishes the resolved container into true global scope as well, which keeps production behavior the same while allowing the entrypoint tests to include the public files safely from inside PHPUnit methods.

## Notes
- SMTP delivery is preferred through `MAILER_DSN`; the native PHP mail transport remains as a fallback for environments that have not configured SMTP yet.
- Abuse protection is configured with `ALLOWED_ORIGINS`, `HONEYPOT_FIELD_NAME`, `RATE_LIMIT_MAX_ATTEMPTS`, and `RATE_LIMIT_WINDOW_SECONDS`.
- `GET /health.php` reports whether database connectivity and outbound mail configuration are ready.
- `php bin/migrate.php status` shows discovered and applied migrations, and `php bin/migrate.php migrate` applies pending ones.
- `docs/openapi.yaml` is the source-of-truth API contract for `/contact.php` and `/health.php`.
- The test suite now includes entrypoint-level checks for `public/contact.php` and `public/health.php`.
- Tests are included for service and validation behavior. Local execution requires PHP and Composer, and GitHub Actions is configured to run them on push.
