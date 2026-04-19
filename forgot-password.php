<?php
require_once __DIR__ . '/config/config.php';
require_once ROOT_PATH . '/includes/auth.php';

$message = getFlash('success');
$error = getFlash('error');
$previewLink = $_SESSION['dev_reset_link'] ?? null;
unset($_SESSION['dev_reset_link']);

require_once ROOT_PATH . '/includes/header.php';
require_once ROOT_PATH . '/includes/navbar.php';
?>

<div class="container py-5 auth-page">
    <div class="row justify-content-center">
        <div class="col-lg-6 col-xl-5">
            <div class="soft-panel auth-card p-4 p-md-5">
                <div class="section-kicker mb-2">Account recovery</div>
                <h1 class="mb-3">Forgot your password?</h1>
                <p class="mb-4">
                    Enter your username, email, or phone number to request a password reset.
                </p>

                <?php if ($error): ?>
                    <div class="alert alert-danger border-0 rounded-4 mb-4"><?= htmlspecialchars($error); ?></div>
                <?php endif; ?>

                <?php if ($message): ?>
                    <div class="alert alert-success border-0 rounded-4 mb-4"><?= htmlspecialchars($message); ?></div>
                <?php endif; ?>

                <form method="POST" action="<?= BASE_URL; ?>/auth/forgot-password-handler.php" class="row g-3">
                    <div class="col-12">
                        <label for="identifier" class="form-label auth-label">Username, Email, or Phone</label>
                        <input type="text" class="form-control auth-input" id="identifier" name="identifier" placeholder="username / email / +447123456789" required>
                    </div>

                    <div class="col-12 d-grid mt-3">
                        <button type="submit" class="btn btn-dark-soft">Generate Reset Link</button>
                    </div>
                </form>

                <?php if ($previewLink): ?>
                    <div class="dev-preview-box mt-4">
                        <div class="dev-preview-title">Local development reset link</div>
                        <p class="mb-2">
                            This preview link is shown only for your local/student setup.
                            Later, this can be replaced with email delivery.
                        </p>
                        <a href="<?= htmlspecialchars($previewLink); ?>" class="dev-preview-link">
                            Open Reset Password Page
                        </a>
                    </div>
                <?php endif; ?>

                <p class="mt-4 mb-0 auth-note">
                    Remembered your password?
                    <a href="<?= BASE_URL; ?>/login.php">Go back to login</a>
                </p>
            </div>
        </div>
    </div>
</div>

<?php require_once ROOT_PATH . '/includes/footer.php'; ?>