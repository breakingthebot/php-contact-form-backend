<?php
// src/services/DatabaseConnectionFactoryInterface.php
// Defines the contract for creating PDO connections.
// Connects to: src/services/DatabaseConnectionFactory.php, src/services/Health/DatabaseHealthChecker.php
// Created: 2026-06-28

declare(strict_types=1);

namespace App\Services;

use PDO;

interface DatabaseConnectionFactoryInterface
{
    /**
     * Creates a PDO connection.
     *
     * @return PDO
     */
    public function create(): PDO;
}
