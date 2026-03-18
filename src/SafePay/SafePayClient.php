<?php

namespace ShaqiLabs\SafePay;
use Safepay\Safepay;
class SafePayClient
{
    private $environment;
    private $apiKey;
    private $v1Secret;
    private $webhookSecret;
    private $Safepay;
    public $success_url;
    public $cancel_url;


    /**
    * SafePayClient constructor.
    * @param array $config.
    */
    public function __construct($config)
    {
        //LIVE = production
        //TEST = sandbox
        $this->success_url = (isset($config['success_url']) && $config['success_url'] != "") ? $config['success_url'] : "";
        $this->cancel_url = (isset($config['cancel_url']) && $config['cancel_url'] != "") ? $config['cancel_url'] : "";
        $config = [
            "environment" => (isset($config['environment']) && $config['environment'] != "") ? $config['environment'] : "production",
            "apiKey" => (isset($config['apiKey']) && $config['apiKey'] != "") ? $config['apiKey'] : throw new SafePayException("API Key is missing"),
            "v1Secret" => (isset($config['v1Secret']) && $config['v1Secret'] != "") ? $config['v1Secret'] : throw new SafePayException("v1Secret is missing"),
            "webhookSecret" => (isset($config['webhookSecret']) && $config['webhookSecret'] != "") ? $config['webhookSecret'] : "",
        ];
        $this->Safepay = new Safepay($config);
    }

    /**
    * SafePay Get Token.
    * @param array $config.
    */
    public function getToken($amount, $currency)
    {
        $response =  $this->Safepay->payments->getToken(['amount'=>$amount,'currency'=>$currency]);
        if(count($response) == 0 || !isset($response['token']) || $response['token'] == ""){
            throw new SafePayException("Error generating Token.");
        }
        return $response['token'];
    }

    /**
    * SafePay Get Checkout Link.
    * @param array $config.
    */
    public function getCheckoutLink($config)
    {
        $response = $this->Safepay->checkout->create($config);
        if(count($response) == 0 || !isset($response['result']) || $response['result'] != "success"){
            throw new SafePayException("Error generating Checkout Link.");
        }
        return array(
            "status" => 1,
            "checkout_url" => $response['redirect'],
            "tracker" => $config['token']
        );
    }

    /**
    * SafePay Verify Signature Success.
    * @param array $config.
    */
    public function verifySuccessSignature($tracker, $signature)
    {
        if( $this->Safepay->verify->signature($tracker,$signature) === true) {
            return true;
        } 
        throw new SafePayException("Failed to verify Signature.");
    }

    /**
    * SafePay Verify Signature Webhook.
    * @param array $config.
    */
    public function verifyWebhookSignature($data, $X_SFPY_SIGNATURE)
    {
        if( $this->Safepay->verify->webhook($data, $X_SFPY_SIGNATURE) === true) {
            return true;
        } 
        throw new SafePayException("Failed to verify Signature.");
    }
}
