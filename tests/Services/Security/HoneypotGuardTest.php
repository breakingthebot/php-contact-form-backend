<?php
// tests/Services/Security/HoneypotGuardTest.php
// Verifies hidden honeypot field validation for likely bot traffic.
// Connects to: src/services/Security/HoneypotGuard.php
// Created: 2026-06-28

declare(strict_types=1);

namespace Tests\Services\Security;

use App\Services\Security\HoneypotGuard;
use App\Utils\Environment;
use PHPUnit\Framework\TestCase;

final class HoneypotGuardTest extends TestCase
{
    /**
     * Restores honeypot configuration after each test.
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($_ENV['HONEYPOT_FIELD_NAME'], $_SERVER['HONEYPOT_FIELD_NAME']);
    }

    /**
     * Confirms the guard accepts requests when the honeypot field is empty.
     *
     * @return void
     */
    public function testPassesReturnsTrueWhenHoneypotFieldIsEmpty(): void
    {
        $_ENV['HONEYPOT_FIELD_NAME'] = 'company';
        $guard = new HoneypotGuard(new Environment(null));

        self::assertTrue($guard->passes(['company' => '']));
    }

    /**
     * Confirms the guard rejects requests when the honeypot field is populated.
     *
     * @return void
     */
    public function testPassesReturnsFalseWhenHoneypotFieldHasValue(): void
    {
        $_ENV['HONEYPOT_FIELD_NAME'] = 'company';
        $guard = new HoneypotGuard(new Environment(null));

        self::assertFalse($guard->passes(['company' => 'spam']));
    }
}
