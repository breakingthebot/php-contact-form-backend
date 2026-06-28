<?php
// src/services/ContactSubmissionRepositoryInterface.php
// Defines the persistence contract for contact submissions.
// Connects to: src/services/ContactFormService.php, src/services/ContactSubmissionRepository.php
// Created: 2026-06-28

declare(strict_types=1);

namespace App\Services;

use App\Models\ContactSubmission;

interface ContactSubmissionRepositoryInterface
{
    /**
     * Persists a contact submission.
     *
     * @param ContactSubmission $submission Validated submission model.
     *
     * @return void
     */
    public function save(ContactSubmission $submission): void;
}
