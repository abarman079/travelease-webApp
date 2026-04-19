<?php
require_once dirname(__DIR__) . '/config/config.php';
require_once ROOT_PATH . '/config/db.php';
require_once ROOT_PATH . '/includes/auth.php';

requireRole(['agent']);
$user = currentUser();

$success = getFlash('success');
$error = getFlash('error');

$stmt = $pdo->query("
    SELECT 
        t.trip_id,
        t.title,
        t.destination,
        t.category,
        t.duration_days,
        t.price,
        t.available_slots,
        t.status,
        t.created_at,
        u.full_name AS creator_name,
        u.user_id AS creator_id
    FROM trips t
    LEFT JOIN users u ON t.created_by = u.user_id
    ORDER BY t.created_at DESC
");
$trips = $stmt->fetchAll(PDO::FETCH_ASSOC);

require_once ROOT_PATH . '/includes/header.php';
require_once ROOT_PATH . '/includes/navbar.php';
?>

<div class="container py-5">
    <div class="soft-panel p-4 p-md-5 mb-4">
        <div class="section-kicker mb-2">Agent tools</div>
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
            <div>
                <h1 class="mb-3">Manage Trips</h1>
                <p class="mb-0">View available trips and manage the offers you create.</p>
            </div>
            <div>
                <a href="<?= BASE_URL; ?>/agent/add-trip.php" class="btn btn-dark-soft">Add New Trip</a>
            </div>
        </div>
    </div>

    <?php if ($success): ?>
        <div class="alert alert-success border-0 rounded-4 mb-4"><?= htmlspecialchars($success); ?></div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert alert-danger border-0 rounded-4 mb-4"><?= htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <div class="row g-4">
        <?php foreach ($trips as $trip): ?>
            <div class="col-md-6 col-xl-4">
                <div class="soft-panel management-card p-4 h-100">
                    <div class="d-flex justify-content-between align-items-start gap-3 mb-3">
                        <span class="mini-chip"><?= htmlspecialchars($trip['category']); ?></span>
                        <span class="status-chip"><?= htmlspecialchars(ucfirst($trip['status'])); ?></span>
                    </div>

                    <h5 class="mb-2"><?= htmlspecialchars($trip['title']); ?></h5>
                    <p class="mb-2"><strong>Destination:</strong> <?= htmlspecialchars($trip['destination']); ?></p>
                    <p class="mb-2"><strong>Duration:</strong> <?= (int) $trip['duration_days']; ?> days</p>
                    <p class="mb-2"><strong>Price:</strong> £<?= number_format($trip['price'], 2); ?></p>
                    <p class="mb-3"><strong>Seats:</strong> <?= (int) $trip['available_slots']; ?></p>

                    <p class="mb-0 text-muted small">
                        Created by:
                        <?= $trip['creator_id'] ? htmlspecialchars($trip['creator_name']) : 'System / Seed Data'; ?>
                    </p>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<?php require_once ROOT_PATH . '/includes/footer.php'; ?>