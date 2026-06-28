<?php
// tests/Services/Health/DatabaseHealthCheckerTest.php
// Verifies database readiness reporting for the health endpoint.
// Connects to: src/services/Health/DatabaseHealthChecker.php
// Created: 2026-06-28

declare(strict_types=1);

namespace Tests\Services\Health;

use App\Services\DatabaseConnectionFactoryInterface;
use App\Services\Health\DatabaseHealthChecker;
use App\Utils\RequestLogger;
use PDO;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class DatabaseHealthCheckerTest extends TestCase
{
    /**
     * Confirms the checker reports success when the database probe works.
     *
     * @return void
     */
    public function testCheckReturnsOkWhenDatabaseProbeSucceeds(): void
    {
        /** @var DatabaseConnectionFactoryInterface&MockObject $factory */
        $factory = $this->createMock(DatabaseConnectionFactoryInterface::class);
        $pdo = $this->createMock(PDO::class);

        $factory->expects($this->once())
            ->method('create')
            ->willReturn($pdo);
        $pdo->expects($this->once())
            ->method('query')
            ->with('SELECT 1');

        $checker = new DatabaseHealthChecker(
            $factory,
            new RequestLogger(dirname(__DIR__, 3) . '/logs/test.log')
        );

        self::assertSame(
            [
                'status' => 'ok',
                'message' => 'Database connection is healthy.',
            ],
            $checker->check()
        );
    }

    /**
     * Confirms the checker reports failure when the database probe throws.
     *
     * @return void
     */
    public function testCheckReturnsErrorWhenDatabaseProbeFails(): void
    {
        /** @var DatabaseConnectionFactoryInterface&MockObject $factory */
        $factory = $this->createMock(DatabaseConnectionFactoryInterface::class);

        $factory->expects($this->once())
            ->method('create')
            ->willThrowException(new RuntimeException('db unavailable'));

        $checker = new DatabaseHealthChecker(
            $factory,
            new RequestLogger(dirname(__DIR__, 3) . '/logs/test.log')
        );

        self::assertSame(
            [
                'status' => 'error',
                'message' => 'Database connection failed.',
            ],
            $checker->check()
        );
    }
}
