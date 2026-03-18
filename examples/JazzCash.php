<?php

require __DIR__ . '/../vendor/autoload.php';

use ShaqiLabs\JazzCash\JazzCashAPI;
use ShaqiLabs\JazzCash\JazzCashClient;
use ShaqiLabs\JazzCash\JazzCashException;

$client = new JazzCashClient([
    'environment' => 'sandbox', // sandbox / production
    'merchant_id' => 'YOUR_MERCHANT_ID',
    'password' => 'YOUR_PASSWORD',
    'integerity_salt' => 'YOUR_INTEGRITY_SALT',
    'domain_code' => 'TA',
    'return_url' => 'https://example.com/return',
    'timeout' => 30,
    'connect_timeout' => 10,
]);

$api = new JazzCashAPI($client);

try {
    $result = $api->createCheckoutLink([
        'amount' => 25.30,
        'bill_reference' => 'billRef',
        'transaction_reference' => '',
        'description' => 'description',
        'date_time' => date('YmdHis'),
        'order_id' => '',
    ], 'form'); // form / redirect

    echo $result;
} catch (JazzCashException $e) {
    echo 'Error: ' . $e->getMessage() . PHP_EOL;
}

