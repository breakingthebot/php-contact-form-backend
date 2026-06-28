<?php
// tests/Services/Health/MailHealthCheckerTest.php
// Verifies outbound mail readiness reporting for the health endpoint.
// Connects to: src/services/Health/MailHealthChecker.php
// Created: 2026-06-28

declare(strict_types=1);

namespace Tests\Services\Health;

use App\Services\Health\MailHealthChecker;
use App\Utils\Environment;
use App\Utils\RequestLogger;
use PHPUnit\Framework\TestCase;

final class MailHealthCheckerTest extends TestCase
{
    /**
     * Restores mail-related environment variables after each test.
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset(
            $_ENV['MAILER_DSN'],
            $_ENV['CONTACT_TO_EMAIL'],
            $_ENV['CONTACT_FROM_EMAIL'],
            $_SERVER['MAILER_DSN'],
            $_SERVER['CONTACT_TO_EMAIL'],
            $_SERVER['CONTACT_FROM_EMAIL']
        );
    }

    /**
     * Confirms SMTP readiness is reported when a valid DSN is configured.
     *
     * @return void
     */
    public function testCheckReturnsOkForValidMailerDsn(): void
    {
        $_ENV['MAILER_DSN'] = 'smtp://localhost:1025';

        $checker = new MailHealthChecker(
            new Environment(null),
            new RequestLogger(dirname(__DIR__, 3) . '/logs/test.log')
        );

        self::assertSame(
            [
                'status' => 'ok',
                'message' => 'SMTP mail transport is configured.',
            ],
            $checker->check()
        );
    }

    /**
     * Confirms fallback readiness is reported when native mail config is present.
     *
     * @return void
     */
    public function testCheckReturnsOkForNativeMailFallbackConfig(): void
    {
        $_ENV['CONTACT_TO_EMAIL'] = 'owner@example.com';
        $_ENV['CONTACT_FROM_EMAIL'] = 'no-reply@example.com';

        $checker = new MailHealthChecker(
            new Environment(null),
            new RequestLogger(dirname(__DIR__, 3) . '/logs/test.log')
        );

        self::assertSame(
            [
                'status' => 'ok',
                'message' => 'Native mail fallback is configured.',
            ],
            $checker->check()
        );
    }

    /**
     * Confirms missing outbound mail config is reported as an error.
     *
     * @return void
     */
    public function testCheckReturnsErrorForMissingMailConfig(): void
    {
        $checker = new MailHealthChecker(
            new Environment(null),
            new RequestLogger(dirname(__DIR__, 3) . '/logs/test.log')
        );

        self::assertSame(
            [
                'status' => 'error',
                'message' => 'Mail configuration is incomplete.',
            ],
            $checker->check()
        );
    }
}
