<?php
// tests/Services/Version/VersionInfoServiceTest.php
// Verifies version metadata reporting from runtime configuration.
// Connects to: src/services/Version/VersionInfoService.php
// Created: 2026-06-28

declare(strict_types=1);

namespace Tests\Services\Version;

use App\Services\Version\VersionInfoService;
use App\Utils\Environment;
use PHPUnit\Framework\TestCase;

final class VersionInfoServiceTest extends TestCase
{
    /**
     * Restores version-related environment variables after each test.
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset(
            $_ENV['APP_VERSION'],
            $_ENV['APP_REVISION'],
            $_ENV['APP_ENV'],
            $_SERVER['APP_VERSION'],
            $_SERVER['APP_REVISION'],
            $_SERVER['APP_ENV']
        );
    }

    /**
     * Confirms the service returns configured version metadata.
     *
     * @return void
     */
    public function testGetReturnsConfiguredVersionMetadata(): void
    {
        $_ENV['APP_VERSION'] = '0.13.0';
        $_ENV['APP_REVISION'] = 'abc1234';
        $_ENV['APP_ENV'] = 'staging';

        $report = (new VersionInfoService(new Environment(null)))->get();

        self::assertSame(200, $report->statusCode);
        self::assertSame('0.13.0', $report->version);
        self::assertSame('abc1234', $report->revision);
        self::assertSame('staging', $report->environment);
    }
}
