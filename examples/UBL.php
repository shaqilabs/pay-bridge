<?php

require __DIR__ . '/../vendor/autoload.php';

use ShaqiLabs\UBL\UBLAPI;
use ShaqiLabs\UBL\UBLClient;
use ShaqiLabs\UBL\UBLException;

$client = new UBLClient([
    'api_url' => 'https://demo-ipg.ctdev.comtrust.ae:2443/',
    'customer' => 'Demo Merchant', // Defaults to Pay Bridge
    'store' => '0000',
    'terminal' => '0000',
    'channel' => 'Web',
    'currency' => 'PKR',
    'transaction_hint' => 'CPT:Y',
    'callback_url' => 'https://example.com/return',
    'username' => 'YOUR_USERNAME',
    'password' => 'YOUR_PASSWORD',
    'certificate' => __DIR__ . '/../src/UBL/ca.crt',
    'timeout' => 30,
    'connect_timeout' => 10,
]);

$api = new UBLAPI($client);

try {
    $result = $api->createCheckoutLink([
        'Amount' => 5000,
        'OrderName' => 'Test Order',
    ]);

    print_r($result);
} catch (UBLException $e) {
    echo 'Error: ' . $e->getMessage() . PHP_EOL;
}

