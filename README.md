# Smart PDF — Online PDF Converter & Document Management

> Every PDF tool you need in one place — convert, compress, merge, split, edit,
> protect and manage PDF documents through a beautiful, modern web interface.
> Built with **Core PHP**, **MySQL**, **TCPDF / FPDI / FPDF** and **LibreOffice**.

Smart PDF is a complete, production-quality PHP/MySQL web application inspired by
iLovePDF and Smallpdf. It ships with **18 working PDF tools**, a full
authentication system, a user dashboard, an admin panel, a clean SaaS-grade UI
with light/dark mode, and real backend processing — not static demos.

---

## Table of Contents

1. [Features](#features)
2. [Tech Stack](#tech-stack)
3. [Project Structure](#project-structure)
4. [Installation Guide](#installation-guide)
   - [Step 1 — Install XAMPP](#step-1--install-xampp)
   - [Step 2 — Copy the project](#step-2--copy-the-project)
   - [Step 3 — Install Composer & PHP libraries](#step-3--install-composer--php-libraries)
   - [Step 4 — Install system binaries](#step-4--install-system-binaries)
   - [Step 5 — Create the database](#step-5--create-the-database)
   - [Step 6 — Configure the app](#step-6--configure-the-app)
   - [Step 7 — Run the website](#step-7--run-the-website)
5. [Default Admin Account](#default-admin-account)
6. [PDF Tools — How Each One Works](#pdf-tools--how-each-one-works)
7. [Testing Every Tool](#testing-every-tool)
8. [Security Notes](#security-notes)
9. [Troubleshooting](#troubleshooting)
10. [Folder Map](#folder-map)

---

## Features

### 18 Working PDF Tools
- **Convert from PDF:** PDF→Word, PDF→Excel, PDF→PowerPoint, PDF→JPG
- **Convert to PDF:** Word→PDF, Excel→PDF, PowerPoint→PDF, JPG→PDF
- **Organize:** Merge, Split, Rotate, Organize (delete/keep pages)
- **Optimize:** Compress (Ghostscript + FPDI fallback)
- **Edit:** Watermark, Page Numbers, PDF Editor (text overlay)
- **Security:** Protect (password + permissions), Unlock (qpdf)

### User System
- Registration, Login, Logout (PHP `password_hash()` / `password_verify()`)
- Forgot & Reset Password (token-based, 1-hour expiry)
- "Remember me" cookie (30 days)
- Role-based access control (`user` / `admin`)
- Protected dashboard pages

### User Dashboard
- Welcome banner + statistics (total conversions, success rate, uploads)
- Conversion history with bulk delete
- Profile editing + password change
- Settings (theme: light/dark, language)

### Admin Panel
- Site-wide stats (users, conversions, uploads, messages)
- Manage users (activate/deactivate, promote/demote, delete)
- View all conversion history site-wide
- View contact messages with reply-by-email
- Manual file cleanup utility

### Design & UX
- Modern SaaS-grade UI (iLovePDF/Smallpdf-inspired)
- Light **and** dark mode (persisted per user)
- Drag-and-drop uploader with progress bar
- Fully responsive (desktop, tablet, mobile)
- Bootstrap 5 + Font Awesome 6 + Google Fonts (Inter / Poppins)
- Page loader, flash messages, back-to-top, animated counters
- CSRF protection on every form

---

## Tech Stack

| Layer        | Technology                                            |
|--------------|-------------------------------------------------------|
| Frontend     | HTML5, CSS3, JavaScript, Bootstrap 5, Font Awesome 6  |
| Backend      | Core PHP 7.4+ (no framework)                          |
| Database     | MySQL / MariaDB via PDO                               |
| PDF libs     | FPDF, FPDI (Setasign), TCPDF                          |
| Office conv. | LibreOffice (headless)                                |
| Image conv.  | ImageMagick / Poppler (`pdftoppm`)                    |
| Compression  | Ghostscript (`gs`)                                    |
| Unlock       | qpdf                                                  |
| Dependencies | Composer                                              |

---

## Project Structure

```
smart-pdf/
├── index.php                  ← Homepage
├── .htaccess                  ← Apache rewrite rules + security
├── composer.json              ← PHP dependencies
├── README.md                  ← This file
│
├── config/
│   ├── app.php                ← App constants (URL, paths, limits)
│   └── database.php           ← PDO connection (singleton)
│
├── includes/
│   ├── functions.php          ← Core helpers (security, uploads, PDF ops)
│   ├── header.php             ← <head> + opening layout + navbar
│   ├── navbar.php             ← Top navigation
│   ├── footer.php             ← Footer + scripts
│   ├── sidebar.php            ← Dashboard sidebar
│   ├── auth_check.php         ← require_login() helper
│   ├── tool_page.php          ← Shared tool-page template
│   ├── dashboard_header.php   ← Dashboard layout (open)
│   └── dashboard_footer.php   ← Dashboard layout (close)
│
├── assets/
│   ├── css/
│   │   ├── style.css          ← Main stylesheet (light/dark)
│   │   ├── responsive.css     ← Media queries
│   │   └── dashboard.css      ← Dashboard / admin styles
│   ├── js/
│   │   ├── main.js            ← Global helpers, theme toggle
│   │   ├── upload.js          ← ToolUpload class (DnD, progress)
│   │   ├── tools.js           ← Tools index search/filter
│   │   └── dashboard.js       ← Sidebar, counters, confirms
│   └── images/                ← (Place your logo / illustrations here)
│
├── auth/
│   ├── login.php              ← Login form + handler
│   ├── register.php           ← Registration form + handler
│   ├── logout.php             ← Destroys session
│   ├── forgot_password.php    ← Generates reset token
│   └── reset_password.php     ← Consumes token, sets new password
│
├── tools/
│   ├── index.php              ← All Tools page (filterable grid)
│   ├── pdf_to_word.php        ┐
│   ├── word_to_pdf.php        │
│   ├── pdf_to_excel.php       │
│   ├── excel_to_pdf.php       │
│   ├── pdf_to_ppt.php         │  18 tool pages — each one is real,
│   ├── ppt_to_pdf.php         │  not a static demo.
│   ├── pdf_to_jpg.php         │
│   ├── jpg_to_pdf.php         │
│   ├── merge_pdf.php          │
│   ├── split_pdf.php          │
│   ├── compress_pdf.php       │
│   ├── rotate_pdf.php         │
│   ├── unlock_pdf.php         │
│   ├── protect_pdf.php        │
│   ├── watermark_pdf.php      │
│   ├── page_numbers.php       │
│   ├── organize_pdf.php       │
│   └── pdf_editor.php         ┘
│
├── api/
│   ├── upload.php             ← Accepts a file, validates, stores
│   ├── process.php            ← Runs the requested PDF operation
│   ├── download.php           ← Streams processed file to browser
│   ├── delete_file.php        ← Removes an uploaded file
│   └── set_theme.php          ← Persists theme preference
│
├── dashboard/
│   ├── index.php              ← Overview (stats + recent activity)
│   ├── history.php            ← Conversion history (bulk delete)
│   ├── profile.php            ← Edit name / change password
│   └── settings.php           ← Theme + language
│
├── admin/
│   ├── index.php              ← Admin dashboard
│   ├── users.php              ← Manage users
│   ├── conversions.php        ← Site-wide conversion history
│   ├── messages.php           ← Contact messages
│   └── cleanup.php            ← Run file cleanup
│
├── pages/
│   ├── about.php              ← About Us
│   ├── contact.php            ← Contact form (stores in DB)
│   ├── privacy.php            ← Privacy Policy
│   └── terms.php              ← Terms & Conditions
│
├── database/
│   └── smart_pdf_db.sql       ← Full schema + seed admin user
│
└── storage/                   ← Auto-cleaned every hour (DO NOT expose)
    ├── uploads/               ← Original uploaded files
    ├── processed/             ← Output PDFs / images
    └── temp/                  ← Working area
```

---

## Installation Guide

> Tested with **XAMPP 8.x** on Windows 10/11, macOS and Linux.
> Total setup time: ~15 minutes.

### Step 1 — Install XAMPP

Download and install XAMPP from <https://www.apachefriends.org/>. This gives
you Apache, PHP 8.x and MySQL/MariaDB.

After install, open the **XAMPP Control Panel** and start **Apache** and
**MySQL**.

### Step 2 — Copy the project

Copy the entire `smart-pdf/` folder into your XAMPP htdocs directory:

- **Windows:** `C:\xampp\htdocs\smart-pdf`
- **macOS:**   `/Applications/XAMPP/htdocs/smart-pdf`
- **Linux:**   `/opt/lampp/htdocs/smart-pdf`

### Step 3 — Install Composer & PHP libraries

If you don't have Composer:

1. Download it from <https://getcomposer.org/download/>.
2. On Windows, run `Composer-Setup.exe`.
3. On macOS/Linux:
   ```bash
   php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
   php composer-setup.php
   php -r "unlink('composer-setup.php');"
   sudo mv composer.phar /usr/local/bin/composer
   ```

Then, inside the project folder, install dependencies:

```bash
cd /path/to/htdocs/smart-pdf
composer install
```

This creates a `vendor/` folder containing:
- `setasign/fpdf` + `setasign/fpdi` — PDF merging, splitting, rotating
- `tecnickcom/tcpdf` — protection, watermark, page numbers, JPG→PDF
- `phpoffice/phpword`, `phpoffice/phpspreadsheet`, `phpoffice/phppresentation`
  — for richer Office conversion (optional; LibreOffice is the primary engine)

### Step 4 — Install system binaries

The PHP libraries handle most PDF operations natively, but the **office
conversions** (Word/Excel/PPT ↔ PDF) require **LibreOffice**, and a few tools
use additional command-line utilities.

#### Windows
1. Install **LibreOffice** from <https://www.libreoffice.org/> (default path
   is `C:\Program Files\LibreOffice\program\soffice.exe`).
2. *(Optional, recommended)* Install **Ghostscript** for better PDF
   compression: <https://ghostscript.com/releases/gsdnld.html>.
3. *(Optional)* Install **ImageMagick**: <https://imagemagick.org/script/download.php#windows>.
4. *(Optional, for Unlock PDF)* Install **qpdf**: <https://github.com/qpdf/qpdf/releases>.

#### macOS (Homebrew)
```bash
brew install --cask libreoffice
brew install ghostscript imagemagick qpdf popper
```

#### Linux (Debian/Ubuntu)
```bash
sudo apt update
sudo apt install -y libreoffice ghostscript imagemagick qpdf poppler-utils
```

> If LibreOffice is installed at a non-default path on Windows, edit
> `config/app.php` and update the `LIBREOFFICE_PATH` constant.

### Step 5 — Create the database

1. Open phpMyAdmin: **http://localhost/phpmyadmin**
2. Click **Import** in the top menu.
3. Choose the file `database/smart_pdf_db.sql` from the project.
4. Press **Go**.

This will:
- Drop & recreate the `smart_pdf_db` database
- Create all 7 tables (`users`, `social_accounts`, `conversion_history`,
  `uploaded_files`, `user_settings`, `password_resets`, `contact_messages`) with proper
  foreign keys, indexes and constraints
- Insert a default **admin user** (see below) and a settings row

### Step 6 — Configure the app

Open `config/app.php` and `config/database.php` and verify the values
match your environment. XAMPP defaults work out of the box:

```php
// config/app.php
define('APP_URL', 'http://localhost/smart-pdf');

// config/database.php
define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'smart_pdf_db');
define('DB_USER', 'root');
define('DB_PASS', '');          // XAMPP default = empty
```

### Enable Google and Facebook sign-in

1. Copy `config/social_auth.local.example.php` to `config/social_auth.local.php`.
2. Create OAuth apps with Google and Meta, then enter their client IDs/secrets
   in the copied file. Keep that local file private; it is excluded from Git.
3. Register this exact callback URL with **both** providers:

   ```
   http://localhost/smart-pdf/auth/social_login.php
   ```

   In Google Cloud Console, create a **Web application** OAuth client and add
   the callback as an authorized redirect URI. In Meta for Developers, add
   Facebook Login and add the callback under Valid OAuth Redirect URIs.
4. Import `database/migrations/2026_11_11_create_social_accounts.sql` in
   phpMyAdmin for databases created before social sign-in was added. New
   database installs already include the table in `database/smart_pdf_db.sql`.
5. Google may require adding your account as a test user while the OAuth app
   is in testing mode. Set the Facebook Graph API version in the local config
   to one supported by your Meta app.

OAuth credentials are provider-specific; the login buttons report an explicit
configuration message until valid credentials and the database migration are
in place. For a deployed site, use its HTTPS URL instead of localhost in both
the app configuration and provider callback settings.

### Step 7 — Run the website

Open your browser and visit:

```
http://localhost/smart-pdf/index.php
```

You should see the Smart PDF homepage. 🎉

---

## Default Admin Account

The SQL seed file inserts an administrator account:

| Field   | Value                    |
|---------|--------------------------|
| Email   | `admin@smartpdf.local`   |
| Password| `Admin@12345`            |

Log in via **http://localhost/smart-pdf/auth/login.php** and you'll see an
**Admin Panel** link in your user dropdown.

> **Important:** Change this password immediately after first login via the
> Profile page. For production, regenerate the bcrypt hash with
> `password_hash('YourNewPassword', PASSWORD_DEFAULT)` and update the SQL.

---

## PDF Tools — How Each One Works

| Tool              | Engine                                   | Notes |
|-------------------|------------------------------------------|-------|
| PDF → Word        | PyMuPDF + python-docx                     | Preserves text styling and approximate page positioning; complex layouts may need adjustment, scanned PDFs need OCR first |
| Word → PDF        | LibreOffice (`docx → pdf`)               | Preserves formatting well |
| PDF → Excel       | LibreOffice (`pdf → xlsx`)               | Works best with real table structures |
| Excel → PDF       | LibreOffice (`xlsx → pdf`)               | Each sheet becomes a page |
| PDF → PowerPoint  | LibreOffice (`pdf → pptx`)               | Each PDF page → one slide |
| PowerPoint → PDF  | LibreOffice (`pptx → pdf`)               | Animations flattened |
| PDF → JPG         | ImageMagick or `pdftoppm` (poppler)      | 150 DPI per page |
| JPG → PDF         | TCPDF                                    | Each image becomes one page |
| Merge PDF         | FPDI + FPDF                              | Combines in list order |
| Split PDF         | FPDI                                     | Each page or page range |
| Compress PDF      | Ghostscript (preferred) / FPDI fallback  | `/ebook` quality (150 DPI) |
| Rotate PDF        | FPDI                                     | 90, 180 or 270 degrees |
| Unlock PDF        | `qpdf --decrypt`                         | Requires the password |
| Protect PDF       | TCPDF `SetProtection()`                  | User + owner password, permissions |
| Watermark PDF     | TCPDF                                    | Diagonal red text on every page |
| Page Numbers      | TCPDF                                    | Bottom or top center |
| Organize PDF      | FPDI                                     | Delete or keep pages by range |
| PDF Editor        | TCPDF                                    | Adds text overlay at (x,y) |

---

## Testing Every Tool

Use the **sample PDFs** included in `database/` (or any of your own) and run
through this checklist:

| # | Tool                | Test steps                                                                                       | Expected result |
|---|---------------------|--------------------------------------------------------------------------------------------------|-----------------|
| 1 | PDF to Word         | Upload `sample.pdf` → click Convert                                                              | `.docx` file downloads |
| 2 | Word to PDF         | Upload any `.docx` → Convert                                                                     | `.pdf` downloads |
| 3 | PDF to Excel        | Upload a table-based PDF → Convert                                                                | `.xlsx` downloads |
| 4 | Excel to PDF        | Upload any `.xlsx` → Convert                                                                     | `.pdf` downloads |
| 5 | PDF to PowerPoint   | Upload `sample.pdf` → Convert                                                                    | `.pptx` downloads |
| 6 | PowerPoint to PDF   | Upload any `.pptx` → Convert                                                                     | `.pdf` downloads |
| 7 | PDF to JPG          | Upload `sample.pdf` → Convert                                                                    | One JPG per page (downloadable individually) |
| 8 | JPG to PDF          | Upload 2-3 JPGs → Convert                                                                        | Single combined PDF |
| 9 | Merge PDF           | Upload 2 PDFs → Merge                                                                            | One merged PDF |
| 10| Split PDF           | Upload a 5-page PDF → "Extract every page"                                                       | 5 separate PDFs |
| 11| Split PDF (range)   | Upload a 10-page PDF → "Extract page range" → from 2 to 4                                        | One 3-page PDF |
| 12| Compress PDF        | Upload a large PDF → Compress                                                                    | Smaller PDF + message showing % saved |
| 13| Rotate PDF          | Upload a PDF → choose 90° → Rotate                                                               | Rotated PDF |
| 14| Unlock PDF          | Upload a password-protected PDF, enter password → Unlock                                         | Decrypted PDF (requires `qpdf`) |
| 15| Protect PDF         | Upload PDF → set password → Protect                                                              | Password-protected PDF |
| 16| Watermark PDF       | Upload PDF → enter "CONFIDENTIAL" → Watermark                                                    | Diagonal red watermark on every page |
| 17| Page Numbers        | Upload PDF → choose "Bottom Center" → Add Numbers                                                | Pages numbered 1, 2, 3… |
| 18| Organize PDF        | Upload 10-page PDF → "Pages to Delete" = `1, 5-7` → Organize                                     | PDF with pages 2, 3, 4, 8, 9, 10 |
| 19| PDF Editor          | Upload PDF → enter text "APPROVED" at x=50, y=50 → Edit                                          | PDF with red bold text on every page |

### Authentication & dashboard tests

1. **Register** a new user at `/auth/register.php` → should auto-login and
   redirect to dashboard.
2. **Logout** → **Login** with the new credentials.
3. Visit **Profile** → change name → save → verify it updated.
4. Visit **Profile** → change password (wrong current) → should error.
5. Visit **Settings** → toggle dark mode → entire site should switch theme.
6. Use any tool while logged in → check **Conversion History** records it.
7. **Forgot password** → enter email → click the generated link → reset
   password → login with new password.

### Admin tests

1. Login as `admin@smartpdf.local` / `Admin@12345`.
2. Click **Admin Panel** in the user dropdown.
3. Verify stats are populated.
4. Visit **Manage Users** → toggle a user's active status → verify they
   can no longer login.
5. Visit **Contact Messages** → submit the contact form on `/pages/contact.php`
   → return and verify the message appears.
6. Visit **Run Cleanup** → expired files (older than 1 hour) should be
   deleted.

---

## Security Notes

This project implements the security best-practices expected from a
university-level PHP project:

| Concern                  | How it's handled |
|--------------------------|------------------|
| SQL injection            | All queries use PDO prepared statements |
| XSS                      | All output escaped via `e()` (`htmlspecialchars`) |
| CSRF                     | Every form includes `csrf_field()`; verified in `csrf_verify()` |
| Password storage         | `password_hash()` (bcrypt) + `password_verify()` |
| File upload validation   | `finfo` MIME check server-side, never trusting browser-supplied type |
| File path traversal      | `realpath()` check inside storage dirs in `download.php` |
| Direct file access       | `.htaccess` blocks `/storage`, `/config`, `/includes`, `/database`, `/vendor` |
| Session fixation         | `session_regenerate_id()` on login + every 30 min |
| Session cookies          | `HttpOnly` + `SameSite=Lax` |
| Path traversal in files  | Filenames sanitized; unique random stored names |
| Auto-cleanup             | Files older than 1 hour auto-deleted (1-in-50 request chance + manual admin button) |
| Admin access             | `require_admin()` on every admin page |

---

## Troubleshooting

**"Could not connect to the database."**
→ Make sure MySQL is started in XAMPP Control Panel and that the credentials
  in `config/database.php` are correct.

**"Composer autoloader not found."**
→ Run `composer install` inside the project folder.

**Office conversion tools say "LibreOffice is not installed."**
→ Install LibreOffice and (on Windows) verify the path in
  `config/app.php` (`LIBREOFFICE_PATH`).

**"Unlock PDF" fails with "qpdf is not available."**
→ Install `qpdf` (Linux: `apt install qpdf`; macOS: `brew install qpdf`;
  Windows: download from <https://github.com/qpdf/qpdf/releases>).

**PDF→JPG produces no output.**
→ Install either ImageMagick or Poppler (`pdftoppm`). On Linux:
  `apt install poppler-utils imagemagick`.

**Compress shows "0%" reduction.**
→ Ghostscript isn't installed. Install it and try again, or accept the
  FPDI fallback which doesn't always reduce size.

**Uploads above 8 MB fail.**
→ Edit your `php.ini` (in XAMPP: `C:\xampp\php\php.ini`) and raise:
  ```ini
  upload_max_filesize = 100M
  post_max_size       = 100M
  memory_limit        = 256M
  ```
  Restart Apache after saving.

**"CSRF token expired" appears.**
→ Refresh the page; the token rotates every 30 minutes for security.

**Forgot-password link doesn't arrive.**
→ For university projects the link is shown on screen (no SMTP). In
  production, replace the `$demoLink` block in `auth/forgot_password.php`
  with a real `mail()` call.

---

## License

MIT — free to use, modify and distribute. Attribution appreciated but not
required.

---

## Credits

Built with:
- [TCPDF](https://tcpdf.org/) by Nicola Asuni
- [FPDF](http://www.fpdf.org/) / [FPDI](https://www.setasign.com/products/fpdi/) by Setasign
- [LibreOffice](https://www.libreoffice.org/)
- [Bootstrap 5](https://getbootstrap.com/) by the Bootstrap team
- [Font Awesome 6](https://fontawesome.com/)

Inspired by [iLovePDF](https://www.ilovepdf.com/) and
[Smallpdf](https://smallpdf.com/).
