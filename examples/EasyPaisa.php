<?php

require __DIR__ . '/../vendor/autoload.php';

use ShaqiLabs\EasyPaisa\EasyPaisaAPI;
use ShaqiLabs\EasyPaisa\EasyPaisaClient;
use ShaqiLabs\EasyPaisa\EasyPaisaException;

$client = new EasyPaisaClient([
    'environment' => 'sandbox', // sandbox / production
    'store_id' => 'YOUR_STORE_ID',
    'return_url' => 'https://example.com/return',
    'ewp_account_number' => 'YOUR_EWP_ACCOUNT_NUMBER',
    'username' => 'YOUR_USERNAME',
    'password' => 'YOUR_PASSWORD',
    'hash_key' => 'YOUR_HASH_KEY',
    'timeout' => 30,
    'connect_timeout' => 10,
]);

$api = new EasyPaisaAPI($client);

try {
    $init = $api->initiateHostedCheckout([
        'amount' => 25.33,
        'order_id' => '',
        'email' => 'contact@example.com',
        'phone' => '03001234567',
        'return_url' => '',
        'payment_method' => '',
    ], 'follow'); // form / redirect / follow

    print_r($init);
} catch (EasyPaisaException $e) {
    echo 'Error: ' . $e->getMessage() . PHP_EOL;
}

