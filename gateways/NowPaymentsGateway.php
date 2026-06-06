<?php
// gateways/NowPaymentsGateway.php

require_once 'GatewayInterface.php';

class NowPaymentsGateway implements GatewayInterface {
    protected $apiKey;
    protected $ipnSecret;
    protected $apiUrl = "https://api.nowpayments.io/v1";
    protected $sslVerifyPeer = true;
    protected $sslVerifyHost = 2;

    public function __construct($apiKey, $ipnSecret = null, array $options = []) {
        $this->apiKey = $apiKey;
        $this->ipnSecret = $ipnSecret;
        $this->apiUrl = $options['base_url'] ?? $this->apiUrl;
        $this->sslVerifyPeer = (bool)($options['ssl_verify_peer'] ?? true);
        $this->sslVerifyHost = (int)($options['ssl_verify_host'] ?? 2);
    }

    public function initiatePayment($data) {
        $endpoint = $this->apiUrl . "/payment";
        
        $payload = [
            "price_amount"     => $data['amount'],
            "price_currency"   => $data['currency'] ?? "usd",
            "pay_currency"     => $data['pay_currency'] ?? "usdtbsc",
            "order_id"         => $data['identifier'],
            "ipn_callback_url" => $data['ipn_url'],
            "success_url"      => $data['success_url'],
            "cancel_url"       => $data['cancel_url'],
        ];

        $ch = curl_init($endpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_TIMEOUT, 45);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 15);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, $this->sslVerifyHost);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $this->sslVerifyPeer);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'x-api-key: ' . $this->apiKey
        ]);
        
        $result = curl_exec($ch);
        $curlError = curl_error($ch);
        $httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($result === false) {
            return [
                'message' => $curlError ?: 'Unable to connect to NOWPayments.',
                'http_code' => $httpCode,
            ];
        }

        $decoded = json_decode($result, true);
        if (!is_array($decoded)) {
            return [
                'message' => 'Invalid NOWPayments response.',
                'raw' => $result,
                'http_code' => $httpCode,
            ];
        }

        $decoded['http_code'] = $httpCode;
        return $decoded;
    }

    public function validatePayment($data) {
        $rawBody = is_array($data) && isset($data['_raw_body']) ? $data['_raw_body'] : file_get_contents('php://input');
        $receivedSig = is_array($data) && isset($data['_signature']) ? $data['_signature'] : ($_SERVER['HTTP_X_NOWPAYMENTS_SIG'] ?? '');
        
        if (!$this->ipnSecret || $rawBody === false || $rawBody === '') {
            return false;
        }

        $expectedSig = hash_hmac('sha512', $rawBody, $this->ipnSecret);
        
        if (hash_equals($expectedSig, $receivedSig)) {
            $payload = json_decode($rawBody, true);
            $status = $payload['payment_status'] ?? '';
            return ($status === 'finished' || $status === 'confirmed');
        }
        
        return false;
    }

    public function fetchPaymentStatus($paymentId) {
        $endpoint = $this->apiUrl . "/payment/" . rawurlencode($paymentId);
        $ch = curl_init($endpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 45);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, $this->sslVerifyHost);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $this->sslVerifyPeer);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'x-api-key: ' . $this->apiKey
        ]);

        $result = curl_exec($ch);
        $httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $decoded = json_decode($result, true) ?: [];
        $decoded['http_code'] = $httpCode;
        return $decoded;
    }
}
