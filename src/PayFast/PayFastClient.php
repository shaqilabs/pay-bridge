<?php

namespace ShaqiLabs\PayFast;
class PayFastClient
{
    public $api_url;
    public $merchant_id;
    public $api_password;
    public $currency_code;
    public $merchant_name;
    public $success_url;
    public $cancel_url;
    public $checkout_url;
    public $proccode;
    public $tran_type;
    public $base_url;
    public $verify_url;
    private $timeout;
    private $connect_timeout;


    /**
    * PayFastClient constructor.
    * @param array $config.
    */
    public function __construct($config)
    {
        //LIVE = https://ipg1.apps.net.pk/Ecommerce/api/
        //TEST = https://ipguat.apps.net.pk/Ecommerce/api/
        $this->api_url = (isset($config['api_url']) && $config['api_url'] != "") ? $config['api_url'] . "Ecommerce/api/" : "https://ipg1.apps.net.pk/Ecommerce/api/";
        $this->merchant_id = (isset($config['merchant_id']) && $config['merchant_id'] != "") ? $config['merchant_id'] : throw new PayFastException("Merchant ID is missing");
        $this->api_password = (isset($config['api_password']) && $config['api_password'] != "") ? $config['api_password'] : throw new PayFastException("API Password is missing");
        $this->currency_code = (isset($config['currency_code']) && $config['currency_code'] != "") ? $config['currency_code'] : "PKR";
        $this->merchant_name = (isset($config['merchant_name']) && $config['merchant_name'] != "") ? $config['merchant_name'] : "Pay Bridge";
        $this->success_url = (isset($config['success_url']) && $config['success_url'] != "") ? $config['success_url'] : "";
        $this->cancel_url = (isset($config['cancel_url']) && $config['cancel_url'] != "") ? $config['cancel_url'] : $this->success_url;
        $this->checkout_url = (isset($config['checkout_url']) && $config['checkout_url'] != "") ? $config['checkout_url'] : $this->success_url;
        $this->proccode = (isset($config['proccode']) && $config['proccode'] != "") ? $config['proccode'] : "00";
        $this->tran_type = (isset($config['tran_type']) && $config['tran_type'] != "") ? $config['tran_type'] : "ECOMM_PURCHASE";
        $this->timeout = (isset($config['timeout']) && is_numeric($config['timeout'])) ? (int)$config['timeout'] : 30;
        $this->connect_timeout = (isset($config['connect_timeout']) && is_numeric($config['connect_timeout'])) ? (int)$config['connect_timeout'] : 10;


        //API Based Transaction
        //LIVE = https://apipxyuat.apps.net.pk:8443/api/
        //TEST = https://apipxyuat.apps.net.pk:8443/api/
        $this->base_url = $config['api_url'] ==  "https://ipg1.apps.net.pk/Ecommerce/api/" ? "https://apipxyuat.apps.net.pk:8443/api/" : "https://apipxyuat.apps.net.pk:8443/api/";
        $this->verify_url = $config['api_url'] ==  "https://ipg1.apps.net.pk/Ecommerce/api/" ? "https://payfast-portal.apps.net.pk:5557/api/" : "https://payfast-portal.apps.net.pk:5557/api/";
    }

