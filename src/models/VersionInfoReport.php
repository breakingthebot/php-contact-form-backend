<?php
// src/models/VersionInfoReport.php
// Immutable API report for app version and revision metadata.
// Connects to: src/services/Version/VersionInfoService.php, src/controllers/VersionController.php
// Created: 2026-06-28

declare(strict_types=1);

namespace App\Models;

final class VersionInfoReport
{
    /**
     * Initializes the version metadata report.
     *
     * @param int $statusCode HTTP status code for the report.
     * @param string $version Application version string.
     * @param string $revision Build or commit revision string.
     * @param string $environment Runtime environment name.
     */
    public function __construct(
        public readonly int $statusCode,
        public readonly string $version,
        public readonly string $revision,
        public readonly string $environment
    ) {
    }

    /**
     * Converts the report into a JSON response payload.
     *
     * @return array<string, string>
     */
    public function toArray(): array
    {
        return [
            'version' => $this->version,
            'revision' => $this->revision,
            'environment' => $this->environment,
        ];
    }
}
