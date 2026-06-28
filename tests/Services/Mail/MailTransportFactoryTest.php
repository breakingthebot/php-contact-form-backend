<?php
// tests/Services/Mail/MailTransportFactoryTest.php
// Verifies transport selection between native and Symfony SMTP delivery.
// Connects to: src/services/Mail/MailTransportFactory.php
// Created: 2026-06-28

declare(strict_types=1);

namespace Tests\Services\Mail;

use App\Services\Mail\MailTransportFactory;
use App\Services\Mail\NativeMailTransport;
use App\Services\Mail\SymfonyMailerTransport;
use App\Utils\Environment;
use App\Utils\RequestLogger;
use PHPUnit\Framework\TestCase;

final class MailTransportFactoryTest extends TestCase
{
    /**
     * Restores the mailer DSN environment variable after each test.
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($_ENV['MAILER_DSN'], $_SERVER['MAILER_DSN']);
    }

    /**
     * Confirms native mail is used when no SMTP DSN is configured.
     *
     * @return void
     */
    public function testCreateReturnsNativeTransportWithoutDsn(): void
    {
        $factory = new MailTransportFactory(
            new Environment(null),
            new RequestLogger(dirname(__DIR__, 3) . '/logs/test.log')
        );

        self::assertInstanceOf(NativeMailTransport::class, $factory->create());
    }

    /**
     * Confirms Symfony Mailer is used when a DSN is configured.
     *
     * @return void
     */
    public function testCreateReturnsSymfonyTransportWithDsn(): void
    {
        $_ENV['MAILER_DSN'] = 'smtp://localhost:1025';

        $factory = new MailTransportFactory(
            new Environment(null),
            new RequestLogger(dirname(__DIR__, 3) . '/logs/test.log')
        );

        self::assertInstanceOf(SymfonyMailerTransport::class, $factory->create());
    }
}
