/* ============================================================
 * Smart PDF - upload.js
 * Drag & drop, multi-file upload, progress, validation.
 * Designed to be reusable across all tool pages.
 *
 * Each tool page declares a ToolUpload instance with config:
 * {
 *   dropZone:    '#dropZone',
 *   input:       '#fileInput',
 *   list:        '#fileList',
 *   btn:         '#processBtn',
 *   progressUrl: '/api/upload.php',
 *   processUrl:  '/api/process.php',
 *   toolName:    'pdf_to_word',
 *   allowedTypes:['application/pdf'],
 *   maxFiles:    1,
 *   allowMultiple: false,
 *   extraFields: function() { return { ... }; }
 * }
 * ============================================================ */

class ToolUpload {
    constructor(cfg) {
        this.cfg = Object.assign({
            dropZone: '#dropZone',
            input: '#fileInput',
            list: '#fileList',
            btn: '#processBtn',
            browseBtn: '#browseBtn',
            uploadUrl: '/api/upload.php',
            processUrl: '/api/process.php',
            toolName: '',
            allowedTypes: ['application/pdf'],
            allowedExt: ['.pdf'],
            maxFiles: 1,
            allowMultiple: false,
            maxUploadSize: window.SMART_PDF ? window.SMART_PDF.maxUploadSize : 104857600,
            onProgress: null,
            onComplete: null,
            onError: null,
            extraFields: null,
        }, cfg || {});

        this.files = []; // [{file, storedName, originalName, size, path}]

        this.dropZone = document.querySelector(this.cfg.dropZone);
        this.input    = document.querySelector(this.cfg.input);
        this.list     = document.querySelector(this.cfg.list);
        this.btn      = document.querySelector(this.cfg.btn);
        this.browseBtn= document.querySelector(this.cfg.browseBtn);

        if (!this.dropZone || !this.input) {
            console.warn('ToolUpload: missing required elements.');
            return;
        }
        this.bindEvents();
        this.renderFiles();
    }

    bindEvents() {
        const self = this;

        if (this.browseBtn) {
            this.browseBtn.addEventListener('click', () => this.input.click());
        }
        this.dropZone.addEventListener('click', (e) => {
            if (e.target.closest('.file-remove')) return;
            this.input.click();
        });

        this.input.addEventListener('change', function () {
            self.addFiles(this.files);
            this.value = '';
        });

        ['dragenter', 'dragover'].forEach(ev => {
            this.dropZone.addEventListener(ev, (e) => {
                e.preventDefault(); e.stopPropagation();
                this.dropZone.classList.add('dragover');
            });
        });
        ['dragleave', 'dragend', 'drop'].forEach(ev => {
            this.dropZone.addEventListener(ev, (e) => {
                e.preventDefault(); e.stopPropagation();
                this.dropZone.classList.remove('dragover');
            });
        });
        this.dropZone.addEventListener('drop', (e) => {
            const dt = e.dataTransfer;
            if (dt && dt.files) self.addFiles(dt.files);
        });

        if (this.btn) {
            this.btn.addEventListener('click', () => this.process());
        }
    }

    addFiles(fileList) {
        const incoming = Array.from(fileList || []);
        if (!this.cfg.allowMultiple) {
            // Only keep the first new file, ignore existing
            this.files = [];
        }
        for (const file of incoming) {
            if (this.files.length >= this.cfg.maxFiles) {
                this.error('You can only upload up to ' + this.cfg.maxFiles + ' file(s).');
                break;
            }
            if (!this.validate(file)) continue;
            this.files.push({
                file: file,
                originalName: file.name,
                size: file.size,
                storedName: null,
                path: null,
            });
        }
        this.renderFiles();
    }

