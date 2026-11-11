        </div><!-- /.container-fluid -->
    </div><!-- /.dash-content -->
</div><!-- /.dash-layout -->

<!-- Flash messages -->
<?php if ($flashes = get_flashes()): ?>
    <div class="flash-container">
        <?php foreach ($flashes as $f): ?>
            <div class="alert alert-<?= $f['type'] === 'error' ? 'danger' : $f['type'] ?> alert-dismissible fade show rounded-pill shadow-sm" role="alert">
                <i class="fas fa-circle-info me-2"></i><?= e($f['msg']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= APP_URL ?>/assets/js/main.js"></script>
<script src="<?= APP_URL ?>/assets/js/dashboard.js"></script>
<script>
window.SMART_PDF = {
    appUrl: <?= json_encode(APP_URL) ?>,
    csrfToken: <?= json_encode(csrf_token()) ?>,
    maxUploadSize: <?= (int)MAX_UPLOAD_SIZE ?>
};
// Theme toggle in dashboard
const themeToggle = document.getElementById('themeToggle');
if (themeToggle) {
    themeToggle.addEventListener('click', async function () {
        const html = document.documentElement;
        const next = html.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
        html.setAttribute('data-theme', next);
        const icon = themeToggle.querySelector('i');
        if (icon) icon.className = next === 'dark' ? 'fas fa-sun' : 'fas fa-moon';
        try { localStorage.setItem('smartpdf_theme', next); } catch (e) {}
        try {
            await fetch(window.SMART_PDF.appUrl + '/api/set_theme.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'theme=' + encodeURIComponent(next) + '&csrf_token=' + encodeURIComponent(window.SMART_PDF.csrfToken)
            });
        } catch (e) {}
    });
}
</script>
</body>
</html>
