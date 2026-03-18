<?php

namespace ShaqiLabs\AlfalahAPG;

class AlfalahAPGAPI
{
    private $AlfalahAPGClient;
    private $callback_url;

    public function __construct(AlfalahAPGClient $AlfalahAPGClient)
    {
        $this->AlfalahAPGClient = $AlfalahAPGClient;
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
            throw new AlfalahAPGException("Transaction Amount is missing.");
        }
        if (!is_numeric($order['amount']) || filter_var($order['amount'], FILTER_VALIDATE_FLOAT) === false) {
            throw new AlfalahAPGException("Transaction Amount must be a number or float.");
        }
        if($this->AlfalahAPGClient->return_url == "" && (!isset($order['return_url']) || $order['return_url'] == "")){
            throw new AlfalahAPGException("Return URL is missing. It can either be set once for all transactions or provided against each order or both.");
        }
        $order['order_id'] = (isset($order['order_id']) && $order['order_id'] != "") ? $order['order_id'] : uniqid();
        $auth_data = $this->AlfalahAPGClient->hash_request;
        $auth_data["HS_TransactionReferenceNumber"] = $order['order_id'];
        $cipher = openssl_encrypt(urldecode(http_build_query($auth_data)), $this->AlfalahAPGClient->cipher, $this->AlfalahAPGClient->key1, OPENSSL_RAW_DATA , $this->AlfalahAPGClient->key2);
        $auth_data["HS_RequestHash"] = base64_encode($cipher);
        
        $endpoint = "";
        $method = 'POST';
        $payload = $this->AlfalahAPGClient->makeRequest($endpoint, $method, $auth_data);
        if(!isset($payload['success']) || (isset($payload['success']) && $payload['success'] != true)){
            throw new AlfalahAPGException("There was an error generating access token. Response: " . json_encode($payload));
        }
        $order_data = $this->AlfalahAPGClient->form_request;
        $order_data["TransactionReferenceNumber"] = $order['order_id'];
        $order_data["AuthToken"] = $payload['AuthToken'];
        $order_data["RequestHash"] = NULL;
        $order_data["IsBIN"] = "0";
        $order_data["Currency"] = (isset($config['currency']) && $config['currency'] != "") ? $config['currency'] : $this->AlfalahAPGClient->currency;
        $order_data["TransactionTypeId"] = $this->AlfalahAPGClient->transaction_type;
        $order_data["TransactionAmount"] = $order['amount'];
        $cipher = openssl_encrypt(urldecode(http_build_query($order_data)), $this->AlfalahAPGClient->cipher, $this->AlfalahAPGClient->key1, OPENSSL_RAW_DATA , $this->AlfalahAPGClient->key2);
        $order_data["RequestHash"] = base64_encode($cipher);

        if($response_type == "data"){
            return $order_data;
        } else if($response_type == "form"){
            return $this->generateForm($order_data);
        } else if($response_type == "redirect"){
            $form = $this->generateForm($order_data);
            $form .= '<script>
                document.addEventListener("DOMContentLoaded", function() {
                    document.getElementById("alfalahapg-form").submit();
                });
            </script>';
            echo $form;
            return;
        }
    }

    public function dynamicRedirect($data){
        $form = $this->generateForm($data);
        $form .= '<script>
            document.addEventListener("DOMContentLoaded", function() {
                document.getElementById("alfalahapg-form").submit();
            });
        </script>';
        echo $form;
        return;
    }
    
    public function generateForm($order){
        $form = '<form action="' . $this->AlfalahAPGClient->form_url . '" id="alfalahapg-form" method="post" novalidate="novalidate" style = "display:none;">                                                              
            <input id="AuthToken" name="AuthToken" type="hidden" value="' . $order['AuthToken'] . '">                                                                                                                                
            <input id="RequestHash" name="RequestHash" type="hidden" value="' . $order['RequestHash'] . '">                                                                                                                            
            <input id="ChannelId" name="ChannelId" type="hidden" value="' . $order['ChannelId'] . '">                                                                                                                            
            <input id="Currency" name="Currency" type="hidden" value="' . $order['Currency'] . '">                                                                                                                               
            <input id="IsBIN" name="IsBIN" type="hidden" value="' . $order['IsBIN'] . '">                                                                                     
            <input id="ReturnURL" name="ReturnURL" type="hidden" value="' . $order['ReturnURL'] . '">                                                                            
            <input id="MerchantId" name="MerchantId" type="hidden" value="' . $order['MerchantId'] . '">                                                                                                                           
            <input id="StoreId" name="StoreId" type="hidden" value="' . $order['StoreId'] . '">                                                                                                                     
            <input id="MerchantHash" name="MerchantHash" type="hidden" value="' . $order['MerchantHash'] . '">                                  
            <input id="MerchantUsername" name="MerchantUsername" type="hidden" value="' . $order['MerchantUsername'] . '">                                                                                                            
            <input id="MerchantPassword" name="MerchantPassword" type="hidden" value="' . $order['MerchantPassword'] . '">  
            <input id="TransactionTypeId" name="TransactionTypeId" type="hidden" value="' . $order['TransactionTypeId'] . '">                                                                                                                                                     
            <input autocomplete="off" id="TransactionReferenceNumber" name="TransactionReferenceNumber" type="hidden" value="' . $order['TransactionReferenceNumber'] . '">                                  
            <input autocomplete="off"  id="TransactionAmount" name="TransactionAmount" placeholder="Transaction Amount" type="hidden" value="' . $order['TransactionAmount'] . '">  
            <input type="SUBMIT" value="SUBMIT">                                                                    
        </form>';
        return $form;
    }
}
