<?php
// src/models/SubmissionResult.php
// Standardizes the service response returned to the controller.
// Connects to: src/services/ContactFormService.php, src/controllers/ContactController.php
// Created: 2026-06-28

declare(strict_types=1);

namespace App\Models;

final class SubmissionResult
{
    /**
     * Initializes a service result object.
     *
     * @param int $statusCode HTTP status code.
     * @param string $status Machine-readable status string.
     * @param string $message Human-readable message.
     * @param array<int, string> $errors Validation or processing errors.
     */
    public function __construct(
        public readonly int $statusCode,
        public readonly string $status,
        public readonly string $message,
        public readonly array $errors = []
    ) {
    }

    /**
     * Converts the result into an API response array.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $response = [
            'status' => $this->status,
            'message' => $this->message,
        ];

        if ($this->errors !== []) {
            $response['errors'] = $this->errors;
        }

        return $response;
    }
}
