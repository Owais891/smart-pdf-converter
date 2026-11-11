<?php
/**
 * Smart PDF - Homepage
 */
require_once __DIR__ . '/includes/functions.php';

$activePage = 'home';
$pageTitle = 'Smart PDF - Every PDF Tool You Need in One Place';
$pageDescription = 'Convert, compress, merge, split, and edit your PDF files online. Free, secure, and easy to use.';

$convertTools = [
    ['slug' => 'pdf_to_word', 'name' => 'PDF to Word',  'desc' => 'Convert PDFs into editable Word documents.', 'color' => 'tc-blue'],
    ['slug' => 'word_to_pdf', 'name' => 'Word to PDF',  'desc' => 'Convert DOC / DOCX files into PDF.',         'color' => 'tc-blue'],
    ['slug' => 'pdf_to_excel','name' => 'PDF to Excel', 'desc' => 'Extract tabular data into Excel sheets.',    'color' => 'tc-green'],
    ['slug' => 'excel_to_pdf','name' => 'Excel to PDF', 'desc' => 'Convert XLS / XLSX into PDF.',               'color' => 'tc-green'],
    ['slug' => 'pdf_to_ppt',  'name' => 'PDF to PowerPoint', 'desc' => 'Convert PDFs into PPT slides.',         'color' => 'tc-orange'],
    ['slug' => 'ppt_to_pdf',  'name' => 'PowerPoint to PDF', 'desc' => 'Convert PPT / PPTX into PDF.',          'color' => 'tc-orange'],
    ['slug' => 'pdf_to_jpg',  'name' => 'PDF to JPG',   'desc' => 'Convert each PDF page into an image.',        'color' => 'tc-pink'],
    ['slug' => 'jpg_to_pdf',  'name' => 'JPG to PDF',   'desc' => 'Combine multiple images into one PDF.',      'color' => 'tc-pink'],
];
$organizeTools = [
    ['slug' => 'merge_pdf',     'name' => 'Merge PDF',    'desc' => 'Combine PDFs into a single file.',           'color' => 'tc-red'],
    ['slug' => 'split_pdf',     'name' => 'Split PDF',    'desc' => 'Extract pages or split into many files.',    'color' => 'tc-red'],
    ['slug' => 'rotate_pdf',    'name' => 'Rotate PDF',   'desc' => 'Rotate pages 90, 180 or 270 degrees.',       'color' => 'tc-purple'],
    ['slug' => 'organize_pdf',  'name' => 'Organize PDF', 'desc' => 'Delete, reorder and rearrange pages.',       'color' => 'tc-purple'],
];
$optimizeTools = [
    ['slug' => 'compress_pdf',  'name' => 'Compress PDF', 'desc' => 'Reduce file size without losing quality.',   'color' => 'tc-yellow'],
];
$editTools = [
    ['slug' => 'watermark_pdf', 'name' => 'Add Watermark','desc' => 'Stamp text watermarks across pages.',         'color' => 'tc-teal'],
    ['slug' => 'page_numbers',  'name' => 'Page Numbers', 'desc' => 'Insert page numbers easily.',                 'color' => 'tc-teal'],
    ['slug' => 'pdf_editor',    'name' => 'PDF Editor',   'desc' => 'Add text and shapes to PDF pages.',           'color' => 'tc-teal'],
];
$securityTools = [
    ['slug' => 'protect_pdf', 'name' => 'Protect PDF', 'desc' => 'Encrypt PDFs with a password.',     'color' => 'tc-gray'],
    ['slug' => 'unlock_pdf',  'name' => 'Unlock PDF',  'desc' => 'Remove PDF password protection.',   'color' => 'tc-gray'],
];

require __DIR__ . '/includes/header.php';
?>

<!-- Hero -->
<section class="hero">
    <div class="container">
        <h1>Every PDF Tool You Need <span class="accent">in One Place</span></h1>
        <p class="lead">
            Convert, compress, merge, split, and edit your PDF files quickly and easily.
            Everything you need to manage your documents - right here in your browser.
        </p>

    </div>
</section>

