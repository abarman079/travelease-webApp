<?php
require_once __DIR__ . '/config/config.php';
require_once ROOT_PATH . '/config/db.php';
require_once ROOT_PATH . '/includes/header.php';
require_once ROOT_PATH . '/includes/navbar.php';

$stmt = $pdo->query("SELECT trip_id, title, destination, category, description, duration_days, price, available_slots, status
                     FROM trips
                     WHERE status = 'active'
                     ORDER BY created_at DESC");
$trips = $stmt->fetchAll(PDO::FETCH_ASSOC);

function getTripAccentClass(string $destination): string {
    $destination = strtolower($destination);

    if (strpos($destination, 'london') !== false) return 'london';
    if (strpos($destination, 'cotswolds') !== false) return 'cotswolds';
    if (strpos($destination, 'lake') !== false) return 'lake';
    if (strpos($destination, 'bath') !== false) return 'bath';
    if (strpos($destination, 'york') !== false) return 'york';
    if (strpos($destination, 'cornwall') !== false) return 'cornwall';

    return '';
}
?>

<div class="container py-5">
    <div class="soft-panel p-4 p-md-5 mb-5">
        <div class="section-heading">
            <div class="section-kicker mb-2">Curated England Journeys</div>
            <h1 class="fw-bold mb-3">Explore Refined UK Travel Experiences</h1>
            <p class="mb-0">
                Discover editorial-style city breaks, countryside retreats, coastal stays, and heritage-led journeys
                designed for a modern travel audience.
            </p>
        </div>
    </div>

    <div class="row g-4">
        <?php if (!empty($trips)): ?>
            <?php foreach ($trips as $trip): ?>
                <?php $accentClass = getTripAccentClass($trip['destination']); ?>
                <div class="col-md-6 col-xl-4">
                    <div class="trip-card <?= htmlspecialchars($accentClass); ?>">
                        <div class="card-body p-4 d-flex flex-column h-100">
                            <div class="d-flex justify-content-between align-items-start gap-3 mb-3">
                                <span class="trip-badge"><?= htmlspecialchars($trip['category']); ?></span>
                                <span class="trip-meta"><?= (int)$trip['available_slots']; ?> seats left</span>
                            </div>

                            <h4 class="mb-2"><?= htmlspecialchars($trip['title']); ?></h4>
                            <p class="trip-meta mb-2">
                                <strong>Destination:</strong> <?= htmlspecialchars($trip['destination']); ?>
                            </p>
                            <p class="trip-meta mb-3">
                                <strong>Duration:</strong> <?= (int)$trip['duration_days']; ?> days
                            </p>

                            <p class="mb-4">
                                <?= htmlspecialchars(mb_strimwidth($trip['description'], 0, 140, '...')); ?>
                            </p>

                            <div class="mt-auto d-flex justify-content-between align-items-center gap-3">
                                <div>
                                    <div class="trip-meta">Starting from</div>
                                    <div class="trip-price">£<?= number_format($trip['price'], 2); ?></div>
                                </div>
                                <a href="<?= BASE_URL; ?>/trip-details.php?id=<?= (int)$trip['trip_id']; ?>" class="btn btn-card">
                                    View Details
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12">
                <div class="alert alert-warning">
                    No active trips found in the database.
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once ROOT_PATH . '/includes/footer.php'; ?>