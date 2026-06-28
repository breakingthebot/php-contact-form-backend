<?php
// src/models/MigrationDefinition.php
// Immutable metadata and SQL payload for a versioned database migration.
// Connects to: src/services/Migrations/MigrationFileLoader.php, src/services/Migrations/MigrationRepository.php
// Created: 2026-06-28

declare(strict_types=1);

namespace App\Models;

final class MigrationDefinition
{
    /**
     * Initializes a migration definition.
     *
     * @param string $version Ordered migration version identifier.
     * @param string $name Human-readable migration name derived from the file name.
     * @param string $path Absolute file path for the migration.
     * @param string $sql SQL content to apply.
     */
    public function __construct(
        public readonly string $version,
        public readonly string $name,
        public readonly string $path,
        public readonly string $sql
    ) {
    }
}
