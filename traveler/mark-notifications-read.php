<?php
require_once dirname(__DIR__) . '/config/config.php';
require_once ROOT_PATH . '/config/db.php';
require_once ROOT_PATH . '/includes/auth.php';

requireRole(['traveler']);
$user = currentUser();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_URL . '/traveler/notifications.php');
    exit;
}

try {
    $stmt = $pdo->prepare("
        UPDATE notifications
        SET is_read = 1
        WHERE user_id = :user_id AND is_read = 0
    ");
    $stmt->execute(['user_id' => $user['user_id']]);

    setFlash('success', 'All notifications marked as read.');
    header('Location: ' . BASE_URL . '/traveler/notifications.php');
    exit;
} catch (PDOException $e) {
    setFlash('error', 'Something went wrong while updating notifications.');
    header('Location: ' . BASE_URL . '/traveler/notifications.php');
    exit;
}