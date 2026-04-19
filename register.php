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
        <div class="col-lg-8 col-xl-7">
            <div class="soft-panel auth-card p-4 p-md-5">
                <div class="section-kicker mb-2">Create traveler account</div>
                <h1 class="mb-3">Start your TravelEase journey</h1>
                <p class="mb-4">
                    Create a traveler account to manage bookings, itineraries, and support in one place.
                </p>

                <?php if ($error): ?>
                    <div class="alert alert-danger border-0 rounded-4 mb-4"><?= htmlspecialchars($error); ?></div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="alert alert-success border-0 rounded-4 mb-4"><?= htmlspecialchars($success); ?></div>
                <?php endif; ?>

                <form method="POST" action="<?= BASE_URL; ?>/auth/register-handler.php" class="row g-3">
                    <div class="col-12">
                        <label for="username" class="form-label auth-label">Username</label>
                        <input type="text" class="form-control auth-input" id="username" name="username" placeholder="e.g. tommy.shelby" required>
                    </div>

                    <div class="col-md-6">
                        <label for="first_name" class="form-label auth-label">First Name</label>
                        <input type="text" class="form-control auth-input" id="first_name" name="first_name" required>
                    </div>

                    <div class="col-md-6">
                        <label for="last_name" class="form-label auth-label">Last Name</label>
                        <input type="text" class="form-control auth-input" id="last_name" name="last_name" required>
                    </div>

                    <div class="col-12">
                        <label for="email" class="form-label auth-label">Email Address</label>
                        <input type="email" class="form-control auth-input" id="email" name="email" required>
                    </div>

                    <div class="col-md-4">
                        <label for="country_code" class="form-label auth-label">Country Code</label>
                        <select class="form-select auth-input" id="country_code" name="country_code" required>
                            <option value="+44" selected>United Kingdom (+44)</option>
                            <option value="+33">France (+33)</option>
                            <option value="+49">Germany (+49)</option>
                            <option value="+39">Italy (+39)</option>
                            <option value="+34">Spain (+34)</option>
                            <option value="+31">Netherlands (+31)</option>
                            <option value="+41">Switzerland (+41)</option>
                            <option value="+46">Sweden (+46)</option>
                            <option value="+47">Norway (+47)</option>
                            <option value="+45">Denmark (+45)</option>
                            <option value="+353">Ireland (+353)</option>
                            <option value="+880">Bangladesh (+880)</option>
                            <option value="+91">India (+91)</option>
                            <option value="+977">Nepal (+977)</option>
                            <option value="+92">Pakistan (+92)</option>
                            <option value="+94">Sri Lanka (+94)</option>
                            <option value="+81">Japan (+81)</option>
                            <option value="+82">South Korea (+82)</option>
                            <option value="+86">China (+86)</option>
                            <option value="+971">UAE (+971)</option>
                        </select>
                    </div>

                    <div class="col-md-8">
                        <label for="phone" class="form-label auth-label">Phone Number</label>
                        <input type="text" class="form-control auth-input" id="phone" name="phone" placeholder="e.g. 7123456789" required>
                    </div>

                    <div class="col-md-6">
                        <label for="password" class="form-label auth-label">Password</label>
                        <input type="password" class="form-control auth-input" id="password" name="password" required>
                    </div>

                    <div class="col-md-6">
                        <label for="confirm_password" class="form-label auth-label">Confirm Password</label>
                        <input type="password" class="form-control auth-input" id="confirm_password" name="confirm_password" required>
                    </div>

                    <div class="col-12 d-grid mt-3">
                        <button type="submit" class="btn btn-dark-soft">Create Account</button>
                    </div>
                </form>

                <p class="mt-4 mb-0 auth-note">
                    Already have an account?
                    <a href="<?= BASE_URL; ?>/login.php">Sign in here</a>
                </p>
            </div>
        </div>
    </div>
</div>

<?php require_once ROOT_PATH . '/includes/footer.php'; ?>