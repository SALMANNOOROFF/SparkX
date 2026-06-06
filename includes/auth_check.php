<?php
// includes/auth_check.php
require_once 'db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header("Location: " . SITE_URL . "/login");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user and wallet data globally for dashboard
$user_query = mysqli_query($conn, "SELECT u.*, w.deposit_balance, w.earning_balance, w.total_invested, w.total_withdrawn 
                                   FROM users u 
                                   LEFT JOIN wallets w ON u.id = w.user_id 
                                   WHERE u.id = '$user_id'");
$user_data = mysqli_fetch_assoc($user_query);

// If user exists but wallet is missing, create it automatically
if ($user_data && is_null($user_data['deposit_balance'])) {
    mysqli_query($conn, "INSERT IGNORE INTO wallets (user_id) VALUES ('$user_id')");
    // Re-fetch to get new wallet data
    $user_query = mysqli_query($conn, "SELECT u.*, w.deposit_balance, w.earning_balance, w.total_invested, w.total_withdrawn 
                                       FROM users u 
                                       LEFT JOIN wallets w ON u.id = w.user_id 
                                       WHERE u.id = '$user_id'");
    $user_data = mysqli_fetch_assoc($user_query);
}

if (!$user_data) {
    session_destroy();
    header("Location: " . SITE_URL . "/login");
    exit();
}
?>
