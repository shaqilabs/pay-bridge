## Usage Guide - EasyPaisa

## Table of Contents

- [Initialize](#initialize)
- [Hosted Checkout](#hosted-checkout)
- [Transaction Status](#transaction-status)
- [Wallet Transaction](#wallet-transaction)

## Initialize

```php
<?php

require 'vendor/autoload.php';

use ShaqiLabs\EasyPaisa\EasyPaisaClient;
use ShaqiLabs\EasyPaisa\EasyPaisaAPI;

$EasyPaisaClient = new EasyPaisaClient([
    "environment" => "sandbox", // Optional - Defaults to production. Options: sandbox / production
    "store_id" => "YOUR_STORE_ID",
    "hash_key" => "YOUR_HASH_KEY",
    "ewp_account_number" => "YOUR_EWP_ACCOUNT_NUMBER",
    "username" => "YOUR_USERNAME", // Optional for some endpoints
    "password" => "YOUR_PASSWORD", // Optional for some endpoints
    "return_url" => "https://domain.com/return", // Optional - can also be provided per request
    "timeout" => 30, // Optional
    "connect_timeout" => 10, // Optional
]);

$EasyPaisaAPI = new EasyPaisaAPI($EasyPaisaClient);
```

## Hosted Checkout

### Step 1: Initiate Hosted Checkout

`initiateHostedCheckout()` supports:
- `form`: returns an HTML form (you render + submit)
- `redirect`: returns an HTML form + auto-submit script
- `follow`: performs a cURL follow and tries to return `auth_token`

```php
$init = $EasyPaisaAPI->initiateHostedCheckout([
    "amount" => 25.33,
    "order_id" => "", // Optional - auto-generated if empty
    "email" => "contact@domain.com", // Optional
    "phone" => "03001234567", // Optional
    "bank_id" => "", // Optional
    "expiry_datetime" => "", // Optional - Format: Ymd His
    "return_url" => "", // Optional - uses client return_url if empty
    "payment_method" => "", // Optional - OTC_PAYMENT_METHOD / MA_PAYMENT_METHOD / CC_PAYMENT_METHOD / QR_PAYMENT_METHOD / DD_PAYMENT_METHOD
], "follow");
```

### Step 2: Process Hosted Checkout (Confirm)

```php
$html = $EasyPaisaAPI->processHostedCheckout([
    "auth_token" => $init["auth_token"],
    "redirect_url" => "https://domain.com/return", // Optional - uses client return_url if empty
], "redirect"); // form / redirect
```

## Transaction Status

```php
$status = $EasyPaisaAPI->transactionStatus("YOUR_ORDER_ID");
```

## Wallet Transaction

```php
$wallet = $EasyPaisaAPI->performWalletTransaction([
    "order_id" => "",
    "amount" => 2,
    "account_number" => "03001234567",
    "email_address" => "contact@domain.com",
]);
```
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
