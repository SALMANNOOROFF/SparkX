<?php
// admin/includes/admin_config.php
// Separate Configuration for Admin panel

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Require the admin-specific database connection
require_once __DIR__ . '/admin_db.php';

// Override the global PDO connection object for admin scripts
$pdo = $admin_pdo;

// Admin Authentication Helpers
if (!function_exists('admin_is_logged_in')) {
    function admin_is_logged_in() {
        return isset($_SESSION['admin_id']);
    }
}

if (!function_exists('requireAdminLogin')) {
    function requireAdminLogin() {
        if (!admin_is_logged_in()) {
            header("Location: /sparkx1/admin/login.php");
            exit();
        }
    }
}

if (!function_exists('redirect')) {
    function redirect($url) {
        header("Location: " . $url);
        exit();
    }
}
?>
