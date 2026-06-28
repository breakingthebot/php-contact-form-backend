<?php
// src/services/Mail/NativeMailTransport.php
// Sends contact notifications using PHP's native mail transport as a fallback path.
// Connects to: src/utils/Environment.php, src/utils/RequestLogger.php
// Created: 2026-06-28

declare(strict_types=1);

namespace App\Services\Mail;

use App\Models\ContactSubmission;
use App\Utils\Environment;
use App\Utils\RequestLogger;
use RuntimeException;

final class NativeMailTransport implements MailTransportInterface
{
    /**
     * Initializes the mail transport.
     *
     * @param Environment $environment Reads mail settings.
     * @param RequestLogger $logger Records delivery failures.
     */
    public function __construct(
        private readonly Environment $environment,
        private readonly RequestLogger $logger
    ) {
    }

    /**
     * Sends a contact notification email.
     *
     * @param ContactSubmission $submission Validated submission model.
     *
     * @return void
     */
    public function send(ContactSubmission $submission): void
    {
        $notification = (new ContactNotificationContentBuilder($this->environment))
            ->build($submission);

        $headers = [
            'From: ' . $notification->fromEmail,
            'Reply-To: ' . $submission->email,
            'Content-Type: text/plain; charset=UTF-8',
        ];

        $sent = mail(
            $notification->toEmail,
            $notification->subject,
            $notification->textBody,
            implode(PHP_EOL, $headers)
        );

        if ($sent) {
            return;
        }

        $this->logger->error(
            'Native mail transport failed to send contact notification.',
            ['recipient' => $notification->toEmail]
        );

        throw new RuntimeException('Native mail transport failed.');
    }
}
