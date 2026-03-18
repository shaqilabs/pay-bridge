
## Usage Guide - JazzCash
## Table of Contents - JazzCash Usage Guide
- [Initialize JazzCash Client](#initialize)
- [Process Response](#process-response)
- [Mobile Account Linking](#mobile-account-linking)
- [Linked Mobile Account Transaction](#linked-mobile-account-transaction)
- [Transaction Status](#transaction-status)
- [Refund Card Transaction](#refund-card-transaction)
- [Refund Wallet Transaction](#refund-wallet-transaction)


## Initialize

Certain keys can be set as defaults during initialize stage. You can set them here or manually specify during createCheckoutLink. If you specify in createCheckoutLink the default key will be overridden for that transaction.

```php
<?php

require 'vendor/autoload.php';

use ShaqiLabs\JazzCash\JazzCashClient;
use ShaqiLabs\JazzCash\JazzCashAPI;

$JazzCashClient = new JazzCashClient(array(
    "environment" => "sandbox", // Optional - Defaults to production. Options are: sandbox / production
    "merchant_id" => "MC108944",
    "password" => "5990v09a6d",
    "integerity_salt" => "zx82t8029e",
    "domain_code" => "TA", //max 3 character code to be appended for all Transaction Reference numbers 
    "return_url" => "https://domain.com/success",
));

$JazzCashAPI = new JazzCashAPI($JazzCashClient);
?>
```
## Create Checkout Link

```php
<?php
try {
    $data = array(
        "amount" => 25.30,
        "bill_reference" =>  "billRef",
        "transaction_reference" => "", // Optional - max 17 character length - domain_code will be added in the beggining - leave empty for auto generated
        "description" => "description",
        "date_time" => date("YmdHis"), // Optional - will use current time if not provided
        "order_id" => "", // Optional - Will generate unique ID if not provided
        "metafield_1" => "", //Optional Metadata for order
        "metafield_2" => "", //Optional Metadata for order
        "metafield_3" => "", //Optional Metadata for order
        "metafield_4" => "", //Optional Metadata for order
        "metafield_5" => "", //Optional Metadata for order
    );
    $response_type = "redirect"; // redirect / form - Defaults to redirect, Redirect will automatically redirect user to payment page, form will return html form with fields and values
    $response = $JazzCashAPI->createCheckoutLink($data, $response_type);
    return $response;
} catch (ShaqiLabs\JazzCash\JazzCashException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
```

## Process Response

```php
<?php
try {
    $response = $JazzCashAPI->processResponse();
    return $response;
} catch (ShaqiLabs\JazzCash\JazzCashException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
```

## Mobile Account Linking

```php
<?php
try {
    $data = array(
        "account_number" => "03234896599",
    );
    $response_type = "redirect"; // redirect / form - Defaults to redirect, Redirect will automatically redirect user to payment page, form will return html form with fields and values
    $response = $JazzCashAPI->mobileAccountLinking($data, $response_type);
    return $response;
} catch (ShaqiLabs\JazzCash\JazzCashException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
```

## Linked Mobile Account Transaction

```php
<?php
try {
    $data = array(
        "amount" => 25.30,
        "payment_token" => "jwMcs3dX20iM0Kz6gg3kTrtst7TS/juK",
        "bill_reference" =>  "billRef",
        "transaction_reference" => "", // Optional - max 17 character length - domain_code will be added in the beggining - leave empty for auto generated
        "description" => "description",
        "date_time" => date("YmdHis"), // Optional - will use current time if not provided
        "order_id" => "", // Optional - Will generate unique ID if not provided
        "metafield_1" => "", //Optional Metadata for order
        "metafield_2" => "", //Optional Metadata for order
        "metafield_3" => "", //Optional Metadata for order
        "metafield_4" => "", //Optional Metadata for order
        "metafield_5" => "", //Optional Metadata for order
    );
    $response = $JazzCashAPI->linkedMobileAccountTransaction($data);
    return $response;
} catch (ShaqiLabs\JazzCash\JazzCashException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
```

## Transaction Status

```php
<?php
try {
    $transaction_reference = "T20220203110109";
    $response = $JazzCashAPI->transactionStatus($transaction_reference);
    return $response;
} catch (ShaqiLabs\JazzCash\JazzCashException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
```

## Refund Card Transaction

```php
<?php
try {
    $transaction_reference = "TREF2022051812564132";
    $amount = 100;
    $response = $JazzCashAPI->refundCardTransaction($transaction_reference, $amount);
    return $response;
} catch (ShaqiLabs\JazzCash\JazzCashException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
```

## Refund Wallet Transaction

```php
<?php
try {
    $transaction_reference = "T20220518150213";
    $amount = 100;
    $mpin = "1234";
    $response = $JazzCashAPI->refundWalletTransaction($transaction_reference, $amount, $mpin);
    return $response;
} catch (ShaqiLabs\JazzCash\JazzCashException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
```