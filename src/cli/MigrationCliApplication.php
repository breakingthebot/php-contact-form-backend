<?php
// src/cli/MigrationCliApplication.php
// Runs migration commands from the command line.
// Connects to: src/services/Migrations/MigrationService.php
// Created: 2026-06-28

declare(strict_types=1);

namespace App\Cli;

use App\Services\Migrations\MigrationService;

final class MigrationCliApplication
{
    /**
     * Executes the migration CLI command.
     *
     * @param array<int, string> $argv Raw CLI arguments.
     *
     * @return void
     */
    public function run(array $argv): void
    {
        global $container;

        /** @var MigrationService $service */
        $service = $container[MigrationService::class];
        $command = $argv[1] ?? 'migrate';

        switch ($command) {
            case 'status':
                $status = $service->status();
                $this->writeStatus($status);
                return;

            case 'migrate':
                $status = $service->migrate();
                $this->writeStatus($status);
                return;

            case '--help':
            case '-h':
            case 'help':
                $this->writeHelp();
                return;

            default:
                fwrite(STDERR, "Unknown command: {$command}" . PHP_EOL);
                $this->writeHelp();
                exit(1);
        }
    }

    /**
     * Writes migration status output to the terminal.
     *
     * @param array<string, mixed> $status Migration status payload.
     *
     * @return void
     */
    private function writeStatus(array $status): void
    {
        fwrite(STDOUT, 'Migrations: ' . ($status['summary'] ?? 'unknown') . PHP_EOL);
        fwrite(STDOUT, 'Applied: ' . (string) ($status['applied_count'] ?? 0) . PHP_EOL);
        fwrite(STDOUT, 'Pending: ' . (string) ($status['pending_count'] ?? 0) . PHP_EOL);

        /** @var array<int, string> $pending */
        $pending = $status['pending_versions'] ?? [];

        if ($pending === []) {
            fwrite(STDOUT, 'Pending versions: none' . PHP_EOL);
            return;
        }

        fwrite(STDOUT, 'Pending versions: ' . implode(', ', $pending) . PHP_EOL);
    }

    /**
     * Writes the CLI help output.
     *
     * @return void
     */
    private function writeHelp(): void
    {
        fwrite(STDOUT, 'Usage: php bin/migrate.php [migrate|status|help]' . PHP_EOL);
    }
}
