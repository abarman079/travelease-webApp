<?php
require_once dirname(__DIR__) . '/config/config.php';
require_once ROOT_PATH . '/config/db.php';
require_once ROOT_PATH . '/includes/auth.php';

requireRole(['traveler']);
$user = currentUser();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_URL . '/traveler/support.php');
    exit;
}

$subject = trim($_POST['subject'] ?? '');
$message = trim($_POST['message'] ?? '');
$priority = trim($_POST['priority'] ?? '');

$allowedPriorities = ['low', 'medium', 'high'];

if ($subject === '' || $message === '' || !in_array($priority, $allowedPriorities, true)) {
    setFlash('error', 'Please complete all support ticket fields.');
    header('Location: ' . BASE_URL . '/traveler/support.php');
    exit;
}

try {
    $stmt = $pdo->prepare("
        INSERT INTO support_tickets (user_id, subject, message, priority, status)
        VALUES (:user_id, :subject, :message, :priority, 'open')
    ");
    $stmt->execute([
        'user_id' => $user['user_id'],
        'subject' => $subject,
        'message' => $message,
        'priority' => $priority
    ]);

    $notify = $pdo->prepare("
        INSERT INTO notifications (user_id, message, type)
        VALUES (:user_id, :message, :type)
    ");
    $notify->execute([
        'user_id' => $user['user_id'],
        'message' => 'Your support ticket has been submitted successfully.',
        'type' => 'support'
    ]);

    setFlash('success', 'Support ticket submitted successfully.');
    header('Location: ' . BASE_URL . '/traveler/support.php');
    exit;
} catch (PDOException $e) {
    setFlash('error', 'Something went wrong while submitting the ticket.');
    header('Location: ' . BASE_URL . '/traveler/support.php');
    exit;
}