    validate(file) {
        if (file.size > this.cfg.maxUploadSize) {
            this.error('"' + file.name + '" exceeds the maximum allowed size of ' +
                       window.formatBytes(this.cfg.maxUploadSize) + '.');
            return false;
        }
        const ext = '.' + (file.name.split('.').pop() || '').toLowerCase();
        if (this.cfg.allowedExt.length && !this.cfg.allowedExt.includes(ext)) {
            this.error('"' + file.name + '" has an unsupported extension. Allowed: ' +
                       this.cfg.allowedExt.join(', '));
            return false;
        }
        // MIME check (best-effort; final check is server-side)
        if (this.cfg.allowedTypes.length && file.type && !this.cfg.allowedTypes.includes(file.type)) {
            // Don't hard fail here: some browsers report empty MIME for valid files
            // Server re-validates via finfo anyway.
        }
        return true;
    }

    removeFile(idx) {
        this.files.splice(idx, 1);
        this.renderFiles();
    }

    renderFiles() {
        if (!this.list) return;
        if (!this.files.length) {
            this.list.classList.remove('has-files');
            this.list.innerHTML = '';
            if (this.btn) this.btn.disabled = false;
            return;
        }
        this.list.classList.add('has-files');
        this.list.innerHTML = this.files.map((f, i) => `
            <div class="file-row">
                <span class="file-icon"><i class="fas fa-file"></i></span>
                <div class="file-info">
                    <div class="file-name">${escapeHtml(f.originalName)}</div>
                    <div class="file-meta">${window.formatBytes(f.size)}</div>
                </div>
                <button type="button" class="file-remove" data-idx="${i}" aria-label="Remove file">
                    <i class="fas fa-xmark"></i>
                </button>
            </div>
        `).join('');
        const self = this;
        this.list.querySelectorAll('.file-remove').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.stopPropagation();
                self.removeFile(parseInt(btn.dataset.idx, 10));
            });
        });
    }

    error(msg) {
        const errEl = document.querySelector('.tool-error');
        if (errEl) {
            errEl.textContent = msg;
            errEl.classList.add('is-active');
            setTimeout(() => errEl.classList.remove('is-active'), 4500);
        } else {
            alert(msg);
        }
        if (typeof this.cfg.onError === 'function') this.cfg.onError(msg);
    }

    showProgress(active, text) {
        const wrap = document.querySelector('.tool-progress');
        const bar  = document.querySelector('.tool-progress .progress-bar');
        const txt  = document.querySelector('.tool-progress .progress-text');
        if (!wrap) return;
        wrap.classList.toggle('is-active', !!active);
        if (txt && text) txt.textContent = text;
        if (bar) bar.style.width = '0%';
    }

    setProgress(pct, text) {
        const bar = document.querySelector('.tool-progress .progress-bar');
        const txt = document.querySelector('.tool-progress .progress-text');
        if (bar) bar.style.width = pct + '%';
        if (txt && text) txt.textContent = text;
    }

    async uploadAll() {
        // Step 1: upload each file to /api/upload.php and collect stored filenames.
        const uploaded = [];
        for (let i = 0; i < this.files.length; i++) {
            const entry = this.files[i];
            const formData = new FormData();
            formData.append('file', entry.file);
            formData.append('tool', this.cfg.toolName);
            formData.append('csrf_token', window.SMART_PDF.csrfToken);

            this.setProgress(Math.round((i / this.files.length) * 50),
                             'Uploading "' + entry.originalName + '"...');

            try {
                const res = await window.smartUpload({
                    url: this.cfg.uploadUrl,
                    formData: formData,
                    onProgress: (p) => {
                        const overall = Math.round(((i + p / 100) / this.files.length) * 50);
                        this.setProgress(overall, 'Uploading "' + entry.originalName + '"... ' + p + '%');
                    },
                });
                entry.storedName = res.storedName;
                entry.path = res.path;
                uploaded.push(entry);
            } catch (err) {
                this.error((err && err.error) || 'Upload failed.');
                return null;
            }
        }
        return uploaded;
    }

    async process() {
        if (!this.files.length) {
            this.error('Please choose at least one file first.');
            return;
        }
        const resultEl = document.querySelector('.tool-result');
        if (resultEl) resultEl.classList.remove('is-active');
        const errEl = document.querySelector('.tool-error');
        if (errEl) errEl.classList.remove('is-active');
        if (this.btn) this.btn.disabled = true;

        this.showProgress(true, 'Starting...');
        this.setProgress(5, 'Preparing files...');

        const uploaded = await this.uploadAll();
        if (!uploaded) {
            this.showProgress(false);
            if (this.btn) this.btn.disabled = false;
            return;
        }

        // Step 2: call /api/process.php with the list of stored files.
        const formData = new FormData();
        formData.append('tool', this.cfg.toolName);
        formData.append('csrf_token', window.SMART_PDF.csrfToken);
        uploaded.forEach((entry, i) => {
            formData.append('files[' + i + ']', entry.storedName);
            formData.append('originals[' + i + ']', entry.originalName);
        });
        if (typeof this.cfg.extraFields === 'function') {
            const extra = this.cfg.extraFields() || {};
            for (const k in extra) {
                formData.append(k, extra[k]);
            }
        }

        this.setProgress(60, 'Processing on server...');

        try {
            const res = await window.smartUpload({
                url: this.cfg.processUrl,
                formData: formData,
            });
            this.setProgress(100, 'Done!');
            this.showResult(res);
            if (typeof this.cfg.onComplete === 'function') this.cfg.onComplete(res);
        } catch (err) {
            this.error((err && err.error) || 'Processing failed.');
            if (typeof this.cfg.onError === 'function') this.cfg.onError((err && err.error) || 'Processing failed.');
        } finally {
            if (this.btn) this.btn.disabled = false;
            setTimeout(() => this.showProgress(false), 800);
        }
    }

    showResult(res) {
        const wrap = document.querySelector('.tool-result');
        if (!wrap) {
            // Fallback: simple alert
            if (res.download_url) {
                window.location.href = res.download_url;
            } else if (res.downloads && res.downloads.length) {
                res.downloads.forEach(d => window.open(d.url, '_blank'));
            }
            return;
        }
        let html = '';
        if (res.downloads && res.downloads.length > 1) {
            html = `
                <div class="result-card">
                    <div class="result-icon"><i class="fas fa-circle-check"></i></div>
                    <h4 class="mb-1">Done! ${res.downloads.length} files ready</h4>
                    <p class="mb-3" style="opacity:.85">${escapeHtml(res.message || 'Your files are ready to download.')}</p>
                    <div class="d-flex flex-wrap justify-content-center gap-2">
                        ${res.downloads.map(d => `
                            <a href="${escapeHtml(d.url)}" class="btn" download>
                                <i class="fas fa-download me-1"></i> ${escapeHtml(d.name)}
                            </a>`).join('')}
                    </div>
                </div>`;
        } else if (res.downloads && res.downloads.length === 1) {
            html = `
                <div class="result-card">
                    <div class="result-icon"><i class="fas fa-circle-check"></i></div>
                    <h4 class="mb-1">Done! Your file is ready.</h4>
                    <p class="mb-3" style="opacity:.85">${escapeHtml(res.message || res.downloads[0].name)}</p>
                    <a href="${escapeHtml(res.downloads[0].url)}" class="btn" download>
                        <i class="fas fa-download me-1"></i> Download File
                    </a>
                </div>`;
        } else if (res.download_url) {
            html = `
                <div class="result-card">
                    <div class="result-icon"><i class="fas fa-circle-check"></i></div>
                    <h4 class="mb-1">Done! Your file is ready.</h4>
                    <a href="${escapeHtml(res.download_url)}" class="btn" download>
                        <i class="fas fa-download me-1"></i> Download File
                    </a>
                </div>`;
        } else if (res.message) {
            html = `<div class="result-card"><h4 class="mb-0">${escapeHtml(res.message)}</h4></div>`;
        }
        wrap.innerHTML = html;
        wrap.classList.add('is-active');
        wrap.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
}

function escapeHtml(str) {
    return String(str == null ? '' : str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
}

window.ToolUpload = ToolUpload;
window.escapeHtml = escapeHtml;
