<?php
// ipn_nowpayments.php - NOWPayments Automatic Webhook IPN Listener
require_once 'config/config.php';
require_once 'gateways/NowPaymentsGateway.php';

http_response_code(200); // Always respond 200 immediately to prevent gateway retry loop

$rawBody = file_get_contents('php://input');
$signature = $_SERVER['HTTP_X_NOWPAYMENTS_SIG'] ?? ($_SERVER['HTTP_HTTP_X_NOWPAYMENTS_SIG'] ?? '');
$payload = json_decode($rawBody ?: '', true);
$identifier = trim((string)($payload['order_id'] ?? ''));
$status = (string)($payload['payment_status'] ?? '');

if ($identifier === '') {
    exit('OK');
}

try {
    $pdo->beginTransaction();

    // Lock payment row to prevent race-condition/double-spend
    $stmt = $pdo->prepare("SELECT * FROM payments WHERE identifier = ? FOR UPDATE");
    $stmt->execute([$identifier]);
    $payment = $stmt->fetch();

    if (!$payment || $payment['status'] === 'completed') {
        $pdo->commit();
        exit('OK');
    }

    // Dynamic credentials fetch from payment_gateways table
    $stmt = $pdo->prepare("SELECT * FROM payment_gateways WHERE slug = ?");
    $stmt->execute([$payment['gateway_slug']]);
    $runtime = $stmt->fetch();

    if (!$runtime) {
        $pdo->commit();
        exit('OK');
    }

    $is_sandbox = (get_setting('gateway_env_nowpayments', 'live') === 'sandbox');
    $options = [];
    if ($is_sandbox) {
        $options['base_url'] = 'https://api.sandbox.nowpayments.io/v1';
    }
    $gateway = new NowPaymentsGateway(
        $runtime['api_key'],
        $runtime['api_secret'],
        $options
    );

    $isValid = $gateway->validatePayment([
        '_raw_body' => $rawBody,
        '_signature' => $signature,
    ]);

    if (!$isValid || !in_array($status, ['finished', 'confirmed'], true)) {
        $pdo->commit();
        exit('OK');
    }

    $trx_remote_id = $payload['payment_id'] ?? $identifier;

    // 1. Update Payments Tracker
    $pdo->prepare("UPDATE payments SET status = 'completed', transaction_id = ?, gateway_message = ?, gateway_response = ?, credited_at = NOW(), updated_at = NOW() WHERE id = ?")
        ->execute([
            $trx_remote_id,
            'NOWPayments IPN verified. Status: ' . $status,
            json_encode($payload),
            $payment['id']
        ]);

    $amount = (float)$payment['amount'];
    $user_id = $payment['user_id'];

    // 2. Credit Wallets
    $pdo->prepare("UPDATE wallets SET deposit_balance = deposit_balance + ? WHERE user_id = ?")
        ->execute([$amount, $user_id]);

    // 3. Insert Deposits Row
    $method_name = 'NOWPayments (USDT BEP20)';
    $pdo->prepare("INSERT INTO deposits (user_id, amount, method, status, proof_image) VALUES (?, ?, ?, 'approved', ?)")
        ->execute([$user_id, $amount, $method_name, $trx_remote_id]);
    $deposit_id = $pdo->lastInsertId();

    // 4. Record Transaction Ledger
    $desc = "Deposited via " . $method_name . " (Trx: " . $trx_remote_id . ")";
    $pdo->prepare("INSERT INTO transactions (user_id, type, amount, status, description, reference_id) VALUES (?, 'deposit', ?, 'completed', ?, ?)")
        ->execute([$user_id, $amount, $desc, $deposit_id]);

    $pdo->commit();
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
}

echo 'OK';
