
## Usage Guide - Alfalah IPG
## Table of Contents - Alfalah IPG Usage Guide
- [Initialize Alfalah IPG Client](#initialize)
- [Create Checkout Link](#create-checkout-link)
- [Dynamic Redirect](#dynamic-redirect)
## Initialize

Certain keys can be set as defaults during initialize stage. You can set them here or manually specify during createCheckoutLink. If you specify in createCheckoutLink the default key will be overridden for that transaction.

```php
<?php

require 'vendor/autoload.php';

use ShaqiLabs\AlfalahIPG\AlfalahIPGClient;
use ShaqiLabs\AlfalahIPG\AlfalahIPGAPI;

$AlfalahIPGClient = new AlfalahIPGClient(array(
    "environment" =>"production", // Optional - Defaults to production
    "merchant_id" => "YOURMERCHANTID",
    "merchant_name" =>  "Pay Bridge",
    "password" =>  "YOURMERCHANTPASSWORD",
    "operator_id" =>  "YOUROPERATORID",  // Optional
    "api_key" =>  "YOURAPIKEY", // Optional 
    "return_url" =>  "https://domain.com/success", // Required/Optional - Must be provided either during initialize or during checkout link creation.
    "transaction_type" =>"PURCHASE", // Optional - Defaults to PURCHASE. Options are: PURCHASE / AUTHORIZE / VERIFY / NONE
    "currency_code" =>  "PKR",
));

$AlfalahIPGAPI = new AlfalahIPGAPI($AlfalahIPGClient);
?>
```
## Create Checkout Link

You can add any fields available in the documentation in the "data" key. This is optional and only useful/required when customizing the appearance or functionality of the checkout. Refer to the documentation for full list of parameters.

```php
<?php
try {
    $data = array(
        "amount" => 500,
        "order_id" => "", // Optional - Will generate unique ID if not provided
        "currency_code" =>  "PKR", // Optional - Will use one set during initializing
        "description" => "Test Order",
        "return_url" =>  "https://domain.com/success", // Optional - Will use one set during initializing
        "transaction_type" => "PURCHASE", // Optional - will use one set during initializing
        // Use this to provide more data into the checkout intiation. Details can be found on official documentation
        "data" => array(
            "billing" => array(
                "address" => array(
                    "city" => 'Lahore',
                    "company" => 'Tech Andaz',
                    "country" => 'PAK',
                    "postcodeZip" => '54000',
                    "stateProvince" => 'Punjab',
                    "street" => '119/2 M Quaid-e-Azam Industrial Estate',
                    "street2" => 'Kot Lakhpat, Township',
                )
            ),
            "customer" => array(
                "account" => array(
                    "id" => '12345',
                ),
                "email" => 'test@test.com',
                "firstName" => 'Tech',
                "lastName" => 'Andaz',
                "mobilePhone" => '+924235113700',
                "phone" => '+924235113700',
                "taxRegistrationId" => '123456',
            ),
            "interaction" => array(
                "cancelUrl" => "https://domain.com/cancel",
                "merchant" => array(
                    "logo" => "https://domain.com/images/logo.png",
                    "name" => "Tech Andaz",
                    "url" => "https://domain.com"
                ),
                "timeout" => 1800
            ),
            "order" => array(
                "notificationUrl" => "https://domain.com/webhook",
                "item" => array(
                    array(
                        "name" => "Test Product",
                        "quantity" => 1,
                        "unitPrice" => 100,
                    ),
                    array(
                        "name" => "Test Product2",
                        "quantity" => 4,
                        "unitPrice" => 100,
                    ),
                )
            )
        ), // Use this to provide more data into the checkout intiation. Details can be found on official documentation
    );
    $response_type = "data"; // redirect / data - Defaults to redirect, Redirect will automatically redirect user to payment page, data will return array with all values
    $response = $AlfalahIPGAPI->createCheckoutLink($data, $response_type);
    return $response;
} catch (ShaqiLabs\AlfalahIPG\AlfalahIPGException $e) {
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
        "description" => "Test Order",
    );
    $response_type = "data"; // redirect / data - Defaults to redirect, Redirect will automatically redirect user to payment page, data will return array with all values
    $response = $AlfalahIPGAPI->createCheckoutLink($data, $response_type);
    $access_token = $response['access_token'];
    $success_indicator = $response['success_indicator'];
    return;
} catch (ShaqiLabs\AlfalahIPG\AlfalahIPGException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
```


### Step 2 - Call Dynamic Checkout

```php
<?php
try {
    $AlfalahIPGAPI->dynamicRedirect($access_token);
    return;
} catch (ShaqiLabs\AlfalahIPG\AlfalahIPGException $e) {
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
        "order_id" => "", // Optional - Will generate unique ID if not provided
        "currency_code" =>  "PKR", // Optional - Will use one set during initializing
        "description" => "Test Order",
        "return_url" =>  "https://domain.com/success", // Optional - Will use one set during initializing
        "transaction_type" => "PURCHASE", // Optional - will use one set during initializing
        // Use this to provide more data into the checkout intiation. Details can be found on official documentation
        "data" => array(
            "billing" => array(
                "address" => array(
                    "city" => 'Lahore',
                    "company" => 'Tech Andaz',
                    "country" => 'PAK',
                    "postcodeZip" => '54000',
                    "stateProvince" => 'Punjab',
                    "street" => '119/2 M Quaid-e-Azam Industrial Estate',
                    "street2" => 'Kot Lakhpat, Township',
                )
            ),
            "customer" => array(
                "account" => array(
                    "id" => '12345',
                ),
                "email" => 'test@test.com',
                "firstName" => 'Tech',
                "lastName" => 'Andaz',
                "mobilePhone" => '+924235113700',
                "phone" => '+924235113700',
                "taxRegistrationId" => '123456',
            ),
            "interaction" => array(
                "cancelUrl" => "https://domain.com/cancel",
                "merchant" => array(
                    "logo" => "https://domain.com/images/logo.png",
                    "name" => "Tech Andaz",
                    "url" => "https://domain.com"
                ),
                "timeout" => 1800
            ),
            "order" => array(
                "notificationUrl" => "https://domain.com/webhook",
                "item" => array(
                    array(
                        "name" => "Test Product",
                        "quantity" => 1,
                        "unitPrice" => 100,
                    ),
                    array(
                        "name" => "Test Product2",
                        "quantity" => 4,
                        "unitPrice" => 100,
                    ),
                )
            )
        ), // Use this to provide more data into the checkout intiation. Details can be found on official documentation
    );
    $response_type = "data"; // redirect / data - Defaults to redirect, Redirect will automatically redirect user to payment page, data will return array with all values
    $response = $AlfalahIPGAPI->createCheckoutLink($data, $response_type);
    $access_token = $response['access_token'];
    $success_indicator = $response['success_indicator'];
    $AlfalahIPGAPI->dynamicRedirect($access_token);
} catch (ShaqiLabs\AlfalahIPG\AlfalahIPGException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
```
