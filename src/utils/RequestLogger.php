<?php
// src/utils/RequestLogger.php
// Writes structured JSON log lines for operational events.
// Connects to: src/services/ContactFormService.php, src/services/Mail/NativeMailTransport.php
// Created: 2026-06-28

declare(strict_types=1);

namespace App\Utils;

final class RequestLogger
{
    /**
     * Initializes the request logger.
     *
     * @param string $logPath Destination log file path.
     */
    public function __construct(
        private readonly string $logPath
    ) {
    }

    /**
     * Writes an informational log entry.
     *
     * @param string $message Log message.
     * @param array<string, mixed> $context Structured context payload.
     *
     * @return void
     */
    public function info(string $message, array $context = []): void
    {
        $this->write('INFO', $message, $context);
    }

    /**
     * Writes an error log entry.
     *
     * @param string $message Log message.
     * @param array<string, mixed> $context Structured context payload.
     *
     * @return void
     */
    public function error(string $message, array $context = []): void
    {
        $this->write('ERROR', $message, $context);
    }

    /**
     * Writes a structured log line to disk.
     *
     * @param string $level Log level.
     * @param string $message Log message.
     * @param array<string, mixed> $context Structured context payload.
     *
     * @return void
     */
    private function write(string $level, string $message, array $context): void
    {
        $directory = dirname($this->logPath);

        if (!is_dir($directory)) {
            mkdir($directory, 0777, true);
        }

        $record = [
            'timestamp' => gmdate('c'),
            'level' => $level,
            'message' => $message,
            'context' => $context,
        ];

        file_put_contents(
            $this->logPath,
            json_encode($record, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . PHP_EOL,
            FILE_APPEND
        );
    }
}
