<?php
// src/models/HealthCheckReport.php
// Immutable aggregate health report for operational diagnostics.
// Connects to: src/services/Health/HealthCheckService.php, src/controllers/HealthController.php
// Created: 2026-06-28

declare(strict_types=1);

namespace App\Models;

final class HealthCheckReport
{
    /**
     * Initializes the health report.
     *
     * @param int $statusCode HTTP status code for the report.
     * @param string $status Aggregate health status.
     * @param array<string, array<string, string>> $checks Individual check results keyed by component.
     */
    public function __construct(
        public readonly int $statusCode,
        public readonly string $status,
        public readonly array $checks
    ) {
    }

    /**
     * Converts the report into a JSON response payload.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'status' => $this->status,
            'checks' => $this->checks,
        ];
    }
}
