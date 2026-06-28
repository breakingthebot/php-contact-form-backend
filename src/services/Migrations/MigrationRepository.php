<?php
// src/services/Migrations/MigrationRepository.php
// Executes SQL migrations and records applied versions in MySQL.
// Connects to: src/services/DatabaseConnectionFactoryInterface.php, src/models/MigrationDefinition.php
// Created: 2026-06-28

declare(strict_types=1);

namespace App\Services\Migrations;

use App\Models\MigrationDefinition;
use App\Services\DatabaseConnectionFactoryInterface;
use PDO;

final class MigrationRepository implements MigrationRepositoryInterface
{
    private const CREATE_TABLE_SQL = '
        CREATE TABLE IF NOT EXISTS schema_migrations (
            version VARCHAR(50) PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            applied_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
        )
    ';

    private const SELECT_APPLIED_SQL = '
        SELECT version
        FROM schema_migrations
        ORDER BY version ASC
    ';

    private const INSERT_APPLIED_SQL = '
        INSERT INTO schema_migrations (version, name)
        VALUES (:version, :name)
    ';

    /**
     * Initializes the migration repository.
     *
     * @param DatabaseConnectionFactoryInterface $connectionFactory Creates database connections.
     */
    public function __construct(
        private readonly DatabaseConnectionFactoryInterface $connectionFactory
    ) {
    }

    /**
     * Ensures the schema migration tracking table exists.
     *
     * @return void
     */
    public function ensureTableExists(): void
    {
        $connection = $this->connectionFactory->create();
        $connection->exec(self::CREATE_TABLE_SQL);
    }

    /**
     * Returns the applied migration versions.
     *
     * @return array<int, string>
     */
    public function appliedVersions(): array
    {
        $connection = $this->connectionFactory->create();
        $statement = $connection->query(self::SELECT_APPLIED_SQL);

        return $statement === false
            ? []
            : $statement->fetchAll(PDO::FETCH_COLUMN) ?: [];
    }

    /**
     * Applies a migration inside a transaction and records it.
     *
     * @param MigrationDefinition $migration Migration to execute.
     *
     * @return void
     */
    public function apply(MigrationDefinition $migration): void
    {
        $connection = $this->connectionFactory->create();
        $connection->beginTransaction();

        try {
            $connection->exec($migration->sql);
            $statement = $connection->prepare(self::INSERT_APPLIED_SQL);
            $statement->execute(
                [
                    ':version' => $migration->version,
                    ':name' => $migration->name,
                ]
            );
            $connection->commit();
        } catch (\Throwable $exception) {
            if ($connection->inTransaction()) {
                $connection->rollBack();
            }

            throw $exception;
        }
    }
}
