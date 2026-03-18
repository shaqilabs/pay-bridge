<?php

namespace ShaqiLabs\AlfalahAPG;
class AlfalahAPGClient
{
    public $environment;
    public $api_url;
    public $form_url;
    public $key1;
    public $key2;
    private $channel_id;
    private $merchant_id;
    private $store_id;
    private $redirection_request;
    private $merchant_hash;
    private $merchant_username;
    private $merchant_password;
    public $transaction_type;
    public $cipher;
    public $return_url;
    public $currency;
    public $hash_request;
    public $form_request;
    private $timeout;
    private $connect_timeout;

    /**
    * AlfalahAPGClient constructor.
    * @param array $config.
    */
    public function __construct($config)
    {
        //LIVE = https://payments.bankalfalah.com/HS/HS/HS
        //TEST = https://sandbox.bankalfalah.com/HS/HS/HS

        //FORM LIVE = https://payments.bankalfalah.com/SSO/SSO/SSO
        //FORM TEST = https://sandbox.bankalfalah.com/SSO/SSO/SSO
        $this->environment = (isset($config['environment']) && in_array($config['environment'], ['sandbox','production'])) ? $config['environment'] : "production";
        $this->api_url = ($this->environment == 'production') ? "https://payments.bankalfalah.com/HS/HS/HS" : "https://sandbox.bankalfalah.com/HS/HS/HS";
        $this->form_url = ($this->environment == 'production') ? "https://payments.bankalfalah.com/SSO/SSO/SSO" : "https://sandbox.bankalfalah.com/SSO/SSO/SSO";
        $this->timeout = (isset($config['timeout']) && is_numeric($config['timeout'])) ? (int)$config['timeout'] : 30;
        $this->connect_timeout = (isset($config['connect_timeout']) && is_numeric($config['connect_timeout'])) ? (int)$config['connect_timeout'] : 10;
        $this->key1 = (isset($config['key1']) && $config['key1'] != "") ? $config['key1'] : throw new AlfalahAPGException("Key1 is missing");
        $this->key2 = (isset($config['key2']) && $config['key2'] != "") ? $config['key2'] : throw new AlfalahAPGException("Key2 is missing");
        $this->channel_id = (isset($config['channel_id']) && $config['channel_id'] != "") ? $config['channel_id'] : throw new AlfalahAPGException("Channel ID is missing");
        $this->merchant_id = (isset($config['merchant_id']) && $config['merchant_id'] != "") ? $config['merchant_id'] : throw new AlfalahAPGException("Merchant ID is missing");
        $this->store_id = (isset($config['store_id']) && $config['store_id'] != "") ? $config['store_id'] : throw new AlfalahAPGException("Store ID is missing");
        $this->redirection_request = (isset($config['redirection_request']) && $config['redirection_request'] != "") ? $config['redirection_request'] : "0";
        $this->merchant_hash = (isset($config['merchant_hash']) && $config['merchant_hash'] != "") ? $config['merchant_hash'] : throw new AlfalahAPGException("Merchant Hash is missing");
        $this->merchant_username = (isset($config['merchant_username']) && $config['merchant_username'] != "") ? $config['merchant_username'] : throw new AlfalahAPGException("Merchant Username is missing");
        $this->merchant_password = (isset($config['merchant_password']) && $config['merchant_password'] != "") ? $config['merchant_password'] : throw new AlfalahAPGException("Merchant Password is missing");
        $this->transaction_type = (isset($config['transaction_type']) && $config['transaction_type'] != "") ? $config['transaction_type'] : "3";
        $this->cipher = (isset($config['cipher']) && $config['cipher'] != "") ? $config['cipher'] : "aes-128-cbc";
        $this->return_url = (isset($config['return_url']) && $config['return_url'] != "") ? $config['return_url'] : throw new AlfalahAPGException("Return URL is missing");
        $this->currency = (isset($config['currency']) && $config['currency'] != "") ? $config['currency'] : "PKR";
        $this->hash_request = (array(
            "HS_ChannelId" => $this->channel_id,
            "HS_IsRedirectionRequest" => $this->redirection_request,
            "HS_MerchantId" => $this->merchant_id,
            "HS_StoreId" => $this->store_id,
            "HS_ReturnURL" => $this->return_url,
            "HS_MerchantHash" => $this->merchant_hash,
            "HS_MerchantUsername" => $this->merchant_username,
            "HS_MerchantPassword" => $this->merchant_password,
        ));      
        $this->form_request = (array(
            "ChannelId" => $this->channel_id,
            "IsRedirectionRequest" => $this->redirection_request,
            "MerchantId" => $this->merchant_id,
            "StoreId" => $this->store_id,
            "ReturnURL" => $this->return_url,
            "MerchantHash" => $this->merchant_hash,
            "MerchantUsername" => $this->merchant_username,
            "MerchantPassword" => $this->merchant_password,
        ));        
    }

    /**
    * Make a request to the AlfalahAPG API.
    * @param string $endpoint   API endpoint.
    * @param string $method     HTTP method (GET, POST, PUT, DELETE).
    * @param array $data        Data to send with the request (for POST, PUT, DELETE).
    * @return array            Decoded response data.
    * @throws AlfalahAPGException    If the request or response encounters an error.
    */
    public function makeRequest($endpoint, $method = 'GET', $data = [], $queryParams = [])
    {
        $url = rtrim($this->api_url, "/") . '/' . ltrim($endpoint, '/');
        $headers = ["Content-Type: application/json"];
        $response = $this->sendRequest($url, $method, $headers, $data, $queryParams);
        $responseData = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new AlfalahAPGException("Unparsable response from server: " . json_last_error_msg() . ". Response: " . substr(trim(strip_tags((string)$response)), 0, 2000));
        }
        return $responseData;
    }

    private function sendRequest($url, $method, $headers, $data, $queryParams = [])
    {
        if (!empty($queryParams)) {
            $url .= '?' . http_build_query($queryParams);
        }
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        // curl_setopt($ch, CURLOPT_USERAGENT, 'CURL/PHP Pay Bridge');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $this->connect_timeout);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        if ($method === 'POST' || $method === 'PUT' || $method === 'DELETE') {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            if($method == "POST"){
                curl_setopt($ch, CURLOPT_POST, true);
            }
        }
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if (curl_errno($ch)) {
            $error = curl_error($ch);
            curl_close($ch);
            throw new AlfalahAPGException('cURL request failed: ' . $error);
        }
        curl_close($ch);
        if ($httpCode >= 400) {
            throw new AlfalahAPGException("HTTP request failed with status {$httpCode}. Response: " . substr(trim(strip_tags((string)$response)), 0, 2000));
        }
        return $response;
    }
}
