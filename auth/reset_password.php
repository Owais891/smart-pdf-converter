<?php
/**
 * Smart PDF - Reset Password (consume token)
 */
require_once __DIR__ . '/../includes/functions.php';

if (is_logged_in()) {
    redirect(APP_URL . '/dashboard/index.php');
}

$token = $_GET['token'] ?? '';
$errors = [];
$valid = false;
$userId = null;

// Validate token presence + correctness
if ($token) {
    try {
        $hashed = hash('sha256', $token);
        $stmt = db()->prepare(
            'SELECT id, user_id, expires_at, used
             FROM password_resets
             WHERE token_hash = ? LIMIT 1'
        );
        $stmt->execute([$hashed]);
        $row = $stmt->fetch();
        if ($row) {
            if ((int)$row['used'] === 1) {
                $errors['general'] = 'This reset link has already been used.';
            } elseif (strtotime($row['expires_at']) < time()) {
                $errors['general'] = 'This reset link has expired. Please request a new one.';
            } else {
                $valid = true;
                $userId = (int)$row['user_id'];
            }
        } else {
            $errors['general'] = 'Invalid reset link.';
        }
    } catch (Throwable $e) {
        $errors['general'] = 'Could not validate the reset link.';
    }
} else {
    $errors['general'] = 'No reset token provided.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $valid) {
    csrf_verify();
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm_password'] ?? '';

    if (strlen($password) < 8) {
        $errors['password'] = 'Password must be at least 8 characters.';
    } elseif (!preg_match('/[A-Za-z]/', $password) || !preg_match('/[0-9]/', $password)) {
        $errors['password'] = 'Password must contain both letters and numbers.';
    } elseif ($password !== $confirm) {
        $errors['confirm_password'] = 'Passwords do not match.';
    }

    if (!$errors) {
        try {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            db()->prepare('UPDATE users SET password = ? WHERE id = ?')->execute([$hash, $userId]);
            db()->prepare('UPDATE password_resets SET used = 1 WHERE token_hash = ?')->execute([hash('sha256', $token)]);

            flash('success', 'Your password has been reset. Please log in.');
            redirect(APP_URL . '/auth/login.php');
        } catch (Throwable $e) {
            $errors['general'] = 'Could not reset password. Please try again.';
        }
    }
}

$pageTitle = 'Reset Password';
$pageDescription = 'Set a new password for your Smart PDF account.';
$bodyClass = 'auth-page';
require __DIR__ . '/../includes/header.php';
?>
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-7 col-lg-5">
            <div class="form-card">
                <div class="form-title">
                    <div class="brand-logo mb-3 mx-auto"><img src="<?= APP_URL ?>/assets/img/smartpdf-logo.svg" alt="Smart PDF logo"></div>
                    <h2>Reset Password</h2>
                    <p>Choose a new password for your account.</p>
                </div>

                <?php if (!empty($errors['general'])): ?>
                    <div class="alert alert-danger"><i class="fas fa-circle-exclamation me-2"></i><?= e($errors['general']) ?></div>
                    <div class="text-center">
                        <a href="<?= APP_URL ?>/auth/forgot_password.php" class="btn btn-primary">Request a new link</a>
                    </div>
                <?php else: ?>
                    <form method="POST" action="" novalidate>
                        <?= csrf_field() ?>
                        <input type="hidden" name="token" value="<?= e($token) ?>">

                        <div class="mb-3">
                            <label class="form-label" for="password">New Password</label>
                            <input type="password" class="form-control <?= isset($errors['password']) ? 'is-invalid' : '' ?>"
                                   id="password" name="password" placeholder="Min 8 characters" required autofocus>
                            <?php if (isset($errors['password'])): ?>
                                <div class="invalid-feedback"><?= e($errors['password']) ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="confirm_password">Confirm Password</label>
                            <input type="password" class="form-control <?= isset($errors['confirm_password']) ? 'is-invalid' : '' ?>"
                                   id="confirm_password" name="confirm_password" placeholder="Re-enter password" required>
                            <?php if (isset($errors['confirm_password'])): ?>
                                <div class="invalid-feedback"><?= e($errors['confirm_password']) ?></div>
                            <?php endif; ?>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 mb-3">
                            <i class="fas fa-key me-2"></i>Reset Password
                        </button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
