<?php

namespace ShaqiLabs\SafePay;

class SafePayAPI
{
    private $SafePayClient;
    private $success_url;
    private $cancel_url;

    public function __construct(SafePayClient $SafePayClient)
    {
        $this->SafePayClient = $SafePayClient;
    }

    /**
    * Create Checkout Link
    *
    * @return array
    *   Decoded response data.
    */
    public function createCheckoutLink($order)
    {
        if($this->SafePayClient->success_url == "" && (!isset($order['success_url']) || $order['success_url'] == "")){
            throw new SafePayException("Success URL is missing. It can either be set once for all transactions or provided against each order or both.");
        }
        if($this->SafePayClient->cancel_url == "" && (!isset($order['cancel_url']) || $order['cancel_url'] == "")){
            throw new SafePayException("Cancel URL is missing. It can either be set once for all transactions or provided against each order or both.");
        }
        $this->success_url = (isset($order['success_url']) && $order['success_url'] != "") ? $order['success_url'] : $this->SafePayClient->success_url;
        $this->cancel_url = (isset($order['cancel_url']) && $order['cancel_url'] != "") ? $order['cancel_url'] : $this->SafePayClient->cancel_url;

        if((!isset($order['amount']) || $order['amount'] == "")){
            throw new SafePayException("Amount is missing.");
        }
        if (!is_numeric($order['amount']) || filter_var($order['amount'], FILTER_VALIDATE_FLOAT) === false) {
            throw new SafePayException("Amount must be a number or float.");
        }
        $order['currency'] = (isset($order['currency']) && $order['currency'] != "") ? $order['currency'] : "PKR";
        $order['order_id'] = (isset($order['order_id']) && $order['order_id'] != "") ? $order['order_id'] : uniqid();
        $order['source'] = (isset($order['source']) && $order['source'] != "") ? $order['source'] : "Pay Bridge";
        $order['webhooks'] = (isset($order['webhooks']) && $order['webhooks'] != "") ? $order['webhooks'] : "false";

        $token = $this->SafePayClient->getToken($order['amount'], $order['currency']);
        $link = $this->SafePayClient->getCheckoutLink([
            "token" => $token,
            "order_id" => $order['order_id'],
            "source"=> $order['source'],
            "webhooks"=> $order['webhooks'],
            "success_url" => $this->success_url,
            "cancel_url" => $this->cancel_url
        ]);
        return $link;
    }

    /**
    * Verify Success Signature
    *
    * @return array
    *   Decoded response data.
    */
    public function verifySuccessSignature($tracker, $signature)
    {
        if($tracker == ""){
            throw new SafePayException("Tracker can not be empty.");
        }
        if($signature == ""){
            throw new SafePayException("Signature can not be empty.");
        }
        if($this->SafePayClient->verifySuccessSignature($tracker,$signature)  === true) {
            return true;
        }
        return false;
    }

    /**
    * Verify Webhook Signature
    *
    * @return array
    *   Decoded response data.
    */
    public function verifyWebhookSignature()
    {
        $X_SFPY_SIGNATURE = @$_SERVER['HTTP_X_SFPY_SIGNATURE'];
        $data = file_get_contents('php://input');
        if($this->SafePayClient->verifyWebhookSignature($data, $X_SFPY_SIGNATURE)  === true) {
            return array(
                "status" => 1,
                "data" => json_decode($data,true)
            );
        }
        return false;
    }
    

    public function validateIdNameData($data)
    {
        if (!is_array($data)) {
            throw new SafePayException('Invalid data structure. Each data must be an associative array.');
        }
        foreach ($data as $item) {
            // Check if the item is an array
            if (!is_array($item)) {
                throw new SafePayException('Invalid data structure. Each item must be an associative array.');
            }

            // Check if the item contains only 'id' and 'name' keys
            $keys = array_keys($item);
            $allowedKeys = ['label', 'value'];
            if (count($keys) != count($allowedKeys)) {
                throw new SafePayException('Invalid data structure. Each item must contain both "value" and "label" keys.');
            }
            if (count($keys) != count(array_intersect($keys, $allowedKeys))) {
                throw new SafePayException('Invalid data structure. Each item must contain only "value" and "label" keys.');
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
            throw new SafePayException('Ivalid response type. Available: form, json');
        }
        if(!isset($config['optional'])){
            $config['optional'] = false;
        }
        $webhooks =  array(
            array(
                "label" => "True",
                "value" => "true"
            ),
            array(
                "label" => "False",
                "value" => "false"
            ),
        );
        $form_fields = array(
            array(
                "name" => "amount",
                "field_type" => "required",
                "classes" => isset($config['amount-class']) ? $config['amount-class'] : "",
                "attr" => isset($config['amount-attr']) ? $config['amount-attr'] : "",
                "wrapper" => isset($config['amount-wrapper']) ? $config['amount-wrapper'] : "",
                "label" => isset($config['amount-label']) ? $config['amount-label'] : "Amount",
                "type" => "number",
                "default" => isset($config['amount']) ? $config['amount'] : 0,
            ),
            array(
                "name" => "order_id",
                "field_type" => "optional",
                "classes" => isset($config['order_id-class']) ? $config['order_id-class'] : "",
                "attr" => isset($config['order_id-attr']) ? $config['order_id-attr'] : "",
                "wrapper" => isset($config['order_id-wrapper']) ? $config['order_id-wrapper'] : "",
                "label" => isset($config['order_id-label']) ? $config['order_id-label'] : "Order ID",
                "type" => "text",
                "default" => isset($config['order_id']) ? $config['order_id'] : "",
            ),
            array(
                "name" => "source",
                "field_type" => "optional",
                "classes" => isset($config['source-class']) ? $config['source-class'] : "",
                "attr" => isset($config['source-attr']) ? $config['source-attr'] : "",
                "wrapper" => isset($config['source-wrapper']) ? $config['source-wrapper'] : "",
                "label" => isset($config['source-label']) ? $config['source-label'] : "Source",
                "type" => "text",
                "default" => isset($config['source']) ? $config['source'] : "",
            ),
            array(
                "name" => "webhooks",
                "field_type" => "required",
                "classes" => isset($config['webhooks-class']) ? $config['webhooks-class'] : "",
                "attr" => isset($config['webhooks-attr']) ? $config['webhooks-attr'] : "",
                "wrapper" => isset($config['webhooks-wrapper']) ? $config['webhooks-wrapper'] : "",
                "label" => isset($config['webhooks-label']) ? $config['webhooks-label'] : "Webhooks",
                "type" => "select",
                "default" => isset($config['webhooks']) && in_array($config['webhooks'], array_column($webhooks, "label")) ? $config['webhooks'] : "False",
                "options" => $webhooks,
                "custom_options" => isset($config['webhooks-custom_options']) ? $config['webhooks-custom_options'] : array(),
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
