<?php
// src/services/Mail/SymfonyMailerTransport.php
// Sends contact notifications through Symfony Mailer using an SMTP DSN.
// Connects to: src/services/Mail/ContactNotificationContentBuilder.php, src/services/Mail/ContactNotificationEmailFactory.php
// Created: 2026-06-28

declare(strict_types=1);

namespace App\Services\Mail;

use App\Models\ContactSubmission;
use App\Utils\RequestLogger;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;

final class SymfonyMailerTransport implements MailTransportInterface
{
    /**
     * Initializes the Symfony mail transport.
     *
     * @param MailerInterface $mailer Symfony mailer client.
     * @param ContactNotificationContentBuilder $contentBuilder Builds normalized notification content.
     * @param ContactNotificationEmailFactory $emailFactory Builds Symfony email messages.
     * @param RequestLogger $logger Records delivery failures.
     */
    public function __construct(
        private readonly MailerInterface $mailer,
        private readonly ContactNotificationContentBuilder $contentBuilder,
        private readonly ContactNotificationEmailFactory $emailFactory,
        private readonly RequestLogger $logger
    ) {
    }

    /**
     * Sends a contact notification email through Symfony Mailer.
     *
     * @param ContactSubmission $submission Validated submission model.
     *
     * @return void
     */
    public function send(ContactSubmission $submission): void
    {
        $notification = $this->contentBuilder->build($submission);
        $email = $this->emailFactory->create($notification);

        try {
            $this->mailer->send($email);
        } catch (TransportExceptionInterface $exception) {
            $this->logger->error(
                'Symfony mail transport failed to send contact notification.',
                [
                    'recipient' => $notification->toEmail,
                    'exception' => $exception->getMessage(),
                ]
            );

            throw $exception;
        }
    }
}
