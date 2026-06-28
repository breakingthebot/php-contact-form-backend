<?php
// src/services/Migrations/MigrationRepositoryInterface.php
// Defines persistence operations for migration state and execution.
// Connects to: src/services/Migrations/MigrationRepository.php, src/services/Migrations/MigrationService.php
// Created: 2026-06-28

declare(strict_types=1);

namespace App\Services\Migrations;

use App\Models\MigrationDefinition;

interface MigrationRepositoryInterface
{
    /**
     * Ensures the migration tracking table exists.
     *
     * @return void
     */
    public function ensureTableExists(): void;

    /**
     * Returns the ordered list of applied migration versions.
     *
     * @return array<int, string>
     */
    public function appliedVersions(): array;

    /**
     * Applies and records a migration.
     *
     * @param MigrationDefinition $migration Migration to execute.
     *
     * @return void
     */
    public function apply(MigrationDefinition $migration): void;
}
