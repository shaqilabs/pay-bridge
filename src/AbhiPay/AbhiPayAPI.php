<?php

namespace ShaqiLabs\AbhiPay;

class AbhiPayAPI
{
    private $AbhiPayClient;

    public function __construct(AbhiPayClient $AbhiPayClient){
        $this->AbhiPayClient = $AbhiPayClient;
    }
    public function createCheckoutLink($order, $response_type = "redirect"){
        if((!isset($order['amount']) || $order['amount'] == "")){
            throw new AbhiPayException("Transaction Amount is missing.");
        }
        if (!is_numeric($order['amount']) || filter_var($order['amount'], FILTER_VALIDATE_FLOAT) === false) {
            throw new AbhiPayException("Transaction Amount must be a number or float.");
        }
        if($this->AbhiPayClient->return_url == "" && (!isset($order['return_url']) || $order['return_url'] == "")){
            throw new AbhiPayException("Return URL is missing. It can either be set once for all transactions or provided against each order or both.");
        }
        $order['amount'] = (float)$order['amount'];
        $order['clientTransactionId'] = (isset($order['transaction_reference']) && $order['transaction_reference'] != "") ? $order['transaction_reference'] : uniqid();
        $order['currency'] = (isset($order['currency']) && $order['currency'] != "") ? $order['currency'] : $this->AbhiPayClient->currency;
        $order['language'] = (isset($order['language']) && $order['language'] != "") ? $order['language'] : $this->AbhiPayClient->language;
        $order['operation'] = (isset($order['operation']) && $order['operation'] != "") ? $order['operation'] : $this->AbhiPayClient->operation;
        $order['cardSave'] = (isset($order['card_save']) && $order['card_save'] != "") ? $order['card_save'] : $this->AbhiPayClient->card_save;
        $order['description'] = (isset($order['description']) && $order['description'] != "") ? $order['description'] : "";
        $order['callbackUrl'] = (isset($order['return_url']) && $order['return_url'] != "") ? $order['return_url'] : $this->AbhiPayClient->return_url;

        $endpoint = "orders";
        $method = 'POST';
        $payload = $this->AbhiPayClient->makeRequest($endpoint, $method, $order);
        if($response_type == "response"){
            return $payload;
        } else if($response_type == "url"){
            if(isset($payload['code']) && $payload['code'] == "00000" && isset($payload['payload']['paymentUrl'])){
                return $payload['payload']['paymentUrl'];
            }
            return $payload;
        } else if($response_type == "redirect"){
            if($payload['code'] == "00000"){
                if(headers_sent()){
                    throw new AbhiPayException("Unable to redirect because headers have already been sent.");
                }
                header('Location: '. $payload['payload']['paymentUrl']);
                return;
            } else {
                return $payload;
            }
        }
    }
    public function getOrder($order_id){
        if((!isset($order_id) || $order_id == "")){
            throw new AbhiPayException("Order ID is missing.");
        }
        $endpoint = "orders/" . $order_id;
        $method = 'GET';
        $payload = $this->AbhiPayClient->makeRequest($endpoint, $method);
        return $payload;
    }
    public function getOrderByTransactionReference($transaction_reference){
        if((!isset($transaction_reference) || $transaction_reference == "")){
            throw new AbhiPayException("Transaction Reference is missing.");
        }
        $endpoint = "orders/by-rrn/" . $transaction_reference;
        $method = 'GET';
        $payload = $this->AbhiPayClient->makeRequest($endpoint, $method);
        return $payload;
    }
    public function autoPay($order){
        if((!isset($order['amount']) || $order['amount'] == "")){
            throw new AbhiPayException("Transaction Amount is missing.");
        }
        if (!is_numeric($order['amount']) || filter_var($order['amount'], FILTER_VALIDATE_FLOAT) === false) {
            throw new AbhiPayException("Transaction Amount must be a number or float.");
        }
        if($this->AbhiPayClient->return_url == "" && (!isset($order['return_url']) || $order['return_url'] == "")){
            throw new AbhiPayException("Return URL is missing. It can either be set once for all transactions or provided against each order or both.");
        }
        if((!isset($order['payment_token']) || $order['payment_token'] == "")){
            throw new AbhiPayException("Payment Token is missing.");
        }
        $order['cardUuid'] = $order['payment_token'];
        $order['clientTransactionId'] = (isset($order['transaction_reference']) && $order['transaction_reference'] != "") ? $order['transaction_reference'] : uniqid();
        $order['currency'] = (isset($order['currency']) && $order['currency'] != "") ? $order['currency'] : $this->AbhiPayClient->currency;
        $order['language'] = (isset($order['language']) && $order['language'] != "") ? $order['language'] : $this->AbhiPayClient->language;
        $order['operation'] = (isset($order['operation']) && $order['operation'] != "") ? $order['operation'] : $this->AbhiPayClient->operation;
        $order['cardSave'] = (isset($order['card_save']) && $order['card_save'] != "") ? $order['card_save'] : $this->AbhiPayClient->card_save;
        $order['description'] = (isset($order['description']) && $order['description'] != "") ? $order['description'] : "";
        $order['callbackUrl'] = (isset($order['return_url']) && $order['return_url'] != "") ? $order['return_url'] : $this->AbhiPayClient->return_url;

        $endpoint = "autoPay";
        $method = 'POST';
        $payload = $this->AbhiPayClient->makeRequest($endpoint, $method, $order);
        return $payload;
    }
}
