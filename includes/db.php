<?php
// includes/db.php
$host = "sql103.infinityfree.com";
$username = "if0_42113447";
$password = "MMYqct9bwHt6";
$dbname = "if0_42113447_sparkx_db";
$port = 3306;

// Define SITE_URL dynamically
if (!defined('SITE_URL')) {
    $is_localhost = ($_SERVER['HTTP_HOST'] === 'localhost' || $_SERVER['HTTP_HOST'] === '127.0.0.1');
    if ($is_localhost) {
        define('SITE_URL', '/sparkx1');
    } else {
        define('SITE_URL', '');
    }
}

$conn = mysqli_connect($host, $username, $password, $dbname, $port);

if (!$conn) {
    die("Database Connection Error");
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!function_exists('get_setting')) {
    function get_setting($key, $default = '')
    {
        global $conn;
        if (!$conn) {
            return $default;
        }
        try {
            $stmt = mysqli_prepare($conn, "SELECT value FROM settings WHERE name = ?");
            if ($stmt) {
                mysqli_stmt_bind_param($stmt, "s", $key);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_bind_result($stmt, $value);
                if (mysqli_stmt_fetch($stmt)) {
                    mysqli_stmt_close($stmt);
                    return $value;
                }
                mysqli_stmt_close($stmt);
            }
            return $default;
        } catch (Exception $e) {
            return $default;
        }
    }
}
if (!function_exists('format_amount_system')) {
    function format_amount_system($usd_amount, $decimals = 2)
    {
        $mode = get_setting('currency_mode', 'pkr');
        $rate = floatval(get_setting('usdt_pkr_rate', '290'));
        $symbol = get_setting('currency_symbol', 'RS');
        if ($mode === 'pkr') {
            $pkr_amount = $usd_amount * $rate;
            return $symbol . ' ' . number_format($pkr_amount, $decimals);
        } else {
            return '$' . number_format($usd_amount, $decimals);
        }
    }
}
?>