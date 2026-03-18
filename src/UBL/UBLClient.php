<?php

namespace ShaqiLabs\UBL;
class UBLClient
{
    private $api_url;
    public $customer;
    public $store;
    public $terminal;
    public $username;
    public $password;
    public $channel;
    public $currency;
    public $transaction_hint;
    public $callback_url;
    private $certifcate;
    private $timeout;
    private $connect_timeout;


    /**
    * UBLClient constructor.
    * @param array $config.
    */
    public function __construct($config)
    {
        //LIVE = https://ipg.comtrust.ae:2443/
        //TEST = https://demo-ipg.ctdev.comtrust.ae:2443/
        $certificatePath = (isset($config['certificate']) && $config['certificate'] != "")
            ? $config['certificate']
            : ((isset($config['certifcate']) && $config['certifcate'] != "") ? $config['certifcate'] : "");

        $this->api_url = (isset($config['api_url']) && $config['api_url'] != "") ? $config['api_url'] : "https://ipg.comtrust.ae:2443/";
        $this->timeout = (isset($config['timeout']) && is_numeric($config['timeout'])) ? (int)$config['timeout'] : 30;
        $this->connect_timeout = (isset($config['connect_timeout']) && is_numeric($config['connect_timeout'])) ? (int)$config['connect_timeout'] : 10;
        $this->customer = (isset($config['customer']) && $config['customer'] != "") ? $config['customer'] : "Pay Bridge";
        $this->store = (isset($config['store']) && $config['store'] != "") ? $config['store'] : "0000";
        $this->terminal = (isset($config['terminal']) && $config['terminal'] != "") ? $config['terminal'] : "0000";
        $this->username = (isset($config['username']) && $config['username'] != "") ? $config['username'] : throw new UBLException("Username is missing");
        $this->password = (isset($config['password']) && $config['password'] != "") ? $config['password'] : throw new UBLException("Password is missing");
        $this->channel = (isset($config['channel']) && $config['channel'] != "") ? $config['channel'] : "Web";
        $this->currency = (isset($config['currency']) && $config['currency'] != "") ? $config['currency'] : "PKR";
        $this->transaction_hint = (isset($config['transaction_hint']) && $config['transaction_hint'] != "") ? $config['transaction_hint'] : "CPT:Y";
        $this->callback_url = (isset($config['callback_url']) && $config['callback_url'] != "") ? $config['callback_url'] : "";
        $this->certifcate = ($certificatePath != "") ? $certificatePath : dirname(__FILE__) . "/ca.crt";
    }

    /**
    * Make a request to the UBL API.
    * @param string $endpoint   API endpoint.
    * @param string $method     HTTP method (GET, POST, PUT, DELETE).
    * @param array $data        Data to send with the request (for POST, PUT, DELETE).
    * @return array            Decoded response data.
    * @throws UBLException    If the request or response encounters an error.
    */
    public function makeRequest($endpoint, $method = 'GET', $data = [], $queryParams = [])
    {
        $url = rtrim($this->api_url, "/") . '/' . ltrim($endpoint, '/');
        $headers = ["Content-Type: application/json", 'Accept:text/xml-standard-api'];
        $response = $this->sendRequest($url, $method, $headers, $data, $queryParams);
        $response = (string)$response;
        $trimmed = ltrim($response);
        if ($trimmed !== '' && isset($trimmed[0]) && $trimmed[0] === '<' && function_exists('simplexml_load_string')) {
            $previous = libxml_use_internal_errors(true);
            $xml = simplexml_load_string($response);
            libxml_clear_errors();
            libxml_use_internal_errors($previous);
            if ($xml !== false) {
                return json_decode(json_encode($xml), true);
            }
        }
        $responseData = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new UBLException("Unparsable response from server: " . json_last_error_msg() . ". Response: " . substr(trim(strip_tags($response)), 0, 2000));
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
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_FORBID_REUSE, 1);
        curl_setopt($ch, CURLOPT_PORT, 2443);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
        curl_setopt($ch, CURLOPT_CAINFO, $this->certifcate);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $this->connect_timeout);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);

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
            throw new UBLException('cURL request failed: ' . $error);
        }
        curl_close($ch);
        if ($httpCode >= 400) {
            throw new UBLException("HTTP request failed with status {$httpCode}. Response: " . substr(trim(strip_tags((string)$response)), 0, 2000));
        }
        return $response;
    }
}
