<?php
require_once ROOT_PATH . '/includes/auth.php';

$currentPage = basename($_SERVER['PHP_SELF']);
$user = currentUser();
$role = currentUserRole();
$dashboardUrl = dashboardPathForRole($role);

function isActive(array $pages, string $currentPage): string
{
    return in_array($currentPage, $pages, true) ? 'active-nav' : '';
}
?>

<nav class="navbar navbar-expand-lg floating-nav py-3">
    <div class="container">
        <a class="navbar-brand brand-mark" href="<?= BASE_URL; ?>/index.php">
            TravelEase
        </a>

        <button class="navbar-toggler border-0 shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="mainNav">
            <ul class="navbar-nav mx-auto align-items-lg-center gap-lg-2">
                <li class="nav-item">
                    <a class="nav-link <?= isActive(['index.php'], $currentPage); ?>" href="<?= BASE_URL; ?>/index.php">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= isActive(['about.php'], $currentPage); ?>" href="<?= BASE_URL; ?>/about.php">About</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= isActive(['trips.php', 'trip-details.php'], $currentPage); ?>" href="<?= BASE_URL; ?>/trips.php">Trips</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= isActive(['contact.php'], $currentPage); ?>" href="<?= BASE_URL; ?>/contact.php">Support</a>
                </li>

                <?php if (isLoggedIn()): ?>
                    <li class="nav-item">
                        <a class="nav-link <?= isActive(['dashboard.php'], $currentPage); ?>" href="<?= $dashboardUrl; ?>">Dashboard</a>
                    </li>
                <?php endif; ?>

                <?php if (isLoggedIn() && $role === 'traveler'): ?>
                    <li class="nav-item">
                        <a class="nav-link <?= isActive(['notifications.php'], $currentPage); ?>" href="<?= BASE_URL; ?>/traveler/notifications.php">Notifications</a>
                    </li>
                <?php endif; ?>
            </ul>

            <div class="d-flex gap-2 mt-3 mt-lg-0 align-items-center flex-wrap">
                <?php if (isLoggedIn()): ?>
                    <span class="user-chip">
                        <?= htmlspecialchars($user['full_name']); ?>
                    </span>
                    <a href="<?= BASE_URL; ?>/logout.php" class="btn btn-hollow">Logout</a>
                <?php else: ?>
                    <a href="<?= BASE_URL; ?>/login.php" class="btn btn-hollow">Login</a>
                    <a href="<?= BASE_URL; ?>/register.php" class="btn btn-dark-soft">Register</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>