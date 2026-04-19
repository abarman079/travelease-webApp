<?php
require_once dirname(__DIR__) . '/config/config.php';
require_once ROOT_PATH . '/config/db.php';
require_once ROOT_PATH . '/includes/auth.php';

requireRole(['admin']);

$stmt = $pdo->query("
    SELECT 
        u.user_id,
        u.username,
        u.first_name,
        u.last_name,
        u.full_name,
        u.email,
        u.phone,
        u.status,
        u.created_at,
        r.role_name
    FROM users u
    INNER JOIN roles r ON u.role_id = r.role_id
    ORDER BY u.created_at DESC
");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

require_once ROOT_PATH . '/includes/header.php';
require_once ROOT_PATH . '/includes/navbar.php';
?>

<div class="container py-5">
    <div class="soft-panel p-4 p-md-5 mb-4">
        <div class="section-kicker mb-2">Admin control</div>
        <h1 class="mb-3">Manage Users</h1>
        <p class="mb-0">View all registered users and their roles in the system.</p>
    </div>

    <div class="soft-panel table-soft p-3 p-md-4">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Created</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $row): ?>
                        <tr>
                            <td>#<?= (int) $row['user_id']; ?></td>
                            <td><?= htmlspecialchars($row['full_name']); ?></td>
                            <td><?= htmlspecialchars($row['username'] ?? '-'); ?></td>
                            <td><?= htmlspecialchars($row['email']); ?></td>
                            <td><?= htmlspecialchars($row['phone'] ?? '-'); ?></td>
                            <td><span class="mini-chip"><?= htmlspecialchars(ucfirst($row['role_name'])); ?></span></td>
                            <td><span class="status-chip"><?= htmlspecialchars(ucfirst($row['status'])); ?></span></td>
                            <td><?= htmlspecialchars($row['created_at']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once ROOT_PATH . '/includes/footer.php'; ?>