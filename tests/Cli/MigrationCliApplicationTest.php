<?php
// tests/Cli/MigrationCliApplicationTest.php
// Verifies human-readable migration CLI output for status and migrate commands.
// Connects to: src/cli/MigrationCliApplication.php
// Created: 2026-06-28

declare(strict_types=1);

namespace Tests\Cli;

use App\Cli\MigrationCliApplication;
use App\Services\Migrations\MigrationService;
use PHPUnit\Framework\TestCase;

final class MigrationCliApplicationTest extends TestCase
{
    /**
     * Clears the CLI container override after each test.
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($GLOBALS['container']);
    }

    /**
     * Confirms status output includes discovered, applied, and pending migration details.
     *
     * @return void
     */
    public function testRunWritesDetailedStatusOutput(): void
    {
        $GLOBALS['container'] = [
            MigrationService::class => new class {
                public function status(): array
                {
                    return [
                        'summary' => 'pending migrations',
                        'discovered_count' => 2,
                        'applied_count' => 1,
                        'pending_count' => 1,
                        'discovered_migrations' => [
                            ['version' => '001', 'name' => 'create table'],
                            ['version' => '002', 'name' => 'add index'],
                        ],
                        'applied_migrations' => [
                            ['version' => '001', 'name' => 'create table'],
                        ],
                        'applied_in_run' => [],
                        'pending_versions' => ['002'],
                        'pending_migrations' => [
                            ['version' => '002', 'name' => 'add index'],
                        ],
                    ];
                }

                public function migrate(): array
                {
                    return [];
                }
            },
        ];

        $application = new MigrationCliApplication();
        ob_start();
        $application->run(['migrate.php', 'status']);
        $output = ob_get_clean();

        self::assertIsString($output);
        self::assertStringContainsString('Discovered: 2', $output);
        self::assertStringContainsString('Applied migrations: 001 (create table)', $output);
        self::assertStringContainsString('Pending migrations: 002 (add index)', $output);
    }
}
