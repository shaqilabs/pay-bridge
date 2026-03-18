<?php

namespace ShaqiLabs\AbhiPay;
class AbhiPayClient
{
    public $currency;
    public $return_url;
    public $api_url;
    public $merchant_id;
    public $secret_key;
    public $card_save;
    public $language;
    public $operation;
    private $timeout;
    private $connect_timeout;

    /**
    * AbhiPayClient constructor.
    * @param array $config.
    */
    public function __construct($config)
    {
        //LIVE = https://api.abhipay.com.pk/api/v3/
        //TEST = https://api.abhipay.com.pk/api/v3/

        $this->api_url = "https://api.abhipay.com.pk/api/v3/";
        $this->timeout = (isset($config['timeout']) && is_numeric($config['timeout'])) ? (int)$config['timeout'] : 30;
        $this->connect_timeout = (isset($config['connect_timeout']) && is_numeric($config['connect_timeout'])) ? (int)$config['connect_timeout'] : 10;
        $this->currency = (isset($config['currency']) && $config['currency'] != "") ? $config['currency'] : "PKR";
        $this->merchant_id = (isset($config['merchant_id']) && $config['merchant_id'] != "") ? $config['merchant_id'] : throw new AbhiPayException("Merchant ID is missing");
        $this->secret_key = (isset($config['secret_key']) && $config['secret_key'] != "") ? $config['secret_key'] : throw new AbhiPayException("Secret Key is missing");
        $this->card_save = (isset($config['card_save']) && $config['card_save'] != "") ? $config['card_save'] : false;
        $this->language = (isset($config['language']) && $config['language'] != "") ? $config['language'] : "EN";
        $this->operation = (isset($config['operation']) && $config['operation'] != "") ? $config['operation'] : "PURCHASE";
        $this->return_url = (isset($config['return_url']) && $config['return_url'] != "") ? $config['return_url'] : "";
    }

    /**
    * Make a request to the AbhiPay API.
    * @param string $endpoint   API endpoint.
    * @param string $method     HTTP method (GET, POST, PUT, DELETE).
    * @param array $data        Data to send with the request (for POST, PUT, DELETE).
    * @return array            Decoded response data.
    * @throws AbhiPayException    If the request or response encounters an error.
    */
    public function makeRequest($endpoint, $method = 'GET', $data = [], $queryParams = [])
    {
        $url = rtrim($this->api_url, "/") . '/' . ltrim($endpoint, '/');
        $headers = [
            "Content-Type: application/json",
            "Authorization: " . $this->secret_key
        ];
        $response = $this->sendRequest($url, $method, $headers, $data, $queryParams);
        $responseData = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new AbhiPayException("Unparsable response from server: " . json_last_error_msg() . ". Response: " . substr(trim(strip_tags((string)$response)), 0, 2000));
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
            throw new AbhiPayException('cURL request failed: ' . $error);
        }
        curl_close($ch);
        if ($httpCode >= 400) {
            throw new AbhiPayException("HTTP request failed with status {$httpCode}. Response: " . substr(trim(strip_tags((string)$response)), 0, 2000));
        }
        return $response;
    }
}
