<?php
// user/dashboard/notifications/unread-count.php
require_once '../../../includes/auth_check.php';
header('Content-Type: application/json');

$query = mysqli_query($conn, "SELECT COUNT(*) as unread_count FROM notifications WHERE user_id = '$user_id' AND is_read = 0");
$result = mysqli_fetch_assoc($query);
$count = $result ? (int)$result['unread_count'] : 0;

echo json_encode([
    'success' => true,
    'count' => $count
]);
exit();
?>
