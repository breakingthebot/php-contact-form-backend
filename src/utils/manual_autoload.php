<?php
// src/utils/manual_autoload.php
// Provides a simple PSR-4 style autoloader for local wiring without Composer.
// Connects to: src/config/container.php
// Created: 2026-06-28

declare(strict_types=1);

spl_autoload_register(
    /**
     * Loads application classes from the src directory.
     *
     * @param string $class Fully qualified class name.
     */
    static function (string $class): void {
        $prefix = 'App\\';

        if (!str_starts_with($class, $prefix)) {
            return;
        }

        $relativeClass = substr($class, strlen($prefix));
        $path = dirname(__DIR__) . '/' . str_replace('\\', '/', $relativeClass) . '.php';

        if (is_file($path)) {
            require_once $path;
        }
    }
);
