<?php
/**
 * Smart PDF - User Registration
 */
require_once __DIR__ . '/../includes/functions.php';

if (is_logged_in()) {
    redirect(APP_URL . '/dashboard/index.php');
}

$errors = [];
$old = ['full_name' => '', 'email' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();

    $old['full_name'] = trim($_POST['full_name'] ?? '');
    $old['email']     = trim($_POST['email'] ?? '');
    $password         = $_POST['password'] ?? '';
    $confirm          = $_POST['confirm_password'] ?? '';

    // Validation
    if (mb_strlen($old['full_name']) < 2) {
        $errors['full_name'] = 'Please enter your full name (min 2 characters).';
    } elseif (mb_strlen($old['full_name']) > 100) {
        $errors['full_name'] = 'Name is too long (max 100 characters).';
    }
    if (!is_valid_email($old['email'])) {
        $errors['email'] = 'Please enter a valid email address.';
    }
    if (strlen($password) < 8) {
        $errors['password'] = 'Password must be at least 8 characters long.';
    } elseif (!preg_match('/[A-Za-z]/', $password) || !preg_match('/[0-9]/', $password)) {
        $errors['password'] = 'Password must contain both letters and numbers.';
    }
    if ($password !== $confirm) {
        $errors['confirm_password'] = 'Passwords do not match.';
    }

    // Check duplicate email
    if (!$errors) {
        try {
            $stmt = db()->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
            $stmt->execute([$old['email']]);
            if ($stmt->fetch()) {
                $errors['email'] = 'An account with this email already exists. Please log in.';
            }
        } catch (Throwable $e) {
            $errors['general'] = 'Could not reach the database. Please try again later.';
        }
    }

    if (!$errors) {
        try {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = db()->prepare(
                'INSERT INTO users (full_name, email, password, role, is_active)
                 VALUES (?, ?, ?, "user", 1)'
            );
            $stmt->execute([$old['full_name'], $old['email'], $hash]);
            $userId = (int)db()->lastInsertId();

            // Create settings row
            db()->prepare('INSERT INTO user_settings (user_id, theme, language) VALUES (?, "light","en")')
                ->execute([$userId]);

            login_user($userId);
            flash('success', 'Welcome to Smart PDF, ' . e($old['full_name']) . '! Your account is ready.');
            redirect(APP_URL . '/dashboard/index.php');
        } catch (Throwable $e) {
            error_log('Registration error: ' . $e->getMessage());
            $errors['general'] = 'Something went wrong while creating your account. Please try again.';
        }
    }
}

$pageTitle = 'Create Your Free Account';
$pageDescription = 'Sign up for Smart PDF to track conversions, save history, and personalise your experience.';
$bodyClass = 'auth-page';
require __DIR__ . '/../includes/header.php';
?>
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-7 col-lg-5">
            <div class="form-card">
                <div class="form-title">
                    <div class="brand-logo mb-3 mx-auto"><img src="<?= APP_URL ?>/assets/img/smartpdf-logo.svg" alt="Smart PDF logo"></div>
                    <h2>Create your account</h2>
                    <p>Join thousands of users managing PDFs the smart way.</p>
                </div>

                <?php if (!empty($errors['general'])): ?>
                    <div class="alert alert-danger" role="alert">
                        <i class="fas fa-circle-exclamation me-2"></i><?= e($errors['general']) ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="" novalidate>
                    <?= csrf_field() ?>

                    <div class="mb-3">
                        <label class="form-label" for="full_name">Full Name</label>
                        <input type="text" class="form-control <?= isset($errors['full_name']) ? 'is-invalid' : '' ?>"
                               id="full_name" name="full_name" value="<?= e($old['full_name']) ?>"
                               placeholder="John Doe" required autofocus>
                        <?php if (isset($errors['full_name'])): ?>
                            <div class="invalid-feedback"><?= e($errors['full_name']) ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="email">Email Address</label>
                        <input type="email" class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>"
                               id="email" name="email" value="<?= e($old['email']) ?>"
                               placeholder="you@example.com" required>
                        <?php if (isset($errors['email'])): ?>
                            <div class="invalid-feedback"><?= e($errors['email']) ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="password">Password</label>
                        <div class="input-group">
                            <input type="password" class="form-control <?= isset($errors['password']) ? 'is-invalid' : '' ?>"
                                   id="password" name="password" placeholder="Min 8 characters" required>
                            <button class="btn btn-outline-secondary" type="button" data-toggle-password="password">
                                <i class="fas fa-eye"></i>
                            </button>
                            <?php if (isset($errors['password'])): ?>
                                <div class="invalid-feedback d-block"><?= e($errors['password']) ?></div>
                            <?php endif; ?>
                        </div>
                        <small class="form-text">Use 8+ characters with letters and numbers.</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="confirm_password">Confirm Password</label>
                        <input type="password" class="form-control <?= isset($errors['confirm_password']) ? 'is-invalid' : '' ?>"
                               id="confirm_password" name="confirm_password" placeholder="Re-enter password" required>
                        <?php if (isset($errors['confirm_password'])): ?>
                            <div class="invalid-feedback"><?= e($errors['confirm_password']) ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="agree" name="agree" required>
                        <label class="form-check-label" for="agree">
                            I agree to the <a href="<?= APP_URL ?>/pages/terms.php">Terms</a> and
                            <a href="<?= APP_URL ?>/pages/privacy.php">Privacy Policy</a>.
                        </label>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 mb-3">
                        <i class="fas fa-user-plus me-2"></i>Create Account
                    </button>

                    <p class="text-center text-muted mb-0">
                        Already have an account?
                        <a href="<?= APP_URL ?>/auth/login.php">Log in</a>
                    </p>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
// Simple password visibility toggle
document.querySelectorAll('[data-toggle-password]').forEach(btn => {
    btn.addEventListener('click', () => {
        const id = btn.getAttribute('data-toggle-password');
        const input = document.getElementById(id);
        if (!input) return;
        const isPwd = input.type === 'password';
        input.type = isPwd ? 'text' : 'password';
        btn.querySelector('i').className = isPwd ? 'fas fa-eye-slash' : 'fas fa-eye';
    });
});
</script>

<?php require __DIR__ . '/../includes/footer.php'; ?>
