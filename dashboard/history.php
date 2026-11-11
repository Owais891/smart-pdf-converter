<?php
/**
 * Smart PDF - Conversion History
 */
$active = 'history';
$pageTitle = 'Conversion History';
require __DIR__ . '/../includes/dashboard_header.php';

$user = current_user();

// Handle delete
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $action = $_POST['action'] ?? '';

    if ($action === 'delete_one') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            try {
                $stmt = db()->prepare('DELETE FROM conversion_history WHERE id = ? AND user_id = ?');
                $stmt->execute([$id, $user['id']]);
                flash('success', 'Record deleted.');
            } catch (Throwable $e) {
                flash('error', 'Could not delete record.');
            }
        }
        redirect(APP_URL . '/dashboard/history.php');
    }

    if ($action === 'delete_all') {
        try {
            db()->prepare('DELETE FROM conversion_history WHERE user_id = ?')->execute([$user['id']]);
            flash('success', 'All history cleared.');
        } catch (Throwable $e) {
            flash('error', 'Could not clear history.');
        }
        redirect(APP_URL . '/dashboard/history.php');
    }

    if ($action === 'delete_selected') {
        $ids = array_map('intval', $_POST['ids'] ?? []);
        if ($ids) {
            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            try {
                $stmt = db()->prepare(
                    "DELETE FROM conversion_history WHERE id IN ($placeholders) AND user_id = ?"
                );
                $stmt->execute(array_merge($ids, [$user['id']]));
                flash('success', count($ids) . ' record(s) deleted.');
            } catch (Throwable $e) {
                flash('error', 'Could not delete selected records.');
            }
        }
        redirect(APP_URL . '/dashboard/history.php');
    }
}

// Fetch all history
$rows = [];
try {
    $stmt = db()->prepare(
        'SELECT * FROM conversion_history WHERE user_id = ? ORDER BY created_at DESC LIMIT 200'
    );
    $stmt->execute([$user['id']]);
    $rows = $stmt->fetchAll();
} catch (Throwable $e) {}

$total = count($rows);
?>

<div class="dash-page-title">Conversion History</div>

<div class="row g-3 mb-3">
    <div class="col-md-3">
        <div class="stat-card">
            <span class="stat-icon sc-red"><i class="fas fa-list"></i></span>
            <div class="stat-meta">
                <div class="num"><?= $total ?></div>
                <div class="lbl">Total Records</div>
            </div>
        </div>
    </div>
</div>

<div class="dash-card">
    <div class="dash-card-header">
        <h5>Your Recent Conversions</h5>
        <form method="POST" class="d-inline" onsubmit="return confirm('Delete ALL conversion history?');">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="delete_all">
            <button type="submit" class="btn btn-sm btn-outline-danger" <?= $total ? '' : 'disabled' ?>>
                <i class="fas fa-trash me-1"></i> Clear All
            </button>
        </form>
    </div>

    <?php if (empty($rows)): ?>
        <div class="empty-state">
            <i class="fas fa-folder-open"></i>
            <h5>No conversion history yet</h5>
            <p class="mb-3">Your conversion records will appear here automatically.</p>
            <a href="<?= APP_URL ?>/tools/index.php" class="btn btn-primary">
                <i class="fas fa-rocket me-1"></i> Try a Tool
            </a>
        </div>
    <?php else: ?>
        <form method="POST" id="bulkHistoryForm">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="delete_selected">
            <div class="table-responsive">
                <table class="history-table">
                    <thead>
                        <tr>
                            <th style="width:30px"><input type="checkbox" id="selectAll"></th>
                            <th>Tool</th>
                            <th>File</th>
                            <th>Size</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($rows as $r): ?>
                            <tr>
                                <td><input type="checkbox" name="ids[]" value="<?= (int)$r['id'] ?>"></td>
                                <td>
                                    <span class="tool-pill">
                                        <i class="fas <?= tool_icon($r['tool_name']) ?> me-1"></i>
                                        <?= e(ucwords(str_replace('_', ' ', $r['tool_name']))) ?>
                                    </span>
                                </td>
                                <td><?= e($r['original_filename']) ?></td>
                                <td><?= format_bytes((int)$r['file_size']) ?></td>
                                <td><span class="status-pill status-<?= e($r['status']) ?>"><?= e($r['status']) ?></span></td>
                                <td><?= time_ago($r['created_at']) ?></td>
                                <td>
                                    <form method="POST" class="d-inline" data-confirm-delete>
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="action" value="delete_one">
                                        <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-icon text-danger" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div class="mt-3">
                <button type="submit" class="btn btn-sm btn-outline-danger">
                    <i class="fas fa-trash me-1"></i> Delete Selected
                </button>
            </div>
        </form>
    <?php endif; ?>
</div>

<script>
document.getElementById('selectAll')?.addEventListener('change', function () {
    document.querySelectorAll('#bulkHistoryForm input[name="ids[]"]').forEach(cb => cb.checked = this.checked);
});
</script>

<?php require __DIR__ . '/../includes/dashboard_footer.php'; ?>
