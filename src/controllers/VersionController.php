<?php
// src/controllers/VersionController.php
// Handles build metadata requests and returns a JSON version report.
// Connects to: src/services/Version/VersionInfoService.php, src/utils/JsonResponder.php
// Created: 2026-06-28

declare(strict_types=1);

namespace App\Controllers;

use App\Services\Version\VersionInfoService;
use App\Utils\JsonResponder;
use App\Utils\RequestContextFactory;
use App\Utils\RequestLogger;

final class VersionController
{
    /**
     * Handles the version request.
     *
     * @return void
     */
    public function handle(): void
    {
        global $container;

        /** @var VersionInfoService $service */
        $service = $container[VersionInfoService::class];
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
            $requestLogger->error(
                'Rejected version request due to unsupported method.',
                ['duration_ms' => $requestContext->durationMilliseconds()]
            );
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

        $report = $service->get();
        $requestLogger->info(
            'Completed version request.',
            [
                'status_code' => $report->statusCode,
                'app_version' => $report->version,
                'duration_ms' => $requestContext->durationMilliseconds(),
            ]
        );
        $responder->send($report->statusCode, $report->toArray(), $responseHeaders);
    }
}
