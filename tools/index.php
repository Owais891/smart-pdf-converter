<?php
/**
 * Smart PDF - All Tools Index
 */
require_once __DIR__ . '/../includes/functions.php';

$activePage = 'tools';
$pageTitle = 'All PDF Tools - Smart PDF';
$pageDescription = 'Browse all 18 PDF tools: convert, merge, split, compress, rotate, protect, watermark and more.';

$categories = [
    'all'        => 'All Tools',
    'convert'    => 'Convert',
    'organize'   => 'Organize',
    'optimize'   => 'Optimize',
    'edit'       => 'Edit',
    'security'   => 'Security',
];

$tools = [
    // Convert
    ['slug' => 'pdf_to_word', 'name' => 'PDF to Word', 'desc' => 'Convert PDF into editable DOCX.', 'color' => 'tc-blue', 'cat' => 'convert'],
    ['slug' => 'word_to_pdf', 'name' => 'Word to PDF', 'desc' => 'Convert DOC/DOCX into PDF.', 'color' => 'tc-blue', 'cat' => 'convert'],
    ['slug' => 'pdf_to_excel','name' => 'PDF to Excel','desc' => 'Extract tables into XLSX.', 'color' => 'tc-green', 'cat' => 'convert'],
    ['slug' => 'excel_to_pdf','name' => 'Excel to PDF','desc' => 'Convert XLS/XLSX into PDF.', 'color' => 'tc-green', 'cat' => 'convert'],
    ['slug' => 'pdf_to_ppt',  'name' => 'PDF to PowerPoint', 'desc' => 'Convert PDF into PPTX.', 'color' => 'tc-orange', 'cat' => 'convert'],
    ['slug' => 'ppt_to_pdf',  'name' => 'PowerPoint to PDF', 'desc' => 'Convert PPT/PPTX into PDF.', 'color' => 'tc-orange', 'cat' => 'convert'],
    ['slug' => 'pdf_to_jpg',  'name' => 'PDF to JPG', 'desc' => 'Each page becomes an image.', 'color' => 'tc-pink', 'cat' => 'convert'],
    ['slug' => 'jpg_to_pdf',  'name' => 'JPG to PDF', 'desc' => 'Combine images into a PDF.', 'color' => 'tc-pink', 'cat' => 'convert'],
    // Organize
    ['slug' => 'merge_pdf',   'name' => 'Merge PDF', 'desc' => 'Combine PDFs into one.', 'color' => 'tc-red', 'cat' => 'organize'],
    ['slug' => 'split_pdf',   'name' => 'Split PDF', 'desc' => 'Extract pages or split.', 'color' => 'tc-red', 'cat' => 'organize'],
    ['slug' => 'rotate_pdf',  'name' => 'Rotate PDF', 'desc' => 'Rotate pages by 90/180/270.', 'color' => 'tc-purple', 'cat' => 'organize'],
    ['slug' => 'organize_pdf','name' => 'Organize PDF', 'desc' => 'Delete or reorder pages.', 'color' => 'tc-purple', 'cat' => 'organize'],
    // Optimize
    ['slug' => 'compress_pdf','name' => 'Compress PDF', 'desc' => 'Reduce file size.', 'color' => 'tc-yellow', 'cat' => 'optimize'],
    // Edit
    ['slug' => 'watermark_pdf','name' => 'Add Watermark', 'desc' => 'Stamp text on every page.', 'color' => 'tc-teal', 'cat' => 'edit'],
    ['slug' => 'page_numbers', 'name' => 'Page Numbers', 'desc' => 'Add page numbers.', 'color' => 'tc-teal', 'cat' => 'edit'],
    ['slug' => 'pdf_editor',   'name' => 'PDF Editor', 'desc' => 'Add text overlays.', 'color' => 'tc-teal', 'cat' => 'edit'],
    // Security
    ['slug' => 'protect_pdf', 'name' => 'Protect PDF', 'desc' => 'Encrypt with password.', 'color' => 'tc-gray', 'cat' => 'security'],
    ['slug' => 'unlock_pdf',  'name' => 'Unlock PDF', 'desc' => 'Remove password.', 'color' => 'tc-gray', 'cat' => 'security'],
];

require __DIR__ . '/../includes/header.php';
?>

<section class="page-hero">
    <div class="container">
        <nav>
            <ol class="breadcrumb breadcrumb-smart">
                <li class="breadcrumb-item"><a href="<?= APP_URL ?>/index.php">Home</a></li>
                <li class="breadcrumb-item active">All Tools</li>
            </ol>
        </nav>
        <h1>All PDF Tools</h1>
        <p>18 powerful, free PDF tools - find exactly what you need below.</p>
    </div>
</section>

<section class="section">
    <div class="container">
        <!-- Search + filters -->
        <div class="row align-items-center g-3 mb-4">
            <div class="col-md-6">
                <div class="input-group">
                    <span class="input-group-text bg-transparent"><i class="fas fa-search"></i></span>
                    <input type="text" id="toolSearch" class="form-control" placeholder="Search tools...">
                </div>
            </div>
            <div class="col-md-6 text-md-end">
                <div class="d-flex flex-wrap gap-2 justify-content-md-end">
                    <?php foreach ($categories as $key => $label): ?>
                        <button class="tool-category-chip <?= $key === 'all' ? 'active' : '' ?>" data-category="<?= $key ?>">
                            <?= $label ?>
                        </button>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Tools grid -->
        <div class="row g-3">
            <?php foreach ($tools as $t): ?>
                <div class="col-6 col-md-4 col-lg-3" data-tool-card
                     data-name="<?= e($t['name']) ?>" data-desc="<?= e($t['desc']) ?>"
                     data-category="<?= e($t['cat']) ?>">
                    <a href="<?= APP_URL ?>/tools/<?= $t['slug'] ?>.php" class="tool-card">
                        <span class="tool-card-icon <?= $t['color'] ?>">
                            <i class="fas <?= tool_icon($t['slug']) ?>"></i>
                        </span>
                        <h5><?= e($t['name']) ?></h5>
                        <p><?= e($t['desc']) ?></p>
                        <div class="tool-arrow"><i class="fas fa-arrow-right"></i></div>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="empty-state" id="toolsEmpty" style="display:none">
            <i class="fas fa-magnifying-glass"></i>
            <h5>No tools match your search</h5>
            <p>Try a different keyword or category.</p>
        </div>
    </div>
</section>

<script src="<?= APP_URL ?>/assets/js/tools.js"></script>
<?php require __DIR__ . '/../includes/footer.php'; ?>
