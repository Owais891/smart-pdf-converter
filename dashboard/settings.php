<?php
/**
 * Smart PDF - User Settings
 */
$active = 'settings';
$pageTitle = 'Settings';
require __DIR__ . '/../includes/dashboard_header.php';

$user = current_user();
$theme = current_theme();
$language = setting('language', 'en');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $newTheme = ($_POST['theme'] ?? 'light') === 'dark' ? 'dark' : 'light';
    $newLang  = in_array($_POST['language'] ?? 'en', ['en', 'ur', 'fr', 'es', 'de']) ? $_POST['language'] : 'en';

    try {
        db()->prepare(
            'INSERT INTO user_settings (user_id, theme, language)
             VALUES (?, ?, ?)
             ON DUPLICATE KEY UPDATE theme = VALUES(theme), language = VALUES(language), updated_at = NOW()'
        )->execute([$user['id'], $newTheme, $newLang]);

        $_SESSION['theme'] = $newTheme;
        flash('success', 'Settings saved.');
        redirect(APP_URL . '/dashboard/settings.php');
    } catch (Throwable $e) {
        flash('error', 'Could not save settings.');
    }
}
?>

<div class="dash-page-title">Settings</div>

<div class="row g-3">
    <div class="col-lg-8">
        <div class="dash-card">
            <div class="dash-card-header"><h5>Preferences</h5></div>
            <form method="POST">
                <?= csrf_field() ?>

                <div class="settings-row">
                    <div>
                        <div class="label">Theme</div>
                        <div class="desc">Switch between light and dark appearance.</div>
                    </div>
                    <div class="theme-switch">
                        <button type="button" class="<?= $theme === 'light' ? 'active' : '' ?>"
                                data-theme="light" aria-pressed="<?= $theme === 'light' ? 'true' : 'false' ?>">
                            <i class="fas fa-sun me-1"></i>Light
                        </button>
                        <button type="button" class="<?= $theme === 'dark' ? 'active' : '' ?>"
                                data-theme="dark" aria-pressed="<?= $theme === 'dark' ? 'true' : 'false' ?>">
                            <i class="fas fa-moon me-1"></i>Dark
                        </button>
                    </div>
                    <input type="hidden" name="theme" id="themeInput" value="<?= e($theme) ?>">
                </div>
                <div class="small text-danger" id="themeStatus" role="status" aria-live="polite"></div>

                <div class="settings-row">
                    <div>
                        <div class="label">Language</div>
                        <div class="desc">UI language preference (English is fully supported).</div>
                    </div>
                    <select class="form-select" name="language" style="width:auto">
                        <option value="en" <?= $language === 'en' ? 'selected' : '' ?>>English</option>
                        <option value="ur" <?= $language === 'ur' ? 'selected' : '' ?>>Urdu</option>
                        <option value="fr" <?= $language === 'fr' ? 'selected' : '' ?>>French</option>
                        <option value="es" <?= $language === 'es' ? 'selected' : '' ?>>Spanish</option>
                        <option value="de" <?= $language === 'de' ? 'selected' : '' ?>>German</option>
                    </select>
                </div>

                <div class="settings-row">
                    <div>
                        <div class="label">Account</div>
                        <div class="desc">Manage your account information.</div>
                    </div>
                    <a href="<?= APP_URL ?>/dashboard/profile.php" class="btn btn-sm btn-ghost">
                        Edit Profile
                    </a>
                </div>

                <div class="text-end mt-3">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Save Settings
                    </button>
                </div>
            </form>
        </div>

        <div class="dash-card">
            <div class="dash-card-header"><h5>Privacy &amp; Data</h5></div>
            <div class="settings-row">
                <div>
                    <div class="label">Conversion History</div>
                    <div class="desc">Delete your entire conversion history.</div>
                </div>
                <a href="<?= APP_URL ?>/dashboard/history.php" class="btn btn-sm btn-outline-danger">
                    Manage History
                </a>
            </div>
            <div class="settings-row">
                <div>
                    <div class="label">Auto File Deletion</div>
                    <div class="desc">Uploaded files are automatically removed after 1 hour.</div>
                </div>
                <span class="badge bg-success">Enabled</span>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="dash-card">
            <div class="dash-card-header"><h5>Account Summary</h5></div>
            <ul class="list-unstyled mb-0">
                <li class="d-flex justify-content-between py-2 border-bottom">
                    <span class="text-muted">Name</span>
                    <strong><?= e($user['full_name']) ?></strong>
                </li>
                <li class="d-flex justify-content-between py-2 border-bottom">
                    <span class="text-muted">Email</span>
                    <strong class="text-truncate ms-2" style="max-width:160px"><?= e($user['email']) ?></strong>
                </li>
                <li class="d-flex justify-content-between py-2 border-bottom">
                    <span class="text-muted">Role</span>
                    <strong><?= e(ucfirst($user['role'])) ?></strong>
                </li>
                <li class="d-flex justify-content-between py-2">
                    <span class="text-muted">Member Since</span>
                    <strong><?= date('M j, Y', strtotime($user['created_at'])) ?></strong>
                </li>
            </ul>
        </div>
    </div>
</div>

<script>
(function () {
    function initializeThemeSettings() {
        const themeButtons = document.querySelectorAll('.theme-switch button');
        const themeInput = document.getElementById('themeInput');
        const themeStatus = document.getElementById('themeStatus');
        const themeToggle = document.getElementById('themeToggle');

        function applySettingsTheme(theme) {
            document.documentElement.setAttribute('data-theme', theme);
            themeInput.value = theme;
            themeButtons.forEach(button => {
                const selected = button.dataset.theme === theme;
                button.classList.toggle('active', selected);
                button.setAttribute('aria-pressed', String(selected));
            });
            const icon = themeToggle && themeToggle.querySelector('i');
            if (icon) icon.className = theme === 'dark' ? 'fas fa-sun' : 'fas fa-moon';
        }

        themeButtons.forEach(button => {
            button.addEventListener('click', async () => {
                const previousTheme = document.documentElement.getAttribute('data-theme') === 'dark' ? 'dark' : 'light';
                const nextTheme = button.dataset.theme;
                if (nextTheme === previousTheme) return;

                applySettingsTheme(nextTheme);
                themeStatus.textContent = '';

                try {
                    const response = await fetch(window.SMART_PDF.appUrl + '/api/set_theme.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                            'Accept': 'application/json'
                        },
                        body: 'theme=' + encodeURIComponent(nextTheme) +
                            '&csrf_token=' + encodeURIComponent(window.SMART_PDF.csrfToken)
                    });
                    const result = await response.json();
                    if (!response.ok || !result.success) {
                        throw new Error(result.error || 'Could not save your theme preference.');
                    }
                    try {
                        localStorage.setItem('smartpdf_theme', nextTheme);
                    } catch (error) {
                        // The server preference is saved; browser storage is optional.
                    }
                } catch (error) {
                    applySettingsTheme(previousTheme);
                    themeStatus.textContent = error.message || 'Could not save your theme preference.';
                }
            });
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initializeThemeSettings, { once: true });
    } else {
        initializeThemeSettings();
    }
})();
</script>

<?php require __DIR__ . '/../includes/dashboard_footer.php'; ?>
