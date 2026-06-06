<?php
// cron.php (Root directory)
require_once 'includes/db.php';

// Check profit distribution restrictions
$today_day = date('l');
$profit_mode = get_setting('profit_distribution_mode', 'daily');
$profit_day = get_setting('profit_distribution_day', 'Friday');

$should_run = false;

if ($profit_mode === 'everyday') {
    $should_run = true;
} elseif ($profit_mode === 'daily') {
    // Mon-Fri
    $should_run = in_array($today_day, ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday']);
} elseif ($profit_mode === 'weekly') {
    // Every Friday
    $should_run = (strcasecmp($today_day, 'Friday') === 0);
} elseif ($profit_mode === 'selected_day') {
    // Only on selected day
    $should_run = (strcasecmp($today_day, $profit_day) === 0);
}

if (!$should_run) {
    echo "Cron skipped today ($today_day) as per Profit Distribution Mode: " . htmlspecialchars($profit_mode) . ($profit_mode === 'selected_day' ? " ($profit_day)" : "");
    exit;
}


// Fetch all active investments
$query = mysqli_query($conn, "SELECT i.*, p.name as plan_name 
                               FROM investments i 
                               JOIN plans p ON i.plan_id = p.id 
                               WHERE i.status = 'active'");

$count = 0;
while ($inv = mysqli_fetch_assoc($query)) {
    $inv_id = $inv['id'];
    $user_id = $inv['user_id'];
    $amount = $inv['amount'];
    $hourly_rate = $inv['hourly_rate'];
    
    // Calculate full profit per run (no division by 60, so amounts are large enough to be visible in transactions)
    $profit = ($amount * $hourly_rate) / 100;

    if ($profit > 0) {
        // Start transaction for each user to ensure consistency
        mysqli_begin_transaction($conn);
        try {
            // 1. Add profit to earning balance
            mysqli_query($conn, "UPDATE wallets SET earning_balance = earning_balance + $profit WHERE user_id = '$user_id'");

            // 2. Log profit transaction
            $desc = "Simulated profit from " . $inv['plan_name'] . " ($" . number_format($amount, 2) . ")";
            mysqli_query($conn, "INSERT INTO transactions (user_id, amount, type, description, status) 
                                 VALUES ('$user_id', '$profit', 'profit', '$desc', 'completed')");

            // 3. Update last profit time if you have such a column (optional)
            
            mysqli_commit($conn);
            $count++;
        } catch (Exception $e) {
            mysqli_rollback($conn);
        }
    }
}

echo "Cron finished. Profits distributed to $count active investments.";
?>
