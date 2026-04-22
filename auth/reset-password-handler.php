<?php
require_once dirname(__DIR__) . '/config/config.php';
require_once ROOT_PATH . '/config/db.php';
require_once ROOT_PATH . '/includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirectTo('/forgot-password.php');
}

$token = trim($_POST['token'] ?? '');
$password = $_POST['password'] ?? '';
$confirmPassword = $_POST['confirm_password'] ?? '';

if ($token === '') {
    setFlash('error', 'Reset token is missing. Please use the latest reset link from your email.');
    redirectTo('/forgot-password.php');
}

if ($password === '' || $confirmPassword === '') {
    setFlash('error', 'Please fill in both password fields.');
    header('Location: ' . BASE_URL . '/reset-password.php?token=' . urlencode($token));
    exit;
}

if (strlen($password) < 6) {
    setFlash('error', 'Password must be at least 6 characters long.');
    header('Location: ' . BASE_URL . '/reset-password.php?token=' . urlencode($token));
    exit;
}

if ($password !== $confirmPassword) {
    setFlash('error', 'Passwords do not match.');
    header('Location: ' . BASE_URL . '/reset-password.php?token=' . urlencode($token));
    exit;
}

try {
    $tokenHash = hash('sha256', $token);

    $stmt = $pdo->prepare("
        SELECT reset_id, user_id, expires_at, used_at
        FROM password_resets
        WHERE token_hash = :token_hash
        LIMIT 1
    ");
    $stmt->execute(['token_hash' => $tokenHash]);
    $resetRow = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$resetRow) {
        setFlash('error', 'This reset link was not found. Please use the newest link.');
        header('Location: ' . BASE_URL . '/reset-password.php?token=' . urlencode($token));
        exit;
    }

    if ($resetRow['used_at'] !== null) {
        setFlash('error', 'This reset link has already been used. Please request a new one.');
        header('Location: ' . BASE_URL . '/reset-password.php?token=' . urlencode($token));
        exit;
    }

    if (strtotime($resetRow['expires_at']) <= time()) {
        setFlash('error', 'This reset link has expired. Please request a new one.');
        header('Location: ' . BASE_URL . '/reset-password.php?token=' . urlencode($token));
        exit;
    }

    $newHash = password_hash($password, PASSWORD_DEFAULT);

    $pdo->beginTransaction();

    $updateUser = $pdo->prepare("
        UPDATE users
        SET password_hash = :password_hash
        WHERE user_id = :user_id
    ");
    $updateUser->execute([
        'password_hash' => $newHash,
        'user_id' => $resetRow['user_id']
    ]);

    $markCurrentUsed = $pdo->prepare("
        UPDATE password_resets
        SET used_at = NOW()
        WHERE reset_id = :reset_id
    ");
    $markCurrentUsed->execute([
        'reset_id' => $resetRow['reset_id']
    ]);

    $markOthersUsed = $pdo->prepare("
        UPDATE password_resets
        SET used_at = NOW()
        WHERE user_id = :user_id
          AND reset_id <> :reset_id
          AND used_at IS NULL
    ");
    $markOthersUsed->execute([
        'user_id' => $resetRow['user_id'],
        'reset_id' => $resetRow['reset_id']
    ]);

    $pdo->commit();

    setFlash('success', 'Your password has been updated successfully. Please log in.');
    redirectTo('/login.php');
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    setFlash('error', 'Something went wrong while resetting your password.');
    header('Location: ' . BASE_URL . '/reset-password.php?token=' . urlencode($token));
    exit;
}