/* ============================================================
 * Smart PDF - main.js
 * Global helpers: page loader, dark mode, navbar scroll,
 * back-to-top, flash auto-dismiss, CSRF header on every fetch.
 * ============================================================ */

(function () {
    'use strict';

    /* ----- Page loader ----- */
    window.addEventListener('load', function () {
        const loader = document.getElementById('pageLoader');
        if (loader) {
            setTimeout(() => loader.classList.add('is-hidden'), 200);
        }
    });

    /* ----- Dark mode toggle ----- */
    const themeToggle = document.getElementById('themeToggle');
    if (themeToggle) {
        syncThemeIcon();
        themeToggle.addEventListener('click', async function () {
            const html = document.documentElement;
            const next = html.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
            html.setAttribute('data-theme', next);
            try { localStorage.setItem('smartpdf_theme', next); } catch (e) {}
            syncThemeIcon();
            // Persist server-side if logged in
            try {
                await fetch(window.SMART_PDF.appUrl + '/api/set_theme.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: 'theme=' + encodeURIComponent(next) + '&csrf_token=' + encodeURIComponent(window.SMART_PDF.csrfToken)
                });
            } catch (e) {}
        });
    }
    // Honour locally saved theme on first paint (avoids flash)
    try {
        const saved = localStorage.getItem('smartpdf_theme');
        if (saved && !document.documentElement.dataset.themeSet) {
            document.documentElement.setAttribute('data-theme', saved);
        }
    } catch (e) {}

    function syncThemeIcon() {
        if (!themeToggle) return;
        const isDark = document.documentElement.getAttribute('data-theme') === 'dark';
        const icon = themeToggle.querySelector('i');
        if (icon) {
            icon.className = isDark ? 'fas fa-sun' : 'fas fa-moon';
        }
    }

    /* ----- Navbar scroll shadow ----- */
    const nav = document.getElementById('mainNav');
    if (nav) {
        const onScroll = () => nav.classList.toggle('scrolled', window.scrollY > 8);
        window.addEventListener('scroll', onScroll, { passive: true });
        onScroll();
    }

    /* ----- Back to top ----- */
    const backBtn = document.getElementById('backToTop');
    if (backBtn) {
        window.addEventListener('scroll', () => {
            backBtn.classList.toggle('is-visible', window.scrollY > 400);
        }, { passive: true });
        backBtn.addEventListener('click', () => {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    }

    /* ----- Flash auto dismiss ----- */
    setTimeout(function () {
        document.querySelectorAll('.flash-container .alert').forEach(el => {
            const bs = bootstrap.Alert.getOrCreateInstance(el);
            setTimeout(() => bs.close(), 4500);
        });
    }, 1000);

    /* ----- Patch fetch to always send CSRF token header ----- */
    const originalFetch = window.fetch;
    window.fetch = function (url, opts) {
        opts = opts || {};
        opts.headers = opts.headers || {};
        // Only set when same-origin
        try {
            const u = new URL(url, window.location.origin);
            if (u.origin === window.location.origin) {
                if (!opts.headers['X-CSRF-Token'] && !opts.headers['x-csrf-token']) {
                    opts.headers['X-CSRF-Token'] = window.SMART_PDF && window.SMART_PDF.csrfToken;
                }
            }
        } catch (e) {}
        return originalFetch(url, opts);
    };

    /* ----- Utility: format bytes ----- */
    window.formatBytes = function (bytes) {
        if (!bytes) return '0 B';
        if (bytes < 1024) return bytes + ' B';
        if (bytes < 1048576) return Math.round(bytes / 1024 * 10) / 10 + ' KB';
        if (bytes < 1073741824) return Math.round(bytes / 1048576 * 10) / 10 + ' MB';
        return Math.round(bytes / 1073741824 * 100) / 100 + ' GB';
    };

    /* ----- Utility: send form data with progress (XHR) ----- */
    window.smartUpload = function (opts) {
        return new Promise((resolve, reject) => {
            const xhr = new XMLHttpRequest();
            xhr.open('POST', opts.url);
            xhr.setRequestHeader('X-CSRF-Token', window.SMART_PDF.csrfToken);
            xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');

            if (typeof opts.onProgress === 'function') {
                xhr.upload.addEventListener('progress', function (e) {
                    if (e.lengthComputable) {
                        opts.onProgress(Math.round((e.loaded / e.total) * 100));
                    }
                });
            }

            xhr.onload = function () {
                let data = null;
                try { data = JSON.parse(xhr.responseText); }
                catch (e) { data = { success: false, error: 'Invalid JSON response from server.' }; }
                if (xhr.status >= 200 && xhr.status < 300 && data.success) {
                    resolve(data);
                } else {
                    reject(data || { success: false, error: 'Request failed with status ' + xhr.status });
                }
            };
            xhr.onerror = function () {
                reject({ success: false, error: 'Network error while uploading.' });
            };
            xhr.send(opts.formData);
        });
    };
})();
