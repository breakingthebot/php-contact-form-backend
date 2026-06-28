<?php
// src/services/Health/HealthCheckerInterface.php
// Defines the contract for component health readiness checks.
// Connects to: src/services/Health/DatabaseHealthChecker.php, src/services/Health/MailHealthChecker.php
// Created: 2026-06-28

declare(strict_types=1);

namespace App\Services\Health;

interface HealthCheckerInterface
{
    /**
     * Returns the component key used in the aggregate report.
     *
     * @return string
     */
    public function key(): string;

    /**
     * Executes the component health check.
     *
     * @return array<string, string>
     */
    public function check(): array;
}
