<?php
// src/utils/RequestInputReaderInterface.php
// Defines the contract for reading raw HTTP request payloads.
// Connects to: src/controllers/ContactController.php, src/utils/NativeRequestInputReader.php
// Created: 2026-06-28

declare(strict_types=1);

namespace App\Utils;

interface RequestInputReaderInterface
{
    /**
     * Reads the raw request body.
     *
     * @return string
     */
    public function read(): string;
}
