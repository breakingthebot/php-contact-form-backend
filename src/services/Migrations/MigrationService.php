<?php
// src/services/Migrations/MigrationService.php
// Computes migration status and applies pending migrations.
// Connects to: src/services/Migrations/MigrationFileLoader.php, src/services/Migrations/MigrationRepositoryInterface.php
// Created: 2026-06-28

declare(strict_types=1);

namespace App\Services\Migrations;

use App\Models\MigrationDefinition;

final class MigrationService
{
    /**
     * Initializes the migration service.
     *
     * @param MigrationFileLoader $loader Loads ordered migration files.
     * @param MigrationRepositoryInterface $repository Persists migration state.
     */
    public function __construct(
        private readonly MigrationFileLoader $loader,
        private readonly MigrationRepositoryInterface $repository
    ) {
    }

    /**
     * Returns the current migration status without applying changes.
     *
     * @return array<string, mixed>
     */
    public function status(): array
    {
        $this->repository->ensureTableExists();
        $migrations = $this->loader->load();
        $appliedVersions = $this->repository->appliedVersions();
        $pending = $this->pendingMigrations($migrations, $appliedVersions);

        return [
            'summary' => $pending === [] ? 'up to date' : 'pending migrations',
            'applied_count' => count($appliedVersions),
            'pending_count' => count($pending),
            'pending_versions' => array_map(
                static fn (MigrationDefinition $migration): string => $migration->version,
                $pending
            ),
        ];
    }

    /**
     * Applies pending migrations and returns the resulting status.
     *
     * @return array<string, mixed>
     */
    public function migrate(): array
    {
        $this->repository->ensureTableExists();
        $migrations = $this->loader->load();
        $appliedVersions = $this->repository->appliedVersions();
        $pending = $this->pendingMigrations($migrations, $appliedVersions);

        foreach ($pending as $migration) {
            $this->repository->apply($migration);
        }

        return [
            'summary' => $pending === [] ? 'up to date' : 'applied pending migrations',
            'applied_count' => count($appliedVersions) + count($pending),
            'pending_count' => 0,
            'pending_versions' => [],
        ];
    }

    /**
     * Returns migrations that have not yet been applied.
     *
     * @param array<int, MigrationDefinition> $migrations Discovered migration definitions.
     * @param array<int, string> $appliedVersions Applied migration versions.
     *
     * @return array<int, MigrationDefinition>
     */
    private function pendingMigrations(array $migrations, array $appliedVersions): array
    {
        return array_values(
            array_filter(
                $migrations,
                static fn (MigrationDefinition $migration): bool => !in_array(
                    $migration->version,
                    $appliedVersions,
                    true
                )
            )
        );
    }
}