<!-- Tools Section -->
<section class="section" id="tools">
    <div class="container">
        <!-- Convert from PDF -->
        <h3 class="h5 mb-3 mt-4"><i class="fas fa-arrows-rotate text-brand me-2"></i>Convert from PDF</h3>
        <div class="row g-3 mb-4">
            <?php foreach ($convertTools as $t): ?>
                <div class="col-6 col-md-4 col-lg-3">
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

        <!-- Organize PDF -->
        <h3 class="h5 mb-3 mt-4"><i class="fas fa-folder-tree text-brand me-2"></i>Organize PDF</h3>
        <div class="row g-3 mb-4">
            <?php foreach ($organizeTools as $t): ?>
                <div class="col-6 col-md-4 col-lg-3">
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

        <!-- Optimize -->
        <h3 class="h5 mb-3 mt-4"><i class="fas fa-gauge-high text-brand me-2"></i>Optimize PDF</h3>
        <div class="row g-3 mb-4">
            <?php foreach ($optimizeTools as $t): ?>
                <div class="col-6 col-md-4 col-lg-3">
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

        <!-- Edit -->
        <h3 class="h5 mb-3 mt-4"><i class="fas fa-pen-to-square text-brand me-2"></i>Edit PDF</h3>
        <div class="row g-3 mb-4">
            <?php foreach ($editTools as $t): ?>
                <div class="col-6 col-md-4 col-lg-3">
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

        <!-- Security -->
        <h3 class="h5 mb-3 mt-4"><i class="fas fa-shield-halved text-brand me-2"></i>Security &amp; Protection</h3>
        <div class="row g-3 mb-4">
            <?php foreach ($securityTools as $t): ?>
                <div class="col-6 col-md-4 col-lg-3">
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

        <div class="text-center mt-4">
            <a href="<?= APP_URL ?>/tools/index.php" class="btn btn-light-pill">
                View All Tools <i class="fas fa-arrow-right ms-1"></i>
            </a>
        </div>
    </div>
</section>

<!-- How it works -->
<section class="section bg-brand-soft" id="how">
    <div class="container">
        <div class="section-title">
            <span class="badge rounded-pill-sm bg-brand-soft text-brand mb-2 px-3 py-2">Simple Process</span>
            <h2>How Smart PDF Works</h2>
            <p>Process your documents in 3 easy steps - no software install required.</p>
        </div>
        <div class="row g-4">
            <div class="col-md-4">
                <div class="how-step">
                    <div class="how-step-num">1</div>
                    <h5>Select Your Tool</h5>
                    <p>Choose from 18 PDF tools - convert, merge, split, compress, protect and more.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="how-step">
                    <div class="how-step-num">2</div>
                    <h5>Upload Your File</h5>
                    <p>Drag &amp; drop or browse to upload. Files are validated and processed securely.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="how-step">
                    <div class="how-step-num">3</div>
                    <h5>Download Result</h5>
                    <p>Get your processed PDF instantly. Your file is auto-deleted within 1 hour.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Why choose us -->
<section class="section" id="why">
    <div class="container">
        <div class="section-title">
            <span class="badge rounded-pill-sm bg-brand-soft text-brand mb-2 px-3 py-2">Why Smart PDF</span>
            <h2>Built for Speed, Privacy &amp; Quality</h2>
            <p>Trusted by students, professionals and businesses worldwide.</p>
        </div>
        <div class="row g-4">
            <div class="col-md-6 col-lg-4">
                <div class="feature-card">
                    <div class="feature-icon"><i class="fas fa-bolt"></i></div>
                    <h5>Lightning Fast</h5>
                    <p>Most operations complete in seconds. Our optimised backend uses native PDF libraries for instant results.</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-4">
                <div class="feature-card">
                    <div class="feature-icon"><i class="fas fa-lock"></i></div>
                    <h5>Privacy First</h5>
                    <p>Your files are processed on your own server and automatically deleted after 1 hour. Nothing is shared.</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-4">
                <div class="feature-card">
                    <div class="feature-icon"><i class="fas fa-puzzle-piece"></i></div>
                    <h5>18 Powerful Tools</h5>
                    <p>Convert, merge, split, compress, watermark, protect and more - all in one place, all free.</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-4">
                <div class="feature-card">
                    <div class="feature-icon"><i class="fas fa-mobile-screen"></i></div>
                    <h5>Works Everywhere</h5>
                    <p>Fully responsive design works on desktop, tablet and mobile. Process PDFs from any device.</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-4">
                <div class="feature-card">
                    <div class="feature-icon"><i class="fas fa-circle-check"></i></div>
                    <h5>No Watermarks</h5>
                    <p>Unlike many "free" tools, we never add watermarks or branding to your output files.</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-4">
                <div class="feature-card">
                    <div class="feature-icon"><i class="fas fa-user-shield"></i></div>
                    <h5>Open Source Stack</h5>
                    <p>Built with PHP, MySQL, TCPDF and LibreOffice. Fully auditable and free to extend.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Stats -->
