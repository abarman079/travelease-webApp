<?php
require_once dirname(__DIR__) . '/config/config.php';
require_once ROOT_PATH . '/config/db.php';
require_once ROOT_PATH . '/includes/auth.php';

requireRole(['traveler']);
$user = currentUser();

$success = getFlash('success');
$error = getFlash('error');

$stmt = $pdo->prepare("
    SELECT 
        b.booking_id,
        b.travel_date,
        b.traveler_count,
        b.total_amount,
        b.booking_status,
        b.booked_at,
        t.title,
        t.destination,
        t.category,
        p.payment_status,
        p.transaction_ref
    FROM bookings b
    INNER JOIN trips t ON b.trip_id = t.trip_id
    LEFT JOIN payments p ON b.booking_id = p.booking_id
    WHERE b.user_id = :user_id
    ORDER BY b.booked_at DESC
");
$stmt->execute(['user_id' => $user['user_id']]);
$bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

require_once ROOT_PATH . '/includes/header.php';
require_once ROOT_PATH . '/includes/navbar.php';
?>

<div class="container py-5">
    <div class="soft-panel p-4 p-md-5 mb-4">
        <div class="section-kicker mb-2">Traveler records</div>
        <h1 class="mb-3">My Bookings</h1>
        <p class="mb-0">
            Review your saved travel bookings and payment progress below.
        </p>
    </div>

    <?php if ($success): ?>
        <div class="alert alert-success border-0 rounded-4 mb-4"><?= htmlspecialchars($success); ?></div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert alert-danger border-0 rounded-4 mb-4"><?= htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <div class="row g-4">
        <?php if (!empty($bookings)): ?>
            <?php foreach ($bookings as $booking): ?>
                <div class="col-12">
                    <div class="soft-panel p-4 booking-record-card">
                        <div class="row g-3 align-items-center">
                            <div class="col-lg-4">
                                <div class="section-kicker mb-2"><?= htmlspecialchars($booking['category']); ?></div>
                                <h4 class="mb-2"><?= htmlspecialchars($booking['title']); ?></h4>
                                <p class="mb-0"><?= htmlspecialchars($booking['destination']); ?></p>
                            </div>

                            <div class="col-lg-5">
                                <div class="booking-record-grid">
                                    <div>
                                        <span class="booking-small-label">Travel Date</span>
                                        <strong><?= htmlspecialchars($booking['travel_date']); ?></strong>
                                    </div>
                                    <div>
                                        <span class="booking-small-label">Travelers</span>
                                        <strong><?= (int) $booking['traveler_count']; ?></strong>
                                    </div>
                                    <div>
                                        <span class="booking-small-label">Total Amount</span>
                                        <strong>£<?= number_format($booking['total_amount'], 2); ?></strong>
                                    </div>
                                    <div>
                                        <span class="booking-small-label">Booking Status</span>
                                        <strong class="booking-status"><?= htmlspecialchars(ucfirst($booking['booking_status'])); ?></strong>
                                    </div>
                                    <div>
                                        <span class="booking-small-label">Payment Status</span>
                                        <strong class="payment-status <?= (($booking['payment_status'] ?? '') === 'paid') ? 'paid' : 'pending'; ?>">
                                            <?= htmlspecialchars(ucfirst($booking['payment_status'] ?? 'pending')); ?>
                                        </strong>
                                    </div>
                                    <div>
                                        <span class="booking-small-label">Transaction</span>
                                        <strong><?= htmlspecialchars($booking['transaction_ref'] ?? 'Not paid yet'); ?></strong>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-3 text-lg-end">
                                <div class="booking-id-text mb-2">Booking #<?= (int) $booking['booking_id']; ?></div>
                                <div class="booking-date-text mb-3">Created: <?= htmlspecialchars($booking['booked_at']); ?></div>

                                <?php if (($booking['payment_status'] ?? '') !== 'paid'): ?>
                                    <a href="<?= BASE_URL; ?>/traveler/payment.php?booking_id=<?= (int) $booking['booking_id']; ?>" class="btn btn-dark-soft btn-sm">
                                        Pay Now
                                    </a>
                                <?php else: ?>
                                    <div class="d-flex flex-column gap-2 align-items-lg-end">
                                        <span class="dashboard-note">Payment Complete</span>
                                        <a href="<?= BASE_URL; ?>/traveler/itinerary.php?booking_id=<?= (int) $booking['booking_id']; ?>" class="btn btn-card btn-sm">
                                            Plan Trip
                                        </a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12">
                <div class="soft-panel p-4 p-md-5 text-center">
                    <h4 class="mb-3">No bookings yet</h4>
                    <p class="mb-4">You have not created any bookings yet.</p>
                    <a href="<?= BASE_URL; ?>/trips.php" class="btn btn-dark-soft">Explore Trips</a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once ROOT_PATH . '/includes/footer.php'; ?>