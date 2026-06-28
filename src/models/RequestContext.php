<?php
// src/models/RequestContext.php
// Immutable per-request tracing context for public HTTP flows.
// Connects to: src/utils/RequestContextFactory.php, src/controllers/ContactController.php
// Created: 2026-06-28

declare(strict_types=1);

namespace App\Models;

final class RequestContext
{
    /**
     * Initializes the request context.
     *
     * @param string $requestId Correlation identifier for the request.
     * @param string $method HTTP request method.
     * @param string $path Request path.
     * @param string|null $ipAddress Client IP address when available.
     */
    public function __construct(
        public readonly string $requestId,
        public readonly string $method,
        public readonly string $path,
        public readonly ?string $ipAddress
    ) {
    }

    /**
     * Converts the request context into a log payload.
     *
     * @return array<string, string|null>
     */
    public function toArray(): array
    {
        return [
            'request_id' => $this->requestId,
            'method' => $this->method,
            'path' => $this->path,
            'ip_address' => $this->ipAddress,
        ];
    }
}
