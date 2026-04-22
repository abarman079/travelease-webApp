<?php
require_once dirname(__DIR__) . '/config/config.php';
require_once ROOT_PATH . '/config/db.php';
require_once ROOT_PATH . '/includes/auth.php';

requireRole(['traveler']);
$user = currentUser();

$bookingId = isset($_GET['booking_id']) ? (int) $_GET['booking_id'] : 0;

$stmt = $pdo->prepare("
    SELECT 
        b.booking_id,
        b.travel_date,
        b.traveler_count,
        b.total_amount,
        b.booking_status,
        t.title,
        t.destination,
        t.category,
        t.duration_days,
        p.payment_id,
        p.payment_status,
        p.payment_method
    FROM bookings b
    INNER JOIN trips t ON b.trip_id = t.trip_id
    LEFT JOIN payments p ON b.booking_id = p.booking_id
    WHERE b.booking_id = :booking_id AND b.user_id = :user_id
    LIMIT 1
");
$stmt->execute([
    'booking_id' => $bookingId,
    'user_id' => $user['user_id']
]);
$booking = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$booking) {
    setFlash('error', 'Booking not found.');
    header('Location: ' . BASE_URL . '/traveler/my-bookings.php');
    exit;
}

$error = getFlash('error');
$success = getFlash('success');

require_once ROOT_PATH . '/includes/header.php';
require_once ROOT_PATH . '/includes/navbar.php';
?>

<div class="container py-5">
    <div class="row g-4">
        <div class="col-lg-7">
            <div class="soft-panel p-4 p-md-5 payment-form-card">
                <div class="section-kicker mb-2">Mock payment</div>
                <h1 class="mb-3">Complete Your Payment</h1>
                <p class="mb-4">
                    This is a demonstration payment flow for your project. No real money is charged.
                </p>

                <?php if ($error): ?>
                    <div class="alert alert-danger border-0 rounded-4 mb-4"><?= htmlspecialchars($error); ?></div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="alert alert-success border-0 rounded-4 mb-4"><?= htmlspecialchars($success); ?></div>
                <?php endif; ?>

                <?php if (($booking['payment_status'] ?? '') === 'paid'): ?>
                    <div class="alert alert-success border-0 rounded-4 mb-0">
                        Payment already completed for this booking.
                    </div>
                <?php else: ?>
                    <form method="POST" action="<?= BASE_URL; ?>/traveler/process-payment.php" class="row g-3">
                        <input type="hidden" name="booking_id" value="<?= (int) $booking['booking_id']; ?>">

                        <div class="col-12">
                            <label for="payment_method" class="form-label auth-label">Payment Method</label>
                            <select class="form-select auth-input" id="payment_method" name="payment_method" required>
                                <option value="">Select a method</option>
                                <option value="Credit Card">Credit Card</option>
                                <option value="Debit Card">Debit Card</option>
                                <option value="Bank Transfer">Bank Transfer</option>
                                <option value="Digital Wallet">Digital Wallet</option>
                            </select>
                        </div>

                        <div class="col-md-8">
                            <label for="account_number" class="form-label auth-label">Account Number</label>
                            <input
                                type="text"
                                class="form-control auth-input"
                                id="account_number"
                                name="account_number"
                                maxlength="16"
                                inputmode="numeric"
                                pattern="\d{12,16}"
                                placeholder="1234567812345678"
                                required
                            >
                        </div>

                        <div class="col-md-4">
                            <label for="cvv" class="form-label auth-label">CVV</label>
                            <input
                                type="text"
                                class="form-control auth-input"
                                id="cvv"
                                name="cvv"
                                maxlength="4"
                                inputmode="numeric"
                                pattern="\d{3,4}"
                                placeholder="123"
                                required
                            >
                        </div>

                        <div class="col-12 d-grid mt-3">
                            <button type="submit" class="btn btn-dark-soft">Confirm Mock Payment</button>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="soft-panel p-4 p-md-4 payment-summary-card">
                <div class="section-kicker mb-2">Booking summary</div>
                <h3 class="mb-3"><?= htmlspecialchars($booking['title']); ?></h3>

                <div class="booking-info-list mb-4">
                    <div class="booking-info-row">
                        <span>Destination</span>
                        <strong><?= htmlspecialchars($booking['destination']); ?></strong>
                    </div>
                    <div class="booking-info-row">
                        <span>Category</span>
                        <strong><?= htmlspecialchars($booking['category']); ?></strong>
                    </div>
                    <div class="booking-info-row">
                        <span>Travel Date</span>
                        <strong><?= htmlspecialchars($booking['travel_date']); ?></strong>
                    </div>
                    <div class="booking-info-row">
                        <span>Travelers</span>
                        <strong><?= (int) $booking['traveler_count']; ?></strong>
                    </div>
                    <div class="booking-info-row">
                        <span>Booking Status</span>
                        <strong><?= htmlspecialchars(ucfirst($booking['booking_status'])); ?></strong>
                    </div>
                </div>

                <div class="price-box mb-4">
                    <div class="price-label">Total amount</div>
                    <div class="price-value">£<?= number_format($booking['total_amount'], 2); ?></div>
                </div>

                <a href="<?= BASE_URL; ?>/traveler/my-bookings.php" class="btn btn-hollow w-100">Back to My Bookings</a>
            </div>
        </div>
    </div>
</div>

<?php require_once ROOT_PATH . '/includes/footer.php'; ?>




