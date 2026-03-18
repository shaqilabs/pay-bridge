<?php

namespace ShaqiLabs\UBL;

class UBLAPI
{
    private $UBLClient;
    private $callback_url;

    public function __construct(UBLClient $UBLClient)
    {
        $this->UBLClient = $UBLClient;
    }

    /**
    * Create Checkout Link
    *
    * @return array
    *   Decoded response data.
    */
    public function createCheckoutLink($order)
    {
        if((!isset($order['Amount']) || $order['Amount'] == "")){
            throw new UBLException("Amount is missing.");
        }
        if (!is_numeric($order['Amount']) || filter_var($order['Amount'], FILTER_VALIDATE_FLOAT) === false) {
            throw new UBLException("Amount must be a number or float.");
        }
        if((!isset($order['OrderName']) || $order['OrderName'] == "")){
            throw new UBLException("Order Name is missing.");
        }
        if($this->UBLClient->callback_url == "" && (!isset($order['ReturnPath']) || $order['ReturnPath'] == "")){
            throw new UBLException("Return Path is missing. It can either be set once for all transactions or provided against each order or both.");
        }
        $order['Customer'] = (isset($order['Customer']) && $order['Customer'] != "") ? $order['Customer'] : $this->UBLClient->customer;
        $order['Store'] = (isset($order['Store']) && $order['Store'] != "") ? $order['Store'] : $this->UBLClient->store;
        $order['Terminal'] = (isset($order['Terminal']) && $order['Terminal'] != "") ? $order['Terminal'] : $this->UBLClient->terminal;
        $order['Channel'] = (isset($order['Channel']) && $order['Channel'] != "") ? $order['Channel'] : $this->UBLClient->channel;
        $order['Currency'] = (isset($order['Currency']) && $order['Currency'] != "") ? $order['Currency'] : $this->UBLClient->currency;
        $order['TransactionHint'] = (isset($order['TransactionHint']) && $order['TransactionHint'] != "") ? $order['TransactionHint'] : $this->UBLClient->transaction_hint;
        $order['OrderID'] = (isset($order['OrderID']) && $order['OrderID'] != "") ? $order['OrderID'] : uniqid();
        $order['ReturnPath'] = (isset($order['ReturnPath']) && $order['ReturnPath'] != "") ? $order['ReturnPath'] : $this->UBLClient->callback_url;
        $order['UserName'] = (isset($order['UserName']) && $order['UserName'] != "") ? $order['UserName'] : $this->UBLClient->username;
        $order['Password'] = (isset($order['Password']) && $order['Password'] != "") ? $order['Password'] : $this->UBLClient->password;
        $endpoint = '';
        $method = 'POST';
        $postData = array(
            "Registration" => $order
        );
        return $this->UBLClient->makeRequest($endpoint, $method, $postData);
        
    }
    public function dynamicRedirect($data){
        if((!isset($data['Transaction']['PaymentPage']))){
            throw new UBLException("There was an error getting payment page.");
        }
        if(headers_sent()){
            throw new UBLException("Unable to redirect because headers have already been sent.");
        }
        header('Location: '. $data['Transaction']['PaymentPage']);
        return;
    }
    function finalizePayment($transaction_id){
        $data = array(
            "TransactionID" => $transaction_id,
            "Customer" => $this->UBLClient->customer,
            "UserName" => $this->UBLClient->username,
            "Password" => $this->UBLClient->password,
        );
        $endpoint = '';
        $method = 'POST';
        $postData = array(
            "Finalization" => $data
        );
        return $this->UBLClient->makeRequest($endpoint, $method, $postData);
    }


    public function validateIdNameData($data)
    {
        if (!is_array($data)) {
            throw new UBLException('Invalid data structure. Each data must be an associative array.');
        }
        foreach ($data as $item) {
            // Check if the item is an array
            if (!is_array($item)) {
                throw new UBLException('Invalid data structure. Each item must be an associative array.');
            }

            // Check if the item contains only 'id' and 'name' keys
            $keys = array_keys($item);
            $allowedKeys = ['label', 'value'];
            if (count($keys) != count($allowedKeys)) {
                throw new UBLException('Invalid data structure. Each item must contain both "value" and "label" keys.');
            }
            if (count($keys) != count(array_intersect($keys, $allowedKeys))) {
                throw new UBLException('Invalid data structure. Each item must contain only "value" and "label" keys.');
            }
        }

        // Validation passed
        return true;
    }

