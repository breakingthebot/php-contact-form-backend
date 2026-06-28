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
    /** @var callable|null */
    private readonly mixed $stdoutWriter;

    /** @var callable|null */
    private readonly mixed $stderrWriter;

    /**
     * Initializes the migration CLI application.
     *
     * @param callable|null $stdoutWriter Optional writer for standard output.
     * @param callable|null $stderrWriter Optional writer for error output.
     */
    public function __construct(?callable $stdoutWriter = null, ?callable $stderrWriter = null)
    {
        $this->stdoutWriter = $stdoutWriter;
        $this->stderrWriter = $stderrWriter;
    }

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
                $this->writeError("Unknown command: {$command}" . PHP_EOL);
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
        $this->writeOutput('Migrations: ' . ($status['summary'] ?? 'unknown') . PHP_EOL);
        $this->writeOutput('Discovered: ' . (string) ($status['discovered_count'] ?? 0) . PHP_EOL);
        $this->writeOutput('Applied: ' . (string) ($status['applied_count'] ?? 0) . PHP_EOL);
        $this->writeOutput('Pending: ' . (string) ($status['pending_count'] ?? 0) . PHP_EOL);

        /** @var array<int, array<string, string>> $discovered */
        $discovered = $status['discovered_migrations'] ?? [];
        /** @var array<int, array<string, string>> $applied */
        $applied = $status['applied_migrations'] ?? [];
        /** @var array<int, array<string, string>> $appliedInRun */
        $appliedInRun = $status['applied_in_run'] ?? [];
        /** @var array<int, array<string, string>> $pending */
        $pending = $status['pending_migrations'] ?? [];

        $this->writeMigrationList('Discovered migrations', $discovered);
        $this->writeMigrationList('Applied migrations', $applied);
        $this->writeMigrationList('Applied in this run', $appliedInRun);

        if ($pending === []) {
            $this->writeOutput('Pending migrations: none' . PHP_EOL);
            return;
        }

        $this->writeMigrationList('Pending migrations', $pending);
    }

    /**
     * Writes the CLI help output.
     *
     * @return void
     */
    private function writeHelp(): void
    {
        $this->writeOutput('Usage: php bin/migrate.php [migrate|status|help]' . PHP_EOL);
    }

    /**
     * Writes a list of migration details to the terminal.
     *
     * @param string $label Section label.
     * @param array<int, array<string, string>> $migrations Migration detail rows.
     *
     * @return void
     */
    private function writeMigrationList(string $label, array $migrations): void
    {
        if ($migrations === []) {
            $this->writeOutput($label . ': none' . PHP_EOL);
            return;
        }

        $values = array_map(
            static fn (array $migration): string => sprintf(
                '%s (%s)',
                $migration['version'] ?? 'unknown',
                $migration['name'] ?? 'unnamed'
            ),
            $migrations
        );

        $this->writeOutput($label . ': ' . implode(', ', $values) . PHP_EOL);
    }

    /**
     * Writes text to standard output.
     *
     * @param string $text Output text.
     *
     * @return void
     */
    private function writeOutput(string $text): void
    {
        if ($this->stdoutWriter !== null) {
            ($this->stdoutWriter)($text);
            return;
        }

        fwrite(STDOUT, $text);
    }

    /**
     * Writes text to standard error.
     *
     * @param string $text Output text.
     *
     * @return void
     */
    private function writeError(string $text): void
    {
        if ($this->stderrWriter !== null) {
            ($this->stderrWriter)($text);
            return;
        }

        fwrite(STDERR, $text);
    }
}