    /**
    * Make a request to the PayFast API.
    * @param string $endpoint   API endpoint.
    * @param string $method     HTTP method (GET, POST, PUT, DELETE).
    * @param array $data        Data to send with the request (for POST, PUT, DELETE).
    * @return array            Decoded response data.
    * @throws PayFastException    If the request or response encounters an error.
    */
    public function makeRequest($endpoint, $method = 'GET', $data = [], $queryParams = []){
        $url = rtrim($this->api_url, "/") . '/' . ltrim($endpoint, '/');
        $headers = ["Content-Type: application/json"];
        $response = $this->sendRequest($url, $method, $headers, $data, $queryParams);
        $responseData = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new PayFastException("Unparsable response from server: " . json_last_error_msg() . ". Response: " . substr(trim(strip_tags((string)$response)), 0, 2000));
        }
        return $responseData;
    }

    private function sendRequest($url, $method, $headers, $data, $queryParams = []){
        if (!empty($queryParams)) {
            $url .= '?' . http_build_query($queryParams);
        }
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_USERAGENT, 'CURL/PHP Pay Bridge');
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
            throw new PayFastException('cURL request failed: ' . $error);
        }
        curl_close($ch);
        if ($httpCode >= 400) {
            throw new PayFastException("HTTP request failed with status {$httpCode}. Response: " . substr(trim(strip_tags((string)$response)), 0, 2000));
        }
        return $response;
    }

    public function getAPIToken(){
        $data = array(
            "grant_type" => "client_credentials",
            "secured_key" => $this->api_password,
            "merchant_id" => $this->merchant_id,
        );
        $endpoint = 'token';
        $method = 'POST';
        $url = rtrim($this->base_url, "/") . '/' . ltrim($endpoint, '/');
        $ch = curl_init();
        curl_setopt_array($ch, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_CONNECTTIMEOUT => $this->connect_timeout,
            CURLOPT_TIMEOUT => $this->timeout,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => http_build_query($data),
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/x-www-form-urlencoded'
            ),
        ));
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if (curl_errno($ch)) {
            $error = curl_error($ch);
            curl_close($ch);
            return array(
                "status" => 0,
                "error" => $error,
            );
        }
        curl_close($ch);
        if ($httpCode >= 400) {
            return array(
                "status" => 0,
                "error" => "HTTP request failed with status {$httpCode}",
                "data" => $response
            );
        }
        $payload = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return array(
                "status" => 0,
                "error" => "Unparsable response from server: " . json_last_error_msg(),
                "data" => $response
            );
        }
        if(isset($payload['token']) && $payload['token'] != ""){
            return array(
                "status" => 1,
                "token" => $payload['token']
            );
        } else {
            return array(
                "status" => 0,
                "error" => "Error when issuing token",
                "data" => $payload
            );
        }
    }
    public function makeAPIRequest($endpoint, $method = 'GET', $data = [], $queryParams = []){
        $token = $this->getAPIToken();
        if($token['status'] == 0){
            return $token;
        }
        $url = rtrim($this->base_url, "/") . '/' . ltrim($endpoint, '/');
        $headers = array(
            'Content-Type: application/x-www-form-urlencoded',
            'Authorization: Bearer ' . $token['token']
        );
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_ENCODING, '');
        curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $this->connect_timeout);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        if ($method === 'POST' || $method === 'PUT' || $method === 'DELETE') {
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
            if($method == "POST"){
                curl_setopt($ch, CURLOPT_POST, true);
            }
        }
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if (curl_errno($ch)) {
            $error = curl_error($ch);
            curl_close($ch);
            return array(
                "status" => 0,
                "error" => $error,
            );
        }
        curl_close($ch);
        if ($httpCode >= 400) {
            return array(
                "status" => 0,
                "error" => "HTTP request failed with status {$httpCode}",
                "data" => $response
            );
        }
        $responseData = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return array(
                "status" => 0,
                "error" => "Unparsable response from server: " . json_last_error_msg(),
                "data" => $response
            );
        }
        return $responseData;
    }
    public function makeVerifyRequest($endpoint, $method = 'GET', $data = [], $queryParams = []){
        $url = rtrim($this->verify_url, "/") . '/' . ltrim($endpoint, '/');
        $headers = array(
            'Content-Type: application/x-www-form-urlencoded',
        );
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_ENCODING, '');
        curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $this->connect_timeout);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_USERPWD, $this->merchant_id . ":" . $this->api_password);
        if ($method === 'POST' || $method === 'PUT' || $method === 'DELETE') {
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
            if($method == "POST"){
                curl_setopt($ch, CURLOPT_POST, true);
            }
        }
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if (curl_errno($ch)) {
            $error = curl_error($ch);
            curl_close($ch);
            return array(
                "status" => 0,
                "error" => $error,
            );
        }
        curl_close($ch);
        if ($httpCode >= 400) {
            return array(
                "status" => 0,
                "error" => "HTTP request failed with status {$httpCode}",
                "data" => $response
            );
        }
        $responseData = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return array(
                "status" => 0,
                "error" => "Unparsable response from server: " . json_last_error_msg(),
                "data" => $response
            );
        }
        return $responseData;
    }
}
