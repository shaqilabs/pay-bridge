<?php

require __DIR__ . '/../vendor/autoload.php';

use ShaqiLabs\AbhiPay\AbhiPayAPI;
use ShaqiLabs\AbhiPay\AbhiPayClient;
use ShaqiLabs\AbhiPay\AbhiPayException;

$client = new AbhiPayClient([
    'merchant_id' => 'YOUR_MERCHANT_ID',
    'secret_key' => 'YOUR_SECRET_KEY',
    'return_url' => 'https://example.com/return',
    'timeout' => 30,
    'connect_timeout' => 10,
]);

$api = new AbhiPayAPI($client);

try {
    $order = [
        'amount' => 25.30,
        'description' => 'Test order',
        'transaction_reference' => '',
        'return_url' => '',
        'card_save' => false,
        'operation' => 'PURCHASE',
    ];

    $result = $api->createCheckoutLink($order, 'url'); // url / response / redirect
    print_r($result);
} catch (AbhiPayException $e) {
    echo 'Error: ' . $e->getMessage() . PHP_EOL;
}

