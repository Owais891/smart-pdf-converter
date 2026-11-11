/* ============================================================
 * Smart PDF - tools.js
 * Helpers specific to the All Tools index page (filtering, etc.)
 * ============================================================ */

(function () {
    'use strict';

    const filterInput = document.getElementById('toolSearch');
    const chips = document.querySelectorAll('.tool-category-chip');
    const cards = document.querySelectorAll('[data-tool-card]');
    if (!filterInput && !chips.length && !cards.length) return;

    let currentCat = 'all';

    function applyFilter() {
        const term = (filterInput ? filterInput.value : '').toLowerCase().trim();
        cards.forEach(card => {
            const name = (card.dataset.name || '').toLowerCase();
            const desc = (card.dataset.desc || '').toLowerCase();
            const cat  = (card.dataset.category || '').toLowerCase();
            const matchesTerm = !term || name.includes(term) || desc.includes(term);
            const matchesCat  = currentCat === 'all' || cat === currentCat;
            card.style.display = (matchesTerm && matchesCat) ? '' : 'none';
        });
        // Show / hide empty-state message
        const empty = document.getElementById('toolsEmpty');
        if (empty) {
            const anyVisible = Array.from(cards).some(c => c.style.display !== 'none');
            empty.style.display = anyVisible ? 'none' : '';
        }
    }

    if (filterInput) {
        filterInput.addEventListener('input', applyFilter);
    }
    chips.forEach(chip => {
        chip.addEventListener('click', () => {
            chips.forEach(c => c.classList.remove('active'));
            chip.classList.add('active');
            currentCat = chip.dataset.category || 'all';
            applyFilter();
        });
    });

    applyFilter();
})();
