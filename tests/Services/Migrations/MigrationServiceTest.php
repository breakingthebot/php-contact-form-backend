<?php
// tests/Services/Migrations/MigrationServiceTest.php
// Verifies migration status reporting and pending migration application.
// Connects to: src/services/Migrations/MigrationService.php
// Created: 2026-06-28

declare(strict_types=1);

namespace Tests\Services\Migrations;

use App\Models\MigrationDefinition;
use App\Services\Migrations\MigrationFileLoader;
use App\Services\Migrations\MigrationRepositoryInterface;
use App\Services\Migrations\MigrationService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class MigrationServiceTest extends TestCase
{
    /**
     * Confirms status reports pending migrations that have not been applied.
     *
     * @return void
     */
    public function testStatusReportsPendingMigrations(): void
    {
        $loader = $this->createMock(MigrationFileLoader::class);
        /** @var MigrationRepositoryInterface&MockObject $repository */
        $repository = $this->createMock(MigrationRepositoryInterface::class);

        $loader->expects($this->once())
            ->method('load')
            ->willReturn(
                [
                    new MigrationDefinition('001', 'create table', '/tmp/001.sql', 'SELECT 1;'),
                    new MigrationDefinition('002', 'add index', '/tmp/002.sql', 'SELECT 2;'),
                ]
            );

        $repository->expects($this->once())
            ->method('ensureTableExists');
        $repository->expects($this->once())
            ->method('appliedVersions')
            ->willReturn(['001']);

        $service = new MigrationService($loader, $repository);
        $status = $service->status();

        self::assertSame('pending migrations', $status['summary']);
        self::assertSame(1, $status['applied_count']);
        self::assertSame(1, $status['pending_count']);
        self::assertSame(['002'], $status['pending_versions']);
    }

    /**
     * Confirms migrate applies only the migrations that are still pending.
     *
     * @return void
     */
    public function testMigrateAppliesOnlyPendingMigrations(): void
    {
        $loader = $this->createMock(MigrationFileLoader::class);
        /** @var MigrationRepositoryInterface&MockObject $repository */
        $repository = $this->createMock(MigrationRepositoryInterface::class);
        $pendingMigration = new MigrationDefinition('002', 'add index', '/tmp/002.sql', 'SELECT 2;');

        $loader->expects($this->once())
            ->method('load')
            ->willReturn(
                [
                    new MigrationDefinition('001', 'create table', '/tmp/001.sql', 'SELECT 1;'),
                    $pendingMigration,
                ]
            );

        $repository->expects($this->once())
            ->method('ensureTableExists');
        $repository->expects($this->once())
            ->method('appliedVersions')
            ->willReturn(['001']);
        $repository->expects($this->once())
            ->method('apply')
            ->with($pendingMigration);

        $service = new MigrationService($loader, $repository);
        $status = $service->migrate();

        self::assertSame('applied pending migrations', $status['summary']);
        self::assertSame(2, $status['applied_count']);
        self::assertSame(0, $status['pending_count']);
    }
}
