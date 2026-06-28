<?php
// src/services/Health/DatabaseHealthChecker.php
// Verifies database connectivity for the health endpoint.
// Connects to: src/services/DatabaseConnectionFactoryInterface.php, src/utils/RequestLogger.php
// Created: 2026-06-28

declare(strict_types=1);

namespace App\Services\Health;

use App\Services\DatabaseConnectionFactoryInterface;
use App\Utils\RequestLogger;
use Throwable;

final class DatabaseHealthChecker implements HealthCheckerInterface
{
    /**
     * Initializes the database health checker.
     *
     * @param DatabaseConnectionFactoryInterface $connectionFactory Creates PDO connections for readiness checks.
     * @param RequestLogger $logger Records readiness failures.
     */
    public function __construct(
        private readonly DatabaseConnectionFactoryInterface $connectionFactory,
        private readonly RequestLogger $logger
    ) {
    }

    /**
     * Returns the report key for this checker.
     *
     * @return string
     */
    public function key(): string
    {
        return 'database';
    }

    /**
     * Verifies whether the database is reachable.
     *
     * @return array<string, string>
     */
    public function check(): array
    {
        try {
            $connection = $this->connectionFactory->create();
            $connection->query('SELECT 1');

            return [
                'status' => 'ok',
                'message' => 'Database connection is healthy.',
            ];
        } catch (Throwable $exception) {
            $this->logger->error(
                'Database health check failed.',
                ['exception' => $exception->getMessage()]
            );

            return [
                'status' => 'error',
                'message' => 'Database connection failed.',
            ];
        }
    }
}
