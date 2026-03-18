
## Usage Guide - UBL
## Table of Contents - UBL Usage Guide
- [Initialize UBL Client](#initialize)
- [Create Checkout Link](#create-checkout-link)
- [Redirect to Checkout Link](#redirect-to-checkout-link)
- [Finalize Payment](#finalize-payment)
- [Get Form Fields](#get-form-fields)
## Initialize

Certain keys can be set as defaults during initialize stage. You can set them here or manually specify during createCheckoutLink. If you specify in createCheckoutLink the default key will be overridden for that transaction.

```php
<?php

require 'vendor/autoload.php';

use ShaqiLabs\UBL\UBLClient;
use ShaqiLabs\UBL\UBLAPI;

$UBLClient = new UBLClient(array(
    "api_url" =>"https://demo-ipg.ctdev.comtrust.ae:2443/", // Optional - Defaults to Production URL
    "customer" => "Demo Merchant", // Optional - Defaults to Pay Bridge
    "store" =>  "0000", // Optional - Defaults to 0000
    "terminal" =>  "0000", // Optional - Defaults to 0000
    "channel" =>  "Web", // Optional - Defaults to Web
    "currency" =>  "PKR", // Optional - Defaults to PKR
    "transaction_hint" =>  "CPT:Y", // Optional - Defaults to CPT:Y. Options are: CPT:N (Authorize, capture to be called seperate). CPT:Y (Capture)
    "callback_url" =>  "https://domain.com/success", // Required/Optional - Must be provided either during initialize or during checkout link creation.
    "username" => "Demo_fY9c",
    "password" => "Comtrust@20182018",
));
$UBLAPI = new UBLAPI($UBLClient);
?>
```
## Create Checkout Link

```php
<?php
try {
    $data = array(
        "Customer" => "", // Optional - Will use default or value set during initialize. Will override if provided
        "Store" => "", // Optional - Will use default or value set during initialize. Will override if provided
        "Terminal" => "", // Optional - Will use default or value set during initialize. Will override if provided
        "Channel" => "", // Optional - Will use default or value set during initialize. Will override if provided
        "Currency" => "", // Optional - Will use default or value set during initialize. Will override if provided
        "OrderID" => "", // Optional - Will generate unique ID if not provided
        "OrderInfo" => "", // Optional
        "TransactionHint" => "", // Optional - Will use default or value set during initialize. Will override if provided
        "ReturnPath" => "", // Required / Optional - Will use value set during initialize if not provided. If both not provided will throw error
        "UserName" => "", // Optional - Will use value provided during initialize or over ride if provided
        "Password" => "", // Optional - Will use value provided during initialize or over ride if provided
        "Amount" => 5000,
        "OrderName" => "Order from Tech Andaz",
    );
    $response = $UBLAPI->createCheckoutLink($data);
    return $response;
} catch (ShaqiLabs\UBL\UBLException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
```

## Redirect to Checkout Link

```php
<?php
try {
    $data = array(
        "Customer" => "", // Optional - Will use default or value set during initialize. Will override if provided
        "Store" => "", // Optional - Will use default or value set during initialize. Will override if provided
        "Terminal" => "", // Optional - Will use default or value set during initialize. Will override if provided
        "Channel" => "", // Optional - Will use default or value set during initialize. Will override if provided
        "Currency" => "", // Optional - Will use default or value set during initialize. Will override if provided
        "OrderID" => "", // Optional - Will generate unique ID if not provided
        "OrderInfo" => "", // Optional
        "TransactionHint" => "", // Optional - Will use default or value set during initialize. Will override if provided
        "ReturnPath" => "", // Required / Optional - Will use value set during initialize if not provided. If both not provided will throw error
        "UserName" => "", // Optional - Will use value provided during initialize or over ride if provided
        "Password" => "", // Optional - Will use value provided during initialize or over ride if provided
        "Amount" => 5000,
        "OrderName" => "Order from Tech Andaz",
    );
    $response = $UBLAPI->createCheckoutLink($data);
    $UBLAPI->dynamicRedirect($response);
    return;
} catch (ShaqiLabs\UBL\UBLException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
```
## Finalize Payment

```php
<?php
try {
    return $UBLAPI->finalizePayment("261807270380");
} catch (ShaqiLabs\UBL\UBLException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
```

## Get Form Fields

Get Form Fields allows you to easily get and customize form fields.


| Field Name | Type | Default Value | Field Type | Options/Info |
| -------- | ------- | ------- | ------- | ------- |
|Amount | Number | - | Required | The Amount of the Checkout |
|OrderName | Number | - | Required | The Name of the Checkout |
|Customer | Number | - | Optional | Will use default or value set during initialize. Will override if provided |
|Store | Select | - | Optional | Will use default or value set during initialize. Will override if provided |
|Terminal | Text | - | Optional | Will use default or value set during initialize. Will override if provided |
|Channel | Text | - | Optional | Will use default or value set during initialize. Will override if provided|
|Currency | Select | - | Optional | Will use default or value set during initialize. Will override if provided |
|OrderID | Number | - | Optional | Will generate unique ID if not provided |
|OrderInfo | Number | - | Optional | Order Description |
|TransactionHint | Number | - | Optional | Will use default or value set during initialize. Will override if provided. Options are: CPT:N (Authorize, capture to be called seperate). CPT:Y (Capture) |
|ReturnPath | Number | - | Required / Optional | Must be provided either during initialize or during checkout link creation. |
|UserName | Number | - | Optional | Will use value provided during initialize or over ride if provided |
|Password | Number | - | Optional | Will use value provided during initialize or over ride if provided |

```php
<?php

try { 
    $config = array(
        "response" => "form",
        "label_class" => "form-label",
        "input_class" => "form-control",
        "wrappers" => array(
            "Amount" => array(
                "input_wrapper_start" => '<div class="mb-3 col-md-6">',
                "input_wrapper_end" => "</div>"
            ),
            "OrderName" => array(
                "input_wrapper_start" => '<div class="mb-3 col-md-6">',
                "input_wrapper_end" => "</div>"
            ),
        ),
        "optional" => false,
        "optional_selective" => array(
        ),
    );
    $response = $UBLAPI->getFormFields($config);
    return $response;
} catch (ShaqiLabs\UBL\UBLException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

?>
```

### Customize Form Fields

All fields of the form can be customized using the following syntax. Pass these keys along with the value in the Config.


| Field Name | Format | Example | Options/Info |
| -------- | ------- | ------- | ------- |
|Classess | {field_name}-class | Amount-class | Add classess to the input field |
|Attributes | {field_name}-attr | Amount-attr | Add custom attributes to the input field |
|Wrappers | {field_name}-wrapper | Amount-wrapper | Add custom html element types to the input field. For example '<div>' or '<custom>' |
|Labels | {field_name}-label | Amount-label | Add custom labels to the input field |
|Default Value | {field_name} | Amount | Add a default value to the input field |
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
        "Amount-class" => "custom_class",
    );
    $response = $UBLAPI->getFormFields($config);
    return $response;
} catch (ShaqiLabs\UBL\UBLException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

?>
```
#### Customize Form Fields - Attributes

```php
<?php

try {
    $config = array(
        "Amount-attr" => "step='0.00' min='0'",
    );
    $response = $UBLAPI->getFormFields($config);
    return $response;
} catch (ShaqiLabs\UBL\UBLException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

?>
```

#### Customize Form Fields - Wrappers

```php
<?php

try {
    $config = array(
        "Terminal-wrapper" => "textarea",
    );
    $response = $UBLAPI->getFormFields($config);
    return $response;
} catch (ShaqiLabs\UBL\UBLException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

?>
```

#### Customize Form Fields - Labels

```php
<?php

try {
    $config = array(
        "Customer" => "Client",
    );
    $response = $UBLAPI->getFormFields($config);
    return $response;
} catch (ShaqiLabs\UBL\UBLException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

?>
```

#### Customize Form Fields - Default Value

```php
<?php

try {
    $config = array(
        "Store" => "1000",
    );
    $response = $UBLAPI->getFormFields($config);
    return $response;
} catch (ShaqiLabs\UBL\UBLException $e) {
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
            "Amount" => array(
                "input_wrapper_start" => '<div class="mb-3 col-md-6">',
                "input_wrapper_end" => "</div>"
            ),
            "OrderName" => array(
                "input_wrapper_start" => '<div class="mb-3 col-md-6">',
                "input_wrapper_end" => "</div>"
            ),
        )
    );
    $response = $UBLAPI->getFormFields($config);
    return $response;
} catch (ShaqiLabs\UBL\UBLException $e) {
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
    $response = $UBLAPI->getFormFields($config);
    return $response;
} catch (ShaqiLabs\UBL\UBLException $e) {
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
            "Amount" => array(
                "input_wrapper_start" => '<div class="mb-3 col-md-6">',
                "input_wrapper_end" => "</div>"
            ),
            "OrderName" => array(
                "input_wrapper_start" => '<div class="mb-3 col-md-6">',
                "input_wrapper_end" => "</div>"
            ),
        ),
        "optional" => false,
        "optional_selective" => array(
        ),
    );
    $response = $UBLAPI->getFormFields($config);
    return $response;
} catch (ShaqiLabs\UBL\UBLException $e) {
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
            "OrderName",
            "Amount",
        )
    );
    $response = $UBLAPI->getFormFields($config);
    return $response;
} catch (ShaqiLabs\UBL\UBLException $e) {
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
            "TransactionHint" => array(
                array(
                    "label" => "Authorize",
                    "value" => "CPT:N"
                )
            )
        )
    );
    $response = $UBLAPI->getFormFields($config);
    return $response;
} catch (ShaqiLabs\UBL\UBLException $e) {
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
    $response = $UBLAPI->getFormFields($config);
    return $response;
} catch (ShaqiLabs\UBL\UBLException $e) {
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
            "ReturnPath",
        ),
    );
    $response = $UBLAPI->getFormFields($config);
    return $response;
} catch (ShaqiLabs\UBL\UBLException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

?>
```
