<?php
require_once dirname(__DIR__) . '/config/config.php';
require_once ROOT_PATH . '/config/db.php';
require_once ROOT_PATH . '/includes/auth.php';

requireRole(['agent']);
$user = currentUser();

$totalTrips = (int) $pdo->query("SELECT COUNT(*) FROM trips")->fetchColumn();

$myTripStmt = $pdo->prepare("SELECT COUNT(*) FROM trips WHERE created_by = :user_id");
$myTripStmt->execute(['user_id' => $user['user_id']]);
$myTrips = (int) $myTripStmt->fetchColumn();

$myBookingStmt = $pdo->prepare("
    SELECT COUNT(*)
    FROM bookings b
    INNER JOIN trips t ON b.trip_id = t.trip_id
    WHERE t.created_by = :user_id
");
$myBookingStmt->execute(['user_id' => $user['user_id']]);
$myBookings = (int) $myBookingStmt->fetchColumn();

require_once ROOT_PATH . '/includes/header.php';
require_once ROOT_PATH . '/includes/navbar.php';
?>

<div class="container py-5">
    <div class="soft-panel p-4 p-md-5 staff-hero mb-4">
        <div class="section-kicker mb-2">Agent dashboard</div>
        <h1 class="mb-3">Welcome, <?= htmlspecialchars($user['first_name'] ?: $user['full_name']); ?></h1>
        <p class="mb-0">
            Add travel offers, review available packages, and help with traveler support tickets.
        </p>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="soft-panel staff-stat-card p-4 h-100">
                <div class="stat-label">All Trips</div>
                <div class="stat-value"><?= $totalTrips; ?></div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="soft-panel staff-stat-card p-4 h-100">
                <div class="stat-label">My Added Trips</div>
                <div class="stat-value"><?= $myTrips; ?></div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="soft-panel staff-stat-card p-4 h-100">
                <div class="stat-label">Bookings on My Trips</div>
                <div class="stat-value"><?= $myBookings; ?></div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-md-6 col-xl-4">
            <div class="soft-panel management-card p-4 h-100">
                <h5 class="mb-3">Manage Trips</h5>
                <p class="mb-4">View available trips and manage the travel offers you add to the system.</p>
                <a href="<?= BASE_URL; ?>/agent/manage-trips.php" class="btn btn-card">Open Trip Management</a>
            </div>
        </div>

        <div class="col-md-6 col-xl-4">
            <div class="soft-panel management-card p-4 h-100">
                <h5 class="mb-3">Add New Trip</h5>
                <p class="mb-4">Create a new travel package with destination, pricing, duration, and available slots.</p>
                <a href="<?= BASE_URL; ?>/agent/add-trip.php" class="btn btn-card">Add Travel Offer</a>
            </div>
        </div>

        <div class="col-md-6 col-xl-4">
            <div class="soft-panel management-card p-4 h-100">
                <h5 class="mb-3">Support Desk</h5>
                <p class="mb-4">Review traveler issues and update support ticket progress from the agent panel.</p>
                <a href="<?= BASE_URL; ?>/agent/manage-support.php" class="btn btn-card">Open Support Desk</a>
            </div>
        </div>
    </div>
</div>

<?php require_once ROOT_PATH . '/includes/footer.php'; ?>