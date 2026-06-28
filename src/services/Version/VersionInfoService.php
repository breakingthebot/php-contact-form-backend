<?php
// src/services/Version/VersionInfoService.php
// Builds application version metadata from runtime configuration.
// Connects to: src/models/VersionInfoReport.php, src/utils/Environment.php
// Created: 2026-06-28

declare(strict_types=1);

namespace App\Services\Version;

use App\Models\VersionInfoReport;
use App\Utils\Environment;

final class VersionInfoService
{
    /**
     * Initializes the version info service.
     *
     * @param Environment $environment Reads version metadata from environment variables.
     */
    public function __construct(
        private readonly Environment $environment
    ) {
    }

    /**
     * Returns the current version metadata report.
     *
     * @return VersionInfoReport
     */
    public function get(): VersionInfoReport
    {
        return new VersionInfoReport(
            200,
            $this->environment->get('APP_VERSION', 'development'),
            $this->environment->get('APP_REVISION', 'unknown'),
            $this->environment->get('APP_ENV', 'production')
        );
    }
}
