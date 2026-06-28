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
use App\Services\Mail\NativeMailTransport;
use App\Utils\Environment;
use App\Utils\JsonResponder;
use App\Utils\RequestLogger;

require_once dirname(__DIR__) . '/utils/manual_autoload.php';

$environment = new Environment(dirname(__DIR__, 2) . '/.env');
$logger = new RequestLogger($environment->get('LOG_PATH', dirname(__DIR__, 2) . '/logs/app.log'));
$databaseFactory = new DatabaseConnectionFactory($environment);
$repository = new ContactSubmissionRepository($databaseFactory);
$mailTransport = new NativeMailTransport($environment, $logger);
$service = new ContactFormService($repository, $mailTransport, $logger, $environment);

return [
    Environment::class => $environment,
    RequestLogger::class => $logger,
    JsonResponder::class => new JsonResponder(),
    ContactSubmissionRepositoryInterface::class => $repository,
    ContactFormService::class => $service,
];
