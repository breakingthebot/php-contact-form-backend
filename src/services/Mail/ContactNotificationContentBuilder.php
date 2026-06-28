<?php
// src/services/Mail/ContactNotificationContentBuilder.php
// Builds a normalized notification payload for outbound contact emails.
// Connects to: src/models/ContactNotification.php, src/utils/Environment.php
// Created: 2026-06-28

declare(strict_types=1);

namespace App\Services\Mail;

use App\Models\ContactNotification;
use App\Models\ContactSubmission;
use App\Utils\Environment;

final class ContactNotificationContentBuilder
{
    /**
     * Initializes the notification content builder.
     *
     * @param Environment $environment Reads mail-related configuration.
     */
    public function __construct(
        private readonly Environment $environment
    ) {
    }

    /**
     * Builds a notification payload for a contact submission.
     *
     * @param ContactSubmission $submission Validated submission model.
     *
     * @return ContactNotification
     */
    public function build(ContactSubmission $submission): ContactNotification
    {
        $toEmail = $this->environment->requireValue('CONTACT_TO_EMAIL');
        $fromEmail = $this->environment->requireValue('CONTACT_FROM_EMAIL');
        $fromName = $this->environment->get('CONTACT_FROM_NAME', 'Contact Form');
        $subjectPrefix = $this->environment->get('CONTACT_SUBJECT_PREFIX', '[Contact]');

        return new ContactNotification(
            $toEmail,
            $fromEmail,
            $fromName,
            $submission->email,
            trim($subjectPrefix . ' New contact request'),
            implode(
                PHP_EOL . PHP_EOL,
                [
                    'Name: ' . $submission->name,
                    'Email: ' . $submission->email,
                    'IP Address: ' . ($submission->ipAddress ?? 'unknown'),
                    'Message:',
                    $submission->message,
                ]
            )
        );
    }
}
