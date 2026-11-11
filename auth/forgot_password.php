<?php
/**
 * Smart PDF - Forgot Password (request reset link)
 *
 * NOTE: This is a local-host / university project setup. Instead of
 * actually sending email (which requires SMTP config), the generated
 * reset link is displayed on screen AND stored in the password_resets
 * table. In production you should call mail() / PHPMailer to email it.
 */
require_once __DIR__ . '/../includes/functions.php';

if (is_logged_in()) {
    redirect(APP_URL . '/dashboard/index.php');
}

$errors = [];
$sent = false;
$demoLink = null;
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $email = trim($_POST['email'] ?? '');

    if (!is_valid_email($email)) {
        $errors['email'] = 'Please enter a valid email address.';
    } else {
        try {
            $stmt = db()->prepare('SELECT id, full_name FROM users WHERE email = ? LIMIT 1');
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            if ($user) {
                // Invalidate previous tokens
                db()->prepare('UPDATE password_resets SET used = 1 WHERE user_id = ? AND used = 0')
                    ->execute([$user['id']]);

                $token   = bin2hex(random_bytes(32));
                $hashed  = hash('sha256', $token);
                $expires = date('Y-m-d H:i:s', time() + 3600); // 1 hour

                db()->prepare(
                    'INSERT INTO password_resets (user_id, token_hash, expires_at) VALUES (?, ?, ?)'
                )->execute([$user['id'], $hashed, $expires]);

                // Build reset URL
                $link = APP_URL . '/auth/reset_password.php?token=' . $token;

                // === Mail sending ===================================================
                // For a local-host university project we DON'T have SMTP set up.
                // We display the link on screen so the user can click it.
                // In production, replace the following lines with:
                //   mail($email, 'Reset your Smart PDF password', "Click here: $link", $headers);
                $demoLink = $link;
                // =====================================================================

                $sent = true;
            } else {
                // Do NOT leak whether email exists. Show same message.
                $sent = true;
            }
        } catch (Throwable $e) {
            error_log('Forgot password error: ' . $e->getMessage());
            $errors['general'] = 'Could not process your request. Please try again later.';
        }
    }
}

$pageTitle = 'Forgot Password';
$pageDescription = 'Reset your Smart PDF account password.';
$bodyClass = 'auth-page';
require __DIR__ . '/../includes/header.php';
?>
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-7 col-lg-5">
            <div class="form-card">
                <div class="form-title">
                    <div class="brand-logo mb-3 mx-auto"><img src="<?= APP_URL ?>/assets/img/smartpdf-logo.svg" alt="Smart PDF logo"></div>
                    <h2>Forgot Password</h2>
                    <p>Enter your email and we'll generate a secure reset link.</p>
                </div>

                <?php if (!empty($errors['general'])): ?>
                    <div class="alert alert-danger"><i class="fas fa-circle-exclamation me-2"></i><?= e($errors['general']) ?></div>
                <?php endif; ?>

                <?php if ($sent): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-circle-check me-2"></i>
                        If an account exists for <strong><?= e($email) ?></strong>, a reset link has been generated.
                    </div>
                    <?php if ($demoLink): ?>
                        <div class="alert alert-info small">
                            <i class="fas fa-circle-info me-1"></i>
                            <strong>Local development mode:</strong> no email is actually sent.<br>
                            Use this link to reset your password:<br>
                            <a href="<?= e($demoLink) ?>" class="text-break"><?= e($demoLink) ?></a>
                        </div>
                    <?php endif; ?>
                    <p class="text-center">
                        <a href="<?= APP_URL ?>/auth/login.php">Back to login</a>
                    </p>
                <?php else: ?>
                    <form method="POST" action="" novalidate>
                        <?= csrf_field() ?>
                        <div class="mb-3">
                            <label class="form-label" for="email">Email Address</label>
                            <input type="email" class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>"
                                   id="email" name="email" value="<?= e($email) ?>"
                                   placeholder="you@example.com" required autofocus>
                            <?php if (isset($errors['email'])): ?>
                                <div class="invalid-feedback"><?= e($errors['email']) ?></div>
                            <?php endif; ?>
                        </div>
                        <button type="submit" class="btn btn-primary w-100 mb-3">
                            <i class="fas fa-paper-plane me-2"></i>Send Reset Link
                        </button>
                        <p class="text-center text-muted mb-0">
                            Remembered it? <a href="<?= APP_URL ?>/auth/login.php">Login</a>
                        </p>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
