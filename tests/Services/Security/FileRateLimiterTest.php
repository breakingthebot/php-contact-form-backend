<?php
// tests/Services/Security/FileRateLimiterTest.php
// Verifies file-backed request rate limiting behavior.
// Connects to: src/services/Security/FileRateLimiter.php
// Created: 2026-06-28

declare(strict_types=1);

namespace Tests\Services\Security;

use App\Services\Security\FileRateLimiter;
use App\Utils\Environment;
use App\Utils\RequestLogger;
use PHPUnit\Framework\TestCase;

final class FileRateLimiterTest extends TestCase
{
    private string $storagePath;

    /**
     * Sets up a temporary rate-limit storage file path.
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->storagePath = dirname(__DIR__, 3) . '/logs/test-rate-limit.json';
        @unlink($this->storagePath);
        $_ENV['RATE_LIMIT_MAX_ATTEMPTS'] = '2';
        $_ENV['RATE_LIMIT_WINDOW_SECONDS'] = '300';
    }

    /**
     * Cleans up temporary storage and environment variables.
     *
     * @return void
     */
    protected function tearDown(): void
    {
        @unlink($this->storagePath);
        unset(
            $_ENV['RATE_LIMIT_MAX_ATTEMPTS'],
            $_ENV['RATE_LIMIT_WINDOW_SECONDS'],
            $_SERVER['RATE_LIMIT_MAX_ATTEMPTS'],
            $_SERVER['RATE_LIMIT_WINDOW_SECONDS']
        );
    }

    /**
     * Confirms the limiter allows requests until the configured threshold is exceeded.
     *
     * @return void
     */
    public function testAllowBlocksRequestsAfterConfiguredThreshold(): void
    {
        $limiter = new FileRateLimiter(
            $this->storagePath,
            new Environment(null),
            new RequestLogger(dirname(__DIR__, 3) . '/logs/test.log')
        );

        self::assertTrue($limiter->allow('127.0.0.1'));
        self::assertTrue($limiter->allow('127.0.0.1'));
        self::assertFalse($limiter->allow('127.0.0.1'));
    }
}
