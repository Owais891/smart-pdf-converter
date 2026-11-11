<?php
/**
 * Smart PDF - set_theme.php
 * Lightweight endpoint to persist the user's theme preference.
 */
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    send_json_error('Method not allowed.', 405);
}

csrf_verify();
$theme = ($_POST['theme'] ?? '') === 'dark' ? 'dark' : 'light';
set_theme($theme);

send_json_success(['theme' => $theme]);
