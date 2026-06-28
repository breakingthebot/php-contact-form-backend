<?php
// src/services/Migrations/MigrationFileLoader.php
// Discovers and loads ordered SQL migrations from disk.
// Connects to: src/models/MigrationDefinition.php
// Created: 2026-06-28

declare(strict_types=1);

namespace App\Services\Migrations;

use App\Models\MigrationDefinition;

class MigrationFileLoader
{
    /**
     * Initializes the migration loader.
     *
     * @param string $directory Migration directory path.
     */
    public function __construct(
        private readonly string $directory
    ) {
    }

    /**
     * Loads ordered migration definitions from the configured directory.
     *
     * @return array<int, MigrationDefinition>
     */
    public function load(): array
    {
        $files = glob($this->directory . '/*.sql') ?: [];
        sort($files, SORT_STRING);

        $migrations = [];

        foreach ($files as $file) {
            $basename = pathinfo($file, PATHINFO_FILENAME);
            [$version, $name] = array_pad(explode('_', $basename, 2), 2, '');
            $sql = file_get_contents($file);

            if ($sql === false) {
                continue;
            }

            $migrations[] = new MigrationDefinition(
                $version,
                str_replace('_', ' ', $name),
                $file,
                $sql
            );
        }

        return $migrations;
    }
}
