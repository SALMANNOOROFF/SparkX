<?php
require_once '../../includes/auth_check.php';

header('Content-Type: application/json');

$identifier = isset($_GET['identifier']) ? mysqli_real_escape_string($conn, $_GET['identifier']) : '';

if (empty($identifier)) {
    echo json_encode(['status' => 'error']);
    exit();
}

$query = mysqli_query($conn, "SELECT status FROM payments WHERE identifier = '$identifier'");
$payment = mysqli_fetch_assoc($query);

echo json_encode([
    'status' => $payment ? $payment['status'] : 'not_found'
]);
