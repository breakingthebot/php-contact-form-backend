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
        $relativePath = str_replace('\\', '/', $relativeClass) . '.php';
        $exactPath = dirname(__DIR__) . '/' . $relativePath;

        if (is_file($exactPath)) {
            require_once $exactPath;
            return;
        }

        $segments = explode('/', $relativePath);

        if ($segments === []) {
            return;
        }

        $segments[0] = strtolower($segments[0]);
        $path = dirname(__DIR__) . '/' . implode('/', $segments);

        if (is_file($path)) {
            require_once $path;
        }
    }
);
