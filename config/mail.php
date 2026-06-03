<?php

$secretsFile = __DIR__ . '/secrets.php';
$secrets = file_exists($secretsFile) ? require $secretsFile : [];

define('MAIL_ENABLED', true);

define('MAIL_FROM_ADDRESS', $secrets['MAIL_FROM_ADDRESS'] ?? getenv('MAIL_FROM_ADDRESS') ?: '');
define('MAIL_FROM_NAME', $secrets['MAIL_FROM_NAME'] ?? getenv('MAIL_FROM_NAME') ?: 'TravelEase');

define('MAIL_SMTP_HOST', $secrets['MAIL_SMTP_HOST'] ?? getenv('MAIL_SMTP_HOST') ?: 'smtp.gmail.com');
define('MAIL_SMTP_PORT', (int)($secrets['MAIL_SMTP_PORT'] ?? getenv('MAIL_SMTP_PORT') ?: 587));
define('MAIL_SMTP_USERNAME', $secrets['MAIL_SMTP_USERNAME'] ?? getenv('MAIL_SMTP_USERNAME') ?: '');
define('MAIL_SMTP_PASSWORD', $secrets['MAIL_SMTP_PASSWORD'] ?? getenv('MAIL_SMTP_PASSWORD') ?: '');
