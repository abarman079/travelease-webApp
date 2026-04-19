<?php
require_once dirname(__DIR__) . '/config/config.php';
require_once ROOT_PATH . '/config/db.php';
require_once ROOT_PATH . '/includes/auth.php';

requireRole(['admin']);

$stmt = $pdo->query("
    SELECT 
        b.booking_id,
        b.travel_date,
        b.traveler_count,
        b.total_amount,
        b.booking_status,
        b.booked_at,
        u.full_name,
        u.email,
        t.title,
        t.destination,
        COALESCE(p.payment_status, 'pending') AS payment_status,
        p.transaction_ref
    FROM bookings b
    INNER JOIN users u ON b.user_id = u.user_id
    INNER JOIN trips t ON b.trip_id = t.trip_id
    LEFT JOIN payments p ON b.booking_id = p.booking_id
    ORDER BY b.booked_at DESC
");
$bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

require_once ROOT_PATH . '/includes/header.php';
require_once ROOT_PATH . '/includes/navbar.php';
?>

<div class="container py-5">
    <div class="soft-panel p-4 p-md-5 mb-4">
        <div class="section-kicker mb-2">Admin control</div>
        <h1 class="mb-3">Booking Overview</h1>
        <p class="mb-0">Review traveler bookings, booking status, and payment information.</p>
    </div>

    <div class="soft-panel table-soft p-3 p-md-4">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th>Booking</th>
                        <th>Traveler</th>
                        <th>Trip</th>
                        <th>Date</th>
                        <th>People</th>
                        <th>Amount</th>
                        <th>Booking Status</th>
                        <th>Payment</th>
                        <th>Transaction</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($bookings as $row): ?>
                        <tr>
                            <td>#<?= (int) $row['booking_id']; ?></td>
                            <td>
                                <div><?= htmlspecialchars($row['full_name']); ?></div>
                                <small class="text-muted"><?= htmlspecialchars($row['email']); ?></small>
                            </td>
                            <td>
                                <div><?= htmlspecialchars($row['title']); ?></div>
                                <small class="text-muted"><?= htmlspecialchars($row['destination']); ?></small>
                            </td>
                            <td><?= htmlspecialchars($row['travel_date']); ?></td>
                            <td><?= (int) $row['traveler_count']; ?></td>
                            <td>£<?= number_format($row['total_amount'], 2); ?></td>
                            <td><span class="status-chip"><?= htmlspecialchars(ucfirst($row['booking_status'])); ?></span></td>
                            <td><span class="mini-chip"><?= htmlspecialchars(ucfirst($row['payment_status'])); ?></span></td>
                            <td><?= htmlspecialchars($row['transaction_ref'] ?? '-'); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once ROOT_PATH . '/includes/footer.php'; ?>