<section class="section-tight">
    <div class="container">
        <div class="row g-3">
            <div class="col-6 col-md-3">
                <div class="stat-counter">
                    <div class="num">18+</div>
                    <div class="lbl">PDF Tools</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-counter" style="background:linear-gradient(135deg,#0d6efd,#4dabf7)">
                    <div class="num">100%</div>
                    <div class="lbl">Free to Use</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-counter" style="background:linear-gradient(135deg,#198754,#51cf66)">
                    <div class="num">1 hr</div>
                    <div class="lbl">Auto File Deletion</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-counter" style="background:linear-gradient(135deg,#6f42c1,#b197fc)">
                    <div class="num">5★</div>
                    <div class="lbl">User Rating</div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- FAQ -->
<section class="section bg-brand-soft" id="faq">
    <div class="container">
        <div class="section-title">
            <span class="badge rounded-pill-sm bg-brand-soft text-brand mb-2 px-3 py-2">FAQ</span>
            <h2>Frequently Asked Questions</h2>
            <p>Everything you need to know about Smart PDF.</p>
        </div>
        <div class="row justify-content-center">
            <div class="col-lg-9">
                <div class="accordion faq-accordion" id="faqAccordion">
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                                Is Smart PDF really free to use?
                            </button>
                        </h2>
                        <div id="faq1" class="accordion-collapse collapse show" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                Yes! All 18 tools are 100% free with no hidden charges, no watermarks, and no
                                registration required for basic use. Creating a free account unlocks history tracking
                                and personalisation.
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                                Are my files safe?
                            </button>
                        </h2>
                        <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                Absolutely. Files are uploaded over HTTPS, processed on the server, and automatically
                                deleted from the storage folders within 1 hour. The database only stores metadata
                                (filename, size, status), never the file content itself.
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                                What file types are supported?
                            </button>
                        </h2>
                        <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                PDF is supported everywhere. We also handle DOC/DOCX (Word), XLS/XLSX (Excel),
                                PPT/PPTX (PowerPoint) and JPG/PNG images. Each tool page lists the exact formats
                                it accepts.
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq4">
                                What is the maximum file size?
                            </button>
                        </h2>
                        <div id="faq4" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                The default limit is 100&nbsp;MB per file. This can be adjusted in
                                <code>config/app.php</code> by changing the <code>MAX_UPLOAD_SIZE</code> constant,
                                and also raised in your <code>php.ini</code> (<code>upload_max_filesize</code> and
                                <code>post_max_size</code>).
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq5">
                                Do I need to install anything?
                            </button>
                        </h2>
                        <div id="faq5" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                Users don't need to install anything - everything runs in the browser.
                                Server-side, the project uses PHP, MySQL and PDF libraries (TCPDF, FPDI, FPDF)
                                installed via Composer, plus LibreOffice for office-format conversions. Full setup
                                instructions are in the README.
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq6">
                                Can I use Smart PDF offline?
                            </button>
                        </h2>
                        <div id="faq6" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                Yes. Smart PDF is designed to be self-hosted. Clone the project, run XAMPP, install
                                dependencies with Composer, import the database, and you have a fully private PDF
                                toolkit on your own machine.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Reviews -->
<section class="section" id="reviews">
    <div class="container">
        <div class="section-title">
            <span class="badge rounded-pill-sm bg-brand-soft text-brand mb-2 px-3 py-2">Reviews</span>
            <h2>Loved by Users Everywhere</h2>
            <p>Sample reviews shown below - replace with real feedback once you launch.</p>
        </div>
        <div class="row g-4">
            <div class="col-md-6 col-lg-4">
                <div class="review-card">
                    <div class="stars">
                        <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
                    </div>
                    <p class="review-text">"Smart PDF saved me during finals week. I merged 12 lecture notes into one PDF in seconds. Clean UI, fast, and completely free."</p>
                    <div class="reviewer">
                        <span class="avatar">S</span>
                        <div>
                            <div class="reviewer-name">Sarah Khan</div>
                            <div class="reviewer-role">Computer Science Student</div>
                        </div>
                    </div>
                    <div class="sample-tag">Sample review</div>
                </div>
            </div>
            <div class="col-md-6 col-lg-4">
                <div class="review-card">
                    <div class="stars">
                        <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
                    </div>
                    <p class="review-text">"As a small business owner, I needed an affordable way to compress and protect invoices. Smart PDF's protect tool worked flawlessly."</p>
                    <div class="reviewer">
                        <span class="avatar">A</span>
                        <div>
                            <div class="reviewer-name">Ahmed Raza</div>
                            <div class="reviewer-role">Freelance Accountant</div>
                        </div>
                    </div>
                    <div class="sample-tag">Sample review</div>
                </div>
            </div>
            <div class="col-md-6 col-lg-4">
                <div class="review-card">
                    <div class="stars">
                        <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star-half-stroke"></i>
                    </div>
                    <p class="review-text">"The PDF to Word conversion is the most accurate I've used. Tables and formatting come out exactly as expected. Highly recommended."</p>
                    <div class="reviewer">
                        <span class="avatar">M</span>
                        <div>
                            <div class="reviewer-name">Maria Lopez</div>
                            <div class="reviewer-role">Project Manager</div>
                        </div>
                    </div>
                    <div class="sample-tag">Sample review</div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA -->
<section class="section">
    <div class="container">
        <div class="cta-section">
            <h2>Ready to simplify your PDF workflow?</h2>
            <p>Create your free account to track conversions, save history, and personalise Smart PDF.</p>
            <a href="<?= APP_URL ?>/auth/register.php" class="btn btn-lg">
                <i class="fas fa-rocket me-2"></i>Get Started for Free
            </a>
        </div>
    </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
