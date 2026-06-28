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
use App\Services\Version\VersionInfoService;
use App\Utils\JsonResponder;
use App\Utils\RequestContextFactory;
use App\Utils\RequestInputReaderInterface;
use App\Utils\RequestLogger;
use PHPUnit\Framework\TestCase;

final class PublicEntrypointsTest extends TestCase
{
    private string $logPath;

    /**
     * Sets up a dedicated log file for each integration test run.
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->logPath = dirname(__DIR__, 2) . '/logs/integration-test.log';
        @unlink($this->logPath);
    }

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
        @unlink($this->logPath);
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
            RequestContextFactory::class => new RequestContextFactory(),
            RequestLogger::class => new RequestLogger($this->logPath),
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
            RequestContextFactory::class => new RequestContextFactory(),
            RequestLogger::class => new RequestLogger($this->logPath),
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

        $logRecord = $this->readLastLogRecord();
        self::assertSame('Completed contact request.', $logRecord['message']);
        self::assertSame(201, $logRecord['context']['status_code']);
        self::assertIsNumeric($logRecord['context']['duration_ms']);
        self::assertGreaterThanOrEqual(0, (float) $logRecord['context']['duration_ms']);
    }

    /**
     * Confirms the contact entrypoint returns a forbidden response for disallowed origins.
     *
     * @return void
     */
    public function testContactEntrypointReturnsForbiddenWhenGuardBlocksOrigin(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
        $_SERVER['HTTP_ORIGIN'] = 'https://evil.example.com';

        $GLOBALS['app_container_override'] = [
            JsonResponder::class => new JsonResponder(),
            RequestContextFactory::class => new RequestContextFactory(),
            RequestLogger::class => new RequestLogger($this->logPath),
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
                public function guard(array $payload, ?string $ipAddress, ?string $origin): object
                {
                    return new \App\Models\SubmissionResult(
                        403,
                        'error',
                        'Request origin is not allowed.'
                    );
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

        self::assertSame(403, $response['status_code']);
        self::assertSame(
            [
                'status' => 'error',
                'message' => 'Request origin is not allowed.',
            ],
            $response['payload']
        );
    }

    /**
     * Confirms the contact entrypoint returns a rate-limit response when the guard blocks the request.
     *
     * @return void
     */
    public function testContactEntrypointReturnsRateLimitedWhenGuardBlocksRequest(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';

        $GLOBALS['app_container_override'] = [
            JsonResponder::class => new JsonResponder(),
            RequestContextFactory::class => new RequestContextFactory(),
            RequestLogger::class => new RequestLogger($this->logPath),
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
                public function guard(array $payload, ?string $ipAddress, ?string $origin): object
                {
                    return new \App\Models\SubmissionResult(
                        429,
                        'error',
                        'Too many requests. Please try again later.'
                    );
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

        self::assertSame(429, $response['status_code']);
        self::assertSame(
            [
                'status' => 'error',
                'message' => 'Too many requests. Please try again later.',
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
            RequestContextFactory::class => new RequestContextFactory(),
            RequestLogger::class => new RequestLogger($this->logPath),
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

        $logRecord = $this->readLastLogRecord();
        self::assertSame('Completed health request.', $logRecord['message']);
        self::assertSame(200, $logRecord['context']['status_code']);
        self::assertIsNumeric($logRecord['context']['duration_ms']);
    }

    /**
     * Confirms the version entrypoint returns build metadata.
     *
     * @return void
     */
    public function testVersionEntrypointReturnsBuildMetadata(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';

        $GLOBALS['app_container_override'] = [
            JsonResponder::class => new JsonResponder(),
            RequestContextFactory::class => new RequestContextFactory(),
            RequestLogger::class => new RequestLogger($this->logPath),
            VersionInfoService::class => new class {
                public function get(): object
                {
                    return new \App\Models\VersionInfoReport(
                        200,
                        '0.13.0',
                        'abc1234',
                        'testing'
                    );
                }
            },
        ];

        $response = $this->runPublicEntrypoint(dirname(__DIR__, 2) . '/public/version.php');

        self::assertSame(200, $response['status_code']);
        self::assertSame(
            [
                'version' => '0.13.0',
                'revision' => 'abc1234',
                'environment' => 'testing',
            ],
            $response['payload']
        );

        $logRecord = $this->readLastLogRecord();
        self::assertSame('Completed version request.', $logRecord['message']);
        self::assertSame('0.13.0', $logRecord['context']['app_version']);
        self::assertIsNumeric($logRecord['context']['duration_ms']);
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

    /**
     * Reads the last structured log record written during the test.
     *
     * @return array<string, mixed>
     */
    private function readLastLogRecord(): array
    {
        $contents = file_get_contents($this->logPath);
        self::assertIsString($contents);

        $lines = array_values(
            array_filter(
                array_map('trim', explode(PHP_EOL, $contents)),
                static fn (string $line): bool => $line !== ''
            )
        );

        self::assertNotEmpty($lines);

        /** @var array<string, mixed> $decoded */
        $decoded = json_decode($lines[count($lines) - 1], true);

        return $decoded;
    }
}
