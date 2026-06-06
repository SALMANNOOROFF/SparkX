<?php 
    require_once '../../includes/auth_check.php';
    $title = "Sparkx - Complete Deposit";
    $base_url = "../..";

    // 1. Get and validate parameters
    $method_id = isset($_GET['method_id']) ? (int)$_GET['method_id'] : 0;
    $amount = isset($_GET['amount']) ? (float)$_GET['amount'] : 0.0;

    // Fetch gateway details
    $gateway_query = mysqli_query($conn, "SELECT * FROM payment_gateways WHERE id = '$method_id'");
    $gateway = mysqli_fetch_assoc($gateway_query);

    if (!$gateway || $gateway['is_active'] == 0 || $amount < $gateway['min_deposit'] || $amount > $gateway['max_deposit']) {
        header("Location: deposit.php");
        exit();
    }

    $conversion_rate = 280; // Standard USD to PKR rate
    $pkr_amount = $amount * $conversion_rate;

    $success = false;
    $error = '';
    
    $is_automatic = ($gateway['type'] === 'automatic');
    $show_crypto_checkout = false;
    $crypto_address = '';
    $crypto_amount = 0.0;
    $crypto_currency = 'USDT (BSC)';
    $crypto_qr = '';
    $checkout_identifier = '';

    if ($is_automatic) {
        $prefix = ($gateway['slug'] === 'nowpayments_bep20') ? 'TCNP' : 'TCPB';
        $checkout_identifier = $prefix . time() . strtoupper(bin2hex(random_bytes(3)));
        $charges = 0.00;
        $request_time = date('Y-m-d H:i:s');

        // Create transaction block to insert payments record
        mysqli_begin_transaction($conn);
        try {
            $stmt = mysqli_prepare($conn, "INSERT INTO payments (user_id, order_id, identifier, gateway_slug, amount, currency, charges, status, created_at, updated_at) VALUES (?, ?, ?, ?, ?, 'USD', ?, 'pending', ?, ?)");
            mysqli_stmt_bind_param($stmt, "isssddss", $user_id, $checkout_identifier, $checkout_identifier, $gateway['slug'], $amount, $charges, $request_time, $request_time);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
            mysqli_commit($conn);
        } catch (Exception $e) {
            mysqli_rollback($conn);
            $error = 'Failed to generate checkout session.';
        }

        if (empty($error)) {
            // Dynamic IPN Callback base URLs
            $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https" : "http";
            $domain = $_SERVER['HTTP_HOST'];
            $base_url_ipn = "$protocol://$domain/sparkx1";

            // If PayBost automatic PKR wallets
            if ($gateway['slug'] === 'easypaisa_payboost' || $gateway['slug'] === 'jazzcash_payboost') {
                require_once '../../gateways/PayBostGateway.php';
                $is_sandbox = (get_setting('gateway_env_paybost', 'live') === 'sandbox');
                $gateway_obj = new PayBostGateway(
                    $gateway['api_key'],
                    $gateway['api_secret'],
                    $is_sandbox
                );

                $user_q = mysqli_query($conn, "SELECT name, email, phone FROM users WHERE id = '$user_id'");
                $user_data = mysqli_fetch_assoc($user_q);
                $customer_name = $user_data['name'] ?? 'User';
                $customer_email = $user_data['email'] ?? ($user_data['phone'] . '@sparkx.com');
                $payment_type = ($gateway['slug'] === 'easypaisa_payboost') ? 'easypaisa' : 'jazzcash';

                $paybost_data = [
                    'identifier' => $checkout_identifier,
                    'currency' => 'PKR',
                    'amount' => $pkr_amount,
                    'details' => 'Sparkx Wallet Deposit',
                    'ipn_url' => $base_url_ipn . '/ipn_paybost.php',
                    'cancel_url' => $base_url_ipn . '/user/dashboard/deposit.php',
                    'success_url' => $base_url_ipn . '/user/dashboard/index.php',
                    'customer_name' => $customer_name,
                    'customer_email' => $customer_email,
                    'payment_type' => $payment_type,
                    'merchant' => 'Sparkx',
                    'checkout_theme' => 'dark'
                ];

                $response = $gateway_obj->initiatePayment($paybost_data);

                if (isset($response['success']) && $response['success'] === 'ok' && !empty($response['url'])) {
                    // Update payments record with generated checkout URL
                    $payment_url = $response['url'];
                    $stmt = mysqli_prepare($conn, "UPDATE payments SET payment_url = ? WHERE identifier = ?");
                    mysqli_stmt_bind_param($stmt, "ss", $payment_url, $checkout_identifier);
                    mysqli_stmt_execute($stmt);
                    mysqli_stmt_close($stmt);

                    header("Location: " . $payment_url);
                    exit();
                } else {
                    $error = ($response['message'] ?? 'Unable to connect to PayBost checkout server.') . ' [Raw: ' . json_encode($response) . ']';
                }
            }
            // If NOWPayments automatic crypto wallets
            elseif ($gateway['slug'] === 'nowpayments_bep20') {
                require_once '../../gateways/NowPaymentsGateway.php';
                $is_sandbox = (get_setting('gateway_env_nowpayments', 'live') === 'sandbox');
                $options = [];
                if ($is_sandbox) {
                    $options['base_url'] = 'https://api.sandbox.nowpayments.io/v1';
                }
                $gateway_obj = new NowPaymentsGateway(
                    $gateway['api_key'],
                    $gateway['api_secret'],
                    $options
                );

                $nowpayments_data = [
                    'identifier' => $checkout_identifier,
                    'currency' => 'usd',
                    'pay_currency' => 'usdtbsc',
                    'amount' => $amount,
                    'ipn_url' => $base_url_ipn . '/ipn_nowpayments.php',
                    'cancel_url' => $base_url_ipn . '/user/dashboard/deposit.php',
                    'success_url' => $base_url_ipn . '/user/dashboard/index.php',
                ];

                $response = $gateway_obj->initiatePayment($nowpayments_data);

                if (isset($response['pay_address']) && isset($response['pay_amount'])) {
                    $pay_address = $response['pay_address'];
                    $pay_amount = $response['pay_amount'];
                    $invoice_url = $response['invoice_url'] ?? '';
                    $raw_resp_str = json_encode($response);
                    $metadata_str = json_encode(['pay_amount' => $pay_amount]);

                    $stmt = mysqli_prepare($conn, "UPDATE payments SET payment_url = ?, gateway_message = ?, gateway_response = ?, metadata = ? WHERE identifier = ?");
                    mysqli_stmt_bind_param($stmt, "sssss", $invoice_url, $pay_address, $raw_resp_str, $metadata_str, $checkout_identifier);
                    mysqli_stmt_execute($stmt);
                    mysqli_stmt_close($stmt);

                    $show_crypto_checkout = true;
                    $crypto_address = $pay_address;
                    $crypto_amount = $pay_amount;
                    $crypto_qr = 'https://api.qrserver.com/v1/create-qr-code/?size=220x220&margin=10&data=' . urlencode($pay_address);
                } else {
                    $error = $response['message'] ?? 'Unable to connect to NOWPayments crypto checkout server.';
                }
            }
        }
    } else {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $trx_id = trim($_POST['trx_id'] ?? '');
            
            if (empty($trx_id)) {
                $error = 'Please enter a valid Transaction ID.';
            } else {
                // Check if approval is required
                $set_query = mysqli_query($conn, "SELECT value FROM settings WHERE name = 'approval_required_deposit'");
                $set_res = mysqli_fetch_assoc($set_query);
                $approval_required = $set_res ? $set_res['value'] : '1';

                // Start db transaction for ultimate integrity
                mysqli_begin_transaction($conn);
                try {
                    $method_name = mysqli_real_escape_string($conn, $gateway['name']);
                    $trx_id_escaped = mysqli_real_escape_string($conn, $trx_id);
                    
                    if ($approval_required === '1') {
                        // Manual admin approval required: insert as pending and do NOT update wallet yet
                        $insert_deposit = mysqli_query($conn, "INSERT INTO deposits (user_id, amount, method, status, proof_image) VALUES ('$user_id', '$amount', '$method_name', 'pending', '$trx_id_escaped')");
                        
                        // Insert transaction record as pending
                        $desc = "Deposit via " . $method_name . " (Trx: " . $trx_id_escaped . ") - Pending Approval";
                        $desc_escaped = mysqli_real_escape_string($conn, $desc);
                        $insert_trx = mysqli_query($conn, "INSERT INTO transactions (user_id, type, amount, status, description) VALUES ('$user_id', 'deposit', '$amount', 'pending', '$desc_escaped')");
                        
                        $update_wallet = true; // No wallet update for pending deposits
                    } else {
                        // Auto approve: Update wallet deposit balance immediately
                        $update_wallet = mysqli_query($conn, "UPDATE wallets SET deposit_balance = deposit_balance + $amount WHERE user_id = '$user_id'");
                        
                        // Insert into deposits table as approved
                        $insert_deposit = mysqli_query($conn, "INSERT INTO deposits (user_id, amount, method, status, proof_image) VALUES ('$user_id', '$amount', '$method_name', 'approved', '$trx_id_escaped')");
                        
                        // Insert transaction record as completed
                        $desc = "Deposited via " . $method_name . " (Trx: " . $trx_id_escaped . ")";
                        $desc_escaped = mysqli_real_escape_string($conn, $desc);
                        $insert_trx = mysqli_query($conn, "INSERT INTO transactions (user_id, type, amount, status, description) VALUES ('$user_id', 'deposit', '$amount', 'completed', '$desc_escaped')");
                    }
                    
                    if ($update_wallet && $insert_deposit && $insert_trx) {
                        mysqli_commit($conn);
                        $success = true;
                        $is_pending = ($approval_required === '1');
                    } else {
                        throw new Exception("Database update failed.");
                    }
                } catch (Exception $e) {
                    mysqli_rollback($conn);
                    $error = 'Failed to process deposit. Please try again.';
                }
            }
        }
    }

    $meta_tags = '<meta name="conversion-rate" content="280">';
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
    color: #10b981;
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
    background: rgba(16, 185, 129, 0.1);
    border: 2px solid #10b981;
    border-radius: 50%;
    color: #10b981;
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
    color: #10b981;
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
                <?php if (isset($is_pending) && $is_pending): ?>
                    <div class="success-icon-wrapper" style="border-color: #f59e0b; color: #f59e0b; background: rgba(245, 158, 11, 0.1);">
                        <i class="fas fa-clock"></i>
                    </div>
                    <h2 class="success-title" style="color: #f59e0b;">Deposit Submitted!</h2>
                    <p class="success-desc">
                        Your deposit request has been submitted for verification.<br>
                        <strong>$<?php echo number_format($amount, 2); ?></strong> will be credited to your deposit wallet upon manual admin review.<br>
                        Reference Transaction ID: <code><?php echo htmlspecialchars($trx_id); ?></code>
                    </p>
                <?php else: ?>
                    <div class="success-icon-wrapper">
                        <i class="fas fa-check"></i>
                    </div>
                    <h2 class="success-title">Deposit Successful!</h2>
                    <p class="success-desc">
                        Your simulated payment has been approved instantly.<br>
                        <strong>$<?php echo number_format($amount, 2); ?></strong> has been credited to your deposit wallet.
                    </p>
                <?php endif; ?>
                <div class="d-flex flex-column gap-2 justify-content-center">
                    <a href="index.php" class="btn-dashboard">Go to Dashboard</a>
                    <a href="deposit.php" class="btn-dashboard" style="background: transparent; border: none; font-size: 0.9rem; color: rgba(255,255,255,0.5);">Deposit Again</a>
                </div>
            </div>
        <?php elseif ($show_crypto_checkout): ?>
            <!-- Premium Crypto Checkout Visual -->
            <div class="success-screen crypto-checkout-screen">
                <div class="checkout-header mb-4">
                    <h2 class="checkout-title" style="color: #ea580c;"><i class="fab fa-ethereum me-2"></i>Pay with USDT BEP20</h2>
                    <p class="checkout-subtitle">Send exactly the specified USDT to the address below via Binance Smart Chain (BSC/BEP20)</p>
                </div>

                <div class="d-flex justify-content-center mb-4">
                    <div class="qr-container p-2" style="background: white; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.3);">
                        <img src="<?php echo $crypto_qr; ?>" alt="QR Code" style="display: block; width: 180px; height: 180px;">
                    </div>
                </div>

                <div class="crypto-details-card text-start mb-4" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08); border-radius: 12px; padding: 20px;">
                    <div class="mb-3">
                        <label class="form-label small text-muted text-uppercase mb-1" style="color: rgba(255,255,255,0.5) !important;">Payable Amount</label>
                        <div class="d-flex align-items-center justify-content-between">
                            <span class="fs-4 fw-bold text-white"><?php echo htmlspecialchars($crypto_amount); ?> <span style="color: #ea580c;">USDT</span></span>
                            <button class="btn btn-sm btn-outline-light copy-btn" data-clipboard="<?php echo htmlspecialchars($crypto_amount); ?>" style="border-color: rgba(255,255,255,0.2);">
                                <i class="far fa-copy me-1"></i>Copy
                            </button>
                        </div>
                    </div>
                    <div class="border-top border-secondary my-3" style="opacity: 0.15;"></div>
                    <div>
                        <label class="form-label small text-muted text-uppercase mb-1" style="color: rgba(255,255,255,0.5) !important;">Destination Address (BSC / BEP-20)</label>
                        <div class="d-flex align-items-center justify-content-between gap-3">
                            <code class="text-break text-light-accent font-monospace" style="font-size: 0.85rem; color: #a5b4fc; word-break: break-all;"><?php echo htmlspecialchars($crypto_address); ?></code>
                            <button class="btn btn-sm btn-outline-light copy-btn flex-shrink-0" data-clipboard="<?php echo htmlspecialchars($crypto_address); ?>" style="border-color: rgba(255,255,255,0.2);">
                                <i class="far fa-copy me-1"></i>Copy
                            </button>
                        </div>
                    </div>
                </div>

                <div class="alert-info-custom d-flex align-items-center gap-3 mb-4" style="background: rgba(234, 88, 12, 0.08); border: 1.5px dashed rgba(234, 88, 12, 0.4); border-radius: 10px; padding: 15px; text-align: left;">
                    <div class="spinner-border spinner-border-sm text-info flex-shrink-0" role="status" style="color: #ea580c; width: 1rem; height: 1rem;"></div>
                    <span class="small text-muted" style="color: rgba(255,255,255,0.7) !important;">
                        <strong>Awaiting blockchain confirmation...</strong> Do not close this page. The system is scanning the network. Your deposit will credit instantly once confirmed.
                    </span>
                </div>

                <div class="d-flex flex-column gap-2 justify-content-center">
                    <a href="index.php" class="btn-dashboard" style="background: #ea580c; border-color: #ea580c; color: white;">Back to Dashboard</a>
                    <a href="deposit.php" class="btn-dashboard" style="background: transparent; border: none; font-size: 0.9rem; color: rgba(255,255,255,0.5);">Cancel Transaction</a>
                </div>
            </div>
        <?php else: ?>
            <!-- Order Summary and Pay Form -->
            <div class="checkout-header">
                <h2 class="checkout-title">Complete Your Deposit</h2>
                <p class="checkout-subtitle">Please enter your payment reference to credit your wallet instantly</p>
            </div>

            <?php if (!empty($error)): ?>
                <div class="alert-danger-custom">
                    <i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?>
                </div>
            <?php endif; ?>

            <div class="order-summary-card">
                <div class="summary-row">
                    <span class="summary-label">Payment Method</span>
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
                    <span class="summary-label">Total Payable in PKR</span>
                    <span class="summary-value summary-total"><?php echo number_format($pkr_amount, 2); ?> PKR</span>
                </div>
            </div>

            <form class="checkout-form" method="POST">
                <div class="form-input-group">
                    <label class="form-label" for="trx_id">Transaction ID / Reference Number</label>
                    <input class="form-control" type="text" id="trx_id" name="trx_id" placeholder="e.g. TRX182937128" required autocomplete="off">
                    <small class="text-muted" style="color: rgba(255,255,255,0.4) !important; font-size: 0.8rem;">
                        Type any simulated Transaction ID (e.g. <b>TRX789456</b>) to credit your wallet instantly.
                    </small>
                </div>
                <button type="submit" class="btn-submit">
                    <i class="fas fa-bolt me-2"></i>Pay & Confirm Instantly
                </button>
            </form>
        <?php endif; ?>
    </div>
