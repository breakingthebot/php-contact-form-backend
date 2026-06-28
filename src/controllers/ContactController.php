<?php
// src/controllers/ContactController.php
// Handles the incoming HTTP request and returns a JSON response.
// Connects to: src/models/ContactSubmission.php, src/services/ContactFormService.php
// Created: 2026-06-28

declare(strict_types=1);

namespace App\Controllers;

use App\Models\ContactSubmission;
use App\Services\ContactFormService;
use App\Utils\JsonResponder;

final class ContactController
{
    /**
     * Handles a contact submission request.
     *
     * @return void
     */
    public function handle(): void
    {
        global $container;

        /** @var ContactFormService $service */
        $service = $container[ContactFormService::class];
        /** @var JsonResponder $responder */
        $responder = $container[JsonResponder::class];

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $responder->send(
                405,
                [
                    'status' => 'error',
                    'message' => 'Method not allowed.',
                ]
            );
            return;
        }

        $rawInput = file_get_contents('php://input');
        $payload = json_decode($rawInput ?: '{}', true);

        if (!is_array($payload)) {
            $responder->send(
                400,
                [
                    'status' => 'error',
                    'message' => 'Invalid JSON payload.',
                ]
            );
            return;
        }

        $submission = ContactSubmission::fromArray(
            $payload,
            $_SERVER['REMOTE_ADDR'] ?? null
        );

        $result = $service->submit($submission);

        $responder->send($result->statusCode, $result->toArray());
    }
}
