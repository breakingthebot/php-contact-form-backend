<?php
// src/services/Security/OriginGuard.php
// Restricts requests to configured frontend origins when origin headers are present.
// Connects to: src/services/Security/RequestGuard.php, src/utils/Environment.php
// Created: 2026-06-28

declare(strict_types=1);

namespace App\Services\Security;

use App\Utils\Environment;

final class OriginGuard
{
    /**
     * Initializes the origin guard.
     *
     * @param Environment $environment Reads allowed origin configuration.
     */
    public function __construct(
        private readonly Environment $environment
    ) {
    }

    /**
     * Returns whether the request origin is allowed.
     *
     * @param string|null $origin Origin header value.
     *
     * @return bool
     */
    public function passes(?string $origin): bool
    {
        $allowedOrigins = $this->environment->get('ALLOWED_ORIGINS', '');

        if (trim($allowedOrigins) === '' || $origin === null || trim($origin) === '') {
            return true;
        }

        $allowed = array_values(
            array_filter(
                array_map('trim', explode(',', $allowedOrigins)),
                static fn (string $value): bool => $value !== ''
            )
        );

        return in_array(trim($origin), $allowed, true);
    }
}
