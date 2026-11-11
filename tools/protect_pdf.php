<?php
require_once __DIR__ . '/../includes/functions.php';
/**
 * Smart PDF - Protect PDF
 */
$toolSlug      = 'protect_pdf';
$toolName      = 'Protect PDF';
$toolIcon      = 'fa-lock';
$toolColor     = 'tc-gray';
$toolDesc      = 'Add password protection to a PDF. Choose what users can do (print, copy, modify).';
$allowedExt    = ['.pdf'];
$allowedTypes  = ALLOWED_PDF_MIME;
$allowMultiple = false;
$maxFiles      = 1;
$extraOptions  = function () {
    ?>
    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label" for="password">Password</label>
            <div class="input-group">
                <input type="password" class="form-control" id="password" name="password" placeholder="Min 4 characters">
                <button class="btn btn-outline-secondary" type="button" data-toggle-password="password">
                    <i class="fas fa-eye"></i>
                </button>
            </div>
        </div>
    </div>
    <label class="form-label d-block mt-3">Permissions</label>
    <div class="form-check">
        <input class="form-check-input" type="checkbox" id="allowPrint" name="allow_print" value="1" checked>
        <label class="form-check-label" for="allowPrint">Allow printing</label>
    </div>
    <div class="form-check">
        <input class="form-check-input" type="checkbox" id="allowCopy" name="allow_copy" value="1">
        <label class="form-check-label" for="allowCopy">Allow copying text</label>
    </div>
    <div class="form-check">
        <input class="form-check-input" type="checkbox" id="allowModify" name="allow_modify" value="1">
        <label class="form-check-label" for="allowModify">Allow modifications</label>
    </div>
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
