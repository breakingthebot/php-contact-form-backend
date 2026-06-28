<?php
// src/services/DatabaseConnectionFactory.php
// Creates configured PDO connections for MySQL persistence.
// Connects to: src/utils/Environment.php, src/services/ContactSubmissionRepository.php
// Created: 2026-06-28

declare(strict_types=1);

namespace App\Services;

use App\Utils\Environment;
use PDO;

final class DatabaseConnectionFactory
{
    /**
     * Initializes the connection factory.
     *
     * @param Environment $environment Reads database settings from environment variables.
     */
    public function __construct(
        private readonly Environment $environment
    ) {
    }

    /**
     * Creates a PDO connection.
     *
     * @return PDO
     */
    public function create(): PDO
    {
        $host = $this->environment->requireValue('DB_HOST');
        $port = $this->environment->get('DB_PORT', '3306');
        $database = $this->environment->requireValue('DB_NAME');
        $user = $this->environment->requireValue('DB_USER');
        $password = $this->environment->requireValue('DB_PASSWORD');
        $charset = $this->environment->get('DB_CHARSET', 'utf8mb4');

        $dsn = sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=%s',
            $host,
            $port,
            $database,
            $charset
        );

        return new PDO(
            $dsn,
            $user,
            $password,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]
        );
    }
}
