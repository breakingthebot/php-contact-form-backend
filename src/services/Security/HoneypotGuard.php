<?php
// src/services/Security/HoneypotGuard.php
// Rejects likely bots that populate a hidden honeypot field.
// Connects to: src/services/Security/RequestGuard.php, src/utils/Environment.php
// Created: 2026-06-28

declare(strict_types=1);

namespace App\Services\Security;

use App\Utils\Environment;

final class HoneypotGuard
{
    /**
     * Initializes the honeypot guard.
     *
     * @param Environment $environment Reads honeypot field configuration.
     */
    public function __construct(
        private readonly Environment $environment
    ) {
    }

    /**
     * Returns whether the request passes the honeypot check.
     *
     * @param array<string, mixed> $payload Incoming request payload.
     *
     * @return bool
     */
    public function passes(array $payload): bool
    {
        $fieldName = $this->environment->get('HONEYPOT_FIELD_NAME', 'website');
        $value = $payload[$fieldName] ?? '';

        return trim((string) $value) === '';
    }
}
