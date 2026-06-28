<?php
// tests/Services/Migrations/MigrationFileLoaderTest.php
// Verifies ordered migration discovery from the migrations directory.
// Connects to: src/services/Migrations/MigrationFileLoader.php
// Created: 2026-06-28

declare(strict_types=1);

namespace Tests\Services\Migrations;

use App\Services\Migrations\MigrationFileLoader;
use PHPUnit\Framework\TestCase;

final class MigrationFileLoaderTest extends TestCase
{
    private string $directory;

    /**
     * Creates a temporary migration directory for the test run.
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->directory = dirname(__DIR__, 3) . '/logs/test-migrations';

        if (!is_dir($this->directory)) {
            mkdir($this->directory, 0777, true);
        }

        file_put_contents($this->directory . '/002_add_index.sql', 'SELECT 2;');
        file_put_contents($this->directory . '/001_create_table.sql', 'SELECT 1;');
    }

    /**
     * Removes the temporary migration directory after the test run.
     *
     * @return void
     */
    protected function tearDown(): void
    {
        $files = glob($this->directory . '/*.sql') ?: [];

        foreach ($files as $file) {
            @unlink($file);
        }

        @rmdir($this->directory);
    }

    /**
     * Confirms migrations are loaded in version order.
     *
     * @return void
     */
    public function testLoadReturnsMigrationsInSortedOrder(): void
    {
        $loader = new MigrationFileLoader($this->directory);
        $migrations = $loader->load();

        self::assertCount(2, $migrations);
        self::assertSame('001', $migrations[0]->version);
        self::assertSame('create table', $migrations[0]->name);
        self::assertSame('002', $migrations[1]->version);
    }
}
