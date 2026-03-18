<?php

namespace ShaqiLabs\SafePayEmbedded;

class SafePayEmbeddedAPI
{
    private $SafePayEmbeddedClient;

    public function __construct(SafePayEmbeddedClient $SafePayEmbeddedClient)
    {
        $this->SafePayEmbeddedClient = $SafePayEmbeddedClient;
    }

    /**
    * Create Customer
    *
    * @return array
    *   Decoded response data.
    */
    public function createCustomer($data)
    {
        return $this->SafePayEmbeddedClient->createCustomer($data);
    }

    /**
    * Update Customer
    *
    * @return array
    *   Decoded response data.
    */
    public function updateCustomer($data)
    {
        return $this->SafePayEmbeddedClient->updateCustomer($data);
    }

    /**
    * Retrieve Customer
    *
    * @return array
    *   Decoded response data.
    */
    public function retrieveCustomer($token)
    {
        return $this->SafePayEmbeddedClient->retrieveCustomer($token);
    }

    /**
    * Delete Customer
    *
    * @return array
    *   Decoded response data.
    */
    public function deleteCustomer($token)
    {
        return $this->SafePayEmbeddedClient->deleteCustomer($token);
    }
    
    /**
    * Card Vault URL
    *
    * @return array
    *   Decoded response data.
    */
    public function getCardVaultURL($token, $url = "redirect")
    {
        return $this->SafePayEmbeddedClient->getCardVaultURL($token, $url);
    }

    /**
    * Get All Payment Methods
    *
    * @return array
    *   Decoded response data.
    */
    public function getAllPaymentMethods($token)
    {
        return $this->SafePayEmbeddedClient->getAllPaymentMethods($token);
    }

    /**
    * Get Payment Method
    *
    * @return array
    *   Decoded response data.
    */
    public function getPaymentMethod($token, $payment_token)
    {
        return $this->SafePayEmbeddedClient->getPaymentMethod($token, $payment_token);
    }
    

    /**
    * Delete Payment Method
    *
    * @return array
    *   Decoded response data.
    */
    public function deletePaymentMethod($token, $payment_token)
    {
        return $this->SafePayEmbeddedClient->deletePaymentMethod($token, $payment_token);
    }

    /**
    * Charge Customer
    *
    * @return array
    *   Decoded response data.
    */
    public function chargeCustomer($data, $threeDS = 0)
    {
        return $this->SafePayEmbeddedClient->chargeCustomer($data, $threeDS);
    }
    
    public function initiate3DSSecure($data)
    {
        return $this->SafePayEmbeddedClient->initiate3DSSecure($data);
    }
    public function process3DSRequest($data)
    {
        return $this->SafePayEmbeddedClient->process3DSRequest($data);
    }
    public function requestOTPCode3DS($data)
    {
        return $this->SafePayEmbeddedClient->requestOTPCode3DS($data);
    }
    public function charge3DS($data)
    {
        return $this->SafePayEmbeddedClient->charge3DS($data);
    }
    
    /**
    * Verify Payment Webhook
    *
    * @return array
    *   Decoded response data.
    */
    public function verifyPayment()
    {
        return $this->SafePayEmbeddedClient->verifyPayment();
    }

    
    /**
    * Verify Payment Secured Webhook
    *
    * @return array
    *   Decoded response data.
    */
    public function verifyPaymentSecured()
    {
        return $this->SafePayEmbeddedClient->verifyPaymentSecured();
    }
}
