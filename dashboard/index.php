<?php
/**
 * Smart PDF - Dashboard Overview
 */
$active = 'dashboard';
$pageTitle = 'Dashboard';
require __DIR__ . '/../includes/dashboard_header.php';

$user = current_user();

// Stats
$stats = ['conversions' => 0, 'success' => 0, 'failed' => 0, 'uploads' => 0, 'today' => 0];
try {
    $stmt = db()->prepare('SELECT COUNT(*) FROM conversion_history WHERE user_id = ?');
    $stmt->execute([$user['id']]);
    $stats['conversions'] = (int)$stmt->fetchColumn();

    $stmt = db()->prepare("SELECT COUNT(*) FROM conversion_history WHERE user_id = ? AND status='success'");
    $stmt->execute([$user['id']]);
    $stats['success'] = (int)$stmt->fetchColumn();

    $stmt = db()->prepare("SELECT COUNT(*) FROM conversion_history WHERE user_id = ? AND status='failed'");
    $stmt->execute([$user['id']]);
    $stats['failed'] = (int)$stmt->fetchColumn();

    $stmt = db()->prepare('SELECT COUNT(*) FROM uploaded_files WHERE user_id = ?');
    $stmt->execute([$user['id']]);
    $stats['uploads'] = (int)$stmt->fetchColumn();

    $stmt = db()->prepare("SELECT COUNT(*) FROM conversion_history WHERE user_id = ? AND created_at >= CURDATE()");
    $stmt->execute([$user['id']]);
    $stats['today'] = (int)$stmt->fetchColumn();
} catch (Throwable $e) {}

// Recent activities
$recent = [];
try {
    $stmt = db()->prepare(
        'SELECT * FROM conversion_history WHERE user_id = ? ORDER BY created_at DESC LIMIT 8'
    );
    $stmt->execute([$user['id']]);
    $recent = $stmt->fetchAll();
} catch (Throwable $e) {}
?>

<!-- Welcome -->
<div class="profile-header mb-4">
    <span class="avatar"><?= strtoupper(substr($user['full_name'], 0, 1)) ?></span>
    <div>
        <div class="profile-name">Welcome, <?= e($user['full_name']) ?>!</div>
        <div class="profile-email"><?= e($user['email']) ?> &middot; Member since <?= date('M Y', strtotime($user['created_at'])) ?></div>
    </div>
    <div class="ms-auto">
        <a href="<?= APP_URL ?>/tools/index.php" class="btn btn-light-pill">
            <i class="fas fa-plus me-1"></i> New Conversion
        </a>
    </div>
</div>

<!-- Stats -->
<div class="row g-3 mb-4">
    <div class="col-6 col-lg-3">
        <div class="stat-card">
            <span class="stat-icon sc-red"><i class="fas fa-arrows-rotate"></i></span>
            <div class="stat-meta">
                <div class="num"><span data-counter="<?= $stats['conversions'] ?>">0</span></div>
                <div class="lbl">Total Conversions</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="stat-card">
            <span class="stat-icon sc-green"><i class="fas fa-circle-check"></i></span>
            <div class="stat-meta">
                <div class="num"><span data-counter="<?= $stats['success'] ?>">0</span></div>
                <div class="lbl">Successful</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="stat-card">
            <span class="stat-icon sc-blue"><i class="fas fa-upload"></i></span>
            <div class="stat-meta">
                <div class="num"><span data-counter="<?= $stats['uploads'] ?>">0</span></div>
                <div class="lbl">Files Uploaded</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="stat-card">
            <span class="stat-icon sc-orange"><i class="fas fa-calendar-day"></i></span>
            <div class="stat-meta">
                <div class="num"><span data-counter="<?= $stats['today'] ?>">0</span></div>
                <div class="lbl">Conversions Today</div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    <!-- Recent activity -->
    <div class="col-lg-8">
        <div class="dash-card">
            <div class="dash-card-header">
                <h5>Recent Activity</h5>
                <a href="<?= APP_URL ?>/dashboard/history.php" class="btn btn-sm btn-ghost">View All</a>
            </div>

            <?php if (empty($recent)): ?>
                <div class="empty-state">
                    <i class="fas fa-inbox"></i>
                    <h5>No activity yet</h5>
                    <p class="mb-3">Start using Smart PDF tools to see your history here.</p>
                    <a href="<?= APP_URL ?>/tools/index.php" class="btn btn-primary">
                        <i class="fas fa-rocket me-1"></i> Try a Tool
                    </a>
                </div>
            <?php else: ?>
                <ul class="activity-list">
                    <?php foreach ($recent as $row): ?>
                        <li>
                            <span class="activity-icon"><i class="fas <?= tool_icon($row['tool_name']) ?>"></i></span>
                            <div class="activity-main">
                                <div class="activity-title">
                                    <?= e(ucwords(str_replace('_', ' ', $row['tool_name']))) ?>:
                                    <?= e($row['original_filename']) ?>
                                </div>
                                <div class="activity-meta">
                                    <?= format_bytes((int)$row['file_size']) ?> &middot; <?= time_ago($row['created_at']) ?>
                                </div>
                            </div>
                            <span class="status-pill status-<?= $row['status'] ?>"><?= e($row['status']) ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
    </div>

    <!-- Quick actions -->
    <div class="col-lg-4">
        <div class="dash-card">
            <div class="dash-card-header"><h5>Quick Actions</h5></div>
            <div class="d-grid gap-2">
                <a href="<?= APP_URL ?>/tools/merge_pdf.php" class="btn btn-light-pill text-start">
                    <i class="fas fa-object-group me-2 text-brand"></i> Merge PDF
                </a>
                <a href="<?= APP_URL ?>/tools/compress_pdf.php" class="btn btn-light-pill text-start">
                    <i class="fas fa-compress me-2 text-brand"></i> Compress PDF
                </a>
                <a href="<?= APP_URL ?>/tools/pdf_to_word.php" class="btn btn-light-pill text-start">
                    <i class="fas fa-file-word me-2 text-brand"></i> PDF to Word
                </a>
                <a href="<?= APP_URL ?>/tools/protect_pdf.php" class="btn btn-light-pill text-start">
                    <i class="fas fa-lock me-2 text-brand"></i> Protect PDF
                </a>
                <a href="<?= APP_URL ?>/tools/index.php" class="btn btn-primary">
                    <i class="fas fa-toolbox me-2"></i> Browse All Tools
                </a>
            </div>
        </div>

        <div class="dash-card">
            <div class="dash-card-header"><h5>Account</h5></div>
            <div class="d-grid gap-2">
                <a href="<?= APP_URL ?>/dashboard/profile.php" class="btn btn-light-pill text-start">
                    <i class="fas fa-user me-2 text-brand"></i> Edit Profile
                </a>
                <a href="<?= APP_URL ?>/dashboard/settings.php" class="btn btn-light-pill text-start">
                    <i class="fas fa-gear me-2 text-brand"></i> Settings
                </a>
                <a href="<?= APP_URL ?>/auth/logout.php" class="btn btn-light-pill text-start text-danger">
                    <i class="fas fa-right-from-bracket me-2"></i> Logout
                </a>
            </div>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../includes/dashboard_footer.php'; ?>
