<?php

require __DIR__ . '/../vendor/autoload.php';

use ShaqiLabs\AlfalahAPG\AlfalahAPGAPI;
use ShaqiLabs\AlfalahAPG\AlfalahAPGClient;
use ShaqiLabs\AlfalahAPG\AlfalahAPGException;

$client = new AlfalahAPGClient([
    'environment' => 'sandbox', // sandbox / production
    'key1' => 'KEY1',
    'key2' => 'KEY2',
    'channel_id' => 'CHANNELID',
    'merchant_id' => 'MERCHANTID',
    'store_id' => 'STOREID',
    'redirection_request' => '0',
    'merchant_hash' => 'MERCHANTHASH',
    'merchant_username' => 'USERNAME',
    'merchant_password' => 'PASSWORD',
    'transaction_type' => '3',
    'cipher' => 'aes-128-cbc',
    'return_url' => 'https://example.com/return',
    'currency' => 'PKR',
    'timeout' => 30,
    'connect_timeout' => 10,
]);

$api = new AlfalahAPGAPI($client);

try {
    $order = [
        'amount' => 500,
        'currency' => 'PKR',
        'order_id' => '',
    ];

    $result = $api->createCheckoutLink($order, 'data'); // data / form / redirect
    print_r($result);
} catch (AlfalahAPGException $e) {
    echo 'Error: ' . $e->getMessage() . PHP_EOL;
}

