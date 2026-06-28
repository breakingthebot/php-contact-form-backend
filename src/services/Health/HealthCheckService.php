<?php
// src/services/Health/HealthCheckService.php
// Aggregates component readiness checks into a single health report.
// Connects to: src/models/HealthCheckReport.php, src/services/Health/HealthCheckerInterface.php
// Created: 2026-06-28

declare(strict_types=1);

namespace App\Services\Health;

use App\Models\HealthCheckReport;

final class HealthCheckService
{
    /**
     * Initializes the health check service.
     *
     * @param array<int, HealthCheckerInterface> $checkers Component health checkers.
     */
    public function __construct(
        private readonly array $checkers
    ) {
    }

    /**
     * Runs the configured component health checks.
     *
     * @return HealthCheckReport
     */
    public function check(): HealthCheckReport
    {
        $checks = [];
        $hasError = false;

        foreach ($this->checkers as $checker) {
            $result = $checker->check();
            $checks[$checker->key()] = $result;

            if (($result['status'] ?? 'error') !== 'ok') {
                $hasError = true;
            }
        }

        return new HealthCheckReport(
            $hasError ? 503 : 200,
            $hasError ? 'degraded' : 'ok',
            $checks
        );
    }
}
