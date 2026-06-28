<?php
// src/services/Mail/MailTransportInterface.php
// Defines the contract for sending contact notification emails.
// Connects to: src/services/ContactFormService.php, src/services/Mail/NativeMailTransport.php
// Created: 2026-06-28

declare(strict_types=1);

namespace App\Services\Mail;

use App\Models\ContactSubmission;

interface MailTransportInterface
{
    /**
     * Sends a notification for a contact submission.
     *
     * @param ContactSubmission $submission Validated submission model.
     *
     * @return void
     */
    public function send(ContactSubmission $submission): void;
}
