<?php
// src/services/ContactSubmissionRepository.php
// Stores validated contact submissions in MySQL.
// Connects to: src/services/DatabaseConnectionFactory.php, src/models/ContactSubmission.php
// Created: 2026-06-28

declare(strict_types=1);

namespace App\Services;

use App\Models\ContactSubmission;

final class ContactSubmissionRepository implements ContactSubmissionRepositoryInterface
{
    private const INSERT_SQL = '
        INSERT INTO contact_submissions (name, email, message, ip_address)
        VALUES (:name, :email, :message, :ip_address)
    ';

    /**
     * Initializes the repository.
     *
     * @param DatabaseConnectionFactory $connectionFactory Creates PDO connections.
     */
    public function __construct(
        private readonly DatabaseConnectionFactory $connectionFactory
    ) {
    }

    /**
     * Persists a contact submission.
     *
     * @param ContactSubmission $submission Validated submission model.
     *
     * @return void
     */
    public function save(ContactSubmission $submission): void
    {
        $connection = $this->connectionFactory->create();
        $statement = $connection->prepare(self::INSERT_SQL);

        $statement->execute(
            [
                ':name' => $submission->name,
                ':email' => $submission->email,
                ':message' => $submission->message,
                ':ip_address' => $submission->ipAddress,
            ]
        );
    }
}
