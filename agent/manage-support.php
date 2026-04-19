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
        st.ticket_id,
        st.subject,
        st.message,
        st.response_note,
        st.priority,
        st.status,
        st.created_at,
        st.updated_at,
        u.full_name,
        u.email,
        handler.full_name AS handler_name
    FROM support_tickets st
    INNER JOIN users u ON st.user_id = u.user_id
    LEFT JOIN users handler ON st.handled_by = handler.user_id
    ORDER BY 
        CASE st.status
            WHEN 'open' THEN 1
            WHEN 'in_progress' THEN 2
            WHEN 'resolved' THEN 3
            WHEN 'closed' THEN 4
            ELSE 5
        END,
        st.created_at DESC
");
$tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);

require_once ROOT_PATH . '/includes/header.php';
require_once ROOT_PATH . '/includes/navbar.php';
?>

<div class="container py-5">
    <div class="soft-panel p-4 p-md-5 mb-4">
        <div class="section-kicker mb-2">Agent tools</div>
        <h1 class="mb-3">Support Desk</h1>
        <p class="mb-0">Review traveler issues and provide ticket updates.</p>
    </div>

    <?php if ($success): ?>
        <div class="alert alert-success border-0 rounded-4 mb-4"><?= htmlspecialchars($success); ?></div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert alert-danger border-0 rounded-4 mb-4"><?= htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <div class="support-ticket-list">
        <?php foreach ($tickets as $ticket): ?>
            <div class="soft-panel p-4 p-md-4 support-ticket-card mb-4">
                <div class="d-flex flex-column flex-lg-row justify-content-between gap-3 mb-3">
                    <div>
                        <h4 class="mb-1"><?= htmlspecialchars($ticket['subject']); ?></h4>
                        <div class="support-meta">
                            Ticket #<?= (int) $ticket['ticket_id']; ?> •
                            <?= htmlspecialchars($ticket['full_name']); ?> •
                            <?= htmlspecialchars($ticket['email']); ?>
                        </div>
                    </div>
                    <div class="d-flex gap-2 flex-wrap">
                        <span class="mini-chip"><?= htmlspecialchars(ucfirst($ticket['priority'])); ?> priority</span>
                        <span class="status-chip"><?= htmlspecialchars(ucfirst(str_replace('_', ' ', $ticket['status']))); ?></span>
                    </div>
                </div>

                <p class="mb-3"><?= nl2br(htmlspecialchars($ticket['message'])); ?></p>

                <?php if (!empty($ticket['response_note'])): ?>
                    <div class="support-response-box mb-3">
                        <strong>Current Response:</strong><br>
                        <?= nl2br(htmlspecialchars($ticket['response_note'])); ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="<?= BASE_URL; ?>/agent/update-ticket.php" class="row g-3">
                    <input type="hidden" name="ticket_id" value="<?= (int) $ticket['ticket_id']; ?>">

                    <div class="col-md-4">
                        <label class="form-label auth-label">Status</label>
                        <select name="status" class="form-select auth-input" required>
                            <option value="open" <?= $ticket['status'] === 'open' ? 'selected' : ''; ?>>Open</option>
                            <option value="in_progress" <?= $ticket['status'] === 'in_progress' ? 'selected' : ''; ?>>In Progress</option>
                            <option value="resolved" <?= $ticket['status'] === 'resolved' ? 'selected' : ''; ?>>Resolved</option>
                            <option value="closed" <?= $ticket['status'] === 'closed' ? 'selected' : ''; ?>>Closed</option>
                        </select>
                    </div>

                    <div class="col-md-8">
                        <label class="form-label auth-label">Response Note</label>
                        <textarea name="response_note" rows="3" class="form-control auth-input"><?= htmlspecialchars($ticket['response_note'] ?? ''); ?></textarea>
                    </div>

                    <div class="col-12 d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <div class="support-meta">
                            Created: <?= htmlspecialchars($ticket['created_at']); ?>
                            <?php if (!empty($ticket['handler_name'])): ?>
                                • Last handled by: <?= htmlspecialchars($ticket['handler_name']); ?>
                            <?php endif; ?>
                        </div>

                        <button type="submit" class="btn btn-card">Update Ticket</button>
                    </div>
                </form>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<?php require_once ROOT_PATH . '/includes/footer.php'; ?>