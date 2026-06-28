<?php
// src/config/container.php
// Builds the application's infrastructure dependencies.
// Connects to: src/services/, src/utils/
// Created: 2026-06-28

declare(strict_types=1);

use App\Services\ContactFormService;
use App\Services\ContactSubmissionRepository;
use App\Services\ContactSubmissionRepositoryInterface;
use App\Services\DatabaseConnectionFactory;
use App\Services\DatabaseConnectionFactoryInterface;
use App\Services\Health\DatabaseHealthChecker;
use App\Services\Health\HealthCheckService;
use App\Services\Health\MailHealthChecker;
use App\Services\Mail\MailTransportFactory;
use App\Services\Migrations\MigrationFileLoader;
use App\Services\Migrations\MigrationRepository;
use App\Services\Migrations\MigrationRepositoryInterface;
use App\Services\Migrations\MigrationService;
use App\Services\Security\FileRateLimiter;
use App\Services\Security\RequestGuard;
use App\Utils\Environment;
use App\Utils\JsonResponder;
use App\Utils\NativeRequestInputReader;
use App\Utils\RequestInputReaderInterface;
use App\Utils\RequestLogger;

require_once dirname(__DIR__) . '/utils/manual_autoload.php';

$environment = new Environment(dirname(__DIR__, 2) . '/.env');
$logger = new RequestLogger($environment->get('LOG_PATH', dirname(__DIR__, 2) . '/logs/app.log'));
$databaseFactory = new DatabaseConnectionFactory($environment);
$repository = new ContactSubmissionRepository($databaseFactory);
$migrationRepository = new MigrationRepository($databaseFactory);
$migrationService = new MigrationService(
    new MigrationFileLoader(dirname(__DIR__, 2) . '/database/migrations'),
    $migrationRepository
);
$mailTransport = (new MailTransportFactory($environment, $logger))->create();
$requestGuard = new RequestGuard(
    $environment,
    $logger,
    new FileRateLimiter(
        $environment->get('LOG_PATH', dirname(__DIR__, 2) . '/logs/app.log') . '.rate-limit.json',
        $environment,
        $logger
    )
);
$service = new ContactFormService($repository, $mailTransport, $logger, $environment);
$healthCheckService = new HealthCheckService(
    [
        new DatabaseHealthChecker($databaseFactory, $logger),
        new MailHealthChecker($environment, $logger),
    ]
);

return [
    Environment::class => $environment,
    RequestLogger::class => $logger,
    JsonResponder::class => new JsonResponder(),
    RequestInputReaderInterface::class => new NativeRequestInputReader(),
    DatabaseConnectionFactoryInterface::class => $databaseFactory,
    ContactSubmissionRepositoryInterface::class => $repository,
    MigrationRepositoryInterface::class => $migrationRepository,
    MigrationService::class => $migrationService,
    RequestGuard::class => $requestGuard,
    HealthCheckService::class => $healthCheckService,
    ContactFormService::class => $service,
];
