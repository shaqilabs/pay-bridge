<?php

namespace ShaqiLabs\JazzCash;

class JazzCashAPI
{
    private $JazzCashClient;
    private $callback_url;

    public function __construct(JazzCashClient $JazzCashClient){
        $this->JazzCashClient = $JazzCashClient;
    }
    public function createCheckoutLink($order, $response_type = "redirect"){
        if((!isset($order['amount']) || $order['amount'] == "")){
            throw new JazzCashException("Transaction Amount is missing.");
        }
        if (!is_numeric($order['amount']) || filter_var($order['amount'], FILTER_VALIDATE_FLOAT) === false) {
            throw new JazzCashException("Transaction Amount must be a number or float.");
        }
        $order['amount'] = $order['amount'] * 100;
        $order['transaction_reference'] = (isset($order['transaction_reference']) && $order['transaction_reference'] != "") ? $this->JazzCashClient->domain_code . $order['transaction_reference'] : $this->JazzCashClient->domain_code . date('YmdHis') . mt_rand(10, 100);;
        if(strlen($order['transaction_reference']) > 20 || strlen($order['transaction_reference']) <= 0){
            throw new JazzCashException("Transaction Reference must be a maximum of 20 characters, can not be empty & must be unique");
        }
        $order['order_id'] = (isset($order['order_id']) && $order['order_id'] != "") ? $order['order_id'] : uniqid();
        $order['date_time'] = (isset($order['date_time']) && $order['date_time'] != "") ? $order['date_time'] : date("Ymdhis");
        $order['expiry_time'] = date('Ymdhis', strtotime($order['date_time'] . ' +1 day'));
        $order['bill_reference'] = (isset($order['bill_reference']) && $order['bill_reference'] != "") ? $order['bill_reference'] : "";
        
        $order['description'] = (isset($order['description']) && $order['description'] != "") ? $order['description'] : "";
        $order['metafield_1'] = (isset($order['metafield_1']) && $order['metafield_1'] != "") ? $order['metafield_1'] : "";
        $order['metafield_2'] = (isset($order['metafield_2']) && $order['metafield_2'] != "") ? $order['metafield_2'] : "";
        $order['metafield_3'] = (isset($order['metafield_3']) && $order['metafield_3'] != "") ? $order['metafield_3'] : "";
        $order['metafield_4'] = (isset($order['metafield_4']) && $order['metafield_4'] != "") ? $order['metafield_4'] : "";
        $order['metafield_5'] = (isset($order['metafield_5']) && $order['metafield_5'] != "") ? $order['metafield_5'] : "";
        $order['bank_id'] = $this->JazzCashClient->bank_id;
        $order['registered_user'] = $this->JazzCashClient->registered_user;
        $order['language'] = $this->JazzCashClient->language;
        $order['currency'] = $this->JazzCashClient->currency;
        $order['product_id'] = $this->JazzCashClient->product_id;
        $order['transaction_type'] =  $this->JazzCashClient->transaction_type;
        $order['version'] = $this->JazzCashClient->version;
        $order['sub_merchant_id'] = $this->JazzCashClient->sub_merchant_id;

        $hash_data = array(
            $order['amount'],
            $order['bank_id'],
            $order['bill_reference'],
            $order['description'],
            $order['registered_user'],
            $order['language'],
            $this->JazzCashClient->merchant_id,
            $this->JazzCashClient->password,
            $order['product_id'],
            $this->JazzCashClient->return_url,
            $order['currency'],
            $order['date_time'],
            $order['expiry_time'],
            $order['transaction_reference'],
            $order['transaction_type'],
            $order['version'],
            $order['metafield_1'],
            $order['metafield_2'],
            $order['metafield_3'],
            $order['metafield_4'],
            $order['metafield_5'],
        );
        $sorted_hash = $this->JazzCashClient->integerity_salt;
        for ($i = 0; $i < count($hash_data); $i++) {
            if ($hash_data[$i] != 'undefined' and $hash_data[$i] != null and $hash_data[$i] != "") {
                $sorted_hash .= "&" . $hash_data[$i];
            }
        }
        $secure_hash = hash_hmac('sha256', $sorted_hash, $this->JazzCashClient->integerity_salt);
        if($response_type == "form"){
            return $this->generateForm($order, $secure_hash);
        } else if($response_type == "redirect"){
            $form = $this->generateForm($order, $secure_hash);
            $form .= '<script>
                document.addEventListener("DOMContentLoaded", function() {
                    document.getElementById("payment_form_jazzcash").submit();
                });
            </script>';
            echo $form;
            return;
        }
    }
    public function generateForm($order, $secure_hash){
        $form = '<form action="' . $this->JazzCashClient->api_url .  $this->JazzCashClient->checkout_url . '" id="payment_form_jazzcash" method="post" novalidate="novalidate" style = "display:none;">                                                              
            <input id="pp_Version" name="pp_Version" type="hidden" value="' . $order['version'] . '">                                                                                                                                
            <input id="pp_TxnType" name="pp_TxnType" type="hidden" value="' . $order['transaction_type'] . '">                                                                                                                            
            <input id="pp_Language" name="pp_Language" type="hidden" value="' . $order['language'] . '">                                                                                                                            
            <input id="pp_MerchantID" name="pp_MerchantID" type="hidden" value="' . $this->JazzCashClient->merchant_id . '">                                                                                                                               
            <input id="pp_SubMerchantID" name="pp_SubMerchantID" type="hidden" value="' . $order['sub_merchant_id'] . '">                                                                                     
            <input id="pp_Password" name="pp_Password" type="hidden" value="' . $this->JazzCashClient->password . '">                                                                            
            <input id="pp_TxnRefNo" name="pp_TxnRefNo" type="hidden" value="' . $order['transaction_reference'] . '">                                                                                                                           
            <input id="pp_Amount" name="pp_Amount" type="hidden" value="' . $order['amount'] . '">                                                                                                                     
            <input id="pp_TxnCurrency" name="pp_TxnCurrency" type="hidden" value="' . $order['currency'] . '">                                  
            <input id="pp_TxnDateTime" name="pp_TxnDateTime" type="hidden" value="' . $order['date_time'] . '">                                                                                                            
            <input id="pp_BillReference" name="pp_BillReference" type="hidden" value="' . $order['bill_reference'] . '">  
            <input id="pp_Description" name="pp_Description" type="hidden" value="' . $order['description'] . '">           
            <input id="pp_IsRegisteredCustomer" name="pp_IsRegisteredCustomer" type="hidden" value="' . $order['registered_user'] . '">      
            <input id="pp_BankID" name="pp_BankID" type="hidden" value="' . $order['bank_id'] . '">      
            <input id="pp_ProductID" name="pp_ProductID" type="hidden" value="' . $order['product_id'] . '">      
            <input id="pp_TxnExpiryDateTime" name="pp_TxnExpiryDateTime" type="hidden" value="' . $order['expiry_time'] . '">      
            <input id="pp_ReturnURL" name="pp_ReturnURL" type="hidden" value="' . $this->JazzCashClient->return_url . '">      
            <input id="pp_SecureHash" name="pp_SecureHash" type="hidden" value="' . $secure_hash . '">      
            <input id="ppmpf_1" name="ppmpf_1" type="hidden" value="' . $order['metafield_1'] . '">        
            <input id="ppmpf_2" name="ppmpf_2" type="hidden" value="' . $order['metafield_2'] . '">       
            <input id="ppmpf_3" name="ppmpf_3" type="hidden" value="' . $order['metafield_3'] . '">       
            <input id="ppmpf_4" name="ppmpf_4" type="hidden" value="' . $order['metafield_4'] . '">       
            <input id="ppmpf_5" name="ppmpf_5" type="hidden" value="' . $order['metafield_5'] . '">     
            <input type="SUBMIT" value="SUBMIT">                                                                    
        </form>';
        return $form;
    }
    public function processResponse(){ 
        if(!isset($_POST['pp_ResponseCode']) || $_POST['pp_ResponseCode'] == ""){
            $_POST['pp_ResponseCode'] = "0";
        }
        if(!isset($_POST['pp_ResponseMessage']) || $_POST['pp_ResponseMessage'] == ""){
            $_POST['pp_ResponseMessage'] = "There was an unknown error with your transaction";
        }
        return array(
            "status" => $_POST['pp_ResponseCode'],
            "message" => $_POST['pp_ResponseMessage'],
            "data" => $_POST
        );
    }
    public function mobileAccountLinking($order, $response_type = "redirect"){
        if((!isset($order['account_number']) || $order['account_number'] == "")){
            throw new JazzCashException("Account Number is missing.");
        }
        $order['request_id'] = (isset($order['request_id']) && $order['request_id'] != "") ? $order['request_id'] : uniqid();
        $order['return_url'] = (isset($order['return_url']) && $order['return_url'] != "") ? $order['return_url'] : $this->JazzCashClient->return_url;

        $hash_data = array(
            $order['account_number'],
            $this->JazzCashClient->merchant_id,
            $this->JazzCashClient->password,
            $order['request_id'],
            $order['return_url'],
        );
        $sorted_hash = $this->JazzCashClient->integerity_salt;
        for ($i = 0; $i < count($hash_data); $i++) {
            if ($hash_data[$i] != 'undefined' and $hash_data[$i] != null and $hash_data[$i] != "") {
                $sorted_hash .= "&" . $hash_data[$i];
            }
        }
        $secure_hash = hash_hmac('sha256', $sorted_hash, $this->JazzCashClient->integerity_salt);
        if($response_type == "form"){
            return $this->generateWalletForm($order, $secure_hash);
        } else if($response_type == "redirect"){
            $form = $this->generateWalletForm($order, $secure_hash);
            $form .= '<script>
                document.addEventListener("DOMContentLoaded", function() {
                    document.getElementById("payment_form_jazzcash").submit();
                });
            </script>';
            return $form;
        }
    }
    public function generateWalletForm($order, $secure_hash){
        $form = '<form action="' . $this->JazzCashClient->api_url .  $this->JazzCashClient->wallet_url . '" id="payment_form_jazzcash" method="post" novalidate="novalidate" style = "display:none;">                                                                                                       
            <input id="pp_MerchantID" name="pp_MerchantID" type="hidden" value="' . $this->JazzCashClient->merchant_id . '">                                                                                    
            <input id="pp_Password" name="pp_Password" type="hidden" value="' . $this->JazzCashClient->password . '">  
            <input id="pp_MSISDN" name="pp_MSISDN" type="hidden" value="' . $order['account_number'] . '">                  
            <input id="pp_RequestID" name="pp_RequestID" type="hidden" value="' . $order['request_id'] . '">                
            <input id="pp_ReturnURL" name="pp_ReturnURL" type="hidden" value="' . $order['return_url'] . '">           
            <input id="pp_SecureHash" name="pp_SecureHash" type="hidden" value="' . $secure_hash . '">
            <input type="SUBMIT" value="SUBMIT">                                                                    
        </form>';
        return $form;
    }
    public function linkedMobileAccountTransaction($order){
        if((!isset($order['amount']) || $order['amount'] == "")){
            throw new JazzCashException("Transaction Amount is missing.");
        }
        if (!is_numeric($order['amount']) || filter_var($order['amount'], FILTER_VALIDATE_FLOAT) === false) {
            throw new JazzCashException("Transaction Amount must be a number or float.");
        }
        if((!isset($order['payment_token']) || $order['payment_token'] == "")){
            throw new JazzCashException("Payment Token is missing.");
        }
        $order['amount'] = $order['amount'] * 100;
        $order['transaction_reference'] = (isset($order['transaction_reference']) && $order['transaction_reference'] != "") ? $this->JazzCashClient->domain_code . $order['transaction_reference'] : $this->JazzCashClient->domain_code . date('YmdHis') . mt_rand(10, 100);;
        if(strlen($order['transaction_reference']) > 20 || strlen($order['transaction_reference']) <= 0){
            throw new JazzCashException("Transaction Reference must be a maximum of 20 characters, can not be empty & must be unique");
        }
        $order['order_id'] = (isset($order['order_id']) && $order['order_id'] != "") ? $order['order_id'] : uniqid();
        $order['date_time'] = (isset($order['date_time']) && $order['date_time'] != "") ? $order['date_time'] : date("Ymdhis");
        $order['expiry_time'] = date('Ymdhis', strtotime($order['date_time'] . ' +1 day'));
        $order['bill_reference'] = (isset($order['bill_reference']) && $order['bill_reference'] != "") ? $order['bill_reference'] : "";
        
        $order['description'] = (isset($order['description']) && $order['description'] != "") ? $order['description'] : "";
        $order['metafield_1'] = (isset($order['metafield_1']) && $order['metafield_1'] != "") ? $order['metafield_1'] : "";
        $order['metafield_2'] = (isset($order['metafield_2']) && $order['metafield_2'] != "") ? $order['metafield_2'] : "";
        $order['metafield_3'] = (isset($order['metafield_3']) && $order['metafield_3'] != "") ? $order['metafield_3'] : "";
        $order['metafield_4'] = (isset($order['metafield_4']) && $order['metafield_4'] != "") ? $order['metafield_4'] : "";
        $order['metafield_5'] = (isset($order['metafield_5']) && $order['metafield_5'] != "") ? $order['metafield_5'] : "";
        $order['cnic'] = (isset($order['cnic']) && $order['cnic'] != "") ? $order['cnic'] : "";
        $order['discounted_amount'] = (isset($order['discounted_amount']) && $order['discounted_amount'] != "") ? $order['discounted_amount'] : "";
        $order['currency'] = $this->JazzCashClient->currency;
        $order['sub_merchant_id'] = $this->JazzCashClient->sub_merchant_id;
        $order['bank_id'] = $this->JazzCashClient->bank_id;
        $order['language'] = $this->JazzCashClient->language;
        $order['product_id'] = $this->JazzCashClient->product_id;

        $hash_data = array(
            $order['amount'],
            $order['bill_reference'],
            $order['description'],
            $order['discounted_amount'],
            $this->JazzCashClient->merchant_id,
            $this->JazzCashClient->password,
            $order['payment_token'],
            $this->JazzCashClient->sub_merchant_id,
            $order['currency'],
            $order['date_time'],
            $order['expiry_time'],
            $order['transaction_reference'],
            $order['metafield_1'],
            $order['metafield_2'],
            $order['metafield_3'],
            $order['metafield_4'],
            $order['metafield_5'],
        );
        $sorted_hash = $this->JazzCashClient->integerity_salt;
        for ($i = 0; $i < count($hash_data); $i++) {
            if ($hash_data[$i] != 'undefined' and $hash_data[$i] != null and $hash_data[$i] != "") {
                $sorted_hash .= "&" . $hash_data[$i];
            }
        }
        $secure_hash = hash_hmac('sha256', $sorted_hash, $this->JazzCashClient->integerity_salt);
        $post_data = array(
            "pp_MerchantID" => $this->JazzCashClient->merchant_id,
            "pp_SubMerchantID" => $this->JazzCashClient->sub_merchant_id,
            "pp_Password" => $this->JazzCashClient->password,
            "pp_PaymentToken" => $order['payment_token'],
            "pp_TxnRefNo" => $order['transaction_reference'],
            "pp_Amount" => $order['amount'],
            "pp_TxnCurrency" => $order['currency'],
            "pp_TxnDateTime" => $order['date_time'],
            "pp_BillReference" => $order['bill_reference'],
            "pp_Description" => $order['description'],
            "pp_TxnExpiryDateTime" => $order['expiry_time'],
            "pp_SecureHash" => $secure_hash,
            "pp_DiscountedAmount" => $order['discounted_amount'],
            "ppmpf_1" => isset($order['metafield_1']) ? $order['metafield_1'] : "",
            "ppmpf_2" => isset($order['metafield_2']) ? $order['metafield_2'] : "",
            "ppmpf_3" => isset($order['metafield_3']) ? $order['metafield_3'] : "",
            "ppmpf_4" => isset($order['metafield_4']) ? $order['metafield_4'] : "",
            "ppmpf_5" => isset($order['metafield_5']) ? $order['metafield_5'] : "",
        );
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->JazzCashClient->api_url .  $this->JazzCashClient->wallet_transaction_url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post_data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if (curl_errno($ch)) {
            $error = curl_error($ch);
            curl_close($ch);
            return array(
                "pp_ResponseCode" => 0,
                "pp_ResponseMessage" => $error
            );
        } else {
            curl_close($ch);
            if ($httpCode >= 400) {
                return array(
                    "pp_ResponseCode" => 0,
                    "pp_ResponseMessage" => "HTTP request failed with status {$httpCode}",
                    "pp_RawResponse" => $response
                );
            }
            $resp = json_decode($response,true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return $resp;
            } else {
                throw new JazzCashException("Unparsable response from server: " . json_last_error_msg() . ". Response: " . strip_tags($response));
            }
        }
    }
    public function transactionStatus($transaction_reference){
        if((!isset($transaction_reference) || $transaction_reference == "")){
            throw new JazzCashException("Transaction Reference is missing.");
        }
        $hash_data = array(
            $this->JazzCashClient->merchant_id,
            $this->JazzCashClient->password,
            $transaction_reference,
        );
        $sorted_hash = $this->JazzCashClient->integerity_salt;
        for ($i = 0; $i < count($hash_data); $i++) {
            if ($hash_data[$i] != 'undefined' and $hash_data[$i] != null and $hash_data[$i] != "") {
                $sorted_hash .= "&" . $hash_data[$i];
            }
        }
        $secure_hash = hash_hmac('sha256', $sorted_hash, $this->JazzCashClient->integerity_salt);
        $post_data = array(
            "pp_MerchantID" => $this->JazzCashClient->merchant_id,
            "pp_Password" => $this->JazzCashClient->password,
            "pp_TxnRefNo" => $transaction_reference,
            "pp_SecureHash" => $secure_hash,
        );
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->JazzCashClient->api_url .  $this->JazzCashClient->transaction_status_url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post_data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if (curl_errno($ch)) {
            $error = curl_error($ch);
            curl_close($ch);
            return array(
                "pp_ResponseCode" => 0,
                "pp_ResponseMessage" => $error
            );
        } else {
            curl_close($ch);
            if ($httpCode >= 400) {
                return array(
                    "pp_ResponseCode" => 0,
                    "pp_ResponseMessage" => "HTTP request failed with status {$httpCode}",
                    "pp_RawResponse" => $response
                );
            }
            $resp = json_decode($response,true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return $resp;
            } else {
                throw new JazzCashException("Unparsable response from server: " . json_last_error_msg() . ". Response: " . strip_tags($response));
            }
        }
    }
    public function refundCardTransaction($transaction_reference, $amount){
        if((!isset($amount) || $amount == "")){
            throw new JazzCashException("Transaction Amount is missing.");
        }
        if (!is_numeric($amount) || filter_var($amount, FILTER_VALIDATE_FLOAT) === false) {
            throw new JazzCashException("Transaction Amount must be a number or float.");
        }
        if((!isset($transaction_reference) || $transaction_reference == "")){
            throw new JazzCashException("Transaction Reference is missing.");
        }
        $amount = $amount * 100;
        $hash_data = array(
            $amount,
            $this->JazzCashClient->merchant_id,
            $this->JazzCashClient->password,
            $this->JazzCashClient->currency,
            $transaction_reference,
        );
        $sorted_hash = $this->JazzCashClient->integerity_salt;
        for ($i = 0; $i < count($hash_data); $i++) {
            if ($hash_data[$i] != 'undefined' and $hash_data[$i] != null and $hash_data[$i] != "") {
                $sorted_hash .= "&" . $hash_data[$i];
            }
        }
        $secure_hash = hash_hmac('sha256', $sorted_hash, $this->JazzCashClient->integerity_salt);
        $post_data = array(
            "pp_MerchantID" => $this->JazzCashClient->merchant_id,
            "pp_Password" => $this->JazzCashClient->password,
            "pp_TxnRefNo" => $transaction_reference,
            "pp_TxnCurrency" => $this->JazzCashClient->currency,
            "pp_Amount" => $amount,
            "pp_SecureHash" => $secure_hash,
        );
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->JazzCashClient->api_url .  $this->JazzCashClient->card_refund_url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post_data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if (curl_errno($ch)) {
            $error = curl_error($ch);
            curl_close($ch);
            return array(
                "pp_ResponseCode" => 0,
                "pp_ResponseMessage" => $error
            );
        } else {
            curl_close($ch);
            if ($httpCode >= 400) {
                return array(
                    "pp_ResponseCode" => 0,
                    "pp_ResponseMessage" => "HTTP request failed with status {$httpCode}",
                    "pp_RawResponse" => $response
                );
            }
            $resp = json_decode($response,true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return $resp;
            } else {
                throw new JazzCashException("Unparsable response from server: " . json_last_error_msg() . ". Response: " . strip_tags($response));
            }
        }
    }
    public function refundWalletTransaction($transaction_reference, $amount, $mpin){
        if((!isset($amount) || $amount == "")){
            throw new JazzCashException("Transaction Amount is missing.");
        }
        if (!is_numeric($amount) || filter_var($amount, FILTER_VALIDATE_FLOAT) === false) {
            throw new JazzCashException("Transaction Amount must be a number or float.");
        }
        if((!isset($mpin) || $mpin == "")){
            throw new JazzCashException("Mobile Pin is missing.");
        }
        if((!isset($transaction_reference) || $transaction_reference == "")){
            throw new JazzCashException("Transaction Reference is missing.");
        }
        $amount = $amount * 100;
        $hash_data = array(
            $amount,
            $this->JazzCashClient->merchant_id,
            $mpin,
            $this->JazzCashClient->password,
            $this->JazzCashClient->currency,
            $transaction_reference,
        );
        $sorted_hash = $this->JazzCashClient->integerity_salt;
        for ($i = 0; $i < count($hash_data); $i++) {
            if ($hash_data[$i] != 'undefined' and $hash_data[$i] != null and $hash_data[$i] != "") {
                $sorted_hash .= "&" . $hash_data[$i];
            }
        }
        $secure_hash = hash_hmac('sha256', $sorted_hash, $this->JazzCashClient->integerity_salt);
        $post_data = array(
            "pp_MerchantID" => $this->JazzCashClient->merchant_id,
            "pp_Password" => $this->JazzCashClient->password,
            "pp_TxnRefNo" => $transaction_reference,
            "pp_MerchantMPIN" => $mpin,
            "pp_TxnCurrency" => $this->JazzCashClient->currency,
            "pp_Amount" => $amount,
            "pp_SecureHash" => $secure_hash,
        );
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->JazzCashClient->api_url .  $this->JazzCashClient->wallet_refund_url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post_data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if (curl_errno($ch)) {
            $error = curl_error($ch);
            curl_close($ch);
            return array(
                "pp_ResponseCode" => 0,
                "pp_ResponseMessage" => $error
            );
        } else {
            curl_close($ch);
            if ($httpCode >= 400) {
                return array(
                    "pp_ResponseCode" => 0,
                    "pp_ResponseMessage" => "HTTP request failed with status {$httpCode}",
                    "pp_RawResponse" => $response
                );
            }
            $resp = json_decode($response,true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return $resp;
            } else {
                throw new JazzCashException("Unparsable response from server: " . json_last_error_msg() . ". Response: " . strip_tags($response));
            }
        }
    }
    public function walletTransactionCNIC($order){
        if((!isset($order['account_number']) || $order['account_number'] == "")){
            throw new JazzCashException("Account Number is missing.");
        }
        if((!isset($order['cnic']) || $order['cnic'] == "")){
            throw new JazzCashException("CNIC is missing.");
        }
        if((!isset($order['amount']) || $order['amount'] == "")){
            throw new JazzCashException("Transaction Amount is missing.");
        }
        if (!is_numeric($order['amount']) || filter_var($order['amount'], FILTER_VALIDATE_FLOAT) === false) {
            throw new JazzCashException("Transaction Amount must be a number or float.");
        }
        $order['amount'] = $order['amount'] * 100;
        $order['transaction_reference'] = (isset($order['transaction_reference']) && $order['transaction_reference'] != "") ? $this->JazzCashClient->domain_code . $order['transaction_reference'] : $this->JazzCashClient->domain_code . date('YmdHis') . mt_rand(10, 100);;
        if(strlen($order['transaction_reference']) > 20 || strlen($order['transaction_reference']) <= 0){
            throw new JazzCashException("Transaction Reference must be a maximum of 20 characters, can not be empty & must be unique");
        }
        $order['order_id'] = (isset($order['order_id']) && $order['order_id'] != "") ? $order['order_id'] : uniqid();
        $order['date_time'] = (isset($order['date_time']) && $order['date_time'] != "") ? $order['date_time'] : date("Ymdhis");
        $order['expiry_time'] = date('Ymdhis', strtotime($order['date_time'] . ' +10 minutes'));
        $order['bill_reference'] = (isset($order['bill_reference']) && $order['bill_reference'] != "") ? $order['bill_reference'] : "";
        
        $order['description'] = (isset($order['description']) && $order['description'] != "") ? $order['description'] : "";
        $order['metafield_1'] = (isset($order['metafield_1']) && $order['metafield_1'] != "") ? $order['metafield_1'] : "";
        $order['metafield_2'] = (isset($order['metafield_2']) && $order['metafield_2'] != "") ? $order['metafield_2'] : "";
        $order['metafield_3'] = (isset($order['metafield_3']) && $order['metafield_3'] != "") ? $order['metafield_3'] : "";
        $order['metafield_4'] = (isset($order['metafield_4']) && $order['metafield_4'] != "") ? $order['metafield_4'] : "";
        $order['metafield_5'] = (isset($order['metafield_5']) && $order['metafield_5'] != "") ? $order['metafield_5'] : "";
        $order['bank_id'] = $this->JazzCashClient->bank_id;
        $order['registered_user'] = $this->JazzCashClient->registered_user;
        $order['language'] = $this->JazzCashClient->language;
        $order['currency'] = $this->JazzCashClient->currency;
        $order['product_id'] = $this->JazzCashClient->product_id;
        $order['transaction_type'] =  $this->JazzCashClient->transaction_type;
        $order['version'] = $this->JazzCashClient->version;
        $order['sub_merchant_id'] = $this->JazzCashClient->sub_merchant_id;

        $hash_data = array(
            $order['amount'],
            $order['bank_id'],
            $order['bill_reference'],
            $order['cnic'],
            $order['description'],
            $order['language'],
            $this->JazzCashClient->merchant_id,
            $order['account_number'],
            $this->JazzCashClient->password,
            $order['product_id'],
            $order['currency'],
            $order['date_time'],
            $order['expiry_time'],
            $order['transaction_reference'],
            $order['metafield_1'],
            $order['metafield_2'],
            $order['metafield_3'],
            $order['metafield_4'],
            $order['metafield_5'],
        );
        $sorted_hash = $this->JazzCashClient->integerity_salt;
        for ($i = 0; $i < count($hash_data); $i++) {
            if ($hash_data[$i] != 'undefined' and $hash_data[$i] != null and $hash_data[$i] != "") {
                $sorted_hash .= "&" . $hash_data[$i];
            }
        }
        $secure_hash = hash_hmac('sha256', $sorted_hash, $this->JazzCashClient->integerity_salt);

        $post_data = array(
            "pp_Amount" => $order['amount'],
            "pp_BillReference" => $order['bill_reference'],
            "pp_CNIC" => $order['cnic'],
            "pp_Description" => $order['description'],
            "pp_Language" => $order['language'],
            "pp_MerchantID" => $this->JazzCashClient->merchant_id,
            "pp_MobileNumber" => $order['account_number'],
            "pp_Password" => $this->JazzCashClient->password,
            "pp_SecureHash" => $secure_hash,
            "pp_TxnCurrency" => $order['currency'],
            "pp_TxnDateTime" => $order['date_time'],
            "pp_TxnExpiryDateTime" => $order['expiry_time'],
            "pp_TxnRefNo" => $order['transaction_reference'],
            "ppmpf_1" => $order['metafield_1'],
            "ppmpf_2" => $order['metafield_2'],
            "ppmpf_3" => $order['metafield_3'],
            "ppmpf_4" => $order['metafield_4'],
            "ppmpf_5" => $order['metafield_5'],
        );
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->JazzCashClient->api_url .  $this->JazzCashClient->wallet_cnic_transaction_url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post_data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if (curl_errno($ch)) {
            $error = curl_error($ch);
            curl_close($ch);
            return array(
                "pp_ResponseCode" => 0,
                "pp_ResponseMessage" => $error
            );
        } else {
            curl_close($ch);
            if ($httpCode >= 400) {
                return array(
                    "pp_ResponseCode" => 0,
                    "pp_ResponseMessage" => "HTTP request failed with status {$httpCode}",
                    "pp_RawResponse" => $response
                );
            }
            $resp = json_decode($response,true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return $resp;
            } else {
                throw new JazzCashException("Unparsable response from server: " . json_last_error_msg() . ". Response: " . strip_tags($response));
            }
        }
    }
}
