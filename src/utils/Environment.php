<?php
// src/utils/Environment.php
// Loads environment variables from process state and optional .env files.
// Connects to: src/config/container.php, src/services/DatabaseConnectionFactory.php
// Created: 2026-06-28

declare(strict_types=1);

namespace App\Utils;

use RuntimeException;

final class Environment
{
    /** @var array<string, string> */
    private array $values = [];

    /**
     * Initializes the environment loader.
     *
     * @param string|null $dotenvPath Optional path to a .env file.
     */
    public function __construct(?string $dotenvPath = null)
    {
        $this->values = array_merge($_ENV, $_SERVER);

        if ($dotenvPath !== null && is_file($dotenvPath)) {
            $this->loadDotenvFile($dotenvPath);
        }
    }

    /**
     * Reads an environment variable with an optional default value.
     *
     * @param string $key Variable name.
     * @param string|null $default Default value when missing.
     *
     * @return string|null
     */
    public function get(string $key, ?string $default = null): ?string
    {
        return $this->values[$key] ?? $default;
    }

    /**
     * Reads a required environment variable.
     *
     * @param string $key Variable name.
     *
     * @return string
     */
    public function requireValue(string $key): string
    {
        $value = $this->get($key);

        if ($value === null || $value === '') {
            throw new RuntimeException(sprintf('Missing required environment variable: %s', $key));
        }

        return $value;
    }

    /**
     * Loads variables from a simple KEY=VALUE dotenv file.
     *
     * @param string $dotenvPath Dotenv file path.
     *
     * @return void
     */
    private function loadDotenvFile(string $dotenvPath): void
    {
        $lines = file($dotenvPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        if ($lines === false) {
            return;
        }

        foreach ($lines as $line) {
            $trimmed = trim($line);

            if ($trimmed === '' || str_starts_with($trimmed, '#')) {
                continue;
            }

            [$key, $value] = array_pad(explode('=', $trimmed, 2), 2, '');
            $this->values[trim($key)] = trim($value);
        }
    }
}
