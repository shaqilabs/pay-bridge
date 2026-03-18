<?php

require __DIR__ . '/../vendor/autoload.php';

use ShaqiLabs\BaadMay\BaadMayAPI;
use ShaqiLabs\BaadMay\BaadMayClient;
use ShaqiLabs\BaadMay\BaadMayException;

$client = new BaadMayClient([
    'environment' => 'sandbox', // sandbox / production
    'api_key' => 'YOUR_API_KEY',
    'success_url' => 'https://example.com/success',
    'failure_url' => 'https://example.com/failure',
    'timeout' => 30,
    'connect_timeout' => 10,
]);

$api = new BaadMayAPI($client);

try {
    $order = [
        'amount' => 25.30,
        'order_id' => '',
        'items' => [
            [
                'item_id' => '1234',
                'sku' => '1234',
                'name' => 'Test Product',
                'qty' => 1,
                'price' => 100,
            ],
        ],
        'customer' => [
            'first_name' => 'First',
            'last_name' => 'Last',
            'address' => ['Street 1', 'Street 2'],
            'city' => 'Lahore',
            'state' => 'Punjab',
            'postcode' => '54000',
            'phone' => '03001234567',
            'email' => 'contact@example.com',
        ],
        'billing' => [
            'first_name' => 'First',
            'last_name' => 'Last',
            'address' => ['Street 1', 'Street 2'],
            'city' => 'Lahore',
            'state' => 'Punjab',
            'postcode' => '54000',
            'phone' => '03001234567',
            'email' => 'contact@example.com',
        ],
        'shipping' => [
            'method' => 'Standard Shipping',
            'cost' => 50,
            'first_name' => 'First',
            'last_name' => 'Last',
            'address' => ['Street 1', 'Street 2'],
            'city' => 'Lahore',
            'state' => 'Punjab',
            'postcode' => '54000',
            'phone' => '03001234567',
            'email' => 'contact@example.com',
        ],
    ];

    $result = $api->createCheckoutLink($order, 'url'); // url / response / redirect
    print_r($result);
} catch (BaadMayException $e) {
    echo 'Error: ' . $e->getMessage() . PHP_EOL;
}

