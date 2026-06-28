<?php
// tests/Utils/ContactSubmissionValidatorTest.php
// Verifies the validator returns expected errors for hostile input.
// Connects to: src/utils/ContactSubmissionValidator.php
// Created: 2026-06-28

declare(strict_types=1);

namespace Tests\Utils;

use App\Models\ContactSubmission;
use App\Utils\ContactSubmissionValidator;
use PHPUnit\Framework\TestCase;

final class ContactSubmissionValidatorTest extends TestCase
{
    /**
     * Confirms the validator accepts a well-formed submission.
     *
     * @return void
     */
    public function testValidateReturnsNoErrorsForValidSubmission(): void
    {
        $errors = ContactSubmissionValidator::validate(
            new ContactSubmission(
                'Grace Hopper',
                'grace@example.com',
                'This message is long enough to pass validation.',
                '127.0.0.1'
            )
        );

        self::assertSame([], $errors);
    }

    /**
     * Confirms the validator reports all critical field errors.
     *
     * @return void
     */
    public function testValidateReturnsErrorsForInvalidSubmission(): void
    {
        $errors = ContactSubmissionValidator::validate(
            new ContactSubmission('', 'not-an-email', 'short', null)
        );

        self::assertContains('Name is required.', $errors);
        self::assertContains('Email format is invalid.', $errors);
        self::assertContains('Message must be at least 10 characters.', $errors);
    }
}
