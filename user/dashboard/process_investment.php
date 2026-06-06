<?php
// user/dashboard/process_investment.php
require_once '../../includes/auth_check.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $plan_id = (int)$_POST['plan_id'];
    $amount = (float)$_POST['amount'];
    $wallet_type = $_POST['wallet_type']; // fund_wallet or earning_balance

    // 1. Get Plan Details
    $plan_query = mysqli_query($conn, "SELECT * FROM plans WHERE id = '$plan_id' AND status = 'active'");
    $plan = mysqli_fetch_assoc($plan_query);

    if (!$plan) {
        die(json_encode(['status' => 'error', 'message' => 'Invalid plan!']));
    }

    // 2. Validate Amount
    if ($amount < $plan['min_investment'] || $amount > $plan['max_investment']) {
        die(json_encode(['status' => 'error', 'message' => 'Amount must be between $' . $plan['min_investment'] . ' and $' . $plan['max_investment']]));
    }

    // 3. Check Balance
    $wallet_col = ($wallet_type === 'fund_wallet') ? 'deposit_balance' : 'earning_balance';
    if ($user_data[$wallet_col] < $amount) {
        die(json_encode(['status' => 'error', 'message' => 'Insufficient balance in your ' . str_replace('_', ' ', $wallet_type)]));
    }

    // 4. Check if approval is required
    $set_query = mysqli_query($conn, "SELECT value FROM settings WHERE name = 'approval_required_investment'");
    $set_res = mysqli_fetch_assoc($set_query);
    $approval_required = $set_res ? $set_res['value'] : '0';

    // 5. Start Transaction
    mysqli_begin_transaction($conn);

    try {
        $daily_roi = $plan['daily_roi_min'];
        $hourly_rate = $plan['hourly_rate'];

        if ($approval_required === '1') {
            // Deduct Balance (Holding state - do not increment total_invested yet)
            mysqli_query($conn, "UPDATE wallets SET $wallet_col = $wallet_col - $amount WHERE user_id = '$user_id'");

            // Create Pending Investment
            mysqli_query($conn, "INSERT INTO investments (user_id, plan_id, amount, daily_roi, hourly_rate, status) 
                                 VALUES ('$user_id', '$plan_id', '$amount', '$daily_roi', '$hourly_rate', 'pending')");

            // Log Transaction as Pending
            $desc = "Invested $" . number_format($amount, 2) . " in " . $plan['name'] . " - Pending Admin Approval";
            mysqli_query($conn, "INSERT INTO transactions (user_id, amount, type, description, status) 
                                 VALUES ('$user_id', '$amount', 'investment', '$desc', 'pending')");

            mysqli_commit($conn);
            echo json_encode(['status' => 'success', 'message' => 'Investment queued! Your node plan will start once admin approves it.']);
        } else {
            // Deduct Balance and increment total_invested immediately
            mysqli_query($conn, "UPDATE wallets SET $wallet_col = $wallet_col - $amount, total_invested = total_invested + $amount WHERE user_id = '$user_id'");

            // Create Active Investment
            mysqli_query($conn, "INSERT INTO investments (user_id, plan_id, amount, daily_roi, hourly_rate, status) 
                                 VALUES ('$user_id', '$plan_id', '$amount', '$daily_roi', '$hourly_rate', 'active')");

            // Log Transaction as Completed
            $desc = "Invested $" . number_format($amount, 2) . " in " . $plan['name'];
            mysqli_query($conn, "INSERT INTO transactions (user_id, amount, type, description, status) 
                                 VALUES ('$user_id', '$amount', 'investment', '$desc', 'completed')");

            // --- 5-Level MLM Referral Commission Distribution ---
            // Fetch up to 5 levels of referrers for this user
            $referrers_query = mysqli_query($conn, "SELECT referrer_id, level FROM referrals WHERE referee_id = '$user_id' AND level <= 5 ORDER BY level ASC");
            
            // Fetch referral commission percentages from settings
            $settings_q = mysqli_query($conn, "SELECT level, commission_pct FROM referral_settings");
            $comms_pct = [];
            if ($settings_q) {
                while ($row = mysqli_fetch_assoc($settings_q)) {
                    $comms_pct[$row['level']] = (float)$row['commission_pct'];
                }
            }
            
            if ($referrers_query) {
                while ($ref = mysqli_fetch_assoc($referrers_query)) {
                    $referrer_id = $ref['referrer_id'];
                    $level = (int)$ref['level'];
                    
                    // Get percentage for this level (fallback to defaults if missing)
                    $pct = isset($comms_pct[$level]) ? $comms_pct[$level] : ($level == 1 ? 10.0 : ($level == 2 ? 5.0 : ($level == 3 ? 3.0 : ($level == 4 ? 2.0 : 1.0))));
                    
                    // Calculate commission amount
                    $commission_amount = $amount * ($pct / 100.0);
                    
                    if ($commission_amount > 0) {
                        // 1. Credit the commission to the referrer's earning_balance
                        mysqli_query($conn, "UPDATE wallets SET earning_balance = earning_balance + $commission_amount WHERE user_id = '$referrer_id'");
                        
                        // 2. Log the transaction for the referrer
                        $referee_name = $user_data['name'];
                        $bonus_desc = "Referral Bonus of $" . number_format($commission_amount, 2) . " from Level {$level} referral ({$referee_name}) investing in " . $plan['name'];
                        mysqli_query($conn, "INSERT INTO transactions (user_id, amount, type, description, status) 
                                             VALUES ('$referrer_id', '$commission_amount', 'referral_bonus', '$bonus_desc', 'completed')");
                    }
                }
            }

            mysqli_commit($conn);
            echo json_encode(['status' => 'success', 'message' => 'Investment successful! Your earning has started.']);
        }
    } catch (Exception $e) {
        mysqli_rollback($conn);
        echo json_encode(['status' => 'error', 'message' => 'System error: ' . $e->getMessage()]);
    }
}
?>
