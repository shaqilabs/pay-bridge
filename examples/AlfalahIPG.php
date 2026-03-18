<?php

require __DIR__ . '/../vendor/autoload.php';

use ShaqiLabs\AlfalahIPG\AlfalahIPGAPI;
use ShaqiLabs\AlfalahIPG\AlfalahIPGClient;
use ShaqiLabs\AlfalahIPG\AlfalahIPGException;

$client = new AlfalahIPGClient([
    'environment' => 'sandbox', // sandbox / production
    'merchant_id' => 'YOUR_MERCHANT_ID',
    'merchant_name' => 'Pay Bridge',
    'password' => 'YOUR_PASSWORD',
    'operator_id' => 'YOUR_OPERATOR_ID',
    'api_key' => 'YOUR_API_KEY',
    'return_url' => 'https://example.com/return',
    'transaction_type' => 'PURCHASE',
    'currency' => 'PKR',
    'timeout' => 30,
    'connect_timeout' => 10,
]);

$api = new AlfalahIPGAPI($client);

try {
    $order = [
        'amount' => 500,
        'order_id' => '',
        'currency_code' => 'PKR',
        'description' => 'Test order',
        'return_url' => 'https://example.com/return',
        'transaction_type' => 'PURCHASE',
    ];

    $result = $api->createCheckoutLink($order, 'data'); // data / redirect
    print_r($result);
} catch (AlfalahIPGException $e) {
    echo 'Error: ' . $e->getMessage() . PHP_EOL;
}

