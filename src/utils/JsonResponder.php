<?php
// src/utils/JsonResponder.php
// Sends consistent JSON HTTP responses to API clients.
// Connects to: src/controllers/ContactController.php
// Created: 2026-06-28

declare(strict_types=1);

namespace App\Utils;

final class JsonResponder
{
    /**
     * Sends a JSON response with an HTTP status code.
     *
     * @param int $statusCode HTTP status code.
     * @param array<string, mixed> $payload Response body payload.
     * @param array<string, string> $headers Additional response headers.
     *
     * @return void
     */
    public function send(int $statusCode, array $payload, array $headers = []): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=UTF-8');

        foreach ($headers as $name => $value) {
            header($name . ': ' . $value);
        }

        echo json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }
}
