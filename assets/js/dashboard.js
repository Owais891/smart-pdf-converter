/* ============================================================
 * Smart PDF - dashboard.js
 * Sidebar toggle on mobile, history delete confirmations,
 * stats counter animation.
 * ============================================================ */

(function () {
    'use strict';

    /* ----- Mobile sidebar toggle ----- */
    const toggle = document.querySelector('.sidebar-toggle');
    const sidebar = document.getElementById('dashSidebar');
    const overlay = document.getElementById('dashOverlay');
    if (toggle && sidebar) {
        toggle.addEventListener('click', () => {
            sidebar.classList.add('is-open');
            if (overlay) overlay.classList.add('is-visible');
        });
        if (overlay) {
            overlay.addEventListener('click', () => {
                sidebar.classList.remove('is-open');
                overlay.classList.remove('is-visible');
            });
        }
    }

    /* ----- Animated counters ----- */
    const counters = document.querySelectorAll('[data-counter]');
    if (counters.length) {
        const animate = (el) => {
            const target = parseInt(el.dataset.counter, 10) || 0;
            const duration = 900;
            const start = performance.now();
            const step = (now) => {
                const p = Math.min(1, (now - start) / duration);
                const val = Math.round(target * (1 - Math.pow(1 - p, 3)));
                el.textContent = val.toLocaleString();
                if (p < 1) requestAnimationFrame(step);
            };
            requestAnimationFrame(step);
        };
        const obs = new IntersectionObserver((entries) => {
            entries.forEach(e => {
                if (e.isIntersecting) {
                    animate(e.target);
                    obs.unobserve(e.target);
                }
            });
        }, { threshold: 0.5 });
        counters.forEach(c => obs.observe(c));
    }

    /* ----- Confirm before deleting history rows ----- */
    document.querySelectorAll('[data-confirm-delete]').forEach(btn => {
        btn.addEventListener('click', (e) => {
            if (!confirm('Delete this conversion record? This cannot be undone.')) {
                e.preventDefault();
            }
        });
    });

    /* ----- Bulk delete ----- */
    const bulkForm = document.getElementById('bulkHistoryForm');
    if (bulkForm) {
        bulkForm.addEventListener('submit', (e) => {
            const checked = bulkForm.querySelectorAll('input[name="ids[]"]:checked').length;
            if (checked === 0) {
                e.preventDefault();
                alert('Please select at least one record to delete.');
                return;
            }
            if (!confirm('Delete ' + checked + ' selected record(s)?')) {
                e.preventDefault();
            }
        });
    }

    /* ----- Settings form: theme preview ----- */
    const themeButtons = document.querySelectorAll('.theme-switch button');
    themeButtons.forEach(btn => {
        btn.addEventListener('click', () => {
            themeButtons.forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
        });
    });
})();
