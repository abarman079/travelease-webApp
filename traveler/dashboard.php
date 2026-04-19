<?php
require_once dirname(__DIR__) . '/config/config.php';
require_once ROOT_PATH . '/includes/auth.php';

requireRole(['traveler']);
$user = currentUser();

require_once ROOT_PATH . '/includes/header.php';
require_once ROOT_PATH . '/includes/navbar.php';
?>

<div class="container py-5">
    <div class="soft-panel p-4 p-md-5 dashboard-hero mb-4">
        <div class="section-kicker mb-2">Traveler dashboard</div>
        <h1 class="mb-3">Welcome back, <?= htmlspecialchars($user['first_name'] ?: $user['full_name']); ?></h1>
        <p class="mb-0">
            Manage your travel activities from one place, including bookings, payments, itinerary planning, and support.
        </p>
    </div>

    <div class="row g-4">
        <div class="col-md-6 col-xl-3">
            <div class="soft-panel dashboard-card p-4 h-100">
                <h5 class="mb-3">Browse trips</h5>
                <p class="mb-4">Explore England-focused travel packages and open any trip to continue your journey.</p>
                <a href="<?= BASE_URL; ?>/trips.php" class="btn btn-card">Explore Trips</a>
            </div>
        </div>

        <div class="col-md-6 col-xl-3">
            <div class="soft-panel dashboard-card p-4 h-100">
                <h5 class="mb-3">My bookings</h5>
                <p class="mb-4">Review your saved bookings, payment progress, and booking status.</p>
                <a href="<?= BASE_URL; ?>/traveler/my-bookings.php" class="btn btn-card">View Bookings</a>
            </div>
        </div>

        <div class="col-md-6 col-xl-3">
            <div class="soft-panel dashboard-card p-4 h-100">
                <h5 class="mb-3">Trip planning</h5>
                <p class="mb-4">Manage itinerary details for confirmed bookings and organize your travel timeline.</p>
                <a href="<?= BASE_URL; ?>/traveler/my-bookings.php" class="btn btn-card">Open Planning</a>
            </div>
        </div>

        <div class="col-md-6 col-xl-3">
            <div class="soft-panel dashboard-card p-4 h-100">
                <h5 class="mb-3">Support center</h5>
                <p class="mb-4">Submit support tickets and follow responses from the TravelEase team.</p>
                <a href="<?= BASE_URL; ?>/traveler/support.php" class="btn btn-card">Open Support</a>
            </div>
        </div>
    </div>
</div>

<?php require_once ROOT_PATH . '/includes/footer.php'; ?><?php
require_once dirname(__DIR__) . '/config/config.php';
require_once ROOT_PATH . '/config/db.php';
require_once ROOT_PATH . '/includes/auth.php';

requireRole(['traveler']);
$user = currentUser();

$unreadStmt = $pdo->prepare("
    SELECT COUNT(*)
    FROM notifications
    WHERE user_id = :user_id AND is_read = 0
");
$unreadStmt->execute(['user_id' => $user['user_id']]);
$unreadNotifications = (int) $unreadStmt->fetchColumn();

require_once ROOT_PATH . '/includes/header.php';
require_once ROOT_PATH . '/includes/navbar.php';
?>

<div class="container py-5">
    <div class="soft-panel p-4 p-md-5 dashboard-hero mb-4">
        <div class="section-kicker mb-2">Traveler dashboard</div>
        <h1 class="mb-3">Welcome back, <?= htmlspecialchars($user['first_name'] ?: $user['full_name']); ?></h1>
        <p class="mb-0">
            Manage your travel activities from one place, including bookings, payments, itinerary planning, support, and notifications.
        </p>
    </div>

    <div class="row g-4">
        <div class="col-md-6 col-xl-3">
            <div class="soft-panel dashboard-card p-4 h-100">
                <h5 class="mb-3">Browse trips</h5>
                <p class="mb-4">Explore England-focused travel packages and open any trip to continue your journey.</p>
                <a href="<?= BASE_URL; ?>/trips.php" class="btn btn-card">Explore Trips</a>
            </div>
        </div>

        <div class="col-md-6 col-xl-3">
            <div class="soft-panel dashboard-card p-4 h-100">
                <h5 class="mb-3">My bookings</h5>
                <p class="mb-4">Review your saved bookings, payment progress, and booking status.</p>
                <a href="<?= BASE_URL; ?>/traveler/my-bookings.php" class="btn btn-card">View Bookings</a>
            </div>
        </div>

        <div class="col-md-6 col-xl-3">
            <div class="soft-panel dashboard-card p-4 h-100">
                <h5 class="mb-3">Trip planning</h5>
                <p class="mb-4">Manage itinerary details for confirmed bookings and organize your travel timeline.</p>
                <a href="<?= BASE_URL; ?>/traveler/my-bookings.php" class="btn btn-card">Open Planning</a>
            </div>
        </div>

        <div class="col-md-6 col-xl-3">
            <div class="soft-panel dashboard-card p-4 h-100">
                <h5 class="mb-3">Support center</h5>
                <p class="mb-4">Submit support tickets and follow responses from the TravelEase team.</p>
                <a href="<?= BASE_URL; ?>/traveler/support.php" class="btn btn-card">Open Support</a>
            </div>
        </div>

        <div class="col-md-6 col-xl-3">
            <div class="soft-panel dashboard-card p-4 h-100">
                <h5 class="mb-3">Notifications</h5>
                <p class="mb-4">
                    Review booking, payment, and support updates.
                    <?php if ($unreadNotifications > 0): ?>
                        <br><span class="dashboard-note mt-2"><?= $unreadNotifications; ?> unread</span>
                    <?php endif; ?>
                </p>
                <a href="<?= BASE_URL; ?>/traveler/notifications.php" class="btn btn-card">Open Notifications</a>
            </div>
        </div>
    </div>
</div>

<?php require_once ROOT_PATH . '/includes/footer.php'; ?>