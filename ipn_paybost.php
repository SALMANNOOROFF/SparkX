<?php
// ipn_paybost.php - PayBost Automatic Webhook IPN Listener
require_once 'config/config.php';
require_once 'gateways/PayBostGateway.php';

http_response_code(200); // Always respond 200 immediately to prevent gateway retry loop

$payload = $_REQUEST;
$status = (string)($payload['status'] ?? '');
$identifier = trim((string)($payload['identifier'] ?? ''));
$signature = strtoupper((string)($payload['signature'] ?? ''));
$data = is_array($payload['data'] ?? null) ? $payload['data'] : [];

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

    $is_sandbox = (get_setting('gateway_env_paybost', 'live') === 'sandbox');
    $gateway = new PayBostGateway(
        $runtime['api_key'],
        $runtime['api_secret'],
        $is_sandbox
    );

    $receivedAmount = $data['amount'] ?? '';
    $possibleSignatures = $gateway->possibleSignatures($identifier, $receivedAmount);
    $matchedAmountFormat = null;
    $expectedSignature = '';

    foreach ($possibleSignatures as $amountFormat => $candidateSignature) {
        if (hash_equals($candidateSignature, $signature)) {
            $matchedAmountFormat = $amountFormat;
            $expectedSignature = $candidateSignature;
            break;
        }
    }

    $isValid = ($status === 'success' && $matchedAmountFormat !== null);

    if (!$isValid) {
        $pdo->commit();
        exit('OK');
    }

    // Reconstruct payment method label
    $method_name = 'PayBost';
    if (strpos($payment['gateway_slug'], 'easypaisa') !== false) {
        $method_name = 'Easypaisa (PayBost)';
    } elseif (strpos($payment['gateway_slug'], 'jazzcash') !== false) {
        $method_name = 'JazzCash (PayBost)';
    }

    $trx_remote_id = $data['transaction_id'] ?? $identifier;

    // 1. Update Payments Tracker
    $pdo->prepare("UPDATE payments SET status = 'completed', transaction_id = ?, gateway_message = ?, gateway_response = ?, credited_at = NOW(), updated_at = NOW() WHERE id = ?")
        ->execute([
            $trx_remote_id,
            'PayBost IPN verified using amount format ' . $matchedAmountFormat . '.',
            json_encode($payload),
            $payment['id']
        ]);

    $amount = (float)$payment['amount'];
    $user_id = $payment['user_id'];

    // 2. Credit Wallets
    $pdo->prepare("UPDATE wallets SET deposit_balance = deposit_balance + ? WHERE user_id = ?")
        ->execute([$amount, $user_id]);

    // 3. Insert Deposits Row
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
