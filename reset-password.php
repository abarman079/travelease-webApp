<?php
require_once __DIR__ . '/config/config.php';
require_once ROOT_PATH . '/config/db.php';
require_once ROOT_PATH . '/includes/auth.php';

$token = trim($_GET['token'] ?? '');
$error = getFlash('error');
$success = getFlash('success');

$isValidToken = false;

if ($token !== '') {
    $tokenHash = hash('sha256', $token);

    $stmt = $pdo->prepare("
        SELECT pr.reset_id, pr.user_id, pr.expires_at, pr.used_at, u.email
        FROM password_resets pr
        INNER JOIN users u ON pr.user_id = u.user_id
        WHERE pr.token_hash = :token_hash
        LIMIT 1
    ");
    $stmt->execute(['token_hash' => $tokenHash]);
    $resetRow = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($resetRow && $resetRow['used_at'] === null && strtotime($resetRow['expires_at']) > time()) {
        $isValidToken = true;
    }
}

require_once ROOT_PATH . '/includes/header.php';
require_once ROOT_PATH . '/includes/navbar.php';
?>

<div class="container py-5 auth-page">
    <div class="row justify-content-center">
        <div class="col-lg-6 col-xl-5">
            <div class="soft-panel auth-card p-4 p-md-5">
                <div class="section-kicker mb-2">Password reset</div>
                <h1 class="mb-3">Set a new password</h1>

                <?php if ($error): ?>
                    <div class="alert alert-danger border-0 rounded-4 mb-4"><?= htmlspecialchars($error); ?></div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="alert alert-success border-0 rounded-4 mb-4"><?= htmlspecialchars($success); ?></div>
                <?php endif; ?>

                <?php if (!$isValidToken): ?>
                    <div class="alert alert-warning border-0 rounded-4 mb-4">
                        This password reset link is invalid, expired, or already used.
                    </div>
                    <p class="mb-0 auth-note">
                        <a href="<?= BASE_URL; ?>/forgot-password.php">Request a new reset link</a>
                    </p>
                <?php else: ?>
                    <p class="mb-4">
                        Enter your new password below. Make sure both fields match.
                    </p>

                    <form method="POST" action="<?= BASE_URL; ?>/auth/reset-password-handler.php" class="row g-3">
                        <input type="hidden" name="token" value="<?= htmlspecialchars($token); ?>">

                        <div class="col-12">
                            <label for="password" class="form-label auth-label">New Password</label>
                            <input type="password" class="form-control auth-input" id="password" name="password" required>
                        </div>

                        <div class="col-12">
                            <label for="confirm_password" class="form-label auth-label">Confirm New Password</label>
                            <input type="password" class="form-control auth-input" id="confirm_password" name="confirm_password" required>
                        </div>

                        <div class="col-12 d-grid mt-3">
                            <button type="submit" class="btn btn-dark-soft">Update Password</button>
                        </div>
                    </form>
                <?php endif; ?>

                <p class="mt-4 mb-0 auth-note">
                    <a href="<?= BASE_URL; ?>/login.php">Back to login</a>
                </p>
            </div>
        </div>
    </div>
</div>

<?php require_once ROOT_PATH . '/includes/footer.php'; ?>