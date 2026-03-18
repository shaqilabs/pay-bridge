<?php

namespace ShaqiLabs\PayFast;

class PayFastAPI
{
    private $PayFastClient;
    private $callback_url;

    public function __construct(PayFastClient $PayFastClient)
    {
        $this->PayFastClient = $PayFastClient;
    }

    /**
    * Create Checkout Link
    *
    * @return array
    *   Decoded response data.
    */
    public function createCheckoutLink($order, $response_type = "redirect"){
        if((!isset($order['TXNAMT']) || $order['TXNAMT'] == "")){
            throw new PayFastException("Transaction Amount is missing.");
        }
        if (!is_numeric($order['TXNAMT']) || filter_var($order['TXNAMT'], FILTER_VALIDATE_FLOAT) === false) {
            throw new PayFastException("Transaction Amount must be a number or float.");
        }
        if($this->PayFastClient->success_url == "" && (!isset($order['success_url']) || $order['success_url'] == "")){
            throw new PayFastException("Success URL is missing. It can either be set once for all transactions or provided against each order or both.");
        }
        if($this->PayFastClient->cancel_url == "" && (!isset($order['cancel_url']) || $order['cancel_url'] == "")){
            $this->PayFastClient->cancel_url = $this->PayFastClient->success_url;
        }
        if($this->PayFastClient->checkout_url == "" && (!isset($order['checkout_url']) || $order['checkout_url'] == "")){
            $this->PayFastClient->checkout_url = $this->PayFastClient->success_url;
        }
        if((!isset($order['customer_email']) || $order['customer_email'] == "")){
            throw new PayFastException("Customer Email is missing.");
        }

        $data['BASKET_ID'] = (isset($order['BASKET_ID']) && $order['BASKET_ID'] != "") ? $order['BASKET_ID'] : uniqid();
        $data['MERCHANT_ID'] = $this->PayFastClient->merchant_id;
        $data['SECURED_KEY'] = $this->PayFastClient->api_password;
        $data['TXNAMT'] = $order['TXNAMT'];

        $endpoint = 'Transaction/GetAccessToken';
        $method = 'POST';
        $payload = $this->PayFastClient->makeRequest($endpoint, $method, $data);
        if(!isset($payload['ACCESS_TOKEN']) || (isset($payload['ACCESS_TOKEN']) && $payload['ACCESS_TOKEN'] == '')){
            throw new PayFastException("There was an error generating access token.");
        }
        $access_token = $payload['ACCESS_TOKEN'];
        $order_data = array(
            "MERCHANT_ID" => $data['MERCHANT_ID'],
            "CURRENCY_CODE" => (isset($order['currency_code']) && $order['currency_code'] != "") ? $order['currency_code'] : $this->PayFastClient->currency_code,
            "MERCHANT_NAME" => $this->PayFastClient->merchant_name,
            "TOKEN" => $access_token,
            "SUCCESS_URL" => (isset($order['success_url']) && $order['success_url'] != "") ? $order['success_url'] : $this->PayFastClient->success_url,
            "FAILURE_URL" => (isset($order['cancel_url']) && $order['cancel_url'] != "") ? $order['cancel_url'] : $this->PayFastClient->cancel_url,
            "CHECKOUT_URL" => (isset($order['checkout_url']) && $order['checkout_url'] != "") ? $order['checkout_url'] : $this->PayFastClient->checkout_url,
            "CUSTOMER_EMAIL_ADDRESS" => (isset($order['customer_email']) && $order['customer_email'] != "") ? $order['customer_email'] : throw new PayFastException("Customer Email is missing"),
            "CUSTOMER_MOBILE_NO" => (isset($order['customer_phone']) && $order['customer_phone'] != "") ? $order['customer_phone'] : throw new PayFastException("Customer Phone is missing"),
            "TXNAMT" => $data['TXNAMT'],
            "BASKET_ID" => $data['BASKET_ID'],
            "ORDER_DATE" => (isset($order['order_date']) && $order['order_date'] != "") ? $order['order_date'] : date("Y-m-d H:i:s"),
            "PROCCODE" => (isset($order['proccode']) && $order['proccode'] != "") ? $order['proccode'] : $this->PayFastClient->proccode,
            "TRAN_TYPE" => (isset($order['tran_type']) && $order['tran_type'] != "") ? $order['tran_type'] : $this->PayFastClient->tran_type,
        );
        $form_submission_url = $this->PayFastClient->api_url . 'Transaction/PostTransaction';
        if($response_type == "data"){
            return $order_data;
        } else if($response_type == "form"){
            return $this->generateForm($order_data, $form_submission_url);
        } else if($response_type == "redirect"){
            $form = $this->generateForm($order_data, $form_submission_url);
            $form .= '<script>
                document.addEventListener("DOMContentLoaded", function() {
                    document.getElementById("payfast_payment_form").submit();
                });
            </script>';
            echo $form;
            return;
        }
    }
    public function getListofBanks(){
        $endpoint = 'list/banks';
        $method = 'GET';
        $payload = $this->PayFastClient->makeAPIRequest($endpoint, $method);
        if(isset($payload['banks']) && is_array($payload['banks']) && count($payload['banks']) > 0){
            $banks = array();
            foreach($payload['banks'] as $bank){
                if($bank['is_wallet'] != 1){
                    array_push($banks, $bank);
                }
            }
            return array(
                "status" => 1,
                "data" => $banks
            );
        }
        return array(
            "status" => 0,
            'error' => "Error getting list of banks",
            "data" => $payload
        );
    }
    public function getListofWallets(){
        $endpoint = 'list/banks';
        $method = 'GET';
        $payload = $this->PayFastClient->makeAPIRequest($endpoint, $method);
        if(isset($payload['banks']) && is_array($payload['banks']) && count($payload['banks']) > 0){
            $banks = array();
            foreach($payload['banks'] as $bank){
                if($bank['is_wallet'] == 1){
                    array_push($banks, $bank);
                }
            }
            return array(
                "status" => 1,
                "data" => $banks
            );
        }
        return array(
            "status" => 0,
            'error' => "Error getting list of wallets",
            "data" => $payload
        );
    }
    public function initiateBankPayment($order){
        if((!isset($order['TXNAMT']) || $order['TXNAMT'] == "")){
            throw new PayFastException("Transaction Amount is missing.");
        }
        if (!is_numeric($order['TXNAMT']) || filter_var($order['TXNAMT'], FILTER_VALIDATE_FLOAT) === false) {
            throw new PayFastException("Transaction Amount must be a number or float.");
        }
        if((!isset($order['cnic_number']) || $order['cnic_number'] == "")){
            throw new PayFastException("CNIC is missing.");
        }
        if((!isset($order['account_number']) || $order['account_number'] == "")){
            throw new PayFastException("Account Number is missing.");
        }
        if((!isset($order['bank_code']) || $order['bank_code'] == "")){
            throw new PayFastException("Bank Code is missing.");
        }
        if((!isset($order['account_type']) || $order['account_type'] == "")){
            throw new PayFastException("Account Type is missing.");
        }
        if($order['account_type'] == "wallet"){
            $account_type_id = 4;
        } else if($order['account_type'] == "bank"){
            $account_type_id = 3;
        } else {
            throw new PayFastException("Incorrect account type");
        }
        $order_data = array(
            "basket_id" => (isset($order['BASKET_ID']) && $order['BASKET_ID'] != "") ? $order['BASKET_ID'] : uniqid(),
            "txnamt" => $order['TXNAMT'],
            "customer_mobile_no" => (isset($order['customer_phone']) && $order['customer_phone'] != "") ? $order['customer_phone'] : "",
            "customer_email_address" => (isset($order['customer_email']) && $order['customer_email'] != "") ? $order['customer_email'] : "",
            "order_date" => (isset($order['order_date']) && $order['order_date'] != "") ? $order['order_date'] : date("Y-m-d H:i:s"),
            "cnic_number" => $order['cnic_number'],
            "bank_code" => $order['bank_code'],
            "account_type_id" => $account_type_id,
            "account_number" => $order['account_number'],
            "currency_code" => (isset($order['currency_code']) && $order['currency_code'] != "") ? $order['currency_code'] : $this->PayFastClient->currency_code,
        );
        $endpoint = 'customer/validate';
        $method = 'POST';
        $payload = $this->PayFastClient->makeAPIRequest($endpoint, $method, $order_data);
        if(!isset($payload['message']) || $payload['message'] != "Validated"){
            return array(
                "status" => 0,
                'error' => $payload['message'],
                "data" => $payload
            );
        }
        $transaction_id = $payload['transaction_id'];
        return array(
            "status" => 1,
            "data" => $payload
        );
    }
    public function makeBankPayment($order){
        if((!isset($order['TXNAMT']) || $order['TXNAMT'] == "")){
            throw new PayFastException("Transaction Amount is missing.");
        }
        if (!is_numeric($order['TXNAMT']) || filter_var($order['TXNAMT'], FILTER_VALIDATE_FLOAT) === false) {
            throw new PayFastException("Transaction Amount must be a number or float.");
        }
        if((!isset($order['cnic_number']) || $order['cnic_number'] == "")){
            throw new PayFastException("CNIC is missing.");
        }
        if((!isset($order['account_number']) || $order['account_number'] == "")){
            throw new PayFastException("Account Number is missing.");
        }
        if((!isset($order['bank_code']) || $order['bank_code'] == "")){
            throw new PayFastException("Bank Code is missing.");
        }
        if((!isset($order['account_type']) || $order['account_type'] == "")){
            throw new PayFastException("Account Type is missing.");
        }
        if((!isset($order['transaction_id']) || $order['transaction_id'] == "")){
            throw new PayFastException("Transaction ID is missing.");
        }
        if((!isset($order['otp_code']) || $order['otp_code'] == "")){
            throw new PayFastException("OTP Code is missing.");
        }
        if($order['account_type'] == "wallet"){
            $account_type_id = 4;
        } else if($order['account_type'] == "bank"){
            $account_type_id = 3;
        } else {
            throw new PayFastException("Incorrect account type");
        }
        $order_data = array(
            "basket_id" => (isset($order['BASKET_ID']) && $order['BASKET_ID'] != "") ? $order['BASKET_ID'] : uniqid(),
            "txnamt" => $order['TXNAMT'],
            "customer_mobile_no" => (isset($order['customer_phone']) && $order['customer_phone'] != "") ? $order['customer_phone'] : "",
            "customer_email_address" => (isset($order['customer_email']) && $order['customer_email'] != "") ? $order['customer_email'] : "",
            "order_date" => (isset($order['order_date']) && $order['order_date'] != "") ? $order['order_date'] : date("Y-m-d H:i:s"),
            "cnic_number" => $order['cnic_number'],
            "bank_code" => $order['bank_code'],
            "account_type_id" => $account_type_id,
            "account_number" => $order['account_number'],
            "currency_code" => (isset($order['currency_code']) && $order['currency_code'] != "") ? $order['currency_code'] : $this->PayFastClient->currency_code,
            "transaction_id" => $order['transaction_id'],
            "otp" => $order['otp_code'],
            "otp_required" => "yes",
        );
        $endpoint = 'transaction';
        $method = 'POST';
        $payload = $this->PayFastClient->makeAPIRequest($endpoint, $method, $order_data);
        if(!isset($payload['status_msg']) || $payload['status_msg'] != "Processed OK"){
            return array(
                "status" => 0,
                'error' => $payload['status_msg'],
                "data" => $payload
            );
        }
        return array(
            "status" => 1,
            "data" => $payload
        );        
    }
    public function validateTransaction($transaction_id){
        $endpoint = 'transaction/view/id?transaction_id=' . $transaction_id;
        $method = 'GET';
        $payload = $this->PayFastClient->makeVerifyRequest($endpoint, $method);
        if(!isset($payload['message']) || $payload['message'] != "Request has been completed successfully."){
            return array(
                "status" => 0,
                'error' => $payload['message'],
                "data" => $payload
            );
        }
        return array(
            "status" => 1,
            "data" => $payload['data']
        );        
    }
    public function dynamicRedirect($order_data){
        $form_submission_url = $this->PayFastClient->api_url . 'Transaction/PostTransaction';
        $form = $this->generateForm($order_data, $form_submission_url);
        $form .= '<script>
            document.addEventListener("DOMContentLoaded", function() {
                document.getElementById("payfast_payment_form").submit();
            });
        </script>';
        echo $form;
        return;
    }
    public function generateForm($order, $url){
        $form = '<form id="payfast_payment_form" name="payfast-payment-form" method="post" action="' . $url . '" style="display:none;">
            <input type="TEXT" name="CURRENCY_CODE" value="' . $order['CURRENCY_CODE'] . '" /><br />
            <input type="TEXT" name="MERCHANT_ID" value="' . $order['MERCHANT_ID'] . '" /><br />
            <input type="TEXT" name="MERCHANT_NAME" value="' . $order['MERCHANT_NAME'] . '" /><br />
            <input type="TEXT" name="TOKEN" value="' . $order['TOKEN'] . '" /><br />
            <input type="TEXT" name="SUCCESS_URL" value="' . $order['SUCCESS_URL'] . '" /><br />
            <input type="TEXT" name="FAILURE_URL" value="' . $order['FAILURE_URL'] . '" /><br />
            <input type="TEXT" name="CHECKOUT_URL" value="' . $order['CHECKOUT_URL'] . '" /><br />
            <input type="TEXT" name="CUSTOMER_EMAIL_ADDRESS" value="' . $order['CUSTOMER_EMAIL_ADDRESS'] . '" /><br />
            <input type="TEXT" name="CUSTOMER_MOBILE_NO" value="' . $order['CUSTOMER_MOBILE_NO'] . '" /><br />
            <input type="TEXT" name="TXNAMT" value="' . $order['TXNAMT'] . '" /><br />
            <input type="TEXT" name="BASKET_ID" value="' . $order['BASKET_ID'] . '" /><br />
            <input type="TEXT" name="ORDER_DATE" value="' . $order['ORDER_DATE'] . '" /><br />
            <input type="TEXT" name="PROCCODE" value="' . $order['PROCCODE'] . '" /><br />
            <input type="TEXT" name="TRAN_TYPE" value="' . $order['TRAN_TYPE'] . '" /><br />
            <input type="SUBMIT" value="SUBMIT">
        </form>';
        return $form;
    }
    public function validateIdNameData($data){
        if (!is_array($data)) {
            throw new PayFastException('Invalid data structure. Each data must be an associative array.');
        }
        foreach ($data as $item) {
            // Check if the item is an array
            if (!is_array($item)) {
                throw new PayFastException('Invalid data structure. Each item must be an associative array.');
            }

            // Check if the item contains only 'id' and 'name' keys
            $keys = array_keys($item);
            $allowedKeys = ['label', 'value'];
            if (count($keys) != count($allowedKeys)) {
                throw new PayFastException('Invalid data structure. Each item must contain both "value" and "label" keys.');
            }
            if (count($keys) != count(array_intersect($keys, $allowedKeys))) {
                throw new PayFastException('Invalid data structure. Each item must contain only "value" and "label" keys.');
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
            throw new PayFastException('Ivalid response type. Available: form, json');
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
                "name" => "CURRENCY_CODE",
                "field_type" => "optional",
                "classes" => isset($config['CURRENCY_CODE-class']) ? $config['CURRENCY_CODE-class'] : "",
                "attr" => isset($config['CURRENCY_CODE-attr']) ? $config['CURRENCY_CODE-attr'] : "",
                "wrapper" => isset($config['CURRENCY_CODE-wrapper']) ? $config['CURRENCY_CODE-wrapper'] : "",
                "label" => isset($config['CURRENCY_CODE-label']) ? $config['CURRENCY_CODE-label'] : "Currency Code",
                "type" => "text",
                "default" => isset($config['CURRENCY_CODE']) ? $config['CURRENCY_CODE'] : $this->PayFastClient->currency_code,
            ),
            array(
                "name" => "MERCHANT_NAME",
                "field_type" => "optional",
                "classes" => isset($config['MERCHANT_NAME-class']) ? $config['MERCHANT_NAME-class'] : "",
                "attr" => isset($config['MERCHANT_NAME-attr']) ? $config['MERCHANT_NAME-attr'] : "",
                "wrapper" => isset($config['MERCHANT_NAME-wrapper']) ? $config['MERCHANT_NAME-wrapper'] : "",
                "label" => isset($config['MERCHANT_NAME-label']) ? $config['MERCHANT_NAME-label'] : "Merchant Name",
                "type" => "text",
                "default" => isset($config['MERCHANT_NAME']) ? $config['MERCHANT_NAME'] : $this->PayFastClient->merchant_name,
            ),
            array(
                "name" => "SUCCESS_URL",
                "field_type" => "optional",
                "classes" => isset($config['SUCCESS_URL-class']) ? $config['SUCCESS_URL-class'] : "",
                "attr" => isset($config['SUCCESS_URL-attr']) ? $config['SUCCESS_URL-attr'] : "",
                "wrapper" => isset($config['SUCCESS_URL-wrapper']) ? $config['SUCCESS_URL-wrapper'] : "",
                "label" => isset($config['SUCCESS_URL-label']) ? $config['SUCCESS_URL-label'] : "Success URL",
                "type" => "text",
                "default" => isset($config['SUCCESS_URL']) ? $config['SUCCESS_URL'] : $this->PayFastClient->success_url,
            ),
            array(
                "name" => "FAILURE_URL",
                "field_type" => "optional",
                "classes" => isset($config['FAILURE_URL-class']) ? $config['FAILURE_URL-class'] : "",
                "attr" => isset($config['FAILURE_URL-attr']) ? $config['FAILURE_URL-attr'] : "",
                "wrapper" => isset($config['FAILURE_URL-wrapper']) ? $config['FAILURE_URL-wrapper'] : "",
                "label" => isset($config['FAILURE_URL-label']) ? $config['FAILURE_URL-label'] : "Cancel URL",
                "type" => "text",
                "default" => isset($config['FAILURE_URL']) ? $config['FAILURE_URL'] : $this->PayFastClient->cancel_url,
            ),
            array(
                "name" => "CHECKOUT_URL",
                "field_type" => "optional",
                "classes" => isset($config['CHECKOUT_URL-class']) ? $config['CHECKOUT_URL-class'] : "",
                "attr" => isset($config['CHECKOUT_URL-attr']) ? $config['CHECKOUT_URL-attr'] : "",
                "wrapper" => isset($config['CHECKOUT_URL-wrapper']) ? $config['CHECKOUT_URL-wrapper'] : "",
                "label" => isset($config['CHECKOUT_URL-label']) ? $config['CHECKOUT_URL-label'] : "Checkout URL",
                "type" => "text",
                "default" => isset($config['CHECKOUT_URL']) ? $config['CHECKOUT_URL'] : $this->PayFastClient->checkout_url,
            ),
            array(
                "name" => "CUSTOMER_EMAIL_ADDRESS",
                "field_type" => "required",
                "classes" => isset($config['CUSTOMER_EMAIL_ADDRESS-class']) ? $config['CUSTOMER_EMAIL_ADDRESS-class'] : "",
                "attr" => isset($config['CUSTOMER_EMAIL_ADDRESS-attr']) ? $config['CUSTOMER_EMAIL_ADDRESS-attr'] : "",
                "wrapper" => isset($config['CUSTOMER_EMAIL_ADDRESS-wrapper']) ? $config['CUSTOMER_EMAIL_ADDRESS-wrapper'] : "",
                "label" => isset($config['CUSTOMER_EMAIL_ADDRESS-label']) ? $config['CUSTOMER_EMAIL_ADDRESS-label'] : "Customer Email Address",
                "type" => "text",
                "default" => isset($config['CUSTOMER_EMAIL_ADDRESS']) ? $config['CUSTOMER_EMAIL_ADDRESS'] : "",
            ),
            array(
                "name" => "CUSTOMER_MOBILE_NO",
                "field_type" => "required",
                "classes" => isset($config['CUSTOMER_MOBILE_NO-class']) ? $config['CUSTOMER_MOBILE_NO-class'] : "",
                "attr" => isset($config['CUSTOMER_MOBILE_NO-attr']) ? $config['CUSTOMER_MOBILE_NO-attr'] : "",
                "wrapper" => isset($config['CUSTOMER_MOBILE_NO-wrapper']) ? $config['CUSTOMER_MOBILE_NO-wrapper'] : "",
                "label" => isset($config['CUSTOMER_MOBILE_NO-label']) ? $config['CUSTOMER_MOBILE_NO-label'] : "Customer Mobile Number",
                "type" => "text",
                "default" => isset($config['CUSTOMER_MOBILE_NO']) ? $config['CUSTOMER_MOBILE_NO'] : "",
            ),
            array(
                "name" => "TXNAMT",
                "field_type" => "required",
                "classes" => isset($config['TXNAMT-class']) ? $config['TXNAMT-class'] : "",
                "attr" => isset($config['TXNAMT-attr']) ? $config['TXNAMT-attr'] : "",
                "wrapper" => isset($config['TXNAMT-wrapper']) ? $config['TXNAMT-wrapper'] : "",
                "label" => isset($config['TXNAMT-label']) ? $config['TXNAMT-label'] : "Amount",
                "type" => "number",
                "default" => isset($config['TXNAMT']) ? $config['TXNAMT'] : "0",
            ),
            array(
                "name" => "BASKET_ID",
                "field_type" => "required",
                "classes" => isset($config['BASKET_ID-class']) ? $config['BASKET_ID-class'] : "",
                "attr" => isset($config['BASKET_ID-attr']) ? $config['BASKET_ID-attr'] : "",
                "wrapper" => isset($config['BASKET_ID-wrapper']) ? $config['BASKET_ID-wrapper'] : "",
                "label" => isset($config['BASKET_ID-label']) ? $config['BASKET_ID-label'] : "Basket ID",
                "type" => "text",
                "default" => isset($config['BASKET_ID']) ? $config['BASKET_ID'] : "",
            ),
            array(
                "name" => "ORDER_DATE",
                "field_type" => "required",
                "classes" => isset($config['ORDER_DATE-class']) ? $config['ORDER_DATE-class'] : "",
                "attr" => isset($config['ORDER_DATE-attr']) ? $config['ORDER_DATE-attr'] : "",
                "wrapper" => isset($config['ORDER_DATE-wrapper']) ? $config['ORDER_DATE-wrapper'] : "",
                "label" => isset($config['ORDER_DATE-label']) ? $config['ORDER_DATE-label'] : "Order Date",
                "type" => "date",
                "default" => isset($config['ORDER_DATE']) ? $config['ORDER_DATE'] : "",
            ),
            array(
                "name" => "PROCCODE",
                "field_type" => "optional",
                "classes" => isset($config['PROCCODE-class']) ? $config['PROCCODE-class'] : "",
                "attr" => isset($config['PROCCODE-attr']) ? $config['PROCCODE-attr'] : "",
                "wrapper" => isset($config['PROCCODE-wrapper']) ? $config['PROCCODE-wrapper'] : "",
                "label" => isset($config['PROCCODE-label']) ? $config['PROCCODE-label'] : "Proc Code",
                "type" => "text",
                "default" => isset($config['PROCCODE']) ? $config['PROCCODE'] : $this->PayFastClient->proccode,
            ),
            array(
                "name" => "TRAN_TYPE",
                "field_type" => "optional",
                "classes" => isset($config['TRAN_TYPE-class']) ? $config['TRAN_TYPE-class'] : "",
                "attr" => isset($config['TRAN_TYPE-attr']) ? $config['TRAN_TYPE-attr'] : "",
                "wrapper" => isset($config['TRAN_TYPE-wrapper']) ? $config['TRAN_TYPE-wrapper'] : "",
                "label" => isset($config['TRAN_TYPE-label']) ? $config['TRAN_TYPE-label'] : "Transaction Type",
                "type" => "text",
                "default" => isset($config['TRAN_TYPE']) ? $config['TRAN_TYPE'] : $this->PayFastClient->tran_type,
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
