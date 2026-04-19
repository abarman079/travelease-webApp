<?php
require_once dirname(__DIR__) . '/config/config.php';
require_once ROOT_PATH . '/config/db.php';
require_once ROOT_PATH . '/includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirectTo('/forgot-password.php');
}

$identifier = trim($_POST['identifier'] ?? '');

if ($identifier === '') {
    setFlash('error', 'Please enter your username, email, or phone number.');
    redirectTo('/forgot-password.php');
}

$phoneFull = preg_replace('/[^\d+]/', '', $identifier);
$phoneDigits = preg_replace('/\D+/', '', $identifier);

try {
    $stmt = $pdo->prepare("
        SELECT user_id, email, username, phone
        FROM users
        WHERE 
            email = :identifier
            OR username = :identifier
            OR phone = :phone_full
            OR REPLACE(phone, '+', '') = :phone_digits
        LIMIT 1
    ");
    $stmt->execute([
        'identifier' => $identifier,
        'phone_full' => $phoneFull,
        'phone_digits' => $phoneDigits
    ]);

    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $token = bin2hex(random_bytes(32));
        $tokenHash = hash('sha256', $token);
        $expiresAt = date('Y-m-d H:i:s', time() + 3600);

        $deleteOld = $pdo->prepare("
            DELETE FROM password_resets
            WHERE user_id = :user_id OR expires_at < NOW() OR used_at IS NOT NULL
        ");
        $deleteOld->execute(['user_id' => $user['user_id']]);

        $insert = $pdo->prepare("
            INSERT INTO password_resets (user_id, token_hash, expires_at)
            VALUES (:user_id, :token_hash, :expires_at)
        ");
        $insert->execute([
            'user_id' => $user['user_id'],
            'token_hash' => $tokenHash,
            'expires_at' => $expiresAt
        ]);

        $_SESSION['dev_reset_link'] = BASE_URL . '/reset-password.php?token=' . urlencode($token);
    }

    setFlash('success', 'If an account matches your details, a reset link has been generated.');
    redirectTo('/forgot-password.php');
} catch (Throwable $e) {
    setFlash('error', 'Something went wrong while processing your request.');
    redirectTo('/forgot-password.php');
}