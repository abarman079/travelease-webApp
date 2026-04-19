<?php
require_once dirname(__DIR__) . '/config/config.php';
require_once ROOT_PATH . '/config/db.php';
require_once ROOT_PATH . '/includes/auth.php';

requireRole(['admin']);
$user = currentUser();

$totalUsers = (int) $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$totalTrips = (int) $pdo->query("SELECT COUNT(*) FROM trips")->fetchColumn();
$totalBookings = (int) $pdo->query("SELECT COUNT(*) FROM bookings")->fetchColumn();
$totalPaid = (int) $pdo->query("SELECT COUNT(*) FROM payments WHERE payment_status = 'paid'")->fetchColumn();

require_once ROOT_PATH . '/includes/header.php';
require_once ROOT_PATH . '/includes/navbar.php';
?>

<div class="container py-5">
    <div class="soft-panel p-4 p-md-5 staff-hero mb-4">
        <div class="section-kicker mb-2">Admin dashboard</div>
        <h1 class="mb-3">Welcome, <?= htmlspecialchars($user['first_name'] ?: $user['full_name']); ?></h1>
        <p class="mb-0">
            Monitor users, bookings, trips, payments, and support activity from one central admin area.
        </p>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-md-6 col-xl-3">
            <div class="soft-panel staff-stat-card p-4 h-100">
                <div class="stat-label">Total Users</div>
                <div class="stat-value"><?= $totalUsers; ?></div>
            </div>
        </div>

        <div class="col-md-6 col-xl-3">
            <div class="soft-panel staff-stat-card p-4 h-100">
                <div class="stat-label">Total Trips</div>
                <div class="stat-value"><?= $totalTrips; ?></div>
            </div>
        </div>

        <div class="col-md-6 col-xl-3">
            <div class="soft-panel staff-stat-card p-4 h-100">
                <div class="stat-label">Total Bookings</div>
                <div class="stat-value"><?= $totalBookings; ?></div>
            </div>
        </div>

        <div class="col-md-6 col-xl-3">
            <div class="soft-panel staff-stat-card p-4 h-100">
                <div class="stat-label">Paid Payments</div>
                <div class="stat-value"><?= $totalPaid; ?></div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-md-6 col-xl-4">
            <div class="soft-panel management-card p-4 h-100">
                <h5 class="mb-3">Manage Users</h5>
                <p class="mb-4">View registered users, roles, contact details, and account status.</p>
                <a href="<?= BASE_URL; ?>/admin/manage-users.php" class="btn btn-card">Open User Management</a>
            </div>
        </div>

        <div class="col-md-6 col-xl-4">
            <div class="soft-panel management-card p-4 h-100">
                <h5 class="mb-3">Booking Overview</h5>
                <p class="mb-4">Review booking records, trip details, traveler information, and payment status.</p>
                <a href="<?= BASE_URL; ?>/admin/manage-bookings.php" class="btn btn-card">Open Booking Overview</a>
            </div>
        </div>

        <div class="col-md-6 col-xl-4">
            <div class="soft-panel management-card p-4 h-100">
                <h5 class="mb-3">Support Tickets</h5>
                <p class="mb-4">Review traveler issues and update ticket progress from the admin help desk.</p>
                <a href="<?= BASE_URL; ?>/admin/manage-support.php" class="btn btn-card">Open Support Desk</a>
            </div>
        </div>
    </div>
</div>

<?php require_once ROOT_PATH . '/includes/footer.php'; ?>