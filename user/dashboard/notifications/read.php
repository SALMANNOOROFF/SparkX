<?php
// user/dashboard/notifications/read.php
require_once '../../../includes/auth_check.php';
header('Content-Type: application/json');

// Check parameter via GET or JSON body
$param = isset($_GET['id']) ? trim($_GET['id']) : '';

if (empty($param)) {
    $input = json_decode(file_get_contents('php://input'), true);
    $param = isset($input['id']) ? trim($input['id']) : '';
}

$success = false;

if ($param === 'all') {
    $query = mysqli_query($conn, "UPDATE notifications SET is_read = 1 WHERE user_id = '$user_id'");
    if ($query) {
        $success = true;
    }
} elseif (!empty($param)) {
    $notif_id = (int)$param;
    $query = mysqli_query($conn, "UPDATE notifications SET is_read = 1 WHERE id = '$notif_id' AND user_id = '$user_id'");
    if ($query) {
        $success = true;
    }
}

echo json_encode([
    'success' => $success
]);
exit();
?>
