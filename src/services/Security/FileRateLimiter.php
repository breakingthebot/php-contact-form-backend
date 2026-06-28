<?php
// src/services/Security/FileRateLimiter.php
// Applies file-backed IP rate limiting for contact form requests.
// Connects to: src/services/Security/RequestGuard.php, src/utils/Environment.php
// Created: 2026-06-28

declare(strict_types=1);

namespace App\Services\Security;

use App\Utils\Environment;
use App\Utils\RequestLogger;

final class FileRateLimiter
{
    /**
     * Initializes the file-backed rate limiter.
     *
     * @param string $storagePath JSON storage path for request buckets.
     * @param Environment $environment Reads rate-limit settings.
     * @param RequestLogger $logger Records limiter read and write failures.
     */
    public function __construct(
        private readonly string $storagePath,
        private readonly Environment $environment,
        private readonly RequestLogger $logger
    ) {
    }

    /**
     * Records a request attempt and returns whether it is still allowed.
     *
     * @param string $key Rate-limit key, usually the client IP.
     *
     * @return bool
     */
    public function allow(string $key): bool
    {
        $maxAttempts = (int) $this->environment->get('RATE_LIMIT_MAX_ATTEMPTS', '5');
        $windowSeconds = (int) $this->environment->get('RATE_LIMIT_WINDOW_SECONDS', '300');
        $now = time();
        $entries = $this->readEntries();
        $validAfter = $now - $windowSeconds;

        $timestamps = array_values(
            array_filter(
                $entries[$key] ?? [],
                static fn (mixed $timestamp): bool => is_int($timestamp) && $timestamp >= $validAfter
            )
        );

        if (count($timestamps) >= $maxAttempts) {
            $entries[$key] = $timestamps;
            $this->writeEntries($entries);
            return false;
        }

        $timestamps[] = $now;
        $entries[$key] = $timestamps;
        $this->writeEntries($entries);

        return true;
    }

    /**
     * Reads the persisted request buckets from disk.
     *
     * @return array<string, array<int, int>>
     */
    private function readEntries(): array
    {
        if (!is_file($this->storagePath)) {
            return [];
        }

        $raw = file_get_contents($this->storagePath);

        if ($raw === false || trim($raw) === '') {
            return [];
        }

        $decoded = json_decode($raw, true);

        if (!is_array($decoded)) {
            $this->logger->error(
                'Rate limiter storage contained invalid JSON.',
                ['path' => $this->storagePath]
            );
            return [];
        }

        return $decoded;
    }

    /**
     * Persists the request buckets to disk.
     *
     * @param array<string, array<int, int>> $entries Rate-limit buckets keyed by client identifier.
     *
     * @return void
     */
    private function writeEntries(array $entries): void
    {
        $directory = dirname($this->storagePath);

        if (!is_dir($directory)) {
            mkdir($directory, 0777, true);
        }

        $encoded = json_encode($entries, JSON_UNESCAPED_SLASHES);

        if ($encoded === false) {
            $this->logger->error(
                'Rate limiter storage failed to encode JSON.',
                ['path' => $this->storagePath]
            );
            return;
        }

        file_put_contents($this->storagePath, $encoded);
    }
}
