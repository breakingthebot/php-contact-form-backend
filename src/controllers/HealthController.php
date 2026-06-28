<?php
// src/controllers/HealthController.php
// Handles operational readiness requests and returns a JSON health report.
// Connects to: src/services/Health/HealthCheckService.php, src/utils/JsonResponder.php
// Created: 2026-06-28

declare(strict_types=1);

namespace App\Controllers;

use App\Services\Health\HealthCheckService;
use App\Utils\JsonResponder;
use App\Utils\RequestContextFactory;
use App\Utils\RequestLogger;

final class HealthController
{
    /**
     * Handles the health-check request.
     *
     * @return void
     */
    public function handle(): void
    {
        global $container;

        /** @var HealthCheckService $service */
        $service = $container[HealthCheckService::class];
        /** @var JsonResponder $responder */
        $responder = $container[JsonResponder::class];
        /** @var RequestContextFactory $requestContextFactory */
        $requestContextFactory = $container[RequestContextFactory::class];
        /** @var RequestLogger $logger */
        $logger = $container[RequestLogger::class];
        $requestContext = $requestContextFactory->createFromGlobals();
        $requestLogger = $logger->withContext($requestContext->toArray());
        $responseHeaders = ['X-Request-Id' => $requestContext->requestId];

        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            $requestLogger->error('Rejected health request due to unsupported method.');
            $responder->send(
                405,
                [
                    'status' => 'error',
                    'message' => 'Method not allowed.',
                ],
                $responseHeaders
            );
            return;
        }

        $report = $service->check();
        $requestLogger->info(
            'Completed health request.',
            ['status_code' => $report->statusCode, 'result_status' => $report->status]
        );
        $responder->send($report->statusCode, $report->toArray(), $responseHeaders);
    }
}
