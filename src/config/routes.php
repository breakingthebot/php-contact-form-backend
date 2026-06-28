<?php
// src/config/routes.php
// Documents the public endpoint map for the application.
// Connects to: public/contact.php
// Created: 2026-06-28

declare(strict_types=1);

return [
    'POST /contact.php' => 'ContactController::handle',
    'GET /health.php' => 'HealthController::handle',
    'GET /version.php' => 'VersionController::handle',
];
