
## Usage Guide - BaadMay
## Table of Contents - BaadMay Usage Guide
- [Initialize BaadMay Client](#initialize)
- [Create Checkout Link](#create-checkout-link)
- [Get Order Status](#get-order-status)

## Initialize

Certain keys can be set as defaults during initialize stage. You can set them here or manually specify during createCheckoutLink. If you specify in createCheckoutLink the default key will be overridden for that transaction.

```php
<?php

require 'vendor/autoload.php';

use ShaqiLabs\BaadMay\BaadMayClient;
use ShaqiLabs\BaadMay\BaadMayAPI;

$BaadMayClient = new BaadMayClient(array(
    "environment" => "sandbox", //Optional - Defaults to production. Options are sandbox/production
    "api_key" => "6ae4a3ee-3dc5-4da1-97cf-329c9b9b804d",
    "success_url" => "", // Optional either set here or during checkout
    "failure_url" => "", // Optional either set here or during checkout
));

$BaadMayAPI = new BaadMayAPI($BaadMayClient);
?>
```
## Create Checkout Link

```php
<?php
try {
    $data = array(
        "amount" => 25.30,
        "order_id" => "", // Optional - leave empty for auto generated
        "items" => array(
            array(
                "item_id" => "1234",
                "sku" => "1234",
                "name" => "Test Product",
                "qty" => 1,
                "price" => 100
            )
        ),
        "customer" => array(
            "first_name" => "Tech",
            "last_name" => "Andaz",
            "address" => array(
                "Street 1",
                "Street 2"
            ),
            "city" => "Lahore",
            "state" => "Punjab",
            "postcode" => "54000",
            "phone" => "04235113700",
            "email" => "contact@domain.com",
        ),
        "billing" => array(
            "first_name" => "Tech",
            "last_name" => "Andaz",
            "address" => array(
                "Street 1",
                "Street 2"
            ),
            "city" => "Lahore",
            "state" => "Punjab",
            "postcode" => "54000",
            "phone" => "04235113700",
            "email" => "contact@domain.com",
        ),
        "shipping" => array(
            "method" => "Standard Shipping",
            "cost" => 50,
            "first_name" => "Tech",
            "last_name" => "Andaz",
            "address" => array(
                "Street 1",
                "Street 2"
            ),
            "city" => "Lahore",
            "state" => "Punjab",
            "postcode" => "54000",
            "phone" => "04235113700",
            "email" => "contact@domain.com",
        ),
        "success_url" => "https://domain.com/success", // Optional if set during client initialization
        "failure_url" => "https://domain.com/failure", // Optional if set during client initialization
    );
    $response_type = "redirect"; // redirect / response - Defaults to redirect, Redirect will automatically redirect user to payment page, response will return response
    $response = $BaadMayAPI->createCheckoutLink($data, $response_type);
    return $response;
} catch (ShaqiLabs\BaadMay\BaadMayException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
```

## Get Order Status

```php
<?php
try {
    $order_id = "123456789";
    $response = $BaadMayAPI->getOrderStatus($order_id);
    return $response;
} catch (ShaqiLabs\BaadMay\BaadMayException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
```
