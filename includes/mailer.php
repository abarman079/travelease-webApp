<?php
if (!defined('ROOT_PATH')) {
    require_once dirname(__DIR__) . '/config/config.php';
}

require_once ROOT_PATH . '/config/mail.php';
require_once ROOT_PATH . '/lib/PHPMailer/src/Exception.php';
require_once ROOT_PATH . '/lib/PHPMailer/src/PHPMailer.php';
require_once ROOT_PATH . '/lib/PHPMailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function appBaseUrl(): string
{
    $isHttps =
        (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ||
        (isset($_SERVER['SERVER_PORT']) && (int) $_SERVER['SERVER_PORT'] === 443) ||
        (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');

    $scheme = $isHttps ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';

    return rtrim($scheme . '://' . $host . BASE_URL, '/');
}

function sendAppMail(array $recipients, string $subject, string $htmlBody, string $altBody = ''): bool
{
    if (!defined('MAIL_ENABLED') || !MAIL_ENABLED) {
        if (defined('APP_ENV') && APP_ENV === 'local') {
            $_SESSION['mail_debug_error'] = 'MAIL_ENABLED is false.';
        }
        return false;
    }

    $validRecipients = [];

    foreach ($recipients as $recipient) {
        $recipient = trim((string) $recipient);
        if ($recipient !== '' && filter_var($recipient, FILTER_VALIDATE_EMAIL)) {
            $validRecipients[$recipient] = true;
        }
    }

    if (empty($validRecipients)) {
        if (defined('APP_ENV') && APP_ENV === 'local') {
            $_SESSION['mail_debug_error'] = 'No valid recipient email address found.';
        }
        return false;
    }

    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host = MAIL_SMTP_HOST;
        $mail->SMTPAuth = true;
        $mail->Username = MAIL_SMTP_USERNAME;
        $mail->Password = MAIL_SMTP_PASSWORD;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = MAIL_SMTP_PORT;
        $mail->CharSet = 'UTF-8';

        $mail->setFrom(MAIL_FROM_ADDRESS, MAIL_FROM_NAME);

        foreach (array_keys($validRecipients) as $address) {
            $mail->addAddress($address);
        }

        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $htmlBody;
        $mail->AltBody = $altBody !== ''
            ? $altBody
            : trim(strip_tags(str_replace(['<br>', '<br/>', '<br />'], "\n", $htmlBody)));

        $sent = $mail->send();

        if (defined('APP_ENV') && APP_ENV === 'local') {
            unset($_SESSION['mail_debug_error']);
        }

        return $sent;
    } catch (Throwable $e) {
        error_log('TravelEase mail error: ' . $e->getMessage());

        if (defined('APP_ENV') && APP_ENV === 'local') {
            $_SESSION['mail_debug_error'] = $e->getMessage();
        }

        return false;
    }
}

function getRoleEmails(PDO $pdo, string $roleName): array
{
    $stmt = $pdo->prepare("
        SELECT u.email
        FROM users u
        INNER JOIN roles r ON u.role_id = r.role_id
        WHERE r.role_name = :role_name
          AND u.status = 'active'
          AND u.email IS NOT NULL
          AND u.email <> ''
    ");
    $stmt->execute(['role_name' => $roleName]);

    return $stmt->fetchAll(PDO::FETCH_COLUMN) ?: [];
}

function getTripAgentEmail(PDO $pdo, int $tripId): ?string
{
    $stmt = $pdo->prepare("
        SELECT u.email
        FROM trips t
        INNER JOIN users u ON t.created_by = u.user_id
        WHERE t.trip_id = :trip_id
        LIMIT 1
    ");
    $stmt->execute(['trip_id' => $tripId]);

    $email = $stmt->fetchColumn();

    return $email ?: null;
}

function sendRegistrationEmails(PDO $pdo, string $userEmail, string $fullName, string $username): void
{
    $safeName = htmlspecialchars($fullName, ENT_QUOTES, 'UTF-8');
    $safeUsername = htmlspecialchars($username, ENT_QUOTES, 'UTF-8');

    $userSubject = 'TravelEase Registration Confirmed';
    $userHtml = "
        <h2>Welcome to TravelEase, {$safeName}!</h2>
        <p>Your account has been created successfully.</p>
        <p><strong>Username:</strong> {$safeUsername}</p>
        <p>You can now sign in and start exploring trips, bookings, and itinerary planning.</p>
    ";
    sendAppMail([$userEmail], $userSubject, $userHtml);

    $adminEmails = getRoleEmails($pdo, 'admin');
    if (!empty($adminEmails)) {
        $adminSubject = 'New Traveler Registration';
        $adminHtml = "
            <h2>New traveler account created</h2>
            <p><strong>Name:</strong> {$safeName}</p>
            <p><strong>Username:</strong> {$safeUsername}</p>
            <p><strong>Email:</strong> " . htmlspecialchars($userEmail, ENT_QUOTES, 'UTF-8') . "</p>
        ";
        sendAppMail($adminEmails, $adminSubject, $adminHtml);
    }
}

function sendForgotPasswordEmail(string $userEmail, string $fullName, string $resetLink): bool
{
    $safeName = htmlspecialchars($fullName, ENT_QUOTES, 'UTF-8');
    $safeLink = htmlspecialchars($resetLink, ENT_QUOTES, 'UTF-8');

    $subject = 'TravelEase Password Reset';
    $html = "
        <h2>Password Reset Request</h2>
        <p>Hello {$safeName},</p>
        <p>We received a request to reset your password.</p>
        <p><a href=\"{$safeLink}\">Click here to reset your password</a></p>
        <p>This link will expire in 1 hour.</p>
        <p>If you did not request this, you can ignore this email.</p>
    ";

    return sendAppMail([$userEmail], $subject, $html);
}

function sendTripConfirmedEmails(PDO $pdo, array $data): void
{
    $travelerEmail = $data['traveler_email'] ?? '';
    $travelerName = $data['traveler_name'] ?? 'Traveler';
    $tripTitle = $data['trip_title'] ?? 'Trip';
    $destination = $data['destination'] ?? '';
    $bookingId = $data['booking_id'] ?? '';
    $travelDate = $data['travel_date'] ?? '';
    $agentEmail = $data['agent_email'] ?? null;

    $safeTraveler = htmlspecialchars($travelerName, ENT_QUOTES, 'UTF-8');
    $safeTrip = htmlspecialchars($tripTitle, ENT_QUOTES, 'UTF-8');
    $safeDestination = htmlspecialchars($destination, ENT_QUOTES, 'UTF-8');
    $safeBooking = htmlspecialchars((string) $bookingId, ENT_QUOTES, 'UTF-8');
    $safeTravelDate = htmlspecialchars($travelDate, ENT_QUOTES, 'UTF-8');

    $userSubject = 'TravelEase Booking Confirmed';
    $userHtml = "
        <h2>Your booking is confirmed</h2>
        <p>Hello {$safeTraveler},</p>
        <p>Your booking has been confirmed successfully.</p>
        <p><strong>Booking ID:</strong> #{$safeBooking}</p>
        <p><strong>Trip:</strong> {$safeTrip}</p>
        <p><strong>Destination:</strong> {$safeDestination}</p>
        <p><strong>Travel Date:</strong> {$safeTravelDate}</p>
    ";
    sendAppMail([$travelerEmail], $userSubject, $userHtml);

    $staffEmails = getRoleEmails($pdo, 'admin');
    if ($agentEmail && filter_var($agentEmail, FILTER_VALIDATE_EMAIL)) {
        $staffEmails[] = $agentEmail;
    }
    $staffEmails = array_values(array_unique($staffEmails));

    if (!empty($staffEmails)) {
        $staffSubject = 'Booking Confirmed - TravelEase';
        $staffHtml = "
            <h2>A booking has been confirmed</h2>
            <p><strong>Booking ID:</strong> #{$safeBooking}</p>
            <p><strong>Traveler:</strong> {$safeTraveler}</p>
            <p><strong>Trip:</strong> {$safeTrip}</p>
            <p><strong>Destination:</strong> {$safeDestination}</p>
            <p><strong>Travel Date:</strong> {$safeTravelDate}</p>
        ";
        sendAppMail($staffEmails, $staffSubject, $staffHtml);
    }
}

function sendTripCancelledEmails(PDO $pdo, array $data): void
{
    $travelerEmail = $data['traveler_email'] ?? '';
    $travelerName = $data['traveler_name'] ?? 'Traveler';
    $tripTitle = $data['trip_title'] ?? 'Trip';
    $destination = $data['destination'] ?? '';
    $bookingId = $data['booking_id'] ?? '';
    $travelDate = $data['travel_date'] ?? '';
    $agentEmail = $data['agent_email'] ?? null;

    $safeTraveler = htmlspecialchars($travelerName, ENT_QUOTES, 'UTF-8');
    $safeTrip = htmlspecialchars($tripTitle, ENT_QUOTES, 'UTF-8');
    $safeDestination = htmlspecialchars($destination, ENT_QUOTES, 'UTF-8');
    $safeBooking = htmlspecialchars((string) $bookingId, ENT_QUOTES, 'UTF-8');
    $safeTravelDate = htmlspecialchars($travelDate, ENT_QUOTES, 'UTF-8');

    $userSubject = 'TravelEase Booking Cancelled';
    $userHtml = "
        <h2>Your booking has been cancelled</h2>
        <p>Hello {$safeTraveler},</p>
        <p>Your booking cancellation has been processed.</p>
        <p><strong>Booking ID:</strong> #{$safeBooking}</p>
        <p><strong>Trip:</strong> {$safeTrip}</p>
        <p><strong>Destination:</strong> {$safeDestination}</p>
        <p><strong>Travel Date:</strong> {$safeTravelDate}</p>
        <p>Refund automation is not enabled in this student version.</p>
    ";
    sendAppMail([$travelerEmail], $userSubject, $userHtml);

    $staffEmails = getRoleEmails($pdo, 'admin');
    if ($agentEmail && filter_var($agentEmail, FILTER_VALIDATE_EMAIL)) {
        $staffEmails[] = $agentEmail;
    }
    $staffEmails = array_values(array_unique($staffEmails));

    if (!empty($staffEmails)) {
        $staffSubject = 'Booking Cancelled - TravelEase';
        $staffHtml = "
            <h2>A booking has been cancelled</h2>
            <p><strong>Booking ID:</strong> #{$safeBooking}</p>
            <p><strong>Traveler:</strong> {$safeTraveler}</p>
            <p><strong>Trip:</strong> {$safeTrip}</p>
            <p><strong>Destination:</strong> {$safeDestination}</p>
            <p><strong>Travel Date:</strong> {$safeTravelDate}</p>
        ";
        sendAppMail($staffEmails, $staffSubject, $staffHtml);
    }
}