<?php

require __DIR__ . '/../vendor/autoload.php';

use ShaqiLabs\SafePay\SafePayAPI;
use ShaqiLabs\SafePay\SafePayClient;
use ShaqiLabs\SafePay\SafePayException;

$client = new SafePayClient([
    'environment' => 'sandbox',
    'apiKey' => 'YOUR_API_KEY',
    'v1Secret' => 'YOUR_V1_SECRET',
    'webhookSecret' => 'YOUR_WEBHOOK_SECRET',
    'success_url' => 'https://example.com/success',
    'cancel_url' => 'https://example.com/cancel',
]);

$api = new SafePayAPI($client);

try {
    $result = $api->createCheckoutLink([
        'amount' => 5000,
        'order_id' => 'ORDER-001',
        'source' => 'Pay Bridge',
        'webhooks' => 'true',
    ]);

    print_r($result);
} catch (SafePayException $e) {
    echo 'Error: ' . $e->getMessage() . PHP_EOL;
}

