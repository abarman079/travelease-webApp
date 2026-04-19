<?php
require_once __DIR__ . '/config/config.php';
require_once ROOT_PATH . '/includes/auth.php';

if (isLoggedIn()) {
    header('Location: ' . dashboardPathForRole(currentUserRole()));
    exit;
}

$error = getFlash('error');
$success = getFlash('success');

require_once ROOT_PATH . '/includes/header.php';
require_once ROOT_PATH . '/includes/navbar.php';
?>

<div class="container py-5 auth-page">
    <div class="row justify-content-center">
        <div class="col-lg-6 col-xl-8">
            <div class="soft-panel auth-card p-4 p-md-5">
                <div class="section-kicker mb-2">Traveler sign in</div>
                <h1 class="mb-3">Welcome back</h1>
                <p class="mb-4">
                    Log in with your username, email or full phone number.
                </p>

                <?php if ($error): ?>
                    <div class="alert alert-danger border-0 rounded-4 mb-4"><?= htmlspecialchars($error); ?></div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="alert alert-success border-0 rounded-4 mb-4"><?= htmlspecialchars($success); ?></div>
                <?php endif; ?>

                <form method="POST" action="<?= BASE_URL; ?>/auth/login-handler.php" class="row g-3">
                    <div class="col-12">
                        <label for="identifier" class="form-label auth-label">Username, Email, or Phone</label>
                        <input type="text" class="form-control auth-input" id="identifier" name="identifier" placeholder="username / email / +447123456789" required>
                    </div>

                    <div class="col-12">
                        <label for="password" class="form-label auth-label">Password</label>
                        <input type="password" class="form-control auth-input" id="password" name="password" required>
                    </div>

                    <div class="col-12 text-end">
                        <a href="<?= BASE_URL; ?>/forgot-password.php" class="forgot-link">Forgot password?</a>
                    </div>

                    <div class="col-12 d-grid mt-1">
                        <button type="submit" class="btn btn-dark-soft">Log In</button>
                    </div>
                </form>

                <p class="mt-4 mb-0 auth-note">
                    Don’t have an account?
                    <a href="<?= BASE_URL; ?>/register.php">Create one here</a>
                </p>
            </div>
        </div>
    </div>
</div>

<?php require_once ROOT_PATH . '/includes/footer.php'; ?>