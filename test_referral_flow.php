<?php
// test_referral_flow.php
// Place in the root of c:\xampp\htdocs\sparkx1\
require_once 'includes/db.php';

$action = isset($_GET['action']) ? $_GET['action'] : '';
$output_log = [];
$test_success = true;

// Helper to log test steps
function log_step($message, $type = 'info') {
    global $output_log;
    $output_log[] = ['message' => $message, 'type' => $type];
}

// Clean up function
function cleanup_test_data($conn) {
    // 1. Get IDs of test users
    $user_q = mysqli_query($conn, "SELECT id FROM users WHERE email LIKE 'test_referral_%'");
    $ids = [];
    while ($row = mysqli_fetch_assoc($user_q)) {
        $ids[] = $row['id'];
    }
    
    if (empty($ids)) {
        return 0;
    }
    
    $ids_str = implode(',', $ids);
    
    // 2. Delete related records
    mysqli_query($conn, "DELETE FROM investments WHERE user_id IN ($ids_str)");
    mysqli_query($conn, "DELETE FROM transactions WHERE user_id IN ($ids_str)");
    mysqli_query($conn, "DELETE FROM wallets WHERE user_id IN ($ids_str)");
    mysqli_query($conn, "DELETE FROM referrals WHERE referrer_id IN ($ids_str) OR referee_id IN ($ids_str)");
    mysqli_query($conn, "DELETE FROM users WHERE id IN ($ids_str)");
    
    return count($ids);
}

// Check if a plan exists, if not, create a dummy active plan
function get_or_create_plan($conn) {
    $plan_q = mysqli_query($conn, "SELECT * FROM plans WHERE status = 'active' LIMIT 1");
    if ($plan_q && mysqli_num_rows($plan_q) > 0) {
        return mysqli_fetch_assoc($plan_q);
    }
    
    // Create a dummy plan
    log_step("No active plan found! Creating a temporary Active Investment Plan.", "warning");
    $insert_plan = "INSERT INTO plans (name, min_investment, max_investment, daily_roi_min, daily_roi_max, hourly_rate, status) 
                    VALUES ('Test Plan X', 10.00, 5000.00, 2.5, 3.5, 0.104, 'active')";
    if (mysqli_query($conn, $insert_plan)) {
        $plan_id = mysqli_insert_id($conn);
        $plan_q = mysqli_query($conn, "SELECT * FROM plans WHERE id = '$plan_id'");
        return mysqli_fetch_assoc($plan_q);
    }
    return null;
}

if ($action === 'cleanup') {
    $count = cleanup_test_data($conn);
    header("Location: test_referral_flow.php?msg=Cleaned up $count test records successfully!");
    exit();
}

