<?php
// tests/Services/Mail/ContactNotificationContentBuilderTest.php
// Verifies notification payload generation for outbound contact emails.
// Connects to: src/services/Mail/ContactNotificationContentBuilder.php
// Created: 2026-06-28

declare(strict_types=1);

namespace Tests\Services\Mail;

use App\Models\ContactSubmission;
use App\Services\Mail\ContactNotificationContentBuilder;
use App\Utils\Environment;
use PHPUnit\Framework\TestCase;

final class ContactNotificationContentBuilderTest extends TestCase
{
    /**
     * Restores mail-related environment variables after each test.
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset(
            $_ENV['CONTACT_TO_EMAIL'],
            $_ENV['CONTACT_FROM_EMAIL'],
            $_ENV['CONTACT_FROM_NAME'],
            $_ENV['CONTACT_SUBJECT_PREFIX'],
            $_SERVER['CONTACT_TO_EMAIL'],
            $_SERVER['CONTACT_FROM_EMAIL'],
            $_SERVER['CONTACT_FROM_NAME'],
            $_SERVER['CONTACT_SUBJECT_PREFIX']
        );
    }

    /**
     * Confirms notification content is built from config and submission data.
     *
     * @return void
     */
    public function testBuildReturnsNormalizedNotification(): void
    {
        $_ENV['CONTACT_TO_EMAIL'] = 'owner@example.com';
        $_ENV['CONTACT_FROM_EMAIL'] = 'no-reply@example.com';
        $_ENV['CONTACT_FROM_NAME'] = 'Site Contact';
        $_ENV['CONTACT_SUBJECT_PREFIX'] = '[Support]';

        $builder = new ContactNotificationContentBuilder(new Environment(null));
        $notification = $builder->build(
            new ContactSubmission(
                'Ada Lovelace',
                'ada@example.com',
                'A valid contact message for testing.',
                '127.0.0.1'
            )
        );

        self::assertSame('owner@example.com', $notification->toEmail);
        self::assertSame('no-reply@example.com', $notification->fromEmail);
        self::assertSame('Site Contact', $notification->fromName);
        self::assertSame('ada@example.com', $notification->replyToEmail);
        self::assertSame('[Support] New contact request', $notification->subject);
        self::assertStringContainsString('Ada Lovelace', $notification->textBody);
        self::assertStringContainsString('127.0.0.1', $notification->textBody);
    }
}
