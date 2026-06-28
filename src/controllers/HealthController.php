<?php
// src/controllers/HealthController.php
// Handles operational readiness requests and returns a JSON health report.
// Connects to: src/services/Health/HealthCheckService.php, src/utils/JsonResponder.php
// Created: 2026-06-28

declare(strict_types=1);

namespace App\Controllers;

use App\Services\Health\HealthCheckService;
use App\Utils\JsonResponder;

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

        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            $responder->send(
                405,
                [
                    'status' => 'error',
                    'message' => 'Method not allowed.',
                ]
            );
            return;
        }

        $report = $service->check();
        $responder->send($report->statusCode, $report->toArray());
    }
}
