<?php
require_once dirname(__DIR__) . '/config/config.php';
require_once ROOT_PATH . '/config/db.php';
require_once ROOT_PATH . '/includes/auth.php';

requireRole(['traveler']);

$tripId = isset($_GET['id']) ? (int) $_GET['id'] : 0;

$stmt = $pdo->prepare("
    SELECT trip_id, title, destination, category, description, duration_days, price, available_slots, status
    FROM trips
    WHERE trip_id = :trip_id AND status = 'active'
    LIMIT 1
");
$stmt->execute(['trip_id' => $tripId]);
$trip = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$trip) {
    setFlash('error', 'Trip not found or unavailable.');
    header('Location: ' . BASE_URL . '/trips.php');
    exit;
}

$error = getFlash('error');

require_once ROOT_PATH . '/includes/header.php';
require_once ROOT_PATH . '/includes/navbar.php';
?>

<div class="container py-5">
    <div class="row g-4">
        <div class="col-lg-7">
            <div class="soft-panel p-4 p-md-5 booking-form-card">
                <div class="section-kicker mb-2">Traveler booking</div>
                <h1 class="mb-3">Book Your Trip</h1>
                <p class="mb-4">
                    Complete the form below to create a booking for this selected journey.
                </p>

                <?php if ($error): ?>
                    <div class="alert alert-danger border-0 rounded-4 mb-4"><?= htmlspecialchars($error); ?></div>
                <?php endif; ?>

                <form method="POST" action="<?= BASE_URL; ?>/traveler/create-booking.php" class="row g-3">
                    <input type="hidden" name="trip_id" value="<?= (int) $trip['trip_id']; ?>">
                    <input type="hidden" name="unit_price" value="<?= htmlspecialchars($trip['price']); ?>">

                    <div class="col-12">
                        <label for="travel_date" class="form-label auth-label">Travel Date</label>
                        <input type="date" class="form-control auth-input" id="travel_date" name="travel_date" required>
                    </div>

                    <div class="col-12">
                        <label for="traveler_count" class="form-label auth-label">Number of Travelers</label>
                        <input type="number" class="form-control auth-input" id="traveler_count" name="traveler_count" min="1" max="<?= (int) $trip['available_slots']; ?>" value="1" required>
                    </div>

                    <div class="col-12 d-grid mt-3">
                        <button type="submit" class="btn btn-dark-soft">Create Booking</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="soft-panel p-4 p-md-4 booking-summary-card">
                <div class="section-kicker mb-2">Selected trip</div>
                <h3 class="mb-3"><?= htmlspecialchars($trip['title']); ?></h3>

                <div class="booking-info-list mb-4">
                    <div class="booking-info-row">
                        <span>Destination</span>
                        <strong><?= htmlspecialchars($trip['destination']); ?></strong>
                    </div>
                    <div class="booking-info-row">
                        <span>Category</span>
                        <strong><?= htmlspecialchars($trip['category']); ?></strong>
                    </div>
                    <div class="booking-info-row">
                        <span>Duration</span>
                        <strong><?= (int) $trip['duration_days']; ?> days</strong>
                    </div>
                    <div class="booking-info-row">
                        <span>Available Seats</span>
                        <strong><?= (int) $trip['available_slots']; ?></strong>
                    </div>
                </div>

                <div class="price-box">
                    <div class="price-label">Price per traveler</div>
                    <div class="price-value">£<?= number_format($trip['price'], 2); ?></div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once ROOT_PATH . '/includes/footer.php'; ?>