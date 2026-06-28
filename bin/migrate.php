<?php
// bin/migrate.php
// CLI entrypoint for database migration commands.
// Connects to: src/config/bootstrap.php, src/cli/MigrationCliApplication.php
// Created: 2026-06-28

declare(strict_types=1);

use App\Cli\MigrationCliApplication;

require dirname(__DIR__) . '/src/config/bootstrap.php';

$application = new MigrationCliApplication();
$application->run($argv);
