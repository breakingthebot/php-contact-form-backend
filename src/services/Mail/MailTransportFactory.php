<?php
// src/services/Mail/MailTransportFactory.php
// Selects the outbound mail transport based on runtime configuration.
// Connects to: src/services/Mail/NativeMailTransport.php, src/services/Mail/SymfonyMailerTransport.php
// Created: 2026-06-28

declare(strict_types=1);

namespace App\Services\Mail;

use App\Utils\Environment;
use App\Utils\RequestLogger;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mailer\Transport;

final class MailTransportFactory
{
    /**
     * Initializes the transport factory.
     *
     * @param Environment $environment Reads mail transport configuration.
     * @param RequestLogger $logger Records transport selection and failures.
     */
    public function __construct(
        private readonly Environment $environment,
        private readonly RequestLogger $logger
    ) {
    }

    /**
     * Creates the configured mail transport.
     *
     * @return MailTransportInterface
     */
    public function create(): MailTransportInterface
    {
        $mailerDsn = $this->environment->get('MAILER_DSN');

        if ($mailerDsn === null || trim($mailerDsn) === '') {
            return new NativeMailTransport($this->environment, $this->logger);
        }

        $mailer = $this->createSymfonyMailer($mailerDsn);

        return new SymfonyMailerTransport(
            $mailer,
            new ContactNotificationContentBuilder($this->environment),
            new ContactNotificationEmailFactory(),
            $this->logger
        );
    }

    /**
     * Creates a Symfony mailer from a DSN string.
     *
     * @param string $mailerDsn Transport DSN string.
     *
     * @return MailerInterface
     */
    private function createSymfonyMailer(string $mailerDsn): MailerInterface
    {
        return new Mailer(Transport::fromDsn($mailerDsn));
    }
}