</div>

<?php if ($show_crypto_checkout): ?>
<script>
    // Copy to clipboard functionality
    document.querySelectorAll('.copy-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const text = this.getAttribute('data-clipboard');
            navigator.clipboard.writeText(text).then(() => {
                const originalHtml = this.innerHTML;
                this.innerHTML = '<i class="fas fa-check me-1"></i>Copied!';
                setTimeout(() => {
                    this.innerHTML = originalHtml;
                }, 2000);
            });
        });
    });

    // Dynamic payment verification polling
    const identifier = '<?php echo $checkout_identifier; ?>';
    const interval = setInterval(() => {
        fetch('check_payment_status.php?identifier=' + encodeURIComponent(identifier))
            .then(res => res.json())
            .then(data => {
                if (data.status === 'completed') {
                    clearInterval(interval);
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            title: 'Payment Confirmed!',
                            text: 'Your blockchain transaction has been successfully verified. $<?php echo number_format($amount, 2); ?> has been credited to your wallet.',
                            icon: 'success',
                            confirmButtonText: 'Great!',
                            confirmButtonColor: '#ea580c',
                            background: '#1e1b4b',
                            color: '#fff'
                        }).then(() => {
                            window.location.href = 'index.php';
                        });
                    } else {
                        alert('Payment Confirmed! Redirecting...');
                        window.location.href = 'index.php';
                    }
                }
            })
            .catch(err => console.error('Verification tracking failed', err));
    }, 5000);
</script>
<?php endif; ?>

<?php include('../../components/layout_bottom.php'); ?>
