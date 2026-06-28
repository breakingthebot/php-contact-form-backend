<?php
// public/health.php
// HTTP entrypoint for operational readiness checks.
// Connects to: src/config/bootstrap.php, src/controllers/HealthController.php
// Created: 2026-06-28

declare(strict_types=1);

use App\Controllers\HealthController;

require dirname(__DIR__) . '/src/config/bootstrap.php';

$controller = new HealthController();
$controller->handle();
