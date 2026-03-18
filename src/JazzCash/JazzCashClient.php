<?php

namespace ShaqiLabs\JazzCash;
class JazzCashClient
{
    public $environment;
    public $currency;
    public $return_url;
    public $api_url;
    public $merchant_id;
    public $password;
    public $integerity_salt;
    public $hash_request;
    public $domain_code;
    public $checkout_url;
    public $wallet_url;
    public $wallet_transaction_url;
    public $transaction_status_url;
    public $card_refund_url;
    public $wallet_refund_url;
    public $bank_id;
    public $registered_user;
    public $language;
    public $product_id;
    public $transaction_type;
    public $version;
    public $sub_merchant_id;
    public $wallet_cnic_transaction_url;
    private $timeout;
    private $connect_timeout;

    /**
    * JazzCashClient constructor.
    * @param array $config.
    */
    public function __construct($config)
    {
        //LIVE = https://payments.jazzcash.com.pk/
        //TEST = https://sandbox.jazzcash.com.pk/
        $this->environment = (isset($config['environment']) && in_array($config['environment'], ['sandbox','production'])) ? $config['environment'] : "production";
        $this->api_url = ($this->environment == 'production') ? "https://payments.jazzcash.com.pk/" : "https://sandbox.jazzcash.com.pk/";
        $this->timeout = (isset($config['timeout']) && is_numeric($config['timeout'])) ? (int)$config['timeout'] : 30;
        $this->connect_timeout = (isset($config['connect_timeout']) && is_numeric($config['connect_timeout'])) ? (int)$config['connect_timeout'] : 10;
        $this->currency = (isset($config['currency']) && $config['currency'] != "") ? $config['currency'] : "PKR";
        $this->merchant_id = (isset($config['merchant_id']) && $config['merchant_id'] != "") ? $config['merchant_id'] : throw new JazzCashException("Merchant ID is missing");
        $this->password = (isset($config['password']) && $config['password'] != "") ? $config['password'] : throw new JazzCashException("Password is missing");
        $this->integerity_salt = (isset($config['integerity_salt']) && $config['integerity_salt'] != "") ? $config['integerity_salt'] : throw new JazzCashException("Integerity Salt is missing");
        $this->return_url = (isset($config['return_url']) && $config['return_url'] != "") ? $config['return_url'] : throw new JazzCashException("Return URL is missing");
        $this->bank_id = (isset($config['bank_id']) && $config['bank_id'] != "") ? $config['bank_id'] : "";
        $this->registered_user = (isset($config['registered_user']) && $config['registered_user'] != "") ? $config['registered_user'] : "No";
        $this->language = (isset($config['language']) && $config['language'] != "") ? $config['language'] : "EN";
        $this->product_id = (isset($config['product_id']) && $config['product_id'] != "") ? $config['product_id'] : "";
        $this->transaction_type = (isset($config['transaction_type']) && $config['transaction_type'] != "") ? $config['transaction_type'] : "";
        $this->version = (isset($config['version']) && $config['version'] != "") ? $config['version'] : "2.0";
        $this->sub_merchant_id = (isset($config['sub_merchant_id']) && $config['sub_merchant_id'] != "") ? $config['sub_merchant_id'] : "";
        $this->domain_code = (isset($config['domain_code']) && $config['domain_code'] != "") ? $config['domain_code'] : throw new JazzCashException("Domain Code is missing");
        if(strlen($this->domain_code) > 3){
            throw new JazzCashException("Domain Code can not be more than 3 character long");
        }
        $this->checkout_url = "CustomerPortal/transactionmanagement/merchantform/";
        $this->wallet_url = "WalletLinkingPortal/wallet/LinkWallet/";
        $this->wallet_transaction_url = "ApplicationAPI/API/4.0/purchase/domwallettransactionviatoken";
        $this->transaction_status_url = "ApplicationAPI/API/PaymentInquiry/Inquire";
        $this->card_refund_url = "ApplicationAPI/API/authorize/Refund";
        $this->wallet_refund_url = "ApplicationAPI/API/Purchase/domwalletrefundtransaction";
        $this->wallet_cnic_transaction_url = "ApplicationAPI/API/2.0/Purchase/DoMWalletTransaction";
    }

    /**
    * Make a request to the JazzCash API.
    * @param string $endpoint   API endpoint.
    * @param string $method     HTTP method (GET, POST, PUT, DELETE).
    * @param array $data        Data to send with the request (for POST, PUT, DELETE).
    * @return array            Decoded response data.
    * @throws JazzCashException    If the request or response encounters an error.
    */
    public function makeRequest($endpoint, $method = 'GET', $data = [], $queryParams = [])
    {
        $url = rtrim($this->api_url, "/") . '/' . ltrim($endpoint, '/');
        $headers = ["Content-Type: application/json"];
        $response = $this->sendRequest($url, $method, $headers, $data, $queryParams);
        $responseData = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new JazzCashException("Unparsable response from server: " . json_last_error_msg() . ". Response: " . substr(trim(strip_tags((string)$response)), 0, 2000));
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
            throw new JazzCashException('cURL request failed: ' . $error);
        }
        curl_close($ch);
        if ($httpCode >= 400) {
            throw new JazzCashException("HTTP request failed with status {$httpCode}. Response: " . substr(trim(strip_tags((string)$response)), 0, 2000));
        }
        return $response;
    }
}
