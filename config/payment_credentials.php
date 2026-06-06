<?php
// config/payment_credentials.php - Server-side secure overrides
return [
    'paybost' => [
        'active_environment' => 'live',
        'sandbox_endpoint' => 'https://paybost.com/sandbox/payment/initiate',
        'live_endpoint' => 'https://paybost.com/payment/initiate',
        'ssl_verify_peer' => false,
        'ssl_verify_host' => 0,
        'identifier_prefix' => 'TCPB',
        'gateways' => [
            'easypaisa_payboost' => [
                'label' => 'Easypaisa (PayBost)',
                'api_key' => 'el7m4kt3kq44xuyfk8lz78eggt1ze5vxpe9mpivvm1tjhfa7k1',
                'secret_key' => 'wbjvqx9gnvbdqhs3t37c3ymhle857mdnl0x3xmy0tyt2xgxu7m',
            ],
            'jazzcash_payboost' => [
                'label' => 'JazzCash (PayBost)',
                'api_key' => 'el7m4kt3kq44xuyfk8lz78eggt1ze5vxpe9mpivvm1tjhfa7k1',
                'secret_key' => 'wbjvqx9gnvbdqhs3t37c3ymhle857mdnl0x3xmy0tyt2xgxu7m',
            ],
        ],
    ],
    'nowpayments' => [
        'active_environment' => 'live',
        'base_url' => 'https://api.nowpayments.io/v1',
        'ssl_verify_peer' => true,
        'ssl_verify_host' => 2,
        'identifier_prefix' => 'TCNP',
        'gateways' => [
            'nowpayments_bep20' => [
                'label' => 'NOWPayments (USDT BEP20)',
                'api_key' => '0D77JXB-3ZK487W-GC97DAB-MWCP9EP',
                'ipn_secret' => 'h7JD95Tg8T6q5g6TPh3GMF1rbI4ACQZt',
                'public_key' => 'afdd76ba-26d6-4e0f-9e94-ba92cf8dffc2',
                'pay_currency' => 'usdtbsc',
                'price_currency' => 'usd',
            ],
        ],
    ],
];
