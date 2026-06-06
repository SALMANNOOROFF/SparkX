<?php
// user/dashboard/referrals/claim.php
require_once '../../../includes/auth_check.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    
    // Sum total referral earnings
    $q = mysqli_query($conn, "SELECT SUM(amount) as total FROM transactions WHERE user_id = '$user_id' AND type = 'referral_bonus' AND status = 'completed'");
    $referral_earning = 0;
    if ($q) {
        $referral_earning = mysqli_fetch_assoc($q)['total'] ?? 0;
    }
    
    if ($referral_earning > 0) {
        echo json_encode([
            'success' => true,
            'message' => 'Your referral earnings of $' . number_format($referral_earning, 2) . ' have been fully consolidated and are active in your wallet available balance!'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'You do not have any referral earnings to claim yet.'
        ]);
    }
    exit();
}
?>
