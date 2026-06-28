<?php
// src/config/bootstrap.php
// Loads shared application wiring and autoloading for web requests.
// Connects to: composer.json, src/config/container.php
// Created: 2026-06-28

declare(strict_types=1);

if (is_file(dirname(__DIR__, 2) . '/vendor/autoload.php')) {
    require dirname(__DIR__, 2) . '/vendor/autoload.php';
}

$container = require __DIR__ . '/container.php';
