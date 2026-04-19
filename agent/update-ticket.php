<?php
require_once dirname(__DIR__) . '/config/config.php';
require_once ROOT_PATH . '/config/db.php';
require_once ROOT_PATH . '/includes/auth.php';

requireRole(['agent']);
$user = currentUser();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_URL . '/agent/manage-support.php');
    exit;
}

$ticketId = (int) ($_POST['ticket_id'] ?? 0);
$status = trim($_POST['status'] ?? '');
$responseNote = trim($_POST['response_note'] ?? '');

$allowedStatuses = ['open', 'in_progress', 'resolved', 'closed'];

if ($ticketId <= 0 || !in_array($status, $allowedStatuses, true)) {
    setFlash('error', 'Invalid ticket update request.');
    header('Location: ' . BASE_URL . '/agent/manage-support.php');
    exit;
}

try {
    $stmt = $pdo->prepare("
        UPDATE support_tickets
        SET status = :status,
            response_note = :response_note,
            handled_by = :handled_by,
            updated_at = NOW()
        WHERE ticket_id = :ticket_id
    ");
    $stmt->execute([
        'status' => $status,
        'response_note' => $responseNote,
        'handled_by' => $user['user_id'],
        'ticket_id' => $ticketId
    ]);

    setFlash('success', 'Support ticket updated successfully.');
    header('Location: ' . BASE_URL . '/agent/manage-support.php');
    exit;
} catch (PDOException $e) {
    setFlash('error', 'Something went wrong while updating the ticket.');
    header('Location: ' . BASE_URL . '/agent/manage-support.php');
    exit;
}