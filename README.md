# PHP Contact Form Backend

Receives contact form submissions, validates them, stores them in MySQL, and dispatches an email notification through a modular PHP backend.

## Stack
- PHP 8.2+
- Composer for autoloading and test dependencies
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

5. Create the database table:

```bash
mysql -u your_user -p your_database < database/schema.sql
```

## Environment Variables
The application expects the variables defined in `.env.example`.

- `APP_ENV`
- `APP_DEBUG`
- `APP_URL`
- `CONTACT_TO_EMAIL`
- `CONTACT_FROM_EMAIL`
- `CONTACT_SUBJECT_PREFIX`
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

Send a POST request to `/contact.php` with JSON:

```bash
curl -X POST http://localhost:8000/contact.php \
  -H "Content-Type: application/json" \
  -d "{\"name\":\"Ada Lovelace\",\"email\":\"ada@example.com\",\"message\":\"Hello from the form.\"}"
```

## Deployed
Not deployed in iteration 1.

## Architecture Notes
This first iteration builds the backend in layers so the HTTP endpoint stays thin and the business rules are easy to test. The request comes into a single public entrypoint, gets normalized into a request model, passes through a validator, and then flows into a service that coordinates storage, email delivery, and logging. Storage and mail both sit behind interfaces so the real infrastructure can change later without rewriting the core request handling.

The result is a project that is easy to extend incrementally. Iteration 1 focuses on the contract and control flow: validate hostile input, return clear API responses, log failures with context, and keep MySQL and email integrations isolated behind atomic classes. Later iterations can harden SMTP delivery, add richer persistence features, and wire in CI without changing the public API shape.

The second iteration hardens the project for real deployment environments by making the autoload layer explicit about the repository's source layout. That matters because Windows tolerates path-case mismatches that Linux CI and many production hosts do not.

## Notes
- This iteration uses PHP's native `mail()` transport adapter as the default placeholder. Production SMTP support is a logical next step.
- Tests are included for service and validation behavior. Local execution requires PHP and Composer, and GitHub Actions is configured to run them on push.
