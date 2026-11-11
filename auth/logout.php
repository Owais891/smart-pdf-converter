<?php
/**
 * Smart PDF - Logout
 */
require_once __DIR__ . '/../includes/functions.php';

// CSRF check on GET logout (recommended to use POST in production)
if (!empty($_GET['token']) && hash_equals(csrf_token(), (string)$_GET['token'])) {
    logout_user();
    flash('success', 'You have been logged out. See you soon!');
} else {
    // Soft logout - still destroy the session
    logout_user();
}
redirect(APP_URL . '/index.php');
