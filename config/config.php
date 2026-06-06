<?php
// config/config.php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database Credentials
$host = "sql103.infinityfree.com";
$db_name = "if0_42113447_sparkx_db";
$username = "if0_42113447";
$password = "MMYqct9bwHt6";
$port = 3306;

// Detect if running on localhost or live server
$is_localhost = ($_SERVER['HTTP_HOST'] === 'localhost' || $_SERVER['HTTP_HOST'] === '127.0.0.1');
if ($is_localhost) {
    define('SITE_URL', '/sparkx1');
    define('ADMIN_URL', '/sparkx1/admin');
} else {
    define('SITE_URL', '');
    define('ADMIN_URL', '/admin');
}

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$db_name;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Load separate admin config if accessing admin panel
if (strpos($_SERVER['REQUEST_URI'], '/admin') !== false || strpos($_SERVER['SCRIPT_NAME'], '/admin/') !== false) {
    require_once __DIR__ . '/../admin/includes/admin_config.php';
}

// Admin Security Function (Fallback for non-admin context if needed)
if (!function_exists('requireAdminLogin')) {
    function requireAdminLogin() {
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
            header("Location: " . SITE_URL . "/login");
            exit();
        }
    }
}

// Helper functions for Admin Dashboard
function format_pkr($amount) {
    return 'Rs. ' . number_format($amount, 2);
}

function format_usd($amount) {
    return '$' . number_format($amount, 2);
}

// Helper to fetch settings from database or return default
function get_setting($pdo, $key, $default = '') {
    try {
        $stmt = $pdo->prepare("SELECT value FROM settings WHERE name = ?");
        $stmt->execute([$key]);
        $result = $stmt->fetch();
        return $result ? $result['value'] : $default;
    } catch (Exception $e) {
        return $default; // Return default if settings table doesn't exist yet
    }
}
?>
