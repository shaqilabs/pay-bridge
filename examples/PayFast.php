<?php

require __DIR__ . '/../vendor/autoload.php';

use ShaqiLabs\PayFast\PayFastAPI;
use ShaqiLabs\PayFast\PayFastClient;
use ShaqiLabs\PayFast\PayFastException;

$client = new PayFastClient([
    'api_url' => 'https://ipguat.apps.net.pk/', // or production base URL
    'merchant_id' => 'YOUR_MERCHANT_ID',
    'api_password' => 'YOUR_API_PASSWORD',
    'merchant_name' => 'Pay Bridge',
    'success_url' => 'https://example.com/success',
    'cancel_url' => 'https://example.com/cancel',
    'checkout_url' => 'https://example.com/checkout',
    'currency_code' => 'PKR',
    'timeout' => 30,
    'connect_timeout' => 10,
]);

$api = new PayFastAPI($client);

try {
    $result = $api->createCheckoutLink([
        'TXNAMT' => 5000,
        'BASKET_ID' => '',
        'customer_email' => 'test@example.com',
        'customer_phone' => '+920000000000',
        'order_date' => date('Y-m-d H:i:s'),
    ], 'data'); // data / form / redirect

    print_r($result);
} catch (PayFastException $e) {
    echo 'Error: ' . $e->getMessage() . PHP_EOL;
}

