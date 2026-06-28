<?php
// src/controllers/ContactController.php
// Handles the incoming HTTP request and returns a JSON response.
// Connects to: src/models/ContactSubmission.php, src/services/ContactFormService.php
// Created: 2026-06-28

declare(strict_types=1);

namespace App\Controllers;

use App\Models\ContactSubmission;
use App\Services\ContactFormService;
use App\Services\Security\RequestGuard;
use App\Utils\JsonResponder;
use App\Utils\RequestContextFactory;
use App\Utils\RequestInputReaderInterface;
use App\Utils\RequestLogger;

final class ContactController
{
    /**
     * Handles a contact submission request.
     *
     * @return void
     */
    public function handle(): void
    {
        global $container;

        /** @var ContactFormService $service */
        $service = $container[ContactFormService::class];
        /** @var JsonResponder $responder */
        $responder = $container[JsonResponder::class];
        /** @var RequestContextFactory $requestContextFactory */
        $requestContextFactory = $container[RequestContextFactory::class];
        /** @var RequestInputReaderInterface $inputReader */
        $inputReader = $container[RequestInputReaderInterface::class];
        /** @var RequestGuard $requestGuard */
        $requestGuard = $container[RequestGuard::class];
        /** @var RequestLogger $logger */
        $logger = $container[RequestLogger::class];
        $requestContext = $requestContextFactory->createFromGlobals();
        $requestLogger = $logger->withContext($requestContext->toArray());
        $responseHeaders = ['X-Request-Id' => $requestContext->requestId];

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $requestLogger->error('Rejected contact request due to unsupported method.');
            $responder->send(
                405,
                [
                    'status' => 'error',
                    'message' => 'Method not allowed.',
                ],
                $responseHeaders
            );
            return;
        }

        $rawInput = $inputReader->read();
        $payload = json_decode($rawInput ?: '{}', true);

        if (!is_array($payload)) {
            $requestLogger->error('Rejected contact request due to invalid JSON payload.');
            $responder->send(
                400,
                [
                    'status' => 'error',
                    'message' => 'Invalid JSON payload.',
                ],
                $responseHeaders
            );
            return;
        }

        $requestLogger->info('Received contact request payload.', ['payload_keys' => array_keys($payload)]);

        $guardResult = $requestGuard->guard(
            $payload,
            $_SERVER['REMOTE_ADDR'] ?? null,
            $_SERVER['HTTP_ORIGIN'] ?? null
        );

        if ($guardResult !== null) {
            $requestLogger->error(
                'Blocked contact request during guard checks.',
                ['status_code' => $guardResult->statusCode]
            );
            $responder->send($guardResult->statusCode, $guardResult->toArray(), $responseHeaders);
            return;
        }

        $submission = ContactSubmission::fromArray(
            $payload,
            $_SERVER['REMOTE_ADDR'] ?? null
        );

        $result = $service->submit($submission);

        $requestLogger->info(
            'Completed contact request.',
            ['status_code' => $result->statusCode, 'result_status' => $result->status]
        );

        $responder->send($result->statusCode, $result->toArray(), $responseHeaders);
    }
}
