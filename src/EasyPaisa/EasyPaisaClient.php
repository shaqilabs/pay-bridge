<?php

namespace ShaqiLabs\EasyPaisa;
class EasyPaisaClient
{
    public $environment;
    public $api_url;
    public $store_id;
    public $currency;
    public $return_url;
    public $username;
    public $password;
    public $ewp_account_number;
    public $hash_key;
    private $timeout;
    private $connect_timeout;

    /**
    * EasyPaisaClient constructor.
    * @param array $config.
    */
    public function __construct($config)
    {
        //LIVE = https://easypay.easypaisa.com.pk/
        //TEST = https://easypaystg.easypaisa.com.pk/
        
        $this->environment = (isset($config['environment']) && in_array($config['environment'], ['sandbox','production'])) ? $config['environment'] : "production";
        $this->api_url = ($this->environment == 'production') ? "https://easypay.easypaisa.com.pk/" : "https://easypaystg.easypaisa.com.pk/";
        $this->store_id = (isset($config['store_id']) && $config['store_id'] != "") ? $config['store_id'] : throw new EasyPaisaException("Store ID is missing");
        $this->hash_key = (isset($config['hash_key']) && $config['hash_key'] != "") ? $config['hash_key'] : throw new EasyPaisaException("Hash Key is missing");
        $this->ewp_account_number = (isset($config['ewp_account_number']) && $config['ewp_account_number'] != "") ? $config['ewp_account_number'] : throw new EasyPaisaException("EWP Account Number is missing");
        $this->return_url = (isset($config['return_url']) && $config['return_url'] != "") ? $config['return_url'] : "";
        $this->currency = (isset($config['currency']) && $config['currency'] != "") ? $config['currency'] : "PKR";
        $this->username = (isset($config['username']) && $config['username'] != "") ? $config['username'] : "";
        $this->password = (isset($config['password']) && $config['password'] != "") ? $config['password'] : "";
        $this->timeout = (isset($config['timeout']) && is_numeric($config['timeout'])) ? (int)$config['timeout'] : 30;
        $this->connect_timeout = (isset($config['connect_timeout']) && is_numeric($config['connect_timeout'])) ? (int)$config['connect_timeout'] : 10;
    }

    /**
    * Make a request to the EasyPaisa API.
    * @param string $endpoint   API endpoint.
    * @param string $method     HTTP method (GET, POST, PUT, DELETE).
    * @param array $data        Data to send with the request (for POST, PUT, DELETE).
    * @return array            Decoded response data.
    * @throws EasyPaisaException    If the request or response encounters an error.
    */
    public function makeRequest($endpoint, $method = 'GET', $data = [], $queryParams = [])
    {
        $url = rtrim($this->api_url, "/") . '/' . ltrim($endpoint, '/');
        $credentials = base64_encode($this->username . ":" . $this->password);
        $headers = [
            "Content-Type: application/json",
            "Credentials: $credentials"
        ];
        $response = $this->sendRequest($url, $method, $headers, $data, $queryParams);
        $responseData = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new EasyPaisaException("Unparsable response from server: " . json_last_error_msg() . ". Response: " . substr(trim(strip_tags((string)$response)), 0, 2000));
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
            throw new EasyPaisaException('cURL request failed: ' . $error);
        }
        curl_close($ch);
        if ($httpCode >= 400) {
            throw new EasyPaisaException("HTTP request failed with status {$httpCode}. Response: " . substr(trim(strip_tags((string)$response)), 0, 2000));
        }
        return $response;
    }
}
