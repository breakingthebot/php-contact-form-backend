<?php
// src/utils/RequestContextFactory.php
// Creates request-scoped tracing context from HTTP server variables.
// Connects to: src/models/RequestContext.php, src/controllers/HealthController.php
// Created: 2026-06-28

declare(strict_types=1);

namespace App\Utils;

use App\Models\RequestContext;

final class RequestContextFactory
{
    /**
     * Creates request context from the current HTTP server state.
     *
     * @return RequestContext
     */
    public function createFromGlobals(): RequestContext
    {
        $requestId = $this->resolveRequestId();
        $method = (string) ($_SERVER['REQUEST_METHOD'] ?? 'GET');
        $path = (string) ($_SERVER['REQUEST_URI'] ?? '/');
        $ipAddress = isset($_SERVER['REMOTE_ADDR']) ? (string) $_SERVER['REMOTE_ADDR'] : null;

        return new RequestContext($requestId, $method, $path, $ipAddress);
    }

    /**
     * Resolves the request ID from headers or generates a new one.
     *
     * @return string
     */
    private function resolveRequestId(): string
    {
        $incoming = trim((string) ($_SERVER['HTTP_X_REQUEST_ID'] ?? ''));

        if ($incoming !== '') {
            return $incoming;
        }

        return bin2hex(random_bytes(16));
    }
}
