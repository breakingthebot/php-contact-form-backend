<?php
// tests/Integration/PublicEntrypointsTest.php
// Verifies the public PHP entrypoints return the expected JSON payloads.
// Connects to: public/contact.php, public/health.php
// Created: 2026-06-28

declare(strict_types=1);

namespace Tests\Integration;

use App\Services\ContactFormService;
use App\Services\Health\HealthCheckService;
use App\Services\Security\RequestGuard;
use App\Utils\JsonResponder;
use App\Utils\RequestInputReaderInterface;
use PHPUnit\Framework\TestCase;

final class PublicEntrypointsTest extends TestCase
{
    /**
     * Clears global request state after each test run.
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($GLOBALS['app_container_override']);
        $_SERVER = [];
        http_response_code(200);
    }

    /**
     * Confirms the contact entrypoint rejects unsupported HTTP methods.
     *
     * @return void
     */
    public function testContactEntrypointReturnsMethodNotAllowedForGet(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $GLOBALS['app_container_override'] = [
            JsonResponder::class => new JsonResponder(),
            RequestInputReaderInterface::class => new class implements RequestInputReaderInterface {
                public function read(): string
                {
                    return '';
                }
            },
            RequestGuard::class => new class {
                public function guard(array $payload, ?string $ipAddress, ?string $origin): null
                {
                    return null;
                }
            },
            ContactFormService::class => new class {
                public function submit(object $submission): object
                {
                    throw new \RuntimeException('Should not be called.');
                }
            },
        ];

        $response = $this->runPublicEntrypoint(dirname(__DIR__, 2) . '/public/contact.php');

        self::assertSame(405, $response['status_code']);
        self::assertSame(
            [
                'status' => 'error',
                'message' => 'Method not allowed.',
            ],
            $response['payload']
        );
    }

    /**
     * Confirms the contact entrypoint returns the service payload for a valid POST.
     *
     * @return void
     */
    public function testContactEntrypointReturnsJsonForValidPost(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';

        $GLOBALS['app_container_override'] = [
            JsonResponder::class => new JsonResponder(),
            RequestInputReaderInterface::class => new class implements RequestInputReaderInterface {
                public function read(): string
                {
                    return json_encode(
                        [
                            'name' => 'Ada Lovelace',
                            'email' => 'ada@example.com',
                            'message' => 'This message is valid for integration testing.',
                        ],
                        JSON_UNESCAPED_SLASHES
                    ) ?: '';
                }
            },
            RequestGuard::class => new class {
                public function guard(array $payload, ?string $ipAddress, ?string $origin): null
                {
                    return null;
                }
            },
            ContactFormService::class => new class {
                public function submit(object $submission): object
                {
                    return new \App\Models\SubmissionResult(
                        201,
                        'success',
                        'Contact request received successfully.'
                    );
                }
            },
        ];

        $response = $this->runPublicEntrypoint(dirname(__DIR__, 2) . '/public/contact.php');

        self::assertSame(201, $response['status_code']);
        self::assertSame(
            [
                'status' => 'success',
                'message' => 'Contact request received successfully.',
            ],
            $response['payload']
        );
    }

    /**
     * Confirms the health entrypoint returns the structured health report.
     *
     * @return void
     */
    public function testHealthEntrypointReturnsStructuredReport(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';

        $GLOBALS['app_container_override'] = [
            JsonResponder::class => new JsonResponder(),
            HealthCheckService::class => new class {
                public function check(): object
                {
                    return new \App\Models\HealthCheckReport(
                        200,
                        'ok',
                        [
                            'database' => [
                                'status' => 'ok',
                                'message' => 'Database connection is healthy.',
                            ],
                            'mail' => [
                                'status' => 'ok',
                                'message' => 'SMTP mail transport is configured.',
                            ],
                        ]
                    );
                }
            },
        ];

        $response = $this->runPublicEntrypoint(dirname(__DIR__, 2) . '/public/health.php');

        self::assertSame(200, $response['status_code']);
        self::assertSame('ok', $response['payload']['status']);
        self::assertSame('ok', $response['payload']['checks']['database']['status']);
        self::assertSame('ok', $response['payload']['checks']['mail']['status']);
    }

    /**
     * Executes a public PHP entrypoint and captures its JSON response.
     *
     * @param string $entrypoint Absolute path to the public PHP file.
     *
     * @return array<string, mixed>
     */
    private function runPublicEntrypoint(string $entrypoint): array
    {
        http_response_code(200);
        ob_start();
        try {
            require $entrypoint;
            $output = ob_get_clean();
        } catch (\Throwable $exception) {
            ob_end_clean();
            throw $exception;
        }

        return [
            'status_code' => http_response_code(),
            'payload' => json_decode((string) $output, true),
        ];
    }
}
