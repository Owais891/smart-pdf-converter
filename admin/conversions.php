<?php
/**
 * Smart PDF - Admin: All Conversions
 */
$active = 'admin';
$pageTitle = 'All Conversions';
require __DIR__ . '/../includes/dashboard_header.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $id = (int)($_POST['id'] ?? 0);
    if ($id) {
        try {
            db()->prepare('DELETE FROM conversion_history WHERE id = ?')->execute([$id]);
            flash('success', 'Conversion record deleted.');
        } catch (Throwable $e) { flash('error', 'Could not delete record.'); }
    }
    redirect(APP_URL . '/admin/conversions.php');
}

$rows = [];
try {
    $rows = db()->query(
        'SELECT h.*, u.full_name AS user_name, u.email AS user_email
         FROM conversion_history h
         LEFT JOIN users u ON u.id = h.user_id
         ORDER BY h.created_at DESC LIMIT 300'
    )->fetchAll();
} catch (Throwable $e) {}
?>

<div class="dash-page-title">All Conversion History</div>

<div class="dash-card">
    <div class="dash-card-header"><h5>Total Records: <?= count($rows) ?></h5></div>
    <div class="table-responsive">
        <table class="history-table">
            <thead>
                <tr>
                    <th>ID</th><th>User</th><th>Tool</th><th>File</th>
                    <th>Size</th><th>Status</th><th>Date</th><th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($rows)): ?>
                    <tr><td colspan="8" class="text-center text-muted py-4">No conversion records.</td></tr>
                <?php else: foreach ($rows as $r): ?>
                    <tr>
                        <td>#<?= (int)$r['id'] ?></td>
                        <td>
                            <?php if ($r['user_name']): ?>
                                <?= e($r['user_name']) ?><br>
                                <small class="text-muted"><?= e($r['user_email']) ?></small>
                            <?php else: ?>
                                <span class="text-muted">Guest</span>
                            <?php endif; ?>
                        </td>
                        <td><span class="tool-pill"><?= e(ucwords(str_replace('_', ' ', $r['tool_name']))) ?></span></td>
                        <td><?= e($r['original_filename']) ?></td>
                        <td><?= format_bytes((int)$r['file_size']) ?></td>
                        <td><span class="status-pill status-<?= e($r['status']) ?>"><?= e($r['status']) ?></span></td>
                        <td><?= time_ago($r['created_at']) ?></td>
                        <td>
                            <form method="POST" class="d-inline" onsubmit="return confirm('Delete this record?');">
                                <?= csrf_field() ?>
                                <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
                                <button type="submit" class="btn btn-sm btn-icon text-danger">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require __DIR__ . '/../includes/dashboard_footer.php'; ?>
