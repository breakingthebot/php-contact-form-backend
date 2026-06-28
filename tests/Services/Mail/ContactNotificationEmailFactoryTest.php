<?php
// tests/Services/Mail/ContactNotificationEmailFactoryTest.php
// Verifies Symfony email message generation for contact notifications.
// Connects to: src/services/Mail/ContactNotificationEmailFactory.php
// Created: 2026-06-28

declare(strict_types=1);

namespace Tests\Services\Mail;

use App\Models\ContactNotification;
use App\Services\Mail\ContactNotificationEmailFactory;
use PHPUnit\Framework\TestCase;

final class ContactNotificationEmailFactoryTest extends TestCase
{
    /**
     * Confirms the factory maps notification fields onto the email message.
     *
     * @return void
     */
    public function testCreateBuildsSymfonyEmailFromNotification(): void
    {
        $factory = new ContactNotificationEmailFactory();
        $email = $factory->create(
            new ContactNotification(
                'owner@example.com',
                'no-reply@example.com',
                'Site Contact',
                'ada@example.com',
                '[Support] New contact request',
                'Message body'
            )
        );

        self::assertSame('owner@example.com', $email->getTo()[0]->getAddress());
        self::assertSame('no-reply@example.com', $email->getFrom()[0]->getAddress());
        self::assertSame('Site Contact', $email->getFrom()[0]->getName());
        self::assertSame('ada@example.com', $email->getReplyTo()[0]->getAddress());
        self::assertSame('[Support] New contact request', $email->getSubject());
        self::assertStringContainsString('Message body', $email->getTextBody() ?? '');
    }
}
