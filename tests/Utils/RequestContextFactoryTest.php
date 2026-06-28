<?php
// tests/Utils/RequestContextFactoryTest.php
// Verifies request context creation from HTTP server globals.
// Connects to: src/utils/RequestContextFactory.php
// Created: 2026-06-28

declare(strict_types=1);

namespace Tests\Utils;

use App\Utils\RequestContextFactory;
use PHPUnit\Framework\TestCase;

final class RequestContextFactoryTest extends TestCase
{
    /**
     * Restores server state after each test.
     *
     * @return void
     */
    protected function tearDown(): void
    {
        $_SERVER = [];
    }

    /**
     * Confirms an incoming X-Request-Id is preserved.
     *
     * @return void
     */
    public function testCreateFromGlobalsUsesIncomingRequestId(): void
    {
        $_SERVER['HTTP_X_REQUEST_ID'] = 'req-123';
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SERVER['REQUEST_URI'] = '/contact.php';
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';

        $context = (new RequestContextFactory())->createFromGlobals();

        self::assertSame('req-123', $context->requestId);
        self::assertSame('POST', $context->method);
        self::assertSame('/contact.php', $context->path);
        self::assertSame('127.0.0.1', $context->ipAddress);
        self::assertGreaterThan(0, $context->startedAt);
    }

    /**
     * Confirms a request ID is generated when one is not supplied.
     *
     * @return void
     */
    public function testCreateFromGlobalsGeneratesRequestIdWhenMissing(): void
    {
        $context = (new RequestContextFactory())->createFromGlobals();

        self::assertMatchesRegularExpression('/^[a-f0-9]{32}$/', $context->requestId);
        self::assertGreaterThanOrEqual(0, $context->durationMilliseconds());
    }
}
