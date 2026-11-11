<?php
require_once __DIR__ . '/../includes/functions.php';
/**
 * Smart PDF - Unlock PDF
 */
$toolSlug      = 'unlock_pdf';
$toolName      = 'Unlock PDF';
$toolIcon      = 'fa-lock-open';
$toolColor     = 'tc-gray';
$toolDesc      = 'Remove password protection from a PDF when you know the password. Uses the qpdf command-line tool.';
$allowedExt    = ['.pdf'];
$allowedTypes  = ALLOWED_PDF_MIME;
$allowMultiple = false;
$maxFiles      = 1;
$extraOptions  = function () {
    ?>
    <label class="form-label" for="password">PDF Password</label>
    <div class="input-group">
        <input type="password" class="form-control" id="password" name="password" placeholder="Enter the document password">
        <button class="btn btn-outline-secondary" type="button" data-toggle-password="password">
            <i class="fas fa-eye"></i>
        </button>
    </div>
    <small class="form-text">You must already know the password to remove it. We do not crack PDFs.</small>
    <script>
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
    <?php
};
require __DIR__ . '/../includes/tool_page.php';
