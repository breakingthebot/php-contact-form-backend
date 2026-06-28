<?php
// src/models/ContactNotification.php
// Immutable mail notification payload derived from a contact submission.
// Connects to: src/services/Mail/ContactNotificationContentBuilder.php, src/services/Mail/SymfonyMailerTransport.php
// Created: 2026-06-28

declare(strict_types=1);

namespace App\Models;

final class ContactNotification
{
    /**
     * Initializes a notification payload for outbound email delivery.
     *
     * @param string $toEmail Notification recipient.
     * @param string $fromEmail Sender address.
     * @param string $fromName Sender display name.
     * @param string $replyToEmail Reply-to address from the contact submission.
     * @param string $subject Notification subject.
     * @param string $textBody Notification body.
     */
    public function __construct(
        public readonly string $toEmail,
        public readonly string $fromEmail,
        public readonly string $fromName,
        public readonly string $replyToEmail,
        public readonly string $subject,
        public readonly string $textBody
    ) {
    }
}
