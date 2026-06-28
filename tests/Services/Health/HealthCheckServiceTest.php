<?php
// tests/Services/Health/HealthCheckServiceTest.php
// Verifies aggregate health reporting across component checks.
// Connects to: src/services/Health/HealthCheckService.php
// Created: 2026-06-28

declare(strict_types=1);

namespace Tests\Services\Health;

use App\Services\Health\HealthCheckService;
use App\Services\Health\HealthCheckerInterface;
use PHPUnit\Framework\TestCase;

final class HealthCheckServiceTest extends TestCase
{
    /**
     * Confirms the aggregate report is healthy when all checks pass.
     *
     * @return void
     */
    public function testCheckReturnsOkWhenAllChecksPass(): void
    {
        $service = new HealthCheckService(
            [
                new TestHealthChecker('database', ['status' => 'ok', 'message' => 'ready']),
                new TestHealthChecker('mail', ['status' => 'ok', 'message' => 'ready']),
            ]
        );

        $report = $service->check();

        self::assertSame(200, $report->statusCode);
        self::assertSame('ok', $report->status);
        self::assertSame('ok', $report->checks['database']['status']);
        self::assertSame('ok', $report->checks['mail']['status']);
    }

    /**
     * Confirms the aggregate report is degraded when any check fails.
     *
     * @return void
     */
    public function testCheckReturnsDegradedWhenAnyCheckFails(): void
    {
        $service = new HealthCheckService(
            [
                new TestHealthChecker('database', ['status' => 'ok', 'message' => 'ready']),
                new TestHealthChecker('mail', ['status' => 'error', 'message' => 'bad config']),
            ]
        );

        $report = $service->check();

        self::assertSame(503, $report->statusCode);
        self::assertSame('degraded', $report->status);
        self::assertSame('error', $report->checks['mail']['status']);
    }
}

final class TestHealthChecker implements HealthCheckerInterface
{
    /**
     * Initializes a test health checker.
     *
     * @param string $key Report key.
     * @param array<string, string> $result Check result payload.
     */
    public function __construct(
        private readonly string $key,
        private readonly array $result
    ) {
    }

    /**
     * Returns the test report key.
     *
     * @return string
     */
    public function key(): string
    {
        return $this->key;
    }

    /**
     * Returns the predefined test result.
     *
     * @return array<string, string>
     */
    public function check(): array
    {
        return $this->result;
    }
}
