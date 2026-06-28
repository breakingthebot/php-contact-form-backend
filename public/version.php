<?php
// public/version.php
// HTTP entrypoint for build and environment metadata.
// Connects to: src/config/bootstrap.php, src/controllers/VersionController.php
// Created: 2026-06-28

declare(strict_types=1);

use App\Controllers\VersionController;

require dirname(__DIR__) . '/src/config/bootstrap.php';

$controller = new VersionController();
$controller->handle();
