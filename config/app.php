<?php
/**
 * Smart PDF - Application Configuration
 * ------------------------------------------------------------------
 * Edit these constants to match your environment (XAMPP defaults are shown).
 */

// Show errors in development, hide them in production.
// For production set: error_reporting(0); ini_set('display_errors', 0);
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
date_default_timezone_set('Asia/Karachi');

// Application settings
define('APP_NAME', 'Smart PDF');
define('APP_TAGLINE', 'Every PDF Tool You Need in One Place');
define('APP_URL', 'http://localhost/smart-pdf');   // No trailing slash
define('APP_EMAIL', 'support@smartpdf.local');
define('APP_VERSION', '1.0.0');

// Session settings
define('SESSION_NAME', 'SMARTPDF_SESSION');
define('SESSION_LIFETIME', 7200); // 2 hours

// File upload settings
define('MAX_UPLOAD_SIZE', 104857600);   // 100 MB
define('UPLOAD_DIR', __DIR__ . '/../storage/uploads/');
define('PROCESSED_DIR', __DIR__ . '/../storage/processed/');
define('TEMP_DIR', __DIR__ . '/../storage/temp/');

// Allowed MIME types per category
define('ALLOWED_PDF_MIME', ['application/pdf']);
define('ALLOWED_IMAGE_MIME', ['image/jpeg', 'image/png', 'image/webp']);
define('ALLOWED_OFFICE_MIME', [
    'application/msword',
    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    'application/vnd.ms-excel',
    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    'application/vnd.ms-powerpoint',
    'application/vnd.openxmlformats-officedocument.presentationml.presentation',
]);

// External binary paths (XAMPP/LibreOffice defaults). Adjust if needed.
define('LIBREOFFICE_PATH', 'C:\Program Files\LibreOffice\program\soffice.com');
define('IMAGEMAGICK_PATH', 'convert');       // or 'magick' on Windows

// File expiry in seconds for cleanup (default 1 hour)
define('FILE_EXPIRY_SECONDS', 3600);

// Security
define('CSRF_TOKEN_NAME', 'csrf_token');
define('REMEMBER_ME_DAYS', 30);
