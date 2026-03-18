
## Usage Guide - SafePay
## Table of Contents - SafePay Usage Guide
- [Initialize SafePay Client](#initialize)
- [Create Checkout Link](#create-checkout-link)
- [Verify Success Signature](#verify-success-signature)
- [Verify Webhook Signature](#verify-webhook-signature)
- [Get Form Fields](#get-form-fields)
## Initialize

If you declare a success or cancel url in the inialize stage it will use this value as default success/cancel url for all orders unless specified specifically in the create checkout url. URLs must be provided either during inialize stage or during checkout.

```php
<?php

require 'vendor/autoload.php';

use ShaqiLabs\SafePay\SafePayClient;
use ShaqiLabs\SafePay\SafePayAPI;

$SafePayClient = new SafePayClient(array(
    "environment" =>"sandbox",
    "apiKey" => "YOUR_API_KEY",
    "v1Secret" =>  "YOUR_V1_SECRET",
    "webhookSecret" =>  "YOUR_WEBHOOK_SECRET",
    "success_url" => "https://domain.com/success", // Optional
    "cancel_url" => "https://domain.com/cancel", // Optional
));
$SafePayAPI = new SafePayAPI($SafePayClient);
?>
```
## Create Checkout Link

```php
<?php
try {
    $data = array(
        "amount" => 5000,
        "order_id" => "TA-001", // Optional - Defaults to a unique ID
        "source" => "Tech Andaz", // Optional - Defaults to Pay Bridge
        "webhooks" => "true", // Optional - Defaults to false
    );
    $response = $SafePayAPI->createCheckoutLink($data);
    return $response;
} catch (ShaqiLabs\SafePay\SafePayException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
```
## Verify Success Signature

```php
<?php
try {
    $tracker = $_POST['tracker']; //Will be supplied by the redirect from Safe Pay
    $signature = $_POST['sig'];  //Will be supplied by the redirect from Safe Pay
    $response = $SafePayAPI->verifySuccessSignature($tracker, $signature);
    return $response;
} catch (ShaqiLabs\SafePay\SafePayException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
```
## Verify Webhook Signature

```php
<?php
try {
    $response = $SafePayAPI->verifyWebhookSignature();
    return $response;
} catch (ShaqiLabs\SafePay\SafePayException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
```
## Get Form Fields

Get Form Fields allows you to easily get and customize form fields.


| Field Name | Type | Default Value | Field Type | Options/Info |
| -------- | ------- | ------- | ------- | ------- |
|amount | Number | - | Required | The Amount of the Checkout |
|currency | Select | - | Optional | 3 Digit Country code, defaults to PKR |
|order_id | Text | - | Optional | Order ID, defaults to a unique ID |
|source | Text | - | Optional | The source of the transaction, defaults to Pay Bridge |
|webhooks | Select | - | Optional | Weather to enable or disable webhooks. Defaults to false |
|success_url | Text | - | Required | URL where user is to be redirected upon successful payment. Can be defined in createCheckoutLink data or during initialize. If defined in intiailize and provided in data. It will use the provided one in data.  |
|cancel_url | Text | - | Required | URL where user is to be redirected upon cancelling a payment. Can be defined in createCheckoutLink data or during initialize. If defined in intiailize and provided in data. It will use the provided one in data.  |

```php
<?php

try { 
    $config = array(
        "response" => "form",
        "label_class" => "form-label",
        "input_class" => "form-control",
        "wrappers" => array(
            "amount" => array(
                "input_wrapper_start" => '<div class="mb-3 col-md-6">',
                "input_wrapper_end" => "</div>"
            ),
            "order_id" => array(
                "input_wrapper_start" => '<div class="mb-3 col-md-6">',
                "input_wrapper_end" => "</div>"
            ),
            "source" => array(
                "input_wrapper_start" => '<div class="mb-3 col-md-6">',
                "input_wrapper_end" => "</div>"
            ),
            "webhooks" => array(
                "input_wrapper_start" => '<div class="mb-3 col-md-6">',
                "input_wrapper_end" => "</div>"
            ),
        ),
        "optional" => true,
        "optional_selective" => array(
        ),
    );
    $response = $SafePayAPI->getFormFields($config);
    return $response;
} catch (ShaqiLabs\SafePay\SafePayException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

?>
```

### Customize Form Fields

All fields of the form can be customized using the following syntax. Pass these keys along with the value in the Config.


| Field Name | Format | Example | Options/Info |
| -------- | ------- | ------- | ------- |
|Classess | {field_name}-class | amount-class | Add classess to the input field |
|Attributes | {field_name}-attr | amount-attr | Add custom attributes to the input field |
|Wrappers | {field_name}-wrapper | shipper_contact-wrapper | Add custom html element types to the input field. For example '<div>' or '<custom>' |
|Labels | {field_name}-label | order_id-label | Add custom labels to the input field |
|Default Value | {field_name} | source | Add a default value to the input field |
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
        "amount-class" => "custom_class",
    );
    $response = $SafePayAPI->getFormFields($config);
    return $response;
} catch (ShaqiLabs\SafePay\SafePayException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

?>
```
#### Customize Form Fields - Attributes

```php
<?php

try {
    $config = array(
        "amount-attr" => "step='0.00' min='0'",
    );
    $response = $SafePayAPI->getFormFields($config);
    return $response;
} catch (ShaqiLabs\SafePay\SafePayException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

?>
```

#### Customize Form Fields - Wrappers

```php
<?php

try {
    $config = array(
        "order_id-wrapper" => "textarea",
    );
    $response = $SafePayAPI->getFormFields($config);
    return $response;
} catch (ShaqiLabs\SafePay\SafePayException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

?>
```

#### Customize Form Fields - Labels

```php
<?php

try {
    $config = array(
        "webhooks" => "Mode of Payment",
    );
    $response = $SafePayAPI->getFormFields($config);
    return $response;
} catch (ShaqiLabs\SafePay\SafePayException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

?>
```

#### Customize Form Fields - Default Value

```php
<?php

try {
    $config = array(
        "source" => "Tech Andaz",
    );
    $response = $SafePayAPI->getFormFields($config);
    return $response;
} catch (ShaqiLabs\SafePay\SafePayException $e) {
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
            "webhooks" => array(
                "input_wrapper_start" => '<div class="mb-3 col-md-6">',
                "input_wrapper_end" => "</div>"
            ),
            "source" => array(
                "input_wrapper_start" => '<div class="mb-3 col-md-6">',
                "input_wrapper_end" => "</div>"
            ),
        )
    );
    $response = $SafePayAPI->getFormFields($config);
    return $response;
} catch (ShaqiLabs\SafePay\SafePayException $e) {
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
    $response = $SafePayAPI->getFormFields($config);
    return $response;
} catch (ShaqiLabs\SafePay\SafePayException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

?>
```

#### Example Configuration


```php
<?php
try { 
    $config = array(
        "response" => "form",
        "label_class" => "form-label",
        "input_class" => "form-control",
        "wrappers" => array(
            "amount" => array(
                "input_wrapper_start" => '<div class="mb-3 col-md-6">',
                "input_wrapper_end" => "</div>"
            ),
            "order_id" => array(
                "input_wrapper_start" => '<div class="mb-3 col-md-6">',
                "input_wrapper_end" => "</div>"
            ),
            "source" => array(
                "input_wrapper_start" => '<div class="mb-3 col-md-6">',
                "input_wrapper_end" => "</div>"
            ),
            "webhooks" => array(
                "input_wrapper_start" => '<div class="mb-3 col-md-6">',
                "input_wrapper_end" => "</div>"
            ),
        ),
        "optional" => true,
        "optional_selective" => array(
        ),
    );
    $response = $SafePayAPI->getFormFields($config);
    return $response;
} catch (ShaqiLabs\SafePay\SafePayException $e) {
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
            "amount",
            "source",
        )
    );
    $response = $SafePayAPI->getFormFields($config);
    return $response;
} catch (ShaqiLabs\SafePay\SafePayException $e) {
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
            "webhooks" => array(
                array(
                    "label" => "Not enabled",
                    "value" => "false"
                )
            )
        )
    );
    $response = $SafePayAPI->getFormFields($config);
    return $response;
} catch (ShaqiLabs\SafePay\SafePayException $e) {
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
    $response = $SafePayAPI->getFormFields($config);
    return $response;
} catch (ShaqiLabs\SafePay\SafePayException $e) {
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
            "source",
        ),
    );
    $response = $SafePayAPI->getFormFields($config);
    return $response;
} catch (ShaqiLabs\SafePay\SafePayException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

?>
```
