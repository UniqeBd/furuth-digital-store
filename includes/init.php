<?php
declare(strict_types=1);

$config = require __DIR__ . '/../config/app.php';
date_default_timezone_set($config['timezone'] ?? 'UTC');

if (session_status() === PHP_SESSION_NONE) {
    session_name($config['session_name'] ?? 'furuth_session');
    session_start();
}

require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/functions.php';

// Theme preference
if (isset($_GET['theme']) && in_array($_GET['theme'], ['light', 'dark'], true)) {
    $_SESSION['theme'] = $_GET['theme'];
    setcookie('furuth_theme', $_GET['theme'], time() + 86400 * 365, '/');
}
if (empty($_SESSION['theme']) && !empty($_COOKIE['furuth_theme'])) {
    $_SESSION['theme'] = $_COOKIE['furuth_theme'];
}
$theme = $_SESSION['theme'] ?? 'light';
