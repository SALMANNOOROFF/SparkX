<?php
// admin/includes/admin_db.php
// Separate Database configuration for Admin panel

$admin_host = "sql103.infinityfree.com";
$admin_db_name = "if0_42113447_sparkx_db";
$admin_username = "if0_42113447";
$admin_password = "MMYqct9bwHt6";
$admin_port = 3306;

try {
    $admin_pdo = new PDO("mysql:host=$admin_host;port=$admin_port;dbname=$admin_db_name;charset=utf8mb4", $admin_username, $admin_password);
    $admin_pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $admin_pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Admin Database connection failed: " . $e->getMessage());
}
?>
