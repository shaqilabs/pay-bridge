<?php

namespace ShaqiLabs\BaadMay;
class BaadMayClient
{
    public $environment;
    public $api_url;
    public $status_url;
    public $success_url;
    public $failure_url;
    public $api_key;
    private $timeout;
    private $connect_timeout;

    /**
    * BaadMayClient constructor.
    * @param array $config.
    */
    public function __construct($config)
    {
        //LIVE = https://web.baadmay.com/
        //TEST = https://webdev.baadmay.com/
        $this->environment = (isset($config['environment']) && in_array($config['environment'], ['sandbox','production'])) ? $config['environment'] : "production";
        $this->api_url = ($this->environment == 'production') ? "https://web.baadmay.com/" : "https://webdev.baadmay.com/";
        $this->status_url = ($this->environment == 'production') ? "https://api.baadmay.com/v1/" : "https://devip.baadmay.com/v1/";
        $this->api_key = (isset($config['api_key']) && $config['api_key'] != "") ? $config['api_key'] : throw new BaadMayException("API Key is missing");
        $this->success_url = (isset($config['success_url']) && $config['success_url'] != "") ? $config['success_url'] : "";
        $this->failure_url = (isset($config['failure_url']) && $config['failure_url'] != "") ? $config['failure_url'] : "";
        $this->timeout = (isset($config['timeout']) && is_numeric($config['timeout'])) ? (int)$config['timeout'] : 30;
        $this->connect_timeout = (isset($config['connect_timeout']) && is_numeric($config['connect_timeout'])) ? (int)$config['connect_timeout'] : 10;
    }

    /**
    * Make a request to the BaadMay API.
    * @param string $endpoint   API endpoint.
    * @param string $method     HTTP method (GET, POST, PUT, DELETE).
    * @param array $data        Data to send with the request (for POST, PUT, DELETE).
    * @return array            Decoded response data.
    * @throws BaadMayException    If the request or response encounters an error.
    */
    public function makeRequest($url, $method = 'GET', $data = [], $queryParams = [])
    {
        $url = rtrim($url);
        $headers = [
            "Content-Type: application/json",
            "Authorization: " . $this->api_key
        ];
        $response = $this->sendRequest($url, $method, $headers, $data, $queryParams);
        $responseData = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new BaadMayException("Unparsable response from server: " . json_last_error_msg() . ". Response: " . substr(trim(strip_tags((string)$response)), 0, 2000));
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
            throw new BaadMayException('cURL request failed: ' . $error);
        }
        curl_close($ch);
        if ($httpCode >= 400) {
            throw new BaadMayException("HTTP request failed with status {$httpCode}. Response: " . substr(trim(strip_tags((string)$response)), 0, 2000));
        }
        return $response;
    }
}
