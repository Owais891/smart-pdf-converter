<?php
/**
 * Smart PDF - Admin: Manage Users
 */
$active = 'admin';
$pageTitle = 'Manage Users';
require __DIR__ . '/../includes/dashboard_header.php';
require_admin();

$currentUser = current_user();

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $action = $_POST['action'] ?? '';

    if ($action === 'toggle_active') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id && $id !== (int)$currentUser['id']) {
            try {
                db()->prepare('UPDATE users SET is_active = 1 - is_active WHERE id = ?')->execute([$id]);
                flash('success', 'User status updated.');
            } catch (Throwable $e) { flash('error', 'Could not update user.'); }
        } else {
            flash('error', 'You cannot change your own status.');
        }
        redirect(APP_URL . '/admin/users.php');
    }

    if ($action === 'toggle_role') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id && $id !== (int)$currentUser['id']) {
            try {
                db()->prepare("UPDATE users SET role = IF(role='admin','user','admin') WHERE id = ?")->execute([$id]);
                flash('success', 'User role updated.');
            } catch (Throwable $e) { flash('error', 'Could not update role.'); }
        } else {
            flash('error', 'You cannot change your own role.');
        }
        redirect(APP_URL . '/admin/users.php');
    }

    if ($action === 'delete_user') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id && $id !== (int)$currentUser['id']) {
            try {
                db()->prepare('DELETE FROM users WHERE id = ?')->execute([$id]);
                flash('success', 'User deleted.');
            } catch (Throwable $e) { flash('error', 'Could not delete user.'); }
        } else {
            flash('error', 'You cannot delete your own account.');
        }
        redirect(APP_URL . '/admin/users.php');
    }
}

// Fetch all users
$users = [];
try {
    $users = db()->query('SELECT id, full_name, email, role, is_active, created_at, last_login FROM users ORDER BY created_at DESC')->fetchAll();
} catch (Throwable $e) {}
?>

<div class="dash-page-title">Manage Users</div>

<div class="dash-card">
    <div class="dash-card-header">
        <h5>All Users (<?= count($users) ?>)</h5>
    </div>
    <div class="table-responsive">
        <table class="history-table">
            <thead>
                <tr>
                    <th>ID</th><th>Name</th><th>Email</th><th>Role</th>
                    <th>Status</th><th>Joined</th><th>Last Login</th><th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($users)): ?>
                    <tr><td colspan="8" class="text-center text-muted py-4">No users registered.</td></tr>
                <?php else: foreach ($users as $u): ?>
                    <tr>
                        <td>#<?= (int)$u['id'] ?></td>
                        <td><?= e($u['full_name']) ?></td>
                        <td><?= e($u['email']) ?></td>
                        <td>
                            <span class="badge <?= $u['role'] === 'admin' ? 'bg-danger' : 'bg-secondary' ?>">
                                <?= e(ucfirst($u['role'])) ?>
                            </span>
                        </td>
                        <td>
                            <?php if ((int)$u['is_active'] === 1): ?>
                                <span class="status-pill status-success">Active</span>
                            <?php else: ?>
                                <span class="status-pill status-failed">Disabled</span>
                            <?php endif; ?>
                        </td>
                        <td><?= date('M j, Y', strtotime($u['created_at'])) ?></td>
                        <td><?= $u['last_login'] ? time_ago($u['last_login']) : 'Never' ?></td>
                        <td>
                            <div class="d-flex gap-1">
                                <form method="POST" class="d-inline">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="action" value="toggle_active">
                                    <input type="hidden" name="id" value="<?= (int)$u['id'] ?>">
                                    <button type="submit" class="btn btn-sm btn-icon" title="Toggle active">
                                        <i class="fas <?= (int)$u['is_active']===1 ? 'fa-circle-check text-success' : 'fa-circle-xmark text-danger' ?>"></i>
                                    </button>
                                </form>
                                <form method="POST" class="d-inline">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="action" value="toggle_role">
                                    <input type="hidden" name="id" value="<?= (int)$u['id'] ?>">
                                    <button type="submit" class="btn btn-sm btn-icon" title="Toggle role">
                                        <i class="fas fa-user-shield text-primary"></i>
                                    </button>
                                </form>
                                <?php if ((int)$u['id'] !== (int)$currentUser['id']): ?>
                                <form method="POST" class="d-inline" onsubmit="return confirm('Delete user <?= e($u['email']) ?>?');">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="action" value="delete_user">
                                    <input type="hidden" name="id" value="<?= (int)$u['id'] ?>">
                                    <button type="submit" class="btn btn-sm btn-icon text-danger" title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require __DIR__ . '/../includes/dashboard_footer.php'; ?>
