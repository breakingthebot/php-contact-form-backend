<?php
// src/utils/ContactSubmissionValidator.php
// Validates contact submissions and returns explicit user-facing errors.
// Connects to: src/models/ContactSubmission.php, src/services/ContactFormService.php
// Created: 2026-06-28

declare(strict_types=1);

namespace App\Utils;

use App\Models\ContactSubmission;

final class ContactSubmissionValidator
{
    private const MAX_NAME_LENGTH = 120;
    private const MAX_EMAIL_LENGTH = 190;
    private const MIN_MESSAGE_LENGTH = 10;
    private const MAX_MESSAGE_LENGTH = 5000;

    /**
     * Validates a contact submission.
     *
     * @param ContactSubmission $submission Request model.
     *
     * @return array<int, string>
     */
    public static function validate(ContactSubmission $submission): array
    {
        $errors = [];

        if ($submission->name === '') {
            $errors[] = 'Name is required.';
        } elseif (mb_strlen($submission->name) > self::MAX_NAME_LENGTH) {
            $errors[] = 'Name must be 120 characters or fewer.';
        }

        if ($submission->email === '') {
            $errors[] = 'Email is required.';
        } elseif (mb_strlen($submission->email) > self::MAX_EMAIL_LENGTH) {
            $errors[] = 'Email must be 190 characters or fewer.';
        } elseif (filter_var($submission->email, FILTER_VALIDATE_EMAIL) === false) {
            $errors[] = 'Email format is invalid.';
        }

        if ($submission->message === '') {
            $errors[] = 'Message is required.';
        } elseif (mb_strlen($submission->message) < self::MIN_MESSAGE_LENGTH) {
            $errors[] = 'Message must be at least 10 characters.';
        } elseif (mb_strlen($submission->message) > self::MAX_MESSAGE_LENGTH) {
            $errors[] = 'Message must be 5000 characters or fewer.';
        }

        return $errors;
    }
}
