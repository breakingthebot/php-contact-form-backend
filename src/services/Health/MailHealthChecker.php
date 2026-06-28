<?php
// src/services/Health/MailHealthChecker.php
// Verifies outbound mail configuration readiness for the health endpoint.
// Connects to: src/utils/Environment.php, src/utils/RequestLogger.php
// Created: 2026-06-28

declare(strict_types=1);

namespace App\Services\Health;

use App\Utils\Environment;
use App\Utils\RequestLogger;
use Symfony\Component\Mailer\Transport;
use Throwable;

final class MailHealthChecker implements HealthCheckerInterface
{
    /**
     * Initializes the mail health checker.
     *
     * @param Environment $environment Reads outbound mail configuration.
     * @param RequestLogger $logger Records readiness failures.
     */
    public function __construct(
        private readonly Environment $environment,
        private readonly RequestLogger $logger
    ) {
    }

    /**
     * Returns the report key for this checker.
     *
     * @return string
     */
    public function key(): string
    {
        return 'mail';
    }

    /**
     * Verifies whether outbound mail configuration is ready.
     *
     * @return array<string, string>
     */
    public function check(): array
    {
        $mailerDsn = $this->environment->get('MAILER_DSN');

        if ($mailerDsn !== null && trim($mailerDsn) !== '') {
            try {
                Transport::fromDsn($mailerDsn);

                return [
                    'status' => 'ok',
                    'message' => 'SMTP mail transport is configured.',
                ];
            } catch (Throwable $exception) {
                $this->logger->error(
                    'Mail health check failed due to invalid SMTP configuration.',
                    ['exception' => $exception->getMessage()]
                );

                return [
                    'status' => 'error',
                    'message' => 'SMTP mail transport configuration is invalid.',
                ];
            }
        }

        try {
            $this->environment->requireValue('CONTACT_TO_EMAIL');
            $this->environment->requireValue('CONTACT_FROM_EMAIL');

            return [
                'status' => 'ok',
                'message' => 'Native mail fallback is configured.',
            ];
        } catch (Throwable $exception) {
            $this->logger->error(
                'Mail health check failed due to missing native mail configuration.',
                ['exception' => $exception->getMessage()]
            );

            return [
                'status' => 'error',
                'message' => 'Mail configuration is incomplete.',
            ];
        }
    }
}