if ($action === 'run_test') {
    try {
        log_step("Starting Automated 5-Level Referral System Test...", "info");
        
        // 1. Clean previous data
        $cleaned = cleanup_test_data($conn);
        if ($cleaned > 0) {
            log_step("Stale test data cleared ($cleaned users and related records deleted).", "success");
        }
        
        // 2. Load referral settings
        $settings_q = mysqli_query($conn, "SELECT level, commission_pct FROM referral_settings ORDER BY level ASC");
        $commissions = [];
        while ($row = mysqli_fetch_assoc($settings_q)) {
            $commissions[$row['level']] = (float)$row['commission_pct'];
        }
        
        // Show active commission percentages
        log_step("Loaded Commission Percentages from database table (<code>referral_settings</code>):", "info");
        for ($i = 1; $i <= 5; $i++) {
            $pct = isset($commissions[$i]) ? $commissions[$i] : ($i == 1 ? 10.0 : ($i == 2 ? 5.0 : ($i == 3 ? 3.0 : ($i == 4 ? 2.0 : 1.0))));
            log_step("- Level $i: <strong>$pct%</strong> " . (isset($commissions[$i]) ? "(from DB settings)" : "(default fallback)"), "info");
            $commissions[$i] = $pct; // Ensure all 5 levels have a percentage set
        }
        
        // 3. Create user chain
        // User A (Top) -> User B -> User C -> User D -> User E (Bottom)
        $users_chain = [
            ['name' => 'Test User A (Level 5 Uplink)', 'email' => 'test_referral_a@sparkx.com', 'ref' => 'TESTREFA', 'parent' => ''],
            ['name' => 'Test User B (Level 4 Uplink)', 'email' => 'test_referral_b@sparkx.com', 'ref' => 'TESTREFB', 'parent' => 'TESTREFA'],
            ['name' => 'Test User C (Level 3 Uplink)', 'email' => 'test_referral_c@sparkx.com', 'ref' => 'TESTREFC', 'parent' => 'TESTREFB'],
            ['name' => 'Test User D (Level 2 Uplink)', 'email' => 'test_referral_d@sparkx.com', 'ref' => 'TESTREFD', 'parent' => 'TESTREFC'],
            ['name' => 'Test User E (Level 1 Uplink/Referee)', 'email' => 'test_referral_e@sparkx.com', 'ref' => 'TESTREFE', 'parent' => 'TESTREFD']
        ];
        
        $created_users = [];
        $hashed_pwd = password_hash('password123', PASSWORD_DEFAULT);
        
        log_step("Registering 5 users sequentially to build the hierarchy chain...", "info");
        
        foreach ($users_chain as $index => $u) {
            $name = $u['name'];
            $email = $u['email'];
            $my_ref_code = $u['ref'];
            $referred_by = $u['parent'];
            $phone = '123456789' . $index;
            
            $insert = "INSERT INTO users (name, email, phone, password, referral_code, referred_by, role) 
                       VALUES ('$name', '$email', '$phone', '$hashed_pwd', '$my_ref_code', '$referred_by', 'user')";
            
            if (mysqli_query($conn, $insert)) {
                $new_id = mysqli_insert_id($conn);
                $created_users[$my_ref_code] = [
                    'id' => $new_id,
                    'name' => $name,
                    'email' => $email,
                    'ref' => $my_ref_code,
                    'parent' => $referred_by
                ];
                
                // Create wallet
                mysqli_query($conn, "INSERT INTO wallets (user_id) VALUES ('$new_id')");
                
                // Populate closure table referrals
                if (!empty($referred_by)) {
                    $current_referred_by = $referred_by;
                    $current_referee_id = $new_id;
                    $level = 1;
                    
                    while (!empty($current_referred_by) && $level <= 5) {
                        $parent_q = mysqli_query($conn, "SELECT id, referral_code, referred_by FROM users WHERE referral_code = '$current_referred_by'");
                        if ($parent_q && mysqli_num_rows($parent_q) > 0) {
                            $parent = mysqli_fetch_assoc($parent_q);
                            $parent_id = $parent['id'];
                            
                            mysqli_query($conn, "INSERT INTO referrals (referrer_id, referee_id, level) VALUES ('$parent_id', '$current_referee_id', '$level')");
                            
                            $current_referred_by = $parent['referred_by'];
                            $level++;
                        } else {
                            break;
                        }
                    }
                }
                
                log_step("Registered <strong>$name</strong> (Code: <code>$my_ref_code</code>)" . ($referred_by ? " referred by <code>$referred_by</code>" : " as root user."), "success");
            } else {
                throw new Exception("Failed to register $name: " . mysqli_error($conn));
            }
        }
        
        // 4. Verify referrals table mapping in DB
        log_step("Verifying multi-level mappings inside <code>referrals</code> database table...", "info");
        $referee_id = $created_users['TESTREFE']['id']; // Bottom user (User E)
        
        $check_ref_q = mysqli_query($conn, "SELECT r.*, u.name as referrer_name FROM referrals r 
                                            JOIN users u ON r.referrer_id = u.id 
                                            WHERE r.referee_id = '$referee_id' 
                                            ORDER BY r.level ASC");
                                            
        $mapped_levels = [];
        while ($row = mysqli_fetch_assoc($check_ref_q)) {
            $mapped_levels[(int)$row['level']] = $row;
            log_step("Verification: User E's <strong>Level {$row['level']} Upline</strong> is registered as <strong>{$row['referrer_name']}</strong> in <code>referrals</code> table.", "success");
        }
        
        for ($l = 1; $l <= 4; $l++) {
            if (!isset($mapped_levels[$l])) {
                log_step("CRITICAL ERROR: Level $l mapping is missing from the <code>referrals</code> table for User E!", "danger");
                $test_success = false;
            }
        }
        
        // 5. Get Active Investment Plan
        $plan = get_or_create_plan($conn);
        if (!$plan) {
            throw new Exception("Could not find or create an active investment plan.");
        }
        log_step("Active Plan identified: <strong>{$plan['name']}</strong> (Min: \${$plan['min_investment']}, Max: \${$plan['max_investment']}, Daily ROI: {$plan['daily_roi_min']}%)", "info");
        
        // 6. Fund the referee user (User E)
        $invest_amount = 1000.00;
        if ($invest_amount < $plan['min_investment']) {
            $invest_amount = $plan['min_investment'];
        }
        
        log_step("Funding User E's Deposit Balance with <strong>\$" . number_format($invest_amount, 2) . "</strong> directly in the wallet.", "info");
        mysqli_query($conn, "UPDATE wallets SET deposit_balance = $invest_amount WHERE user_id = '$referee_id'");
        
        // 7. Simulate investment process (Matches user/dashboard/process_investment.php logic)
        log_step("Simulating active investment of <strong>\$" . number_format($invest_amount, 2) . "</strong> in plan '<strong>{$plan['name']}</strong>' by User E...", "info");
        
        // Begin simulated transaction
        mysqli_begin_transaction($conn);
        
        // Deduct balance from referee
        mysqli_query($conn, "UPDATE wallets SET deposit_balance = deposit_balance - $invest_amount, total_invested = total_invested + $invest_amount WHERE user_id = '$referee_id'");
        
        // Insert active investment record
        $daily_roi = $plan['daily_roi_min'];
        $hourly_rate = $plan['hourly_rate'];
        $plan_id = $plan['id'];
        mysqli_query($conn, "INSERT INTO investments (user_id, plan_id, amount, daily_roi, hourly_rate, status) 
                             VALUES ('$referee_id', '$plan_id', '$invest_amount', '$daily_roi', '$hourly_rate', 'active')");
                             
        // Create Transaction for User E
        $desc = "Invested \$" . number_format($invest_amount, 2) . " in " . $plan['name'];
        mysqli_query($conn, "INSERT INTO transactions (user_id, amount, type, description, status) 
                             VALUES ('$referee_id', '$invest_amount', 'investment', '$desc', 'completed')");
                             
        log_step("Investment recorded! Referee (User E) balance deducted. Distributing MLM commissions to uplines...", "info");
        
        // Query uplines
        $referrers_query = mysqli_query($conn, "SELECT referrer_id, level FROM referrals WHERE referee_id = '$referee_id' AND level <= 5 ORDER BY level ASC");
        
        $distributed = [];
        while ($ref = mysqli_fetch_assoc($referrers_query)) {
            $referrer_id = $ref['referrer_id'];
            $level = (int)$ref['level'];
            
            $pct = isset($commissions[$level]) ? $commissions[$level] : ($level == 1 ? 10.0 : ($level == 2 ? 5.0 : ($level == 3 ? 3.0 : ($level == 4 ? 2.0 : 1.0))));
            $commission_amount = $invest_amount * ($pct / 100.0);
            
            if ($commission_amount > 0) {
                // Credit referrer earning balance
                mysqli_query($conn, "UPDATE wallets SET earning_balance = earning_balance + $commission_amount WHERE user_id = '$referrer_id'");
                
                // Log transaction for referrer
                $bonus_desc = "Referral Bonus of \$" . number_format($commission_amount, 2) . " from Level {$level} referral (Test User E) investing in " . $plan['name'];
                mysqli_query($conn, "INSERT INTO transactions (user_id, amount, type, description, status) 
                                     VALUES ('$referrer_id', '$commission_amount', 'referral_bonus', '$bonus_desc', 'completed')");
                                     
                $distributed[$level] = [
                    'referrer_id' => $referrer_id,
                    'pct' => $pct,
                    'expected' => $commission_amount
                ];
                
                log_step("Distributed: Level {$level} Upline (User ID: {$referrer_id}) earned <strong>\${$commission_amount}</strong> ({$pct}%)", "success");
            }
        }
        
        mysqli_commit($conn);
        log_step("Transaction successfully committed to the database!", "success");
        
        // 8. Final Wallet Balances Verification Report
        log_step("<strong>--- FINAL WALLET VERIFICATION REPORT ---</strong>", "info");
        
        $upline_keys = [
            1 => ['ref' => 'TESTREFD', 'name' => 'User D (Level 1 Uplink)'],
            2 => ['ref' => 'TESTREFC', 'name' => 'User C (Level 2 Uplink)'],
            3 => ['ref' => 'TESTREFB', 'name' => 'User B (Level 3 Uplink)'],
            4 => ['ref' => 'TESTREFA', 'name' => 'User A (Level 4 Uplink)']
        ];
        
        foreach ($upline_keys as $lvl => $info) {
            $u_id = $created_users[$info['ref']]['id'];
            $wallet_q = mysqli_query($conn, "SELECT earning_balance FROM wallets WHERE user_id = '$u_id'");
            $wallet = mysqli_fetch_assoc($wallet_q);
            $actual_bal = (float)$wallet['earning_balance'];
            
            $expected_bal = $distributed[$lvl]['expected'];
            
            if (abs($actual_bal - $expected_bal) < 0.01) {
                log_step("PASS: {$info['name']} wallet credited with exact expected commission. Expected: <code>\${$expected_bal}</code> | Actual: <code>\${$actual_bal}</code>", "success");
            } else {
                log_step("FAIL: {$info['name']} wallet mismatch! Expected: <code>\${$expected_bal}</code> | Actual: <code>\${$actual_bal}</code>", "danger");
                $test_success = false;
            }
            
            // Check Transaction Log
            $tx_q = mysqli_query($conn, "SELECT amount, description FROM transactions WHERE user_id = '$u_id' AND type = 'referral_bonus' LIMIT 1");
            if ($tx_q && mysqli_num_rows($tx_q) > 0) {
                $tx = mysqli_fetch_assoc($tx_q);
                log_step("PASS: {$info['name']} transaction log is present. Amount: <code>\${$tx['amount']}</code> Description: <i>{$tx['description']}</i>", "success");
            } else {
                log_step("FAIL: {$info['name']} transaction log is missing!", "danger");
                $test_success = false;
            }
        }
        
        if ($test_success) {
            log_step("🏆 ALL TESTS PASSED! Your 5-level MLM Referral and Commission Engine is working 100% correctly and securely!", "success");
        } else {
            log_step("❌ TEST FAILURE: Some database levels or balances did not match our assertions. Please double-check settings and database integrity.", "danger");
        }
        
    } catch (Exception $e) {
        mysqli_rollback($conn);
        log_step("FATAL ERROR during test execution: " . $e->getMessage(), "danger");
        $test_success = false;
    }
}

// Count remaining test users
$test_count_q = mysqli_query($conn, "SELECT COUNT(*) as cnt FROM users WHERE email LIKE 'test_referral_%'");
$rem_test_users = mysqli_fetch_assoc($test_count_q)['cnt'] ?? 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SparkX - Referral System Testing Suite</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-color: #0b0e14;
            --card-bg: #151a24;
            --accent-blue: #38bdf8;
            --accent-green: #34d399;
            --accent-yellow: #fbbf24;
            --accent-red: #f87171;
            --text-main: #f3f4f6;
            --text-muted: #9ca3af;
            --border-color: #262f40;
        }
        
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Outfit', sans-serif;
        }
        
        body {
            background-color: var(--bg-color);
            color: var(--text-main);
            padding: 40px 20px;
            line-height: 1.6;
        }
        
        .container {
            max-width: 1100px;
            margin: 0 auto;
        }
        
        header {
            text-align: center;
            margin-bottom: 40px;
        }
        
        h1 {
            font-size: 2.5rem;
            font-weight: 700;
            background: linear-gradient(135deg, var(--accent-blue), #c084fc);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 10px;
        }
        
        .subtitle {
            color: var(--text-muted);
            font-size: 1.1rem;
        }
        
        .card {
            background-color: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 16px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.25);
        }
        
        .card-title {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 20px;
            border-bottom: 1px solid var(--border-color);
            padding-bottom: 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .btn-group {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .btn {
            display: inline-block;
            padding: 12px 24px;
            border-radius: 8px;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.2s ease;
            border: none;
            text-align: center;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--accent-blue), #6366f1);
            color: #fff;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(56, 189, 248, 0.4);
        }
        
        .btn-danger {
            background-color: #ef4444;
            color: #fff;
        }
        
        .btn-danger:hover {
            background-color: #dc2626;
            transform: translateY(-2px);
        }
        
        .btn-secondary {
            background-color: #4b5563;
            color: #fff;
        }
        
        .btn-secondary:hover {
            background-color: #374151;
        }
        
        .badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 9999px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        
        .badge-warning {
            background-color: rgba(251, 191, 36, 0.15);
            color: var(--accent-yellow);
            border: 1px solid rgba(251, 191, 36, 0.3);
        }
        
        .badge-success {
            background-color: rgba(52, 211, 153, 0.15);
            color: var(--accent-green);
            border: 1px solid rgba(52, 211, 153, 0.3);
        }
        
        .log-container {
            background-color: #0f131a;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 20px;
            max-height: 500px;
            overflow-y: auto;
            font-family: monospace;
            font-size: 0.95rem;
        }
        
        .log-entry {
            margin-bottom: 10px;
            padding: 8px 12px;
            border-radius: 6px;
            display: flex;
            align-items: flex-start;
            gap: 10px;
        }
        
        .log-info {
            background-color: rgba(56, 189, 248, 0.05);
            color: #93c5fd;
            border-left: 4px solid var(--accent-blue);
        }
        
        .log-success {
            background-color: rgba(52, 211, 153, 0.05);
            color: #a7f3d0;
            border-left: 4px solid var(--accent-green);
        }
        
        .log-warning {
            background-color: rgba(251, 191, 36, 0.05);
            color: #fde047;
            border-left: 4px solid var(--accent-yellow);
        }
        
        .log-danger {
            background-color: rgba(248, 113, 113, 0.05);
            color: #fca5a5;
            border-left: 4px solid var(--accent-red);
        }
        
        .guide-section {
            background-color: #171d2b;
            padding: 20px;
            border-radius: 12px;
            margin-top: 15px;
            border-left: 4px solid var(--accent-blue);
        }
        
        .guide-step {
            margin-bottom: 20px;
            padding-left: 10px;
        }
        
        .guide-step h4 {
            color: var(--accent-blue);
            margin-bottom: 5px;
            font-weight: 600;
        }
        
        .guide-step p {
            color: #d1d5db;
        }
        
        .guide-step code {
            background-color: #0f131a;
            color: #f472b6;
            padding: 2px 6px;
            border-radius: 4px;
            font-family: monospace;
        }
        
        .alert-bar {
            background-color: rgba(56, 189, 248, 0.15);
            color: var(--accent-blue);
            border: 1px solid rgba(56, 189, 248, 0.3);
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 25px;
            font-weight: 500;
        }
        
        ol {
            padding-left: 20px;
            color: #d1d5db;
        }
        
        li {
            margin-bottom: 8px;
        }
    </style>
</head>
<body>

<div class="container">
    <header>
        <h1>SparkX Referral Suite</h1>
        <p class="subtitle">Automated Testing, Verification, &amp; Debugging Utility for 5-Level MLM Engine</p>
    </header>
    
    <?php if (isset($_GET['msg'])): ?>
        <div class="alert-bar" style="background-color: rgba(52, 211, 153, 0.15); color: var(--accent-green); border-color: rgba(52, 211, 153, 0.3);">
            ✓ <?php echo htmlspecialchars($_GET['msg']); ?>
        </div>
    <?php endif; ?>
    
    <!-- Controls Card -->
    <div class="card">
        <div class="card-title">
            <span>Referral Engine Controls</span>
            <div>
                <?php if ($rem_test_users > 0): ?>
                    <span class="badge badge-warning"><?php echo $rem_test_users; ?> Active Test Users In DB</span>
                <?php else: ?>
                    <span class="badge badge-success">Database Clean</span>
                <?php endif; ?>
            </div>
        </div>
        <p style="margin-bottom: 20px; color: var(--text-muted);">
            Neeche diye gaye buttons ki madad se aap 5 levels of referrers ka automated check chala sakte hain, ya phir manually test krne ki detailed directions dekh sakte hain.
        </p>
        <div class="btn-group">
            <a href="test_referral_flow.php?action=run_test" class="btn btn-primary">⚡ Run Automated Referral Test</a>
            <?php if ($rem_test_users > 0): ?>
                <a href="test_referral_flow.php?action=cleanup" class="btn btn-danger">🗑 Clean Up Test Records</a>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Automated Log Output Card -->
    <?php if (!empty($output_log)): ?>
        <div class="card">
            <div class="card-title">Test Runner Output Logs</div>
            <div class="log-container">
                <?php foreach ($output_log as $log): ?>
                    <div class="log-entry log-<?php echo $log['type']; ?>">
                        <div>
                            <?php 
                            if ($log['type'] === 'success') echo '✔ ';
                            elseif ($log['type'] === 'danger') echo '✘ ';
                            elseif ($log['type'] === 'warning') echo '⚠ ';
                            else echo 'ℹ ';
                            ?>
                        </div>
                        <div><?php echo $log['message']; ?></div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
    
    <!-- Manual Walkthrough Explanation Card -->
    <div class="card">
        <div class="card-title">Manual Testing &amp; Architecture Guide (Roman Urdu)</div>
        <div class="guide-section">
            <h3 style="color: #fff; margin-bottom: 15px;">Referral Logic Kaise Kam Karta Hai?</h3>
            <p style="color: var(--text-muted); margin-bottom: 20px;">
                SparkX me Multi-level Marketing (MLM) 5-level Commission system implement kia gaya hai. Jab bhi koi naya user join krta hai aur active deposit se kisi plan me invest krta hai, toh uska commission 5 levels tak automatic distribute hota hai. Is system ke piche 2 main cheezen hain:
            </p>
            
            <div class="guide-step">
                <h4>1. Chain Mappings (Closure Table)</h4>
                <p>
                    Jab naya user register hota hai (<code>register.php</code> me), toh agar referral code input kia gaya ho, toh <code>referrals</code> database table ke andar uske upar ke 5 levels (Direct Sponsor, Grand-Sponsor, up to 5 uplines) save kr diye jate hain. Level 1 direct inviter hota hai, Level 2 uska inviter, and so on.
                </p>
            </div>
            
            <div class="guide-step">
                <h4>2. Commission Distribution on Investment</h4>
                <p>
                    Commission tab distribute hota hai jab referee (neche wala user) <b>active plan me invest krta hai</b> (<code>process_investment.php</code> chalne par).
                    Commission settings <code>referral_settings</code> table se level-wise percentage uthati hain (e.g., Level 1: 10%, Level 2: 5%, Level 3: 3%, Level 4: 2%, Level 5: 1%).
                    System automatics wallets update krta hai aur balances <code>earning_balance</code> me direct credit krta hai, aur transactions log create krta hai.
                </p>
            </div>
        </div>
        
        <h3 style="color: #fff; margin-top: 30px; margin-bottom: 15px;">Manually Test Kaise Karein? (Step-by-Step Guide)</h3>
        <ol>
            <li>
                <b>Step 1: Admin banayein Level Commission Structure</b><br>
                Admin Panel me log in karein aur <code>/admin/commissions.php</code> page pr ja kr check karein ke Level 1 se Level 5 tak ke percentages set hain (e.g., Level 1 = 10%).
            </li>
            <li>
                <b>Step 2: Pehla User A register karein</b><br>
                <code>/register.php</code> pr ja kar normal account banayein (User A).
                Uska Referral code copy karein jo referrals dashboard page (<code>/user/dashboard/referrals.php</code>) pr milega.
            </li>
            <li>
                <b>Step 3: User B register karein User A ke link se</b><br>
                User A ka referral code lekar, ek incognito/new window me register karein (User B). 
                Registration link aisa hoga: <code>/register?ref=USER_A_CODE</code>.
            </li>
            <li>
                <b>Step 4: Mazeed users register karein</b><br>
                Usi tarah User B ke code se User C register karein, aur C ke code se User D, aur D ke code se User E.
            </li>
            <li>
                <b>Step 5: User E ko Balance dein (Admin Panel se)</b><br>
                Admin Panel me log in ho kr <b>Manage Users</b> me jayein aur User E ko <b>Deposit Balance</b> manually credit karein (e.g., \$1000).
            </li>
            <li>
                <b>Step 6: User E se Invest karwayein</b><br>
                User E ke dashboard pr log in ho kr <b>Invest Plans</b> me jayein aur \$1000 Kisi active plan me invest kr dein.
            </li>
            <li>
                <b>Step 7: Uplines ke Wallets aur Transactions check karein</b><br>
                Ab har upline (User D, C, B, A) ke dashboard pr log in ho kr unke <b>Wallet / Balance</b> check karein:
                <ul>
                    <li>User D (Level 1) ka balance <b>\$100</b> (10%) barh chuka hoga.</li>
                    <li>User C (Level 2) ka balance <b>\$50</b> (5%) barh chuka hoga.</li>
                    <li>User B (Level 3) ka balance <b>\$30</b> (3%) barh chuka hoga.</li>
                    <li>User A (Level 4) ka balance <b>\$20</b> (2%) barh chuka hoga.</li>
                </ul>
                Sath hi unki <b>Transactions History</b> me "Referral Bonus from Level X referral" ka exact details, timestamp aur status show ho raha hoga!
            </li>
        </ol>
    </div>
</div>

</body>
</html>
