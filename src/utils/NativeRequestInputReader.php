<?php
// src/utils/NativeRequestInputReader.php
// Reads the raw HTTP request body from php://input.
// Connects to: src/utils/RequestInputReaderInterface.php, src/controllers/ContactController.php
// Created: 2026-06-28

declare(strict_types=1);

namespace App\Utils;

final class NativeRequestInputReader implements RequestInputReaderInterface
{
    /**
     * Reads the raw request body from the PHP input stream.
     *
     * @return string
     */
    public function read(): string
    {
        return (string) file_get_contents('php://input');
    }
}
