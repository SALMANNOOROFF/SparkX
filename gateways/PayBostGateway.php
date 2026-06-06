<?php
// gateways/PayBostGateway.php

require_once 'GatewayInterface.php';

class PayBostGateway implements GatewayInterface {
    protected $publicKey;
    protected $secretKey;
    protected $liveEndpoint = "https://paybost.com/payment/initiate";
    protected $testEndpoint = "https://paybost.com/sandbox/payment/initiate";
    protected $isTest = false;

    public function __construct($publicKey, $secretKey = null, $isTest = false, array $options = []) {
        $this->publicKey = $publicKey;
        $this->secretKey = $secretKey;
        $this->isTest = $isTest;
        $this->liveEndpoint = $options['live_endpoint'] ?? $this->liveEndpoint;
        $this->testEndpoint = $options['sandbox_endpoint'] ?? $this->testEndpoint;
    }

    public function initiatePayment($data) {
        $url = $this->isTest ? $this->testEndpoint : $this->liveEndpoint;
        
        $parameters = [
            'identifier'     => $data['identifier'],
            'currency'       => $data['currency'] ?? 'PKR',
            'amount'         => $data['amount'],
            'details'        => $data['details'] ?? 'Deposit Funds',
            'ipn_url'        => $data['ipn_url'],
            'cancel_url'     => $data['cancel_url'],
            'success_url'    => $data['success_url'],
            'public_key'     => $this->publicKey,
            'site_logo'      => $data['site_logo'] ?? '',
            'checkout_theme' => $data['checkout_theme'] ?? 'light',
            'customer_name'  => $data['customer_name'] ?? '',
            'customer_email' => $data['customer_email'] ?? '',
            'merchant'       => $data['merchant'] ?? 'Trade Cycle',
            'payment_type'   => $data['payment_type'] ?? '',
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $parameters);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 45);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $result = curl_exec($ch);
        $curlError = curl_error($ch);
        $httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($result === false) {
            return [
                'error' => 'true',
                'message' => $curlError ?: 'Unable to connect to PayBost.',
            ];
        }

        $decoded = json_decode($result, true);
        if (!is_array($decoded)) {
            return [
                'error' => 'true',
                'message' => 'Invalid PayBost response.',
                'raw' => $result,
                'http_code' => $httpCode,
            ];
        }

        $decoded['http_code'] = $httpCode;
        return $decoded;
    }

    public function validatePayment($data) {
        $status = $data['status'] ?? '';
        $signature = strtoupper((string)($data['signature'] ?? ''));
        $identifier = $data['identifier'] ?? '';
        $innerData = $data['data'] ?? [];

        if (!$this->secretKey || !isset($innerData['amount'])) {
            return false;
        }

        $amount = number_format((float)$innerData['amount'], 2, '.', '');
        $customKey = $amount . $identifier;
        $expectedSignature = strtoupper(hash_hmac('sha256', $customKey, $this->secretKey));

        return ($status === "success" && hash_equals($expectedSignature, $signature));
    }

    public function possibleSignatures(string $identifier, $amount): array {
        $candidates = [];
        $rawAmount = is_scalar($amount) ? trim((string)$amount) : '';

        if ($rawAmount !== '') {
            $candidates[] = $rawAmount;
        }

        if ($rawAmount !== '' && is_numeric($rawAmount)) {
            $numericAmount = (float)$rawAmount;
            $candidates[] = number_format($numericAmount, 2, '.', '');
            $candidates[] = number_format($numericAmount, 1, '.', '');
            $candidates[] = number_format($numericAmount, 0, '.', '');
            $candidates[] = rtrim(rtrim(number_format($numericAmount, 8, '.', ''), '0'), '.');
            $candidates[] = (string)$numericAmount;
        }

        $candidates = array_values(array_unique(array_filter($candidates)));

        $signatures = [];
        foreach ($candidates as $candidate) {
            $signatures[$candidate] = strtoupper(hash_hmac('sha256', $candidate . $identifier, (string)$this->secretKey));
        }

        return $signatures;
    }
}
