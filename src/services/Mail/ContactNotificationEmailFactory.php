<?php
// src/services/Mail/ContactNotificationEmailFactory.php
// Converts notification payloads into Symfony email messages.
// Connects to: src/models/ContactNotification.php, src/services/Mail/SymfonyMailerTransport.php
// Created: 2026-06-28

declare(strict_types=1);

namespace App\Services\Mail;

use App\Models\ContactNotification;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;

final class ContactNotificationEmailFactory
{
    /**
     * Builds a Symfony email for delivery.
     *
     * @param ContactNotification $notification Normalized notification payload.
     *
     * @return Email
     */
    public function create(ContactNotification $notification): Email
    {
        return (new Email())
            ->to(new Address($notification->toEmail))
            ->from(new Address($notification->fromEmail, $notification->fromName))
            ->replyTo(new Address($notification->replyToEmail))
            ->subject($notification->subject)
            ->text($notification->textBody);
    }
}
