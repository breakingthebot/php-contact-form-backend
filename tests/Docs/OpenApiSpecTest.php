<?php
// tests/Docs/OpenApiSpecTest.php
// Verifies the OpenAPI document still describes the current public endpoints.
// Connects to: docs/openapi.yaml, src/config/routes.php
// Created: 2026-06-28

declare(strict_types=1);

namespace Tests\Docs;

use PHPUnit\Framework\TestCase;

final class OpenApiSpecTest extends TestCase
{
    /**
     * Confirms the OpenAPI spec documents the public routes and core responses.
     *
     * @return void
     */
    public function testOpenApiSpecDocumentsPublicEndpoints(): void
    {
        $spec = file_get_contents(dirname(__DIR__, 2) . '/docs/openapi.yaml');

        self::assertIsString($spec);
        self::assertStringContainsString('/contact.php:', $spec);
        self::assertStringContainsString('/health.php:', $spec);
        self::assertStringContainsString("operationId: submitContactRequest", $spec);
        self::assertStringContainsString("operationId: checkHealth", $spec);
        self::assertStringContainsString("'201':", $spec);
        self::assertStringContainsString("'503':", $spec);
    }
}
