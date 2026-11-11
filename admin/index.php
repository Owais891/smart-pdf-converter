<?php
/**
 * Smart PDF - Admin Panel Dashboard
 */
$active = 'admin';
$pageTitle = 'Admin Panel';
require __DIR__ . '/../includes/dashboard_header.php';
require_admin();

$user = current_user();

// Aggregate stats
$stats = [
    'users'       => 0,
    'conversions' => 0,
    'uploads'     => 0,
    'messages'    => 0,
    'unread'      => 0,
    'success'     => 0,
    'failed'      => 0,
    'today_users' => 0,
];
try {
    $stats['users']       = (int)db()->query('SELECT COUNT(*) FROM users')->fetchColumn();
    $stats['conversions'] = (int)db()->query('SELECT COUNT(*) FROM conversion_history')->fetchColumn();
    $stats['uploads']     = (int)db()->query('SELECT COUNT(*) FROM uploaded_files')->fetchColumn();
    $stats['messages']    = (int)db()->query('SELECT COUNT(*) FROM contact_messages')->fetchColumn();
    $stats['unread']      = (int)db()->query("SELECT COUNT(*) FROM contact_messages WHERE is_read=0")->fetchColumn();
    $stats['success']     = (int)db()->query("SELECT COUNT(*) FROM conversion_history WHERE status='success'")->fetchColumn();
    $stats['failed']      = (int)db()->query("SELECT COUNT(*) FROM conversion_history WHERE status='failed'")->fetchColumn();
    $stats['today_users'] = (int)db()->query("SELECT COUNT(*) FROM users WHERE DATE(created_at)=CURDATE()")->fetchColumn();
} catch (Throwable $e) {}

// Recent users
$recentUsers = [];
try {
    $recentUsers = db()->query('SELECT id, full_name, email, role, created_at FROM users ORDER BY created_at DESC LIMIT 6')->fetchAll();
} catch (Throwable $e) {}

// Recent conversions
$recentConversions = [];
try {
    $recentConversions = db()->query('SELECT * FROM conversion_history ORDER BY created_at DESC LIMIT 8')->fetchAll();
} catch (Throwable $e) {}
?>

<div class="dash-page-title">Admin Panel &mdash; Overview</div>

