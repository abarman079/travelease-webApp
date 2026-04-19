<?php
require_once dirname(__DIR__) . '/config/config.php';
require_once ROOT_PATH . '/config/db.php';
require_once ROOT_PATH . '/includes/auth.php';

requireRole(['traveler']);
$user = currentUser();

$success = getFlash('success');
$error = getFlash('error');

$stmt = $pdo->prepare("
    SELECT ticket_id, subject, message, response_note, priority, status, created_at, updated_at
    FROM support_tickets
    WHERE user_id = :user_id
    ORDER BY created_at DESC
");
$stmt->execute(['user_id' => $user['user_id']]);
$tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);

require_once ROOT_PATH . '/includes/header.php';
require_once ROOT_PATH . '/includes/navbar.php';
?>

<div class="container py-5">
    <div class="row g-4">
        <div class="col-lg-5">
            <div class="soft-panel p-4 p-md-5 support-form-card">
                <div class="section-kicker mb-2">Traveler support</div>
                <h1 class="mb-3">Submit a Support Ticket</h1>
                <p class="mb-4">
                    Tell us about your issue and the team will review it.
                </p>

                <?php if ($success): ?>
                    <div class="alert alert-success border-0 rounded-4 mb-4"><?= htmlspecialchars($success); ?></div>
                <?php endif; ?>

                <?php if ($error): ?>
                    <div class="alert alert-danger border-0 rounded-4 mb-4"><?= htmlspecialchars($error); ?></div>
                <?php endif; ?>

                <form method="POST" action="<?= BASE_URL; ?>/traveler/save-ticket.php" class="row g-3">
                    <div class="col-12">
                        <label class="form-label auth-label">Subject</label>
                        <input type="text" name="subject" class="form-control auth-input" required>
                    </div>

                    <div class="col-12">
                        <label class="form-label auth-label">Priority</label>
                        <select name="priority" class="form-select auth-input" required>
                            <option value="">Select priority</option>
                            <option value="low">Low</option>
                            <option value="medium" selected>Medium</option>
                            <option value="high">High</option>
                        </select>
                    </div>

                    <div class="col-12">
                        <label class="form-label auth-label">Message</label>
                        <textarea name="message" rows="5" class="form-control auth-input" required></textarea>
                    </div>

                    <div class="col-12 d-grid">
                        <button type="submit" class="btn btn-dark-soft">Submit Ticket</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="col-lg-7">
            <div class="soft-panel p-4 p-md-5 support-list-card">
                <div class="section-kicker mb-2">My tickets</div>
                <h3 class="mb-4">Support History</h3>

                <?php if (!empty($tickets)): ?>
                    <div class="support-ticket-list">
                        <?php foreach ($tickets as $ticket): ?>
                            <div class="support-ticket-card">
                                <div class="d-flex justify-content-between align-items-start gap-3 mb-2">
                                    <div>
                                        <h5 class="mb-1"><?= htmlspecialchars($ticket['subject']); ?></h5>
                                        <div class="support-meta">
                                            <?= htmlspecialchars(ucfirst($ticket['priority'])); ?> priority •
                                            <?= htmlspecialchars(ucfirst(str_replace('_', ' ', $ticket['status']))); ?>
                                        </div>
                                    </div>
                                    <span class="mini-chip">#<?= (int) $ticket['ticket_id']; ?></span>
                                </div>

                                <p class="mb-3"><?= nl2br(htmlspecialchars($ticket['message'])); ?></p>

                                <?php if (!empty($ticket['response_note'])): ?>
                                    <div class="support-response-box mb-3">
                                        <strong>Response:</strong><br>
                                        <?= nl2br(htmlspecialchars($ticket['response_note'])); ?>
                                    </div>
                                <?php endif; ?>

                                <div class="support-meta">
                                    Created: <?= htmlspecialchars($ticket['created_at']); ?>
                                    <?php if (!empty($ticket['updated_at'])): ?>
                                        • Updated: <?= htmlspecialchars($ticket['updated_at']); ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="mb-0">No support tickets submitted yet.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once ROOT_PATH . '/includes/footer.php'; ?>