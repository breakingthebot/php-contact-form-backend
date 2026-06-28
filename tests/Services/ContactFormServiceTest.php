<?php
// tests/Services/ContactFormServiceTest.php
// Verifies contact form service success and validation failure behavior.
// Connects to: src/services/ContactFormService.php
// Created: 2026-06-28

declare(strict_types=1);

namespace Tests\Services;

use App\Models\ContactSubmission;
use App\Services\ContactFormService;
use App\Services\ContactSubmissionRepositoryInterface;
use App\Services\Mail\MailTransportInterface;
use App\Utils\Environment;
use App\Utils\RequestLogger;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class ContactFormServiceTest extends TestCase
{
    /**
     * Confirms a valid submission is persisted and mailed.
     *
     * @return void
     */
    public function testSubmitReturnsSuccessForValidSubmission(): void
    {
        /** @var ContactSubmissionRepositoryInterface&MockObject $repository */
        $repository = $this->createMock(ContactSubmissionRepositoryInterface::class);
        $mailTransport = $this->createMock(MailTransportInterface::class);
        $logger = new RequestLogger(dirname(__DIR__, 2) . '/logs/test.log');
        $environment = new Environment(null);

        $repository->expects($this->once())
            ->method('save');
        $mailTransport->expects($this->once())
            ->method('send');

        $service = new ContactFormService(
            $repository,
            $mailTransport,
            $logger,
            $environment
        );

        $result = $service->submit(
            new ContactSubmission(
                'Ada Lovelace',
                'ada@example.com',
                'This is a valid contact form message.',
                '127.0.0.1'
            )
        );

        self::assertSame(201, $result->statusCode);
        self::assertSame('success', $result->status);
    }

    /**
     * Confirms invalid submissions return explicit validation errors.
     *
     * @return void
     */
    public function testSubmitReturnsValidationErrorsForInvalidSubmission(): void
    {
        /** @var ContactSubmissionRepositoryInterface&MockObject $repository */
        $repository = $this->createMock(ContactSubmissionRepositoryInterface::class);
        /** @var MailTransportInterface&MockObject $mailTransport */
        $mailTransport = $this->createMock(MailTransportInterface::class);
        $logger = new RequestLogger(dirname(__DIR__, 2) . '/logs/test.log');
        $environment = new Environment(null);

        $repository->expects($this->never())
            ->method('save');
        $mailTransport->expects($this->never())
            ->method('send');

        $service = new ContactFormService(
            $repository,
            $mailTransport,
            $logger,
            $environment
        );

        $result = $service->submit(
            new ContactSubmission('', 'invalid-email', 'short', null)
        );

        self::assertSame(422, $result->statusCode);
        self::assertNotEmpty($result->errors);
    }
}
