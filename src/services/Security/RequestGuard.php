<?php
// src/services/Security/RequestGuard.php
// Coordinates abuse protection checks before requests reach the contact service.
// Connects to: src/services/Security/OriginGuard.php, src/services/Security/HoneypotGuard.php, src/services/Security/FileRateLimiter.php
// Created: 2026-06-28

declare(strict_types=1);

namespace App\Services\Security;

use App\Models\SubmissionResult;
use App\Utils\Environment;
use App\Utils\RequestLogger;

final class RequestGuard
{
    private readonly OriginGuard $originGuard;
    private readonly HoneypotGuard $honeypotGuard;

    /**
     * Initializes the request guard.
     *
     * @param Environment $environment Reads abuse-protection configuration.
     * @param RequestLogger $logger Records abuse events.
     * @param FileRateLimiter $rateLimiter Applies client IP rate limiting.
     */
    public function __construct(
        Environment $environment,
        private readonly RequestLogger $logger,
        private readonly FileRateLimiter $rateLimiter
    ) {
        $this->originGuard = new OriginGuard($environment);
        $this->honeypotGuard = new HoneypotGuard($environment);
    }

    /**
     * Runs abuse-protection checks for an incoming request.
     *
     * @param array<string, mixed> $payload Incoming JSON payload.
     * @param string|null $ipAddress Client IP address.
     * @param string|null $origin Request origin header.
     *
     * @return SubmissionResult|null
     */
    public function guard(array $payload, ?string $ipAddress, ?string $origin): ?SubmissionResult
    {
        if (!$this->originGuard->passes($origin)) {
            $this->logger->error(
                'Contact request blocked due to disallowed origin.',
                ['origin' => $origin]
            );

            return new SubmissionResult(
                403,
                'error',
                'Request origin is not allowed.'
            );
        }

        if (!$this->honeypotGuard->passes($payload)) {
            $this->logger->error(
                'Contact request blocked by honeypot validation.',
                ['ip_address' => $ipAddress]
            );

            return new SubmissionResult(
                422,
                'error',
                'Validation failed.',
                ['Request could not be accepted.']
            );
        }

        $rateLimitKey = $ipAddress !== null && trim($ipAddress) !== ''
            ? trim($ipAddress)
            : 'unknown';

        if (!$this->rateLimiter->allow($rateLimitKey)) {
            $this->logger->error(
                'Contact request blocked by rate limiter.',
                ['ip_address' => $rateLimitKey]
            );

            return new SubmissionResult(
                429,
                'error',
                'Too many requests. Please try again later.'
            );
        }

        return null;
    }
}
