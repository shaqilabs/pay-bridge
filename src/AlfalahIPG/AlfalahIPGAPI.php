<?php

namespace ShaqiLabs\AlfalahIPG;

class AlfalahIPGAPI
{
    private $AlfalahIPGClient;
    private $callback_url;

    public function __construct(AlfalahIPGClient $AlfalahIPGClient)
    {
        $this->AlfalahIPGClient = $AlfalahIPGClient;
    }
    /**
    * Create Checkout Link
    *
    * @return array
    *   Decoded response data.
    */
    public function createCheckoutLink($order, $response_type = "redirect")
    {
        if((!isset($order['amount']) || $order['amount'] == "")){
            throw new AlfalahIPGException("Transaction Amount is missing.");
        }
        if (!is_numeric($order['amount']) || filter_var($order['amount'], FILTER_VALIDATE_FLOAT) === false) {
            throw new AlfalahIPGException("Transaction Amount must be a number or float.");
        }
        if($this->AlfalahIPGClient->success_url == "" && (!isset($order['return_url']) || $order['return_url'] == "")){
            throw new AlfalahIPGException("Return URL is missing. It can either be set once for all transactions or provided against each order or both.");
        }
        if((!isset($order['description']) || $order['description'] == "")){
            throw new AlfalahIPGException("Order Description is missing.");
        }
        $order['order_id'] = (isset($order['order_id']) && $order['order_id'] != "") ? $order['order_id'] : uniqid();

        $auth_data = array(
            "apiOperation" => "INITIATE_CHECKOUT",
            "interaction" => array(
                "operation" => $this->AlfalahIPGClient->transaction_type,
                "merchant" => array(
                    "name" => $this->AlfalahIPGClient->merchant_name,
                ),
                "returnUrl" => (isset($order['return_url']) && $order['return_url'] != "") ? $order['return_url'] : $this->AlfalahIPGClient->success_url,
            ),
            "order" => array(
                "id" => $order['order_id'],
                "amount" => $order['amount'],
                "currency" => (isset($order['currency_code']) && $order['currency_code'] != "") ? $order['currency_code'] : $this->AlfalahIPGClient->currency,
                "description" => $order['description'],
            ),
        );
        if(isset($order['data'])){
            $auth_data = $this->merge_arrays($auth_data, $order['data']);
        }

        $endpoint = 'api/rest/version/' . $this->AlfalahIPGClient->api_version . "/merchant/" . $this->AlfalahIPGClient->merchant_id . "/session";
        $method = 'POST';
        $payload = $this->AlfalahIPGClient->makeRequest($endpoint, $method, $auth_data);
        if(!isset($payload['result']) || (isset($payload['result']) && $payload['result'] != 'SUCCESS')){
            throw new AlfalahIPGException("There was an error generating access token. Response: " . json_encode($payload));
        }
        $successIndicator = $payload['successIndicator'];
        $access_token = $payload['session']['id'];
        if($response_type == "data"){
            return array(
                "success_indicator" => $successIndicator,
                "access_token" => $access_token,
            );
        } else if($response_type == "redirect"){
            $this->dynamicRedirect($access_token);
        }
    }

    public function dynamicRedirect($access_token){
        if(!isset($access_token) || (isset($access_token) && $access_token == '')){
            throw new AlfalahIPGException("Access Token is required.");
        }
        $session = array(
            "session" => array(
                "id" => $access_token
            )
        );
        $page_html = "<script src = '" . $this->AlfalahIPGClient->checkout_url . "'></script><script>Checkout.configure(" . json_encode($session) . "); Checkout.showPaymentPage()</script>";
        echo $page_html;
        return;
    }

    function merge_arrays($array1, $array2) {
        foreach ($array2 as $key => $value) {
            if (is_array($value) && isset($array1[$key]) && is_array($array1[$key])) {
                $array1[$key] = $this->merge_arrays($array1[$key], $value);
            } else {
                $array1[$key] = $value;
            }
        }
        return $array1;
    }
}
