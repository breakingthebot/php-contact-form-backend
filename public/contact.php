<?php
// public/contact.php
// HTTP entrypoint for contact form submissions.
// Connects to: src/config/bootstrap.php, src/controllers/ContactController.php
// Created: 2026-06-28

declare(strict_types=1);

use App\Controllers\ContactController;

require dirname(__DIR__) . '/src/config/bootstrap.php';

$controller = new ContactController();
$controller->handle();
