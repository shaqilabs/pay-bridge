
## Usage Guide - Alfalah APG
## Table of Contents - Alfalah APG Usage Guide
- [Initialize Alfalah APG Client](#initialize)
- [Create Checkout Link](#create-checkout-link)
- [Dynamic Redirect](#dynamic-redirect)
## Initialize

Certain keys can be set as defaults during initialize stage. You can set them here or manually specify during createCheckoutLink. If you specify in createCheckoutLink the default key will be overridden for that transaction.

```php
<?php

require 'vendor/autoload.php';

use ShaqiLabs\AlfalahAPG\AlfalahAPGClient;
use ShaqiLabs\AlfalahAPG\AlfalahAPGAPI;

$AlfalahAPGClient = new AlfalahAPGClient(array(
    "environment" => "production", // Optional - Defaults to production. Options are: sandbox / production
    "key1" => "KEY1",
    "key2" => "KEY2",
    "channel_id" => "CHANNELID",
    "merchant_id" => "MERCHANTID",
    "store_id" => "STOREID",
    "redirection_request" => "0", // Optional - Defaults to 0
    "merchant_hash" => "MERCHANTHASH",
    "merchant_username" => "USERNAME",
    "merchant_password" => "PASSWORD",
    "transaction_type" => "3", // Optional - Defaults to 3
    "cipher" => "CIPHER", // Optional - Defaults to aes-128-cbc
    "return_url" => "https://domain.com",
    "currency" => "PKR", // Optional - Defaults to PKR
));

$AlfalahAPGAPI = new AlfalahAPGAPI($AlfalahAPGClient);
?>
```
## Create Checkout Link


```php
<?php
try {
    $data = array(
        "amount" => 500,
        "currency" =>  "PKR", // Optional - Will use one set during initializing
        "order_id" => "", // Optional - Will generate unique ID if not provided
    );
    $response_type = "redirect"; // redirect / form / data - Defaults to redirect, Redirect will automatically redirect user to payment page, form will return html form with fields and values, data will return array with all values
    $response = $AlfalahAPGAPI->createCheckoutLink($data, $response_type);
    return $response;
} catch (ShaqiLabs\AlfalahAPG\AlfalahAPGException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
```
## Dynamic Redirect

Dynamic Redirect allows you redirect user to the payment page without having to configure a page for the form. This is useful if you want to first return the checkout data and then redirect dynamically.

### Step 1 - Get Checkout Data

```php
<?php
try {
    $data = array(
        "amount" => 500,
        "currency" =>  "PKR", // Optional - Will use one set during initializing
        "order_id" => "", // Optional - Will generate unique ID if not provided
    );
    $response_type = "data"; // redirect / data - Defaults to redirect, Redirect will automatically redirect user to payment page, data will return array with all values
    $response = $AlfalahAPGAPI->createCheckoutLink($data, $response_type);
    return;
} catch (ShaqiLabs\AlfalahAPG\AlfalahAPGException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
```


### Step 2 - Call Dynamic Checkout

```php
<?php
try {
    $AlfalahAPGAPI->dynamicRedirect($response);
    return;
} catch (ShaqiLabs\AlfalahAPG\AlfalahAPGException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
```


### Complete Example 

```php
<?php
try {
    $data = array(
        "amount" => 500,
        "currency" =>  "PKR", // Optional - Will use one set during initializing
        "order_id" => "", // Optional - Will generate unique ID if not provided
    );
    $response_type = "data"; // redirect / data - Defaults to redirect, Redirect will automatically redirect user to payment page, data will return array with all values
    $response = $AlfalahAPGAPI->createCheckoutLink($data, $response_type);
    $AlfalahAPGAPI->dynamicRedirect($response);
    return;
} catch (ShaqiLabs\AlfalahAPG\AlfalahAPGException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
```