<?php
// tests/Utils/RequestLoggerTest.php
// Verifies structured logging with request-scoped base context.
// Connects to: src/utils/RequestLogger.php
// Created: 2026-06-28

declare(strict_types=1);

namespace Tests\Utils;

use App\Utils\RequestLogger;
use PHPUnit\Framework\TestCase;

final class RequestLoggerTest extends TestCase
{
    private string $logPath;

    /**
     * Sets up a temporary log file path.
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->logPath = dirname(__DIR__, 2) . '/logs/request-logger-test.log';
        @unlink($this->logPath);
    }

    /**
     * Removes the temporary log file after the test run.
     *
     * @return void
     */
    protected function tearDown(): void
    {
        @unlink($this->logPath);
    }

    /**
     * Confirms derived loggers merge base context into emitted records.
     *
     * @return void
     */
    public function testWithContextAddsBaseContextToLogRecords(): void
    {
        $logger = (new RequestLogger($this->logPath))
            ->withContext(['request_id' => 'req-123', 'path' => '/contact.php']);

        $logger->info('Handled request.', ['status_code' => 201]);

        $contents = file_get_contents($this->logPath);
        self::assertIsString($contents);

        $line = trim(explode(PHP_EOL, $contents)[0]);
        /** @var array<string, mixed> $decoded */
        $decoded = json_decode($line, true);

        self::assertSame('INFO', $decoded['level']);
        self::assertSame('req-123', $decoded['context']['request_id']);
        self::assertSame('/contact.php', $decoded['context']['path']);
        self::assertSame(201, $decoded['context']['status_code']);
    }
}
