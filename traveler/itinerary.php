<?php
require_once dirname(__DIR__) . '/config/config.php';
require_once ROOT_PATH . '/config/db.php';
require_once ROOT_PATH . '/includes/auth.php';

requireRole(['traveler']);
$user = currentUser();

$bookingId = isset($_GET['booking_id']) ? (int) $_GET['booking_id'] : 0;

$success = getFlash('success');
$error = getFlash('error');

$bookingStmt = $pdo->prepare("
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
        i.itinerary_id,
        i.title AS itinerary_title,
        i.notes AS itinerary_notes
    FROM bookings b
    INNER JOIN trips t ON b.trip_id = t.trip_id
    LEFT JOIN itineraries i ON b.booking_id = i.booking_id
    WHERE b.booking_id = :booking_id AND b.user_id = :user_id
    LIMIT 1
");
$bookingStmt->execute([
    'booking_id' => $bookingId,
    'user_id' => $user['user_id']
]);
$booking = $bookingStmt->fetch(PDO::FETCH_ASSOC);

if (!$booking) {
    setFlash('error', 'Booking not found.');
    header('Location: ' . BASE_URL . '/traveler/my-bookings.php');
    exit;
}

if ($booking['booking_status'] !== 'confirmed') {
    setFlash('error', 'You can plan a trip only after the booking is confirmed.');
    header('Location: ' . BASE_URL . '/traveler/my-bookings.php');
    exit;
}

$items = [];
if (!empty($booking['itinerary_id'])) {
    $itemStmt = $pdo->prepare("
        SELECT item_id, day_number, item_title, location, activity_time, notes
        FROM itinerary_items
        WHERE itinerary_id = :itinerary_id
        ORDER BY day_number ASC, item_id ASC
    ");
    $itemStmt->execute(['itinerary_id' => $booking['itinerary_id']]);
    $items = $itemStmt->fetchAll(PDO::FETCH_ASSOC);
}

require_once ROOT_PATH . '/includes/header.php';
require_once ROOT_PATH . '/includes/navbar.php';
?>

<div class="container py-5">
    <div class="row g-4">
        <div class="col-lg-7">
            <div class="soft-panel p-4 p-md-5 itinerary-main-card mb-4">
                <div class="section-kicker mb-2">Trip planning</div>
                <h1 class="mb-3">My Itinerary</h1>
                <p class="mb-4">
                    Create your itinerary overview and organize each day of your journey.
                </p>

                <?php if ($success): ?>
                    <div class="alert alert-success border-0 rounded-4 mb-4"><?= htmlspecialchars($success); ?></div>
                <?php endif; ?>

                <?php if ($error): ?>
                    <div class="alert alert-danger border-0 rounded-4 mb-4"><?= htmlspecialchars($error); ?></div>
                <?php endif; ?>

                <form method="POST" action="<?= BASE_URL; ?>/traveler/save-itinerary.php" class="row g-3 mb-4">
                    <input type="hidden" name="booking_id" value="<?= (int) $booking['booking_id']; ?>">

                    <div class="col-12">
                        <label for="title" class="form-label auth-label">Itinerary Title</label>
                        <input
                            type="text"
                            class="form-control auth-input"
                            id="title"
                            name="title"
                            value="<?= htmlspecialchars($booking['itinerary_title'] ?? ''); ?>"
                            placeholder="e.g. London Weekend Escape Plan"
                            required
                        >
                    </div>

                    <div class="col-12">
                        <label for="notes" class="form-label auth-label">General Notes</label>
                        <textarea
                            class="form-control auth-input"
                            id="notes"
                            name="notes"
                            rows="4"
                            placeholder="Write any useful notes about your trip..."><?= htmlspecialchars($booking['itinerary_notes'] ?? ''); ?></textarea>
                    </div>

                    <div class="col-12 d-grid">
                        <button type="submit" class="btn btn-dark-soft">
                            <?= !empty($booking['itinerary_id']) ? 'Update Itinerary' : 'Create Itinerary'; ?>
                        </button>
                    </div>
                </form>

                <?php if (!empty($booking['itinerary_id'])): ?>
                    <hr class="my-4">

                    <div class="section-kicker mb-2">Day-by-day plan</div>
                    <h3 class="mb-3">Add itinerary item</h3>

                    <form method="POST" action="<?= BASE_URL; ?>/traveler/add-itinerary-item.php" class="row g-3">
                        <input type="hidden" name="itinerary_id" value="<?= (int) $booking['itinerary_id']; ?>">
                        <input type="hidden" name="booking_id" value="<?= (int) $booking['booking_id']; ?>">

                        <div class="col-md-4">
                            <label for="day_number" class="form-label auth-label">Day Number</label>
                            <input type="number" class="form-control auth-input" id="day_number" name="day_number" min="1" max="<?= (int) $booking['duration_days']; ?>" required>
                        </div>

                        <div class="col-md-8">
                            <label for="item_title" class="form-label auth-label">Activity Title</label>
                            <input type="text" class="form-control auth-input" id="item_title" name="item_title" placeholder="e.g. Thames River Evening Cruise" required>
                        </div>

                        <div class="col-md-6">
                            <label for="location" class="form-label auth-label">Location</label>
                            <input type="text" class="form-control auth-input" id="location" name="location" placeholder="e.g. Central London">
                        </div>

                        <div class="col-md-6">
                            <label for="activity_time" class="form-label auth-label">Time</label>
                            <input type="text" class="form-control auth-input" id="activity_time" name="activity_time" placeholder="e.g. 6:30 PM">
                        </div>

                        <div class="col-12">
                            <label for="item_notes" class="form-label auth-label">Notes</label>
                            <textarea class="form-control auth-input" id="item_notes" name="item_notes" rows="3" placeholder="Any notes for this itinerary item..."></textarea>
                        </div>

                        <div class="col-12 d-grid">
                            <button type="submit" class="btn btn-card">Add Itinerary Item</button>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="soft-panel p-4 p-md-4 itinerary-side-card mb-4">
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
                        <span>Duration</span>
                        <strong><?= (int) $booking['duration_days']; ?> days</strong>
                    </div>
                    <div class="booking-info-row">
                        <span>Travelers</span>
                        <strong><?= (int) $booking['traveler_count']; ?></strong>
                    </div>
                </div>

                <a href="<?= BASE_URL; ?>/traveler/my-bookings.php" class="btn btn-hollow w-100">Back to My Bookings</a>
            </div>

            <div class="soft-panel p-4 p-md-4 itinerary-items-card">
                <div class="section-kicker mb-2">Saved items</div>
                <h3 class="mb-3">Itinerary Timeline</h3>

                <?php if (!empty($items)): ?>
                    <div class="timeline-list">
                        <?php foreach ($items as $item): ?>
                            <div class="timeline-item">
                                <div class="timeline-day">Day <?= (int) $item['day_number']; ?></div>
                                <h6 class="mb-1"><?= htmlspecialchars($item['item_title']); ?></h6>
                                <?php if (!empty($item['location'])): ?>
                                    <p class="mb-1"><strong>Location:</strong> <?= htmlspecialchars($item['location']); ?></p>
                                <?php endif; ?>
                                <?php if (!empty($item['activity_time'])): ?>
                                    <p class="mb-1"><strong>Time:</strong> <?= htmlspecialchars($item['activity_time']); ?></p>
                                <?php endif; ?>
                                <?php if (!empty($item['notes'])): ?>
                                    <p class="mb-0"><?= htmlspecialchars($item['notes']); ?></p>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="mb-0">No itinerary items added yet.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once ROOT_PATH . '/includes/footer.php'; ?>