<!-- Stats grid -->
<div class="row g-3 mb-4">
    <div class="col-6 col-lg-3">
        <a href="<?= APP_URL ?>/admin/users.php" class="text-decoration-none">
            <div class="stat-card">
                <span class="stat-icon sc-blue"><i class="fas fa-users"></i></span>
                <div class="stat-meta">
                    <div class="num"><span data-counter="<?= $stats['users'] ?>">0</span></div>
                    <div class="lbl">Total Users</div>
                </div>
            </div>
        </a>
    </div>
    <div class="col-6 col-lg-3">
        <a href="<?= APP_URL ?>/admin/conversions.php" class="text-decoration-none">
            <div class="stat-card">
                <span class="stat-icon sc-red"><i class="fas fa-arrows-rotate"></i></span>
                <div class="stat-meta">
                    <div class="num"><span data-counter="<?= $stats['conversions'] ?>">0</span></div>
                    <div class="lbl">Conversions</div>
                </div>
            </div>
        </a>
    </div>
    <div class="col-6 col-lg-3">
        <div class="stat-card">
            <span class="stat-icon sc-green"><i class="fas fa-upload"></i></span>
            <div class="stat-meta">
                <div class="num"><span data-counter="<?= $stats['uploads'] ?>">0</span></div>
                <div class="lbl">Uploaded Files</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <a href="<?= APP_URL ?>/admin/messages.php" class="text-decoration-none">
            <div class="stat-card">
                <span class="stat-icon sc-orange"><i class="fas fa-envelope"></i></span>
                <div class="stat-meta">
                    <div class="num"><span data-counter="<?= $stats['messages'] ?>">0</span></div>
                    <div class="lbl">Messages (<?= $stats['unread'] ?> unread)</div>
                </div>
            </div>
        </a>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-6 col-lg-3">
        <div class="stat-card">
            <span class="stat-icon sc-green"><i class="fas fa-circle-check"></i></span>
            <div class="stat-meta">
                <div class="num"><?= $stats['success'] ?></div>
                <div class="lbl">Successful</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="stat-card">
            <span class="stat-icon sc-red"><i class="fas fa-circle-xmark"></i></span>
            <div class="stat-meta">
                <div class="num"><?= $stats['failed'] ?></div>
                <div class="lbl">Failed</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="stat-card">
            <span class="stat-icon sc-purple"><i class="fas fa-user-plus"></i></span>
            <div class="stat-meta">
                <div class="num"><?= $stats['today_users'] ?></div>
                <div class="lbl">New Users Today</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <a href="<?= APP_URL ?>/admin/cleanup.php" class="text-decoration-none">
            <div class="stat-card">
                <span class="stat-icon sc-blue"><i class="fas fa-broom"></i></span>
                <div class="stat-meta">
                    <div class="num" style="font-size:1rem;line-height:1.4">Run Cleanup</div>
                    <div class="lbl">Delete expired files</div>
                </div>
            </div>
        </a>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-7">
        <div class="dash-card">
            <div class="dash-card-header">
                <h5>Recent Conversions</h5>
                <a href="<?= APP_URL ?>/admin/conversions.php" class="btn btn-sm btn-ghost">View All</a>
            </div>
            <div class="table-responsive">
                <table class="history-table">
                    <thead>
                        <tr><th>Tool</th><th>File</th><th>Status</th><th>When</th></tr>
                    </thead>
                    <tbody>
                        <?php if (empty($recentConversions)): ?>
                            <tr><td colspan="4" class="text-center text-muted py-4">No conversions yet.</td></tr>
                        <?php else: foreach ($recentConversions as $r): ?>
                            <tr>
                                <td><span class="tool-pill"><?= e(ucwords(str_replace('_', ' ', $r['tool_name']))) ?></span></td>
                                <td><?= e($r['original_filename']) ?></td>
                                <td><span class="status-pill status-<?= e($r['status']) ?>"><?= e($r['status']) ?></span></td>
                                <td><?= time_ago($r['created_at']) ?></td>
                            </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="dash-card">
            <div class="dash-card-header">
                <h5>Newest Users</h5>
                <a href="<?= APP_URL ?>/admin/users.php" class="btn btn-sm btn-ghost">Manage</a>
            </div>
            <ul class="activity-list">
                <?php if (empty($recentUsers)): ?>
                    <li class="empty-state"><i class="fas fa-user-slash"></i><p>No users yet.</p></li>
                <?php else: foreach ($recentUsers as $u): ?>
                    <li>
                        <span class="activity-icon"><i class="fas fa-user"></i></span>
                        <div class="activity-main">
                            <div class="activity-title"><?= e($u['full_name']) ?></div>
                            <div class="activity-meta"><?= e($u['email']) ?> &middot; <?= time_ago($u['created_at']) ?></div>
                        </div>
                        <span class="badge <?= $u['role'] === 'admin' ? 'bg-danger' : 'bg-secondary' ?>"><?= e($u['role']) ?></span>
                    </li>
                <?php endforeach; endif; ?>
            </ul>
        </div>

        <div class="dash-card">
            <div class="dash-card-header"><h5>Admin Quick Links</h5></div>
            <div class="d-grid gap-2">
                <a href="<?= APP_URL ?>/admin/users.php" class="btn btn-light-pill text-start">
                    <i class="fas fa-users me-2 text-brand"></i> Manage Users
                </a>
                <a href="<?= APP_URL ?>/admin/conversions.php" class="btn btn-light-pill text-start">
                    <i class="fas fa-clock-rotate-left me-2 text-brand"></i> All Conversions
                </a>
                <a href="<?= APP_URL ?>/admin/messages.php" class="btn btn-light-pill text-start">
                    <i class="fas fa-envelope me-2 text-brand"></i> Contact Messages
                </a>
                <a href="<?= APP_URL ?>/admin/cleanup.php" class="btn btn-light-pill text-start">
                    <i class="fas fa-broom me-2 text-brand"></i> Run File Cleanup
                </a>
            </div>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../includes/dashboard_footer.php'; ?>
