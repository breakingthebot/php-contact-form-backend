<?php
// src/models/ContactSubmission.php
// Immutable request model for a contact form submission.
// Connects to: src/controllers/ContactController.php, src/utils/ContactSubmissionValidator.php
// Created: 2026-06-28

declare(strict_types=1);

namespace App\Models;

final class ContactSubmission
{
    /**
     * Initializes a contact submission model.
     *
     * @param string $name Sender name.
     * @param string $email Sender email.
     * @param string $message Message body.
     * @param string|null $ipAddress Optional client IP.
     */
    public function __construct(
        public readonly string $name,
        public readonly string $email,
        public readonly string $message,
        public readonly ?string $ipAddress
    ) {
    }

    /**
     * Creates a submission from an arbitrary input array.
     *
     * @param array<string, mixed> $payload Incoming request payload.
     * @param string|null $ipAddress Optional client IP.
     *
     * @return self
     */
    public static function fromArray(array $payload, ?string $ipAddress): self
    {
        return new self(
            isset($payload['name']) ? trim((string) $payload['name']) : '',
            isset($payload['email']) ? trim((string) $payload['email']) : '',
            isset($payload['message']) ? trim((string) $payload['message']) : '',
            $ipAddress !== null ? trim($ipAddress) : null
        );
    }
}
