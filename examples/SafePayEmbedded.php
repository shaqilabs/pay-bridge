<?php

require __DIR__ . '/../vendor/autoload.php';

use ShaqiLabs\SafePayEmbedded\SafePayEmbeddedAPI;
use ShaqiLabs\SafePayEmbedded\SafePayEmbeddedClient;
use ShaqiLabs\SafePayEmbedded\SafePayEmbeddedException;

$client = new SafePayEmbeddedClient([
    'environment' => 'sandbox', // sandbox / development / production
    'api_key' => 'YOUR_API_KEY',
    'public_key' => 'YOUR_PUBLIC_KEY',
    'webhook_key' => 'YOUR_WEBHOOK_KEY',
    'intent' => 'CYBERSOURCE',
    'mode' => 'unscheduled_cof',
    'currency' => 'PKR',
    'source' => 'Pay Bridge',
]);

$api = new SafePayEmbeddedAPI($client);

try {
    $result = $api->createCustomer([
        'first_name' => 'First',
        'last_name' => 'Last',
        'email' => 'contact@example.com',
        'phone_number' => '+920000000000',
        'country' => 'PK',
        'is_guest' => true,
    ]);

    print_r($result);
} catch (SafePayEmbeddedException $e) {
    echo 'Error: ' . $e->getMessage() . PHP_EOL;
}

