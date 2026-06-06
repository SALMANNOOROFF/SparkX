<?php 
    require_once '../../includes/auth_check.php';
    $title = "Sparkx - Confirm Withdrawal";
    $base_url = "../..";

    // 1. Get and validate parameters
    $method_id = isset($_GET['method_id']) ? (int)$_GET['method_id'] : 0;
    $amount = isset($_GET['amount']) ? (float)$_GET['amount'] : 0.0;

    // Fetch gateway details
    $gateway_query = mysqli_query($conn, "SELECT * FROM payment_gateways WHERE id = '$method_id' AND is_active = 1");
    $gateway = mysqli_fetch_assoc($gateway_query);

    if (!$gateway || $amount <= 0) {
        header("Location: withdraw.php");
        exit();
    }

    $conversion_rate = 280; // Standard USD to PKR rate
    $pkr_amount = $amount * $conversion_rate;

    $success = false;
    $error = '';

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $account_title = trim($_POST['account_title'] ?? '');
        $account_number = trim($_POST['account_number'] ?? '');
        
        if (empty($account_title) || empty($account_number)) {
            $error = 'Please fill in all account details.';
        } elseif ($user_data['earning_balance'] < $amount) {
            $error = 'Insufficient withdrawal balance.';
        } else {
            // Check if manual approval is required
            $set_query = mysqli_query($conn, "SELECT value FROM settings WHERE name = 'approval_required_withdrawal'");
            $set_res = mysqli_fetch_assoc($set_query);
            $approval_required = $set_res ? $set_res['value'] : '1';

            // Calculate fees (if any gateway setup, otherwise standard 3%)
            $fee_pct = 3.0; // standard fallback
            $fee_query = mysqli_query($conn, "SELECT value FROM settings WHERE name = 'withdrawal_fee_pct'");
            if ($fee_query && $fee_res = mysqli_fetch_assoc($fee_query)) {
                $fee_pct = (float)$fee_res['value'];
            }
            $fee_amount = $amount * ($fee_pct / 100.0);
            $net_amount = $amount - $fee_amount;

            // Start db transaction for safety
            mysqli_begin_transaction($conn);
            try {
                // Deduct balance from user immediately (Locking capital)
                $update_wallet = mysqli_query($conn, "UPDATE wallets SET earning_balance = earning_balance - $amount, total_withdrawn = total_withdrawn + $amount WHERE user_id = '$user_id'");

                $method_name = mysqli_real_escape_string($conn, $gateway['name']);
                $title_escaped = mysqli_real_escape_string($conn, $account_title);
                $num_escaped = mysqli_real_escape_string($conn, $account_number);
                
                if ($approval_required === '1') {
                    // Manual approval: status pending
                    $insert_withdraw = mysqli_query($conn, "INSERT INTO withdrawals (user_id, amount, net_amount, method, account_title, account_number, status) 
                                                          VALUES ('$user_id', '$amount', '$net_amount', '$method_name', '$title_escaped', '$num_escaped', 'pending')");
                    $withdraw_id = mysqli_insert_id($conn);

                    // Insert transaction record as pending
                    $desc = "Withdrawal of $" . number_format($amount, 2) . " via " . $method_name . " (A/C: " . $num_escaped . ") - Pending Approval";
                    $desc_escaped = mysqli_real_escape_string($conn, $desc);
                    $insert_trx = mysqli_query($conn, "INSERT INTO transactions (user_id, type, amount, status, description, reference_id) 
                                                       VALUES ('$user_id', 'withdrawal', '$amount', 'pending', '$desc_escaped', '$withdraw_id')");
                    
                    $is_pending = true;
                } else {
                    // Auto approve: status approved
                    $insert_withdraw = mysqli_query($conn, "INSERT INTO withdrawals (user_id, amount, net_amount, method, account_title, account_number, status) 
                                                          VALUES ('$user_id', '$amount', '$net_amount', '$method_name', '$title_escaped', '$num_escaped', 'approved')");
                    $withdraw_id = mysqli_insert_id($conn);

                    // Insert transaction record as completed
                    $desc = "Withdrawal of $" . number_format($amount, 2) . " via " . $method_name . " (A/C: " . $num_escaped . ")";
                    $desc_escaped = mysqli_real_escape_string($conn, $desc);
                    $insert_trx = mysqli_query($conn, "INSERT INTO transactions (user_id, type, amount, status, description, reference_id) 
                                                       VALUES ('$user_id', 'withdrawal', '$amount', 'completed', '$desc_escaped', '$withdraw_id')");
                    
                    $is_pending = false;
                }

                if ($update_wallet && $insert_withdraw && $insert_trx) {
                    mysqli_commit($conn);
                    $success = true;
                } else {
                    throw new Exception("Database update failed.");
                }
            } catch (Exception $e) {
                mysqli_rollback($conn);
                $error = 'Failed to process withdrawal: ' . $e->getMessage();
            }
        }
    }

    include('../../components/layout_top.php'); 
?>

<style>
.checkout-wrapper {
    display: flex;
    justify-content: center;
    align-items: center;
    padding: 40px 20px;
    min-height: 80vh;
}
.checkout-container {
    width: 100%;
    max-width: 580px;
    background: rgba(30, 41, 59, 0.6);
    backdrop-filter: blur(16px);
    border: 1px solid rgba(255, 255, 255, 0.08);
    border-radius: 24px;
    padding: 35px;
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.35);
    color: #fff;
    font-family: 'Outfit', sans-serif;
}
.checkout-header {
    text-align: center;
    margin-bottom: 25px;
}
.checkout-title {
    font-size: 1.75rem;
    font-weight: 700;
    background: linear-gradient(135deg, #fb923c, #ea580c);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}
.checkout-subtitle {
    color: rgba(255, 255, 255, 0.5);
    font-size: 0.9rem;
    margin-top: 6px;
}
.order-summary-card {
    background: rgba(15, 23, 42, 0.5);
    border: 1px solid rgba(255, 255, 255, 0.05);
    border-radius: 16px;
    padding: 20px;
    margin-bottom: 25px;
}
.summary-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px 0;
    border-bottom: 1px solid rgba(255, 255, 255, 0.05);
}
.summary-row:last-child {
    border-bottom: none;
}
.summary-label {
    color: rgba(255, 255, 255, 0.4);
    font-size: 0.9rem;
}
.summary-value {
    font-weight: 600;
    color: #f8fafc;
}
.summary-total {
    font-size: 1.3rem;
    color: #ef4444;
}
.method-badge {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: rgba(234, 88, 12, 0.15);
    color: #ea580c;
    padding: 5px 12px;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: 500;
}
.gateway-logo {
    max-height: 20px;
    border-radius: 4px;
}
.checkout-form {
    display: flex;
    flex-direction: column;
    gap: 20px;
}
.form-input-group {
    display: flex;
    flex-direction: column;
    gap: 8px;
}
.form-label {
    color: rgba(255, 255, 255, 0.7);
    font-weight: 500;
    font-size: 0.9rem;
}
.form-control {
    background: rgba(15, 23, 42, 0.7);
    border: 1px solid rgba(255, 255, 255, 0.1);
    color: #fff;
    border-radius: 12px;
    padding: 14px 16px;
    font-size: 0.95rem;
    transition: all 0.3s;
}
.form-control:focus {
    border-color: #ea580c;
    box-shadow: 0 0 0 3px rgba(234, 88, 12, 0.2);
    outline: none;
}
.btn-submit {
    background: linear-gradient(135deg, #fb923c, #ea580c);
    color: #fff;
    border: none;
    padding: 16px;
    font-weight: 600;
    font-size: 1rem;
    border-radius: 12px;
    cursor: pointer;
    transition: all 0.3s;
    text-align: center;
    box-shadow: 0 4px 12px rgba(234, 88, 12, 0.2);
}
.btn-submit:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(234, 88, 12, 0.4);
}
.success-screen {
    text-align: center;
    padding: 10px 0;
}
.success-icon-wrapper {
    display: inline-flex;
    justify-content: center;
    align-items: center;
    width: 90px;
    height: 90px;
    border-radius: 50%;
    font-size: 2.75rem;
    margin-bottom: 25px;
    animation: scaleIn 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275);
}
@keyframes scaleIn {
    0% { transform: scale(0); opacity: 0; }
    70% { transform: scale(1.1); }
    100% { transform: scale(1); opacity: 1; }
}
.success-title {
    font-size: 1.8rem;
    font-weight: 700;
    margin-bottom: 12px;
}
.success-desc {
    color: rgba(255, 255, 255, 0.65);
    font-size: 0.95rem;
    margin-bottom: 30px;
    line-height: 1.6;
}
.btn-dashboard {
    display: inline-block;
    background: rgba(255, 255, 255, 0.08);
    color: #fff;
    border: 1px solid rgba(255, 255, 255, 0.12);
    padding: 14px 28px;
    border-radius: 12px;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.3s;
}
.btn-dashboard:hover {
    background: rgba(255, 255, 255, 0.15);
    border-color: rgba(255, 255, 255, 0.25);
    color: #fff;
}
.alert-danger-custom {
    background: rgba(239, 68, 68, 0.15);
    border: 1px solid rgba(239, 68, 68, 0.25);
    color: #ef4444;
    padding: 12px 16px;
    border-radius: 12px;
    margin-bottom: 20px;
    font-size: 0.9rem;
}
</style>

<div class="checkout-wrapper">
    <div class="checkout-container">
        <?php if ($success): ?>
            <!-- Premium Success State Visual -->
            <div class="success-screen">
                <?php if ($is_pending): ?>
                    <div class="success-icon-wrapper" style="border: 2px solid #f59e0b; color: #f59e0b; background: rgba(245, 158, 11, 0.1);">
                        <i class="fas fa-clock"></i>
                    </div>
                    <h2 class="success-title" style="color: #f59e0b;">Withdrawal Requested!</h2>
                    <p class="success-desc">
                        Your payout request has been successfully submitted.<br>
                        <strong>$<?php echo number_format($amount, 2); ?> (Rs <?php echo number_format($pkr_amount, 2); ?>)</strong> is held and queued for administrator manual review.
                    </p>
                <?php else: ?>
                    <div class="success-icon-wrapper" style="border: 2px solid #10b981; color: #10b981; background: rgba(16, 185, 129, 0.1);">
                        <i class="fas fa-check"></i>
                    </div>
                    <h2 class="success-title" style="color: #10b981;">Withdrawal Completed!</h2>
                    <p class="success-desc">
                        Your withdrawal has been successfully processed.<br>
                        <strong>$<?php echo number_format($amount, 2); ?> (Rs <?php echo number_format($pkr_amount, 2); ?>)</strong> has been paid out instantly.
                    </p>
                <?php endif; ?>
                <div class="d-flex flex-column gap-2 justify-content-center">
                    <a href="index.php" class="btn-dashboard">Go to Dashboard</a>
                    <a href="withdraw.php" class="btn-dashboard" style="background: transparent; border: none; font-size: 0.9rem; color: rgba(255,255,255,0.5);">Withdraw Again</a>
                </div>
            </div>
        <?php else: ?>
            <!-- Order Summary and Pay Form -->
            <div class="checkout-header">
                <h2 class="checkout-title">Confirm Your Payout</h2>
                <p class="checkout-subtitle">Enter your receiving bank or wallet credentials to initiate payout</p>
            </div>

            <?php if (!empty($error)): ?>
                <div class="alert-danger-custom">
                    <i class="fas fa-exclamation-circle me-2"></i><?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <div class="order-summary-card">
                <div class="summary-row">
                    <span class="summary-label">Gateway</span>
                    <span class="summary-value">
                        <span class="method-badge">
                            <?php if (!empty($gateway['image'])): ?>
                                <img src="../../<?php echo htmlspecialchars($gateway['image']); ?>" alt="logo" class="gateway-logo">
                            <?php endif; ?>
                            <?php echo htmlspecialchars($gateway['name']); ?>
                        </span>
                    </span>
                </div>
                <div class="summary-row">
                    <span class="summary-label">Amount in USD</span>
                    <span class="summary-value">$<?php echo number_format($amount, 2); ?></span>
                </div>
                <div class="summary-row">
                    <span class="summary-label">Conversion Rate</span>
                    <span class="summary-value">1 USD = 280 PKR</span>
                </div>
                <div class="summary-row">
                    <span class="summary-label">Receivable in PKR</span>
                    <span class="summary-value summary-total"><?php echo number_format($pkr_amount, 2); ?> PKR</span>
                </div>
            </div>

            <form class="checkout-form" method="POST">
                <div class="form-input-group">
                    <label class="form-label" for="account_title">Account Title</label>
                    <input class="form-control" type="text" id="account_title" name="account_title" placeholder="e.g. John Doe" required autocomplete="off">
                </div>
                <div class="form-input-group">
                    <label class="form-label" for="account_number">Account Number / Wallet Address / IBAN</label>
                    <input class="form-control" type="text" id="account_number" name="account_number" placeholder="e.g. 03001234567 or BNB Address" required autocomplete="off">
                </div>
                <button type="submit" class="btn-submit">
                    <i class="fas fa-paper-plane me-2"></i>Confirm & Submit Payout
                </button>
            </form>
        <?php endif; ?>
    </div>
</div>

<?php include('../../components/layout_bottom.php'); ?>
