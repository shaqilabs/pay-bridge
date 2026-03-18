
## Usage Guide - PayFast
## Table of Contents - PayFast Usage Guide
- [Initialize PayFast Client](#initialize)
- [Create Checkout Link](#create-checkout-link)
- [Dynamic Redirect](#dynamic-redirect)
- [Get Form Fields](#get-form-fields)
## Initialize

Certain keys can be set as defaults during initialize stage. You can set them here or manually specify during createCheckoutLink. If you specify in createCheckoutLink the default key will be overridden for that transaction.

```php
<?php

require 'vendor/autoload.php';

use ShaqiLabs\PayFast\PayFastClient;
use ShaqiLabs\PayFast\PayFastAPI;

$PayFastClient = new PayFastClient(array(
    "api_url" =>"https://ipguat.apps.net.pk/", // Optional - Defaults to Production URL
    "merchant_id" => "YOUR_MERCHANT_ID",
    "api_password" =>  "YOUR_API_PASSWORD",
    "merchant_name" =>  "Pay Bridge",
    "success_url" =>  "https://domain.com/success", // Required/Optional - Must be provided either during initialize or during checkout link creation.
    "cancel_url" =>  "https://domain.com/cancel", // Optional - Defaults to success url
    "checkout_url" =>  "https://domain.com/checkout", // Optional - Defaults to success url
    "currency_code" =>  "PKR", // Optional - Defaults to PKR. If provided will default for all transactions except when explicitly mentioned
    "proccode" =>  "00", // Optional - Defaults to 00. If provided will default for all transactions except when explicitly mentioned
    "tran_type" =>  "ECOMM_PURCHASE", // Optional - Defaults to ECOMM_PURCHASE. If provided will default for all transactions except when explicitly mentioned
));

$PayFastAPI = new PayFastAPI($PayFastClient);
?>
```
## Create Checkout Link

```php
<?php
try {
    $data = array(
        "TXNAMT" => 5000,
        "BASKET_ID" => "", // Optional - Will generate unique ID if not provided
        "currency_code" =>  "PKR", // Optional - Will use one set during initializing
        "success_url" =>  "https://domain.com/success", // Optional - Will use one set during initializing
        "cancel_url" =>  "https://domain.com/success", // Optional - Will use one set during initializing
        "checkout_url" =>  "https://domain.com/success", // Optional - Will use one set during initializing
        "customer_email" => "test@test.com",
        "customer_phone" => "+921234567899",
        "order_date" => "2023-12-01 12:00:00", // Optional - Will use date(Y-m-d H:i:s) if not provided
        "proccode" => "00", // Optional - will use one set during initializing
        "tran_type" => "ECOMM_PURCHASE", // Optional - will use one set during initializing
    );
    $response_type = "redirect"; // form / redirect / data - Defaults to redirect, Redirect will automatically redirect user to payment page, form will return an HTML form ready for submission, data will return array with all values
    $response = $PayFastAPI->createCheckoutLink($data, $response_type);
    return $response;
} catch (ShaqiLabs\PayFast\PayFastException $e) {
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
        "TXNAMT" => 5000,
        "BASKET_ID" => "", // Optional - Will generate unique ID if not provided
        "currency_code" =>  "PKR", // Optional - Will use one set during initializing
        "success_url" =>  "https://domain.com/success", // Optional - Will use one set during initializing
        "cancel_url" =>  "https://domain.com/success", // Optional - Will use one set during initializing
        "checkout_url" =>  "https://domain.com/success", // Optional - Will use one set during initializing
        "customer_email" => "test@test.com",
        "customer_phone" => "+921234567899",
        "order_date" => "2023-12-01 12:00:00", // Optional - Will use date(Y-m-d H:i:s) if not provided
        "proccode" => "00", // Optional - will use one set during initializing
        "tran_type" => "ECOMM_PURCHASE", // Optional - will use one set during initializing
    );
    $response_type = "data"; // form / redirect / data - Defaults to redirect, Redirect will automatically redirect user to payment page, form will return an HTML form ready for submission, data will return array with all values
    $response = $PayFastAPI->createCheckoutLink($data, $response_type);
    return $response;
} catch (ShaqiLabs\PayFast\PayFastException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
```


### Step 2 - Call Dynamic Checkout

```php
<?php
try {
    $PayFastAPI->dynamicRedirect($response);
    return;
} catch (ShaqiLabs\PayFast\PayFastException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
```


### Complete Example 

```php
<?php
try {
        $data = array(
            "TXNAMT" => 5000,
            "BASKET_ID" => "", // Optional - Will generate unique ID if not provided
            "currency_code" =>  "PKR", // Optional - Will use one set during initializing
            "success_url" =>  "https://domain.com/success", // Optional - Will use one set during initializing
            "cancel_url" =>  "https://domain.com/success", // Optional - Will use one set during initializing
            "checkout_url" =>  "https://domain.com/success", // Optional - Will use one set during initializing
            "customer_email" => "test@test.com",
            "customer_phone" => "+921234567899",
            "order_date" => "2023-12-01 12:00:00", // Optional - Will use date(Y-m-d H:i:s) if not provided
            "proccode" => "00", // Optional - will use one set during initializing
            "tran_type" => "ECOMM_PURCHASE", // Optional - will use one set during initializing
        );
        $response_type = "data"; // form / redirect / data - Defaults to redirect, Redirect will automatically redirect user to payment page, form will return an HTML form ready for submission, data will return array with all values
        $response = $PayFastAPI->createCheckoutLink($data, $response_type);

        //Use $response data

        $PayFastAPI->dynamicRedirect($response);
        return;
    } catch (ShaqiLabs\PayFast\PayFastException $e) {
        echo "Error: " . $e->getMessage() . "\n";
    }
?>
```
## Get Form Fields

Get Form Fields allows you to easily get and customize form fields.


| Field Name | Type | Default Value | Field Type | Options/Info |
| -------- | ------- | ------- | ------- | ------- |
|TXNAMT | Number | - | Required | The Amount of the transaction  |
|BASKET_ID | Number | - | Optional | Will generate unique ID if not provided |
|currency_code | Number | - | Optional | Will use one set during initializing |
|success_url | Number | - | Optional | Will use one set during initializing |
|cancel_url | Number | - | Optional | Will use one set during initializing |
|checkout_url | Number | - | Optional | Will use one set during initializing |
|customer_email | Number | - | Required | Customer's Email Address |
|customer_phone | Number | - | Required | Customer's Phone Number |
|order_date | Number | - | Optional | Will use date(Y-m-d H:i:s) if not provided |
|proccode | Number | - | Optional | will use one set during initializing |
|tran_type | Number | - | Optional | will use one set during initializing |

```php
<?php

try { 
    $config = array(
        "response" => "form",
        "label_class" => "form-label",
        "input_class" => "form-control",
        "wrappers" => array(
            "CUSTOMER_EMAIL_ADDRESS" => array(
                "input_wrapper_start" => '<div class="mb-3 col-md-6">',
                "input_wrapper_end" => "</div>"
            ),
            "CUSTOMER_MOBILE_NO" => array(
                "input_wrapper_start" => '<div class="mb-3 col-md-6">',
                "input_wrapper_end" => "</div>"
            ),
            "TXNAMT" => array(
                "input_wrapper_start" => '<div class="mb-3 col-md-6">',
                "input_wrapper_end" => "</div>"
            ),
            "BASKET_ID" => array(
                "input_wrapper_start" => '<div class="mb-3 col-md-6">',
                "input_wrapper_end" => "</div>"
            ),
            "ORDER_DATE" => array(
                "input_wrapper_start" => '<div class="mb-3 col-md-6">',
                "input_wrapper_end" => "</div>"
            ),
        ),
        "optional" => false,
        "optional_selective" => array(
        ),
    );
    $response = $PayFastAPI->getFormFields($config);
    return $response;
} catch (ShaqiLabs\PayFast\PayFastException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

?>
```

### Customize Form Fields

All fields of the form can be customized using the following syntax. Pass these keys along with the value in the Config.


| Field Name | Format | Example | Options/Info |
| -------- | ------- | ------- | ------- |
|Classess | {field_name}-class | TXNAMT-class | Add classess to the input field |
|Attributes | {field_name}-attr | TXNAMT-attr | Add custom attributes to the input field |
|Wrappers | {field_name}-wrapper | TXNAMT-wrapper | Add custom html element types to the input field. For example '<div>' or '<custom>' |
|Labels | {field_name}-label | TXNAMT-label | Add custom labels to the input field |
|Default Value | {field_name} | TXNAMT | Add a default value to the input field |
|Input Wrappers | wrappers | - | Add a custom wrappers to the entire input and label field element. For example, wrap everything within a <div> |
|Label Class | label_class | - | Add classess to the label field |
|Sort Order | sort_order | sort_order[] | An array with field names, any missing items will use default order after the defined order |
|Custom Options | custom_options | custom_options[] | An array with label and value keys. Only applicable to select fields |
|Optional Fields | optional | optional | Enable/Disable optional fields. true/false |
|Selective Optional Fields | optional_selective[] | optional_selective[] | Enable/Disable only certain optional fields. An array of optional field names to enable |

#### Customize Form Fields - Classess

```php
<?php

try {
    $config = array(
        "BASKET_ID-class" => "custom_class",
    );
    $response = $PayFastAPI->getFormFields($config);
    return $response;
} catch (ShaqiLabs\PayFast\PayFastException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

?>
```
#### Customize Form Fields - Attributes

```php
<?php

try {
    $config = array(
        "TXNAMT-attr" => "step='0.00' min='0'",
    );
    $response = $PayFastAPI->getFormFields($config);
    return $response;
} catch (ShaqiLabs\PayFast\PayFastException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

?>
```

#### Customize Form Fields - Wrappers

```php
<?php

try {
    $config = array(
        "checkout_url-wrapper" => "textarea",
    );
    $response = $PayFastAPI->getFormFields($config);
    return $response;
} catch (ShaqiLabs\PayFast\PayFastException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

?>
```

#### Customize Form Fields - Labels

```php
<?php

try {
    $config = array(
        "TXNAMT" => "Transaction Amount",
    );
    $response = $PayFastAPI->getFormFields($config);
    return $response;
} catch (ShaqiLabs\PayFast\PayFastException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

?>
```

#### Customize Form Fields - Default Value

```php
<?php

try {
    $config = array(
        "TXNAMT" => "1000",
    );
    $response = $PayFastAPI->getFormFields($config);
    return $response;
} catch (ShaqiLabs\PayFast\PayFastException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

?>
```

#### Customize Form Fields - Input Wrappers

```php
<?php

try {
    $config = array(
        "wrappers" => array(
            "CUSTOMER_EMAIL_ADDRESS" => array(
                "input_wrapper_start" => '<div class="mb-3 col-md-6">',
                "input_wrapper_end" => "</div>"
            ),
            "CUSTOMER_MOBILE_NO" => array(
                "input_wrapper_start" => '<div class="mb-3 col-md-6">',
                "input_wrapper_end" => "</div>"
            ),
        )
    );
    $response = $PayFastAPI->getFormFields($config);
    return $response;
} catch (ShaqiLabs\PayFast\PayFastException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
```


#### Customize Form Fields - Label Class


```php
<?php

try {
    $config = array(
        "label_class" => "form-label",
    );
    $response = $PayFastAPI->getFormFields($config);
    return $response;
} catch (ShaqiLabs\PayFast\PayFastException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

?>
```

#### Example Configuration


```php
<?php
try {
    $data = array(
        "TXNAMT" => 5000,
        "BASKET_ID" => "", // Optional - Will generate unique ID if not provided
        "currency_code" =>  "PKR", // Optional - Will use one set during initializing
        "success_url" =>  "https://domain.com/success", // Optional - Will use one set during initializing
        "cancel_url" =>  "https://domain.com/success", // Optional - Will use one set during initializing
        "checkout_url" =>  "https://domain.com/success", // Optional - Will use one set during initializing
        "customer_email" => "test@test.com",
        "customer_phone" => "+921234567899",
        "order_date" => "2023-12-01 12:00:00", // Optional - Will use date(Y-m-d H:i:s) if not provided
        "proccode" => "00", // Optional - will use one set during initializing
        "tran_type" => "ECOMM_PURCHASE", // Optional - will use one set during initializing
    );
    $response_type = "redirect"; // form / redirect / data - Defaults to redirect, Redirect will automatically redirect user to payment page, form will return an HTML form ready for submission, data will return array with all values
    $response = $PayFastAPI->createCheckoutLink($data, $response_type);
    return $response;
} catch (ShaqiLabs\PayFast\PayFastException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
```

#### Customize Form Fields - Sort Order

```php
<?php

try {
    $config = array(
        "sort_order" => array(
            "currency_code",
            "TXNAMT",
        )
    );
    $response = $PayFastAPI->getFormFields($config);
    return $response;
} catch (ShaqiLabs\PayFast\PayFastException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
```

#### Customize Form Fields - Custom Options

```php
<?php

try {
    $config = array(
        "custom_options" => array(
            "field_name" => array(
                array(
                    "label" => "Label",
                    "value" => "Value"
                )
            )
        )
    );
    $response = $PayFastAPI->getFormFields($config);
    return $response;
} catch (ShaqiLabs\PayFast\PayFastException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
```

#### Customize Form Fields - Optional Fields

```php
<?php

try {
    $config = array(
        "optional" => false
    );
    $response = $PayFastAPI->getFormFields($config);
    return $response;
} catch (ShaqiLabs\PayFast\PayFastException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
```

#### Customize Form Fields - Selective Optional Fields

```php
<?php

try {
    $config = array(
        "optional" => false,
        "optional_selective" => array(
            "currency_code",
        ),
    );
    $response = $PayFastAPI->getFormFields($config);
    return $response;
} catch (ShaqiLabs\PayFast\PayFastException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

?>
```
