<?php
// tests/Services/Security/OriginGuardTest.php
// Verifies request origin validation against configured frontend origins.
// Connects to: src/services/Security/OriginGuard.php
// Created: 2026-06-28

declare(strict_types=1);

namespace Tests\Services\Security;

use App\Services\Security\OriginGuard;
use App\Utils\Environment;
use PHPUnit\Framework\TestCase;

final class OriginGuardTest extends TestCase
{
    /**
     * Restores origin configuration after each test.
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($_ENV['ALLOWED_ORIGINS'], $_SERVER['ALLOWED_ORIGINS']);
    }

    /**
     * Confirms a configured origin is accepted.
     *
     * @return void
     */
    public function testPassesReturnsTrueForAllowedOrigin(): void
    {
        $_ENV['ALLOWED_ORIGINS'] = 'http://localhost:3000,https://app.example.com';
        $guard = new OriginGuard(new Environment(null));

        self::assertTrue($guard->passes('https://app.example.com'));
    }

    /**
     * Confirms an unconfigured origin is rejected.
     *
     * @return void
     */
    public function testPassesReturnsFalseForDisallowedOrigin(): void
    {
        $_ENV['ALLOWED_ORIGINS'] = 'http://localhost:3000,https://app.example.com';
        $guard = new OriginGuard(new Environment(null));

        self::assertFalse($guard->passes('https://evil.example.com'));
    }
}
