<?php
// src/services/ContactFormService.php
// Coordinates validation, persistence, mail delivery, and logging for submissions.
// Connects to: src/services/ContactSubmissionRepository.php, src/services/Mail/MailTransportInterface.php
// Created: 2026-06-28

declare(strict_types=1);

namespace App\Services;

use App\Models\ContactSubmission;
use App\Models\SubmissionResult;
use App\Services\Mail\MailTransportInterface;
use App\Utils\ContactSubmissionValidator;
use App\Utils\Environment;
use App\Utils\RequestLogger;
use Throwable;

final class ContactFormService
{
    /**
     * Initializes the contact form service.
     *
     * @param ContactSubmissionRepositoryInterface $repository Persists submissions.
     * @param MailTransportInterface $mailTransport Sends notification emails.
     * @param RequestLogger $logger Records operational events.
     * @param Environment $environment Reads runtime configuration.
     */
    public function __construct(
        private readonly ContactSubmissionRepositoryInterface $repository,
        private readonly MailTransportInterface $mailTransport,
        private readonly RequestLogger $logger,
        private readonly Environment $environment
    ) {
    }

    /**
     * Processes a contact form submission.
     *
     * @param ContactSubmission $submission Request model.
     *
     * @return SubmissionResult
     */
    public function submit(ContactSubmission $submission): SubmissionResult
    {
        $errors = ContactSubmissionValidator::validate($submission);

        if ($errors !== []) {
            return new SubmissionResult(
                422,
                'error',
                'Validation failed.',
                $errors
            );
        }

        try {
            $this->repository->save($submission);
            $this->mailTransport->send($submission);
        } catch (Throwable $exception) {
            $this->logger->error(
                'Failed to process contact submission.',
                [
                    'email' => $submission->email,
                    'exception' => $exception->getMessage(),
                ]
            );

            return new SubmissionResult(
                500,
                'error',
                'Unable to process the contact request at this time.'
            );
        }

        $this->logger->info(
            'Contact submission processed successfully.',
            [
                'email' => $submission->email,
                'environment' => $this->environment->get('APP_ENV', 'production'),
            ]
        );

        return new SubmissionResult(
            201,
            'success',
            'Contact request received successfully.'
        );
    }
}
