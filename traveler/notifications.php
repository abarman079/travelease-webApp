<?php
require_once dirname(__DIR__) . '/config/config.php';
require_once ROOT_PATH . '/config/db.php';
require_once ROOT_PATH . '/includes/auth.php';

requireRole(['traveler']);
$user = currentUser();

$success = getFlash('success');
$error = getFlash('error');

$stmt = $pdo->prepare("
    SELECT notification_id, message, type, is_read, created_at
    FROM notifications
    WHERE user_id = :user_id
    ORDER BY created_at DESC
");
$stmt->execute(['user_id' => $user['user_id']]);
$notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

$unreadCount = 0;
foreach ($notifications as $notification) {
    if ((int) $notification['is_read'] === 0) {
        $unreadCount++;
    }
}

require_once ROOT_PATH . '/includes/header.php';
require_once ROOT_PATH . '/includes/navbar.php';
?>

<div class="container py-5">
    <div class="soft-panel p-4 p-md-5 mb-4">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
            <div>
                <div class="section-kicker mb-2">Traveler notifications</div>
                <h1 class="mb-3">My Notifications</h1>
                <p class="mb-0">
                    Review important system updates related to your bookings, payments, and support activity.
                </p>
            </div>

            <?php if ($unreadCount > 0): ?>
                <form method="POST" action="<?= BASE_URL; ?>/traveler/mark-notifications-read.php">
                    <button type="submit" class="btn btn-card">Mark All as Read</button>
                </form>
            <?php endif; ?>
        </div>
    </div>

    <?php if ($success): ?>
        <div class="alert alert-success border-0 rounded-4 mb-4"><?= htmlspecialchars($success); ?></div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert alert-danger border-0 rounded-4 mb-4"><?= htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <div class="soft-panel p-4 p-md-5">
        <?php if (!empty($notifications)): ?>
            <div class="notification-list">
                <?php foreach ($notifications as $notification): ?>
                    <div class="notification-card <?= ((int) $notification['is_read'] === 0) ? 'unread' : ''; ?>">
                        <div class="d-flex justify-content-between align-items-start gap-3 mb-2">
                            <div class="d-flex gap-2 flex-wrap">
                                <span class="mini-chip"><?= htmlspecialchars(ucfirst($notification['type'])); ?></span>
                                <?php if ((int) $notification['is_read'] === 0): ?>
                                    <span class="status-chip">Unread</span>
                                <?php else: ?>
                                    <span class="mini-chip">Read</span>
                                <?php endif; ?>
                            </div>
                            <div class="notification-time"><?= htmlspecialchars($notification['created_at']); ?></div>
                        </div>

                        <p class="mb-0"><?= nl2br(htmlspecialchars($notification['message'])); ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p class="mb-0">No notifications yet.</p>
        <?php endif; ?>
    </div>
</div>

<?php require_once ROOT_PATH . '/includes/footer.php'; ?>