    /**
    * Get Form Fields
    *
    * @return array
    *   Decoded response data.
    */
    public function getFormFields($config)
    {
        if(!isset($config['response']) || !in_array($config['response'], ["form", "json"])){
            throw new UBLException('Ivalid response type. Available: form, json');
        }
        if(!isset($config['optional'])){
            $config['optional'] = false;
        }
        $channels =  array(
            array(
                "label" => "Web",
                "value" => "Web"
            ),
            array(
                "label" => "Terminal",
                "value" => "Terminal"
            ),
            array(
                "label" => "POS",
                "value" => "POS"
            ),
            array(
                "label" => "Recurring",
                "value" => "Recurring"
            ),
            array(
                "label" => "Phone",
                "value" => "Phone"
            ),
            array(
                "label" => "Mail",
                "value" => "Mail"
            ),
            array(
                "label" => "USSD",
                "value" => "USSD"
            ),
            array(
                "label" => "MEC",
                "value" => "MEC"
            ),
        );
        $transaction_hint =  array(
            array(
                "label" => "Capture",
                "value" => "CPT:Y"
            ),
            array(
                "label" => "Authorize",
                "value" => "CPT:N"
            ),
        );
        $form_fields = array(
            array(
                "name" => "Customer",
                "field_type" => "optional",
                "classes" => isset($config['Customer-class']) ? $config['Customer-class'] : "",
                "attr" => isset($config['Customer-attr']) ? $config['Customer-attr'] : "",
                "wrapper" => isset($config['Customer-wrapper']) ? $config['Customer-wrapper'] : "",
                "label" => isset($config['Customer-label']) ? $config['Customer-label'] : "Customer",
                "type" => "text",
                "default" => isset($config['Customer']) ? $config['Customer'] : $this->UBLClient->customer,
            ),
            array(
                "name" => "Store",
                "field_type" => "optional",
                "classes" => isset($config['Store-class']) ? $config['Store-class'] : "",
                "attr" => isset($config['Store-attr']) ? $config['Store-attr'] : "",
                "wrapper" => isset($config['Store-wrapper']) ? $config['Store-wrapper'] : "",
                "label" => isset($config['Store-label']) ? $config['Store-label'] : "Store",
                "type" => "text",
                "default" => isset($config['Store']) ? $config['Store'] : $this->UBLClient->store,
            ),
            array(
                "name" => "Terminal",
                "field_type" => "optional",
                "classes" => isset($config['Terminal-class']) ? $config['Terminal-class'] : "",
                "attr" => isset($config['Terminal-attr']) ? $config['Terminal-attr'] : "",
                "wrapper" => isset($config['Terminal-wrapper']) ? $config['Terminal-wrapper'] : "",
                "label" => isset($config['Terminal-label']) ? $config['Terminal-label'] : "Terminal",
                "type" => "text",
                "default" => isset($config['Terminal']) ? $config['Terminal'] : $this->UBLClient->terminal,
            ),
            array(
                "name" => "Channel",
                "field_type" => "optional",
                "classes" => isset($config['Channel-class']) ? $config['Channel-class'] : "",
                "attr" => isset($config['Channel-attr']) ? $config['Channel-attr'] : "",
                "wrapper" => isset($config['Channel-wrapper']) ? $config['Channel-wrapper'] : "",
                "label" => isset($config['Channel-label']) ? $config['Channel-label'] : "Terminal",
                "type" => "select",
                "default" => isset($config['Channel']) && in_array($config['Channel'], array_column($channels, "label")) ? $config['Channel'] : $this->UBLClient->channel,
                "options" => $channels,
                "custom_options" => isset($config['Channel-custom_options']) ? $config['Channel-custom_options'] : array(),
            ),
            array(
                "name" => "Currency",
                "field_type" => "optional",
                "classes" => isset($config['Currency-class']) ? $config['Currency-class'] : "",
                "attr" => isset($config['Currency-attr']) ? $config['Currency-attr'] : "",
                "wrapper" => isset($config['Currency-wrapper']) ? $config['Currency-wrapper'] : "",
                "label" => isset($config['Currency-label']) ? $config['Currency-label'] : "Currency",
                "type" => "text",
                "default" => isset($config['Currency']) ? $config['Currency'] : $this->UBLClient->currency,
            ),
            array(
                "name" => "OrderID",
                "field_type" => "optional",
                "classes" => isset($config['OrderID-class']) ? $config['OrderID-class'] : "",
                "attr" => isset($config['OrderID-attr']) ? $config['OrderID-attr'] : "",
                "wrapper" => isset($config['OrderID-wrapper']) ? $config['OrderID-wrapper'] : "",
                "label" => isset($config['OrderID-label']) ? $config['OrderID-label'] : "Order ID",
                "type" => "text",
                "default" => isset($config['OrderID']) ? $config['OrderID'] : "",
            ),
            array(
                "name" => "OrderInfo",
                "field_type" => "optional",
                "classes" => isset($config['OrderInfo-class']) ? $config['OrderInfo-class'] : "",
                "attr" => isset($config['OrderInfo-attr']) ? $config['OrderInfo-attr'] : "",
                "wrapper" => isset($config['OrderInfo-wrapper']) ? $config['OrderInfo-wrapper'] : "",
                "label" => isset($config['OrderInfo-label']) ? $config['OrderInfo-label'] : "Order Info",
                "type" => "text",
                "default" => isset($config['OrderInfo']) ? $config['OrderInfo'] : "",
            ),
            array(
                "name" => "TransactionHint",
                "field_type" => "optional",
                "classes" => isset($config['TransactionHint-class']) ? $config['TransactionHint-class'] : "",
                "attr" => isset($config['TransactionHint-attr']) ? $config['TransactionHint-attr'] : "",
                "wrapper" => isset($config['TransactionHint-wrapper']) ? $config['TransactionHint-wrapper'] : "",
                "label" => isset($config['TransactionHint-label']) ? $config['TransactionHint-label'] : "Transaction Type",
                "type" => "select",
                "default" => isset($config['TransactionHint']) && in_array($config['TransactionHint'], array_column($transaction_hint, "label")) ? $config['TransactionHint'] : $this->UBLClient->transaction_hint,
                "options" => $transaction_hint,
                "custom_options" => isset($config['TransactionHint-custom_options']) ? $config['TransactionHint-custom_options'] : array(),
            ),
            array(
                "name" => "ReturnPath",
                "field_type" => "optional",
                "classes" => isset($config['ReturnPath-class']) ? $config['ReturnPath-class'] : "",
                "attr" => isset($config['ReturnPath-attr']) ? $config['ReturnPath-attr'] : "",
                "wrapper" => isset($config['ReturnPath-wrapper']) ? $config['ReturnPath-wrapper'] : "",
                "label" => isset($config['ReturnPath-label']) ? $config['ReturnPath-label'] : "Call Back URL",
                "type" => "text",
                "default" => isset($config['ReturnPath']) ? $config['ReturnPath'] : $this->UBLClient->callback_url,
            ),
            array(
                "name" => "UserName",
                "field_type" => "optional",
                "classes" => isset($config['UserName-class']) ? $config['UserName-class'] : "",
                "attr" => isset($config['UserName-attr']) ? $config['UserName-attr'] : "",
                "wrapper" => isset($config['UserName-wrapper']) ? $config['UserName-wrapper'] : "",
                "label" => isset($config['UserName-label']) ? $config['UserName-label'] : "Username",
                "type" => "text",
                "default" => isset($config['UserName']) ? $config['UserName'] : $this->UBLClient->username,
            ),
            array(
                "name" => "Password",
                "field_type" => "optional",
                "classes" => isset($config['Password-class']) ? $config['Password-class'] : "",
                "attr" => isset($config['Password-attr']) ? $config['Password-attr'] : "",
                "wrapper" => isset($config['Password-wrapper']) ? $config['Password-wrapper'] : "",
                "label" => isset($config['Password-label']) ? $config['Password-label'] : "Password",
                "type" => "password",
                "default" => isset($config['Password']) ? $config['Password'] : $this->UBLClient->password,
            ),
            array(
                "name" => "Amount",
                "field_type" => "required",
                "classes" => isset($config['Amount-class']) ? $config['Amount-class'] : "",
                "attr" => isset($config['Amount-attr']) ? $config['Amount-attr'] : "",
                "wrapper" => isset($config['Amount-wrapper']) ? $config['Amount-wrapper'] : "",
                "label" => isset($config['Amount-label']) ? $config['Amount-label'] : "Amount",
                "type" => "number",
                "default" => isset($config['Amount']) ? $config['Amount'] : "0",
            ),
            array(
                "name" => "OrderName",
                "field_type" => "required",
                "classes" => isset($config['OrderName-class']) ? $config['OrderName-class'] : "",
                "attr" => isset($config['OrderName-attr']) ? $config['OrderName-attr'] : "",
                "wrapper" => isset($config['OrderName-wrapper']) ? $config['OrderName-wrapper'] : "",
                "label" => isset($config['OrderName-label']) ? $config['OrderName-label'] : "Order Name",
                "type" => "text",
                "default" => isset($config['OrderName']) ? $config['OrderName'] : "",
            ),
        );
        if(isset($config["sort_order"])){
            $sorted_fields = $config["sort_order"];
            $sortedArray = array();
            foreach ($sorted_fields as $key) {
                foreach ($form_fields as $item) {
                    if ($item['name'] === $key) {
                        $sortedArray[] = $item;
                        break;
                    }
                }
            }
            foreach ($form_fields as $item) {
                if (!in_array($item['name'], $sorted_fields)) {
                    $sortedArray[] = $item;
                }
            }
            $form_fields = $sortedArray;
        }
        if($config['response'] == "form"){
            return $this->getForm($form_fields, $config);
        } else {
            return $form_fields;
        }
    }
    public function getField($form_fields, $config, $field){
        $form_html = "";
        $label_class = isset($config['label_class']) ? $config['label_class'] : "";
        $input_class = isset($config['input_class']) ? $config['input_class'] : "";
        if($field['field_type'] == "optional"){
            if($config['optional'] == false && !in_array($field['name'], $config['optional_selective'])){
                return "";
            }
        }
        if(isset($config['wrappers'][$field['name']]['input_wrapper_start'])){
            $form_html .= $config['wrappers'][$field['name']]['input_wrapper_start'];
        }
        $form_html .= '<label class="' . $label_class . '" for="' . $field['name'] . '">' . $field['label'] . '</label>';
        $wrapper_data = "name='" . $field['name'] . "' " . " class='" . $input_class . " " . $field['classes'] . "' " . $field['attr'] . " " . $field['field_type'] . " placeholder='" . $field['label'] . "'";
        if($field['type'] == "select"){
            $wrapper = "select";
            if($field['wrapper'] != ""){
                $wrapper = $field['wrapper'];
            }
            $options_html = "";
            foreach($field['options'] as $option){
                $selected = "";
                if($field['default'] == $option['label']){
                    $selected = "selected";
                }
                $options_html .= '<option ' . $selected . ' value = "' . $option['value'] . '">' . $option['label'] . '</option>';
            }
            $form_html .= '<' . $wrapper . ' ' . $wrapper_data . '>' . $options_html . '</' . $wrapper . '>';
        } else if($field['type'] == "text"){
            $wrapper = "input";
            if($field['wrapper'] != ""){
                $wrapper = $field['wrapper'];
            }
            $form_html .= '<' . $wrapper . ' type = "text" ' . $wrapper_data . ' value = "' . $field['default'] . '"></' . $wrapper . '>';
        } else if($field['type'] == "phone"){
            $wrapper = "input";
            if($field['wrapper'] != ""){
                $wrapper = $field['wrapper'];
            }
            $form_html .= '<' . $wrapper . ' type = "text" ' . $wrapper_data . ' value = "' . $field['default'] . '"></' . $wrapper . '>';
        } else if($field['type'] == "email"){
            $wrapper = "input";
            if($field['wrapper'] != ""){
                $wrapper = $field['wrapper'];
            }
            $form_html .= '<' . $wrapper . ' type = "email" ' . $wrapper_data . ' value = "' . $field['default'] . '"></' . $wrapper . '>';
        } else if($field['type'] == "number"){
            $wrapper = "input";
            if($field['wrapper'] != ""){
                $wrapper = $field['wrapper'];
            }
            $form_html .= '<' . $wrapper . ' type = "number" ' . $wrapper_data . ' value = "' . $field['default'] . '"></' . $wrapper . '>';
        } else if($field['type'] == "date"){
            $wrapper = "input";
            if($field['wrapper'] != ""){
                $wrapper = $field['wrapper'];
            }
            $form_html .= '<' . $wrapper . ' type = "date" ' . $wrapper_data . ' value = "' . $field['default'] . '"></' . $wrapper . '>';
        } else if($field['type'] == "password"){
            $wrapper = "input";
            if($field['wrapper'] != ""){
                $wrapper = $field['wrapper'];
            }
            $form_html .= '<' . $wrapper . ' type = "password" ' . $wrapper_data . ' value = "' . $field['default'] . '"></' . $wrapper . '>';
        } else if($field['type'] == "textarea"){
            $wrapper = "textarea";
            if($field['wrapper'] != ""){
                $wrapper = $field['wrapper'];
            }
            $form_html .= '<' . $wrapper . ' ' . $wrapper_data . '>' . $field['default'] . '</' . $wrapper . '>';
        }
        if(isset($config['wrappers'][$field['name']]['input_wrapper_end'])){
            $form_html .= $config['wrappers'][$field['name']]['input_wrapper_end'];
        }
        return $form_html;
    }
    public function getForm($form_fields, $config){
        $form_html = "";
        if(!isset($config['optional_selective']) || !is_array($config['optional_selective'])){
            $config['optional_selective'] = array();
        }
        //row
        foreach($form_fields as $field){
            if($field['type'] == "row"){
                
                if(isset($config['wrappers'][$field['name']]['input_wrapper_start'])){
                    $form_html .= $config['wrappers'][$field['name']]['input_wrapper_start'];
                }
                foreach($field['row_fields'] as $row_field){
                    $form_html .= $this->getField($field['row_fields'], $config, $row_field);
                }
                if(isset($config['wrappers'][$field['name']]['input_wrapper_end'])){
                    $form_html .= $config['wrappers'][$field['name']]['input_wrapper_end'];
                }
            } else {
                $form_html .= $this->getField($form_fields, $config, $field);
            }
        }
        return $form_html;
    }
}
