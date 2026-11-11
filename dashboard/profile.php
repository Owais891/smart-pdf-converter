<?php
/**
 * Smart PDF - User Profile
 */
$active = 'profile';
$pageTitle = 'My Profile';
require __DIR__ . '/../includes/dashboard_header.php';

$user = current_user();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $action = $_POST['action'] ?? '';

    if ($action === 'update_profile') {
        $name = trim($_POST['full_name'] ?? '');
        if (mb_strlen($name) < 2) {
            $errors['full_name'] = 'Name must be at least 2 characters.';
        }
        if (!$errors) {
            try {
                db()->prepare('UPDATE users SET full_name = ? WHERE id = ?')
                    ->execute([$name, $user['id']]);
                flash('success', 'Profile updated.');
                redirect(APP_URL . '/dashboard/profile.php');
            } catch (Throwable $e) {
                $errors['general'] = 'Could not save changes.';
            }
        }
    }

    if ($action === 'change_password') {
        $current = $_POST['current_password'] ?? '';
        $new     = $_POST['new_password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';

        if (!password_verify($current, $user['password'])) {
            $errors['current_password'] = 'Current password is incorrect.';
        } elseif (strlen($new) < 8) {
            $errors['new_password'] = 'New password must be at least 8 characters.';
        } elseif (!preg_match('/[A-Za-z]/', $new) || !preg_match('/[0-9]/', $new)) {
            $errors['new_password'] = 'Password must contain both letters and numbers.';
        } elseif ($new !== $confirm) {
            $errors['confirm_password'] = 'New passwords do not match.';
        }

        if (!$errors) {
            try {
                $hash = password_hash($new, PASSWORD_DEFAULT);
                db()->prepare('UPDATE users SET password = ? WHERE id = ?')->execute([$hash, $user['id']]);
                flash('success', 'Password changed successfully.');
                redirect(APP_URL . '/dashboard/profile.php');
            } catch (Throwable $e) {
                $errors['general'] = 'Could not change password.';
            }
        }
    }
}

// Stats
$stats = ['conversions' => 0, 'uploads' => 0];
try {
    $stmt = db()->prepare('SELECT COUNT(*) FROM conversion_history WHERE user_id = ?');
    $stmt->execute([$user['id']]);
    $stats['conversions'] = (int)$stmt->fetchColumn();
    $stmt = db()->prepare('SELECT COUNT(*) FROM uploaded_files WHERE user_id = ?');
    $stmt->execute([$user['id']]);
    $stats['uploads'] = (int)$stmt->fetchColumn();
} catch (Throwable $e) {}
?>

<div class="dash-page-title">My Profile</div>

<div class="profile-header mb-4">
    <span class="avatar"><?= strtoupper(substr($user['full_name'], 0, 1)) ?></span>
    <div>
        <div class="profile-name"><?= e($user['full_name']) ?></div>
        <div class="profile-email"><?= e($user['email']) ?></div>
        <div class="mt-1">
            <span class="badge bg-light text-dark"><?= e(ucfirst($user['role'])) ?></span>
            <?php if ((int)$user['is_active'] === 1): ?>
                <span class="badge bg-success">Active</span>
            <?php else: ?>
                <span class="badge bg-secondary">Disabled</span>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="stat-card">
            <span class="stat-icon sc-red"><i class="fas fa-arrows-rotate"></i></span>
            <div class="stat-meta">
                <div class="num"><?= $stats['conversions'] ?></div>
                <div class="lbl">Conversions</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card">
            <span class="stat-icon sc-blue"><i class="fas fa-upload"></i></span>
            <div class="stat-meta">
                <div class="num"><?= $stats['uploads'] ?></div>
                <div class="lbl">Uploads</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card">
            <span class="stat-icon sc-green"><i class="fas fa-calendar"></i></span>
            <div class="stat-meta">
                <div class="num"><?= date('M Y', strtotime($user['created_at'])) ?></div>
                <div class="lbl">Joined</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card">
            <span class="stat-icon sc-purple"><i class="fas fa-clock"></i></span>
            <div class="stat-meta">
                <div class="num"><?= $user['last_login'] ? date('M j', strtotime($user['last_login'])) : '—' ?></div>
                <div class="lbl">Last Login</div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-6">
        <div class="dash-card">
            <div class="dash-card-header"><h5>Edit Profile</h5></div>
            <?php if (!empty($errors['general'])): ?>
                <div class="alert alert-danger"><i class="fas fa-circle-exclamation me-2"></i><?= e($errors['general']) ?></div>
            <?php endif; ?>
            <form method="POST">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="update_profile">
                <div class="mb-3">
                    <label class="form-label" for="full_name">Full Name</label>
                    <input type="text" class="form-control <?= isset($errors['full_name']) ? 'is-invalid' : '' ?>"
                           id="full_name" name="full_name" value="<?= e($user['full_name']) ?>">
                    <?php if (isset($errors['full_name'])): ?><div class="invalid-feedback"><?= e($errors['full_name']) ?></div><?php endif; ?>
                </div>
                <div class="mb-3">
                    <label class="form-label">Email (read-only)</label>
                    <input type="email" class="form-control" value="<?= e($user['email']) ?>" disabled>
                </div>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-2"></i>Save Changes
                </button>
            </form>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="dash-card">
            <div class="dash-card-header"><h5>Change Password</h5></div>
            <form method="POST">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="change_password">
                <div class="mb-3">
                    <label class="form-label" for="current_password">Current Password</label>
                    <input type="password" class="form-control <?= isset($errors['current_password']) ? 'is-invalid' : '' ?>"
                           id="current_password" name="current_password" required>
                    <?php if (isset($errors['current_password'])): ?><div class="invalid-feedback"><?= e($errors['current_password']) ?></div><?php endif; ?>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="new_password">New Password</label>
                    <input type="password" class="form-control <?= isset($errors['new_password']) ? 'is-invalid' : '' ?>"
                           id="new_password" name="new_password" required>
                    <?php if (isset($errors['new_password'])): ?><div class="invalid-feedback"><?= e($errors['new_password']) ?></div><?php endif; ?>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="confirm_password">Confirm New Password</label>
                    <input type="password" class="form-control <?= isset($errors['confirm_password']) ? 'is-invalid' : '' ?>"
                           id="confirm_password" name="confirm_password" required>
                    <?php if (isset($errors['confirm_password'])): ?><div class="invalid-feedback"><?= e($errors['confirm_password']) ?></div><?php endif; ?>
                </div>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-key me-2"></i>Update Password
                </button>
            </form>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../includes/dashboard_footer.php'; ?>
