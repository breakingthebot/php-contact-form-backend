-- database/schema.sql
-- Creates the table used to persist contact form submissions.
-- Connects to: src/services/ContactSubmissionRepository.php
-- Created: 2026-06-28

CREATE TABLE IF NOT EXISTS contact_submissions (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  email VARCHAR(190) NOT NULL,
  message TEXT NOT NULL,
  ip_address VARCHAR(45) DEFAULT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);
