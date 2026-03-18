<?php

namespace ShaqiLabs\SafePayEmbedded;
use Safepay\SafepayClient;
class SafePayEmbeddedClient
{
    private $environment;
    private $api_url;
    private $api_key;
    private $public_key;
    private $webhook_key;
    private $intent;
    private $mode;
    private $currency;
    private $source;
    private $is_implicit;
    private $vault_source;
    private $three_ds_url;


    /**
    * SafePayEmbeddedClient constructor.
    * @param array $config.
    */
    public function __construct($config)
    {
        //LIVE = production
        //TEST = sandbox
        $this->environment = (isset($config['environment']) && in_array($config['environment'], ['sandbox','development','production'])) ? $config['environment'] : "production";
        $this->api_url = ($this->environment == 'production') ? "https://api.getsafepay.com" : (($this->environment == 'development') ? "https://dev.api.getsafepay.com" : "https://sandbox.api.getsafepay.com");
        $this->three_ds_url = ($this->environment == 'production') ? "https://centinelapi.cardinalcommerce.com" : (($this->environment == 'development') ? "https://centinelapistag.cardinalcommerce.com" : "https://centinelapistag.cardinalcommerce.com");
        $this->card_vault_url = ($this->environment == 'production') ? "https://getsafepay.com" : (($this->environment == 'development') ? "https://dev.api.getsafepay.com" : "https://sandbox.api.getsafepay.com");
        $this->api_key = (isset($config['api_key']) && $config['api_key'] != "") ? $config['api_key'] : throw new SafePayEmbeddedException("API Key is missing");
        $this->public_key = (isset($config['public_key']) && $config['public_key'] != "") ? $config['public_key'] : throw new SafePayEmbeddedException("Public Key is missing");
        $this->webhook_key = (isset($config['webhook_key']) && $config['webhook_key'] != "") ? $config['webhook_key'] : "";
        $this->intent = (isset($config['intent']) && $config['intent'] != "") ? $config['intent'] : "CYBERSOURCE";
        $this->mode = (isset($config['mode']) && $config['mode'] != "") ? $config['mode'] : "unscheduled_cof";
        $this->currency = (isset($config['currency']) && $config['currency'] != "") ? $config['currency'] : "PKR";
        $this->source = (isset($config['source']) && $config['source'] != "") ? $config['source'] : "Pay Bridge";
        $this->vault_source = (isset($config['vault_source']) && $config['vault_source'] != "") ? $config['vault_source'] : "mobile";
        $this->is_implicit = (isset($config['is_implicit']) && $config['is_implicit'] != "") ? $config['is_implicit'] : false;
        $this->Safepay = new SafepayClient(array(
            "api_key" => $this->api_key,
            "api_base" => $this->api_url,
        ));
    }

    /**
    * SafePayEmbedded Create Customer.
    * @param array $config.
    */
    public function createCustomer($customer)
    {
        if(!is_array($customer)){
            throw new SafePayEmbeddedException("Data must be an associative array");
        }
        $first_name = (isset($customer['first_name']) && $customer['first_name'] != "") ? $customer['first_name'] : throw new SafePayEmbeddedException("First Name is missing");
        $last_name = (isset($customer['last_name']) && $customer['last_name'] != "") ? $customer['last_name'] : throw new SafePayEmbeddedException("Last Name is missing");
        $email = (isset($customer['email']) && $customer['email'] != "") ? $customer['email'] : throw new SafePayEmbeddedException("Email is missing");
        $phone_number = (isset($customer['phone_number']) && $customer['phone_number'] != "") ? $customer['phone_number'] : throw new SafePayEmbeddedException("Phone Number is missing");
        $country = (isset($customer['country']) && $customer['country'] != "") ? $customer['country'] : throw new SafePayEmbeddedException("Country is missing");
        $is_guest = (isset($customer['is_guest']) && $customer['is_guest'] != "" && ($customer['is_guest'] === true || $customer['is_guest'] === false)) ? $customer['is_guest'] : false;
        try {
            $customer = $this->Safepay->customer->create([
                "first_name" => $customer['first_name'],
                "last_name" => $customer['last_name'],
                "email" => $customer['email'],
                "phone_number" => $customer['phone_number'],
                "country" => $customer['country'],
                "is_guest" => $is_guest,
            ]);
            return array(
                "status" => 1,
                "token" => $customer->token
            );
        } catch (\Exception $e) {
            return array(
                "status" => 0,
                "message" => "There was an error creating customer.",
                "error" => $e->getError()
            );
        }
    }

    /**
    * SafePayEmbedded Update Customer.
    * @param array $config.
    */
    public function updateCustomer($customer)
    {
        if(!is_array($customer)){
            throw new SafePayEmbeddedException("Data must be an associative array");
        }
        $token = (isset($customer['token']) && $customer['token'] != "") ? $customer['token'] : throw new SafePayEmbeddedException("Customer Token is missing");
        $first_name = (isset($customer['first_name']) && $customer['first_name'] != "") ? $customer['first_name'] : throw new SafePayEmbeddedException("First Name is missing");
        $last_name = (isset($customer['last_name']) && $customer['last_name'] != "") ? $customer['last_name'] : throw new SafePayEmbeddedException("Last Name is missing");
        $email = (isset($customer['email']) && $customer['email'] != "") ? $customer['email'] : throw new SafePayEmbeddedException("Email is missing");
        $phone_number = (isset($customer['phone_number']) && $customer['phone_number'] != "") ? $customer['phone_number'] : throw new SafePayEmbeddedException("Phone Number is missing");
        $country = (isset($customer['country']) && $customer['country'] != "") ? $customer['country'] : throw new SafePayEmbeddedException("Country is missing");
        try {
            $customer = $this->Safepay->customer->update($token, [
                "first_name" => $customer['first_name'],
                "last_name" => $customer['last_name'],
                "email" => $customer['email'],
                "phone_number" => $customer['phone_number'],
                "country" => $customer['country'],
            ]);
            return array(
                "status" => 1,
                "message" => "Customer Updated successfully",
                "token" => $customer->token
            );
        } catch (\Exception $e) {
            return array(
                "status" => 0,
                "message" => "There was an error updating customer.",
                "error" => $e->getError()
            );
        }
    }

    /**
    * SafePayEmbedded Get Customer.
    * @param array $config.
    */
    public function retrieveCustomer($customer_token = "")
    {
        $token = (isset($customer_token) && $customer_token != "") ? $customer_token : throw new SafePayEmbeddedException("Customer Token is missing");
        try {
            $customer = $this->Safepay->customer->retrieve($token);
            return array(
                "status" => 1,
                "token" => $customer->token,
                "customer" => array(
                    "first_name" => $customer->first_name,
                    "last_name" => $customer->last_name,
                    "phone_number" => $customer->phone_number,
                    "email" => $customer->email,
                    "country" => $customer->country,
                    "is_guest" => $customer->is_guest,
                )
            );
        } catch (\Exception $e) {
            return array(
                "status" => 0,
                "message" => "There was an error retrieving customer.",
                "error" => $e->getError()
            );
        }
    }

    /**
    * SafePayEmbedded Delete Customer.
    * @param array $config.
    */
    public function deleteCustomer($customer_token = "")
    {
        $token = (isset($customer_token) && $customer_token != "") ? $customer_token : throw new SafePayEmbeddedException("Customer Token is missing");
        try {
            $customer = $this->Safepay->customer->delete($token);
            return array(
                "status" => 1
            );
        } catch (\Exception $e) {
            return array(
                "status" => 0,
                "message" => "There was an error deleting customer.",
                "error" => $e->getError()
            );
        }
    }

    /**
    * SafePayEmbedded Get All Payment Methods.
    * @param array $config.
    */
    public function getAllPaymentMethods($customer_token = "")
    {
        $token = (isset($customer_token) && $customer_token != "") ? $customer_token : throw new SafePayEmbeddedException("Customer Token is missing");
        try {
            $paymentMethods = json_decode(json_encode($this->Safepay->paymentMethod->all($token)), true);
            $payment_methods = array();
            if(isset($paymentMethods["count"]) && $paymentMethods['count'] > 0){
                foreach($paymentMethods['wallet'] as $wallet){
                    $cybersource = array();
                    if(isset($wallet['cybersource'])) {
                       $cybersource = array(
                            "token" => $wallet['cybersource']['token'],
                            "customer_payment_method" => $wallet['cybersource']['customer_payment_method'],
                            "bin" => $wallet['cybersource']['bin'],
                        );
                    }
                    array_push($payment_methods, array(
                        "token" => $wallet['token'],
                        "kind" => $wallet['kind'],
                        "scheme" => $wallet['cybersource']['scheme'],
                        "max_usage" => $wallet['max_usage'],
                        "usage_count" => $wallet['usage_count'],
                        "usage_interval" => $wallet['usage_interval'],
                        "is_deleted" => $wallet['is_deleted'],
                        "last_four" => $wallet['cybersource']['last_four'],
                        "expiry_month" => $wallet['cybersource']['expiry_month'],
                        "expiry_year" => $wallet['cybersource']['expiry_year'],
                        "expires_at" => date("Y-m-d H:i:s", $wallet['expires_at']['seconds']),
                        "cybersource" => $cybersource,
                    ));
                }
            }
            return array(
                "status" => 1,
                "payment_methods" => $payment_methods
            );
        } catch (\Exception $e) {
            return array(
                "status" => 0,
                "message" => "There was an error getting payment methods.",
                "error" => $e->getError()
            );
        }
    }

    /**
    * SafePayEmbedded Get Payment Method.
    * @param array $config.
    */
    public function getPaymentMethod($customer_token = "", $payment_token = "")
    {
        $token = (isset($customer_token) && $customer_token != "") ? $customer_token : throw new SafePayEmbeddedException("Customer Token is missing");
        $payment_token = (isset($payment_token) && $payment_token != "") ? $payment_token : throw new SafePayEmbeddedException("Payment Token is missing");
        try {
            $wallet = json_decode(json_encode($this->Safepay->paymentMethod->retrieve($token, $payment_token)), true);
            $payment_methods = array();
            if(isset($wallet["token"]) && $wallet['token'] != ""){
                $payment_methods = array(
                    "token" => $wallet['token'],
                    "kind" => $wallet['kind'],
                    "scheme" => $wallet['cybersource']['scheme'],
                    "max_usage" => $wallet['max_usage'],
                    "usage_count" => $wallet['usage_count'],
                    "usage_interval" => $wallet['usage_interval'],
                    "is_deleted" => $wallet['is_deleted'],
                    "last_four" => $wallet['cybersource']['last_four'],
                    "expiry_month" => $wallet['cybersource']['expiry_month'],
                    "expiry_year" => $wallet['cybersource']['expiry_year'],
                    "expires_at" => date("Y-m-d H:i:s", $wallet['expires_at']['seconds']),
                    "cybersource" => array(
                        "token" => $wallet['cybersource']['token'],
                        "customer_payment_method" => $wallet['cybersource']['customer_payment_method'],
                        "bin" => $wallet['cybersource']['bin'],
                    ),
                );
            }
            return array(
                "status" => 1,
                "payment_methods" => $payment_methods
            );
        } catch (\Exception $e) {
            return array(
                "status" => 0,
                "message" => "There was an error getting payment method.",
                "error" => $e->getError()
            );
        }
    }

    /**
    * SafePayEmbedded Delete Payment Method.
    * @param array $config.
    */
    public function deletePaymentMethod($customer_token = "", $payment_token = "")
    {
        $token = (isset($customer_token) && $customer_token != "") ? $customer_token : throw new SafePayEmbeddedException("Customer Token is missing");
        $payment_token = (isset($payment_token) && $payment_token != "") ? $payment_token : throw new SafePayEmbeddedException("Payment Token is missing");
        try {
            $paymentMethod = $this->Safepay->paymentMethod->delete($token, $payment_token);
            return array(
                "status" => 1,
            );
        } catch (\Exception $e) {
            return array(
                "status" => 0,
                "message" => "There was an error deleting payment method.",
                "error" => $e->getError()
            );
        }
    }

    /**
    * SafePayEmbedded Charge Customer.
    * @param array $config.
    */
    public function chargeCustomer($order, $threeDS = 0)
    {
        if(!is_array($order)){
            throw new SafePayEmbeddedException("Data must be an associative array");
        }
        if (!is_numeric($order['amount']) || filter_var($order['amount'], FILTER_VALIDATE_FLOAT) === false) {
            throw new AlfalahAPGException("Transaction Amount must be a number or float.");
        }
        $token = (isset($order['token']) && $order['token'] != "") ? $order['token'] : throw new SafePayEmbeddedException("Customer Token is missing");
        $payment_token = (isset($order['payment_token']) && $order['payment_token'] != "") ? $order['payment_token'] : throw new SafePayEmbeddedException("Payment Token is missing");
        $amount = (isset($order['amount']) && $order['amount'] != "") ? $order['amount'] : throw new SafePayEmbeddedException("Amount is missing");
        $order_id = (isset($order['order_id']) && $order['order_id'] != "") ? $order['order_id'] : uniqid();
        $intent = (isset($order['intent']) && $order['intent'] != "") ? $order['intent'] : $this->intent;
        $mode = (isset($order['mode']) && $order['mode'] != "") ? $order['mode'] : $this->mode;
        $currency = (isset($order['currency']) && $order['currency'] != "") ? $order['currency'] : $this->currency;
        $source = (isset($order['source']) && $order['source'] != "") ? $order['source'] : $this->source;
        $threeDS = (isset($threeDS) && in_array($threeDS, [1,0])) ? $threeDS : 0;
        $entry_mode = "";
        $success_url = "";
        $fail_url = "";
        $verification_url = "";
        $not_enrolled_charge = 0;
        
        if($threeDS == 1){
            $success_url = (isset($order['3ds_verification_success_url']) && $order['3ds_verification_success_url'] != "") ? $order['3ds_verification_success_url'] : throw new SafePayEmbeddedException("3DS Success URL is required");
            $fail_url = (isset($order['3ds_verification_fail_url']) && $order['3ds_verification_fail_url'] != "") ? $order['3ds_verification_fail_url'] : throw new SafePayEmbeddedException("3DS Failure URL is required");
            $verification_url = (isset($order['3ds_verification_verification_url']) && $order['3ds_verification_verification_url'] != "") ? $order['3ds_verification_verification_url'] : throw new SafePayEmbeddedException("3DS Verification URL is required");
            $not_enrolled_charge = (isset($order['3ds_not_entrolled_charge']) && $order['3ds_not_entrolled_charge'] != "") ? $order['3ds_not_entrolled_charge'] : 0;
            $mode = "payment";
            $entry_mode = "tms";
        }
        try {
            $session = $this->Safepay->order->setup([
                "user" => $token,
                "merchant_api_key" => $this->public_key,
                "intent" => $intent,
                "mode" => $mode,
                "entry_mode" => $entry_mode,
                "currency" => $currency,
                "amount" => (float)$amount * 100
            ]);
            $tracking_token = $session->tracker->token;
            $session = $this->Safepay->order->metadata($tracking_token, [
                "data" => [
                    "source" => $source,
                    "order_id" => $order_id
                ]
            ]);
            $tracker = json_decode(json_encode($this->Safepay->order->charge($tracking_token, [
                "payload" => [
                    "payment_method" => [
                        "tokenized_card" => [
                            "token" => $payment_token
                        ],
                    ],
                ]
            ])),true);
            if($threeDS == 1){
                if(isset($tracker['action']['payer_authentication_setup']['access_token']) && $tracker['action']['payer_authentication_setup']['access_token'] != '') {
                    $access_token = $tracker['action']['payer_authentication_setup']['access_token'];
                    $device_data_collection_url = $tracker['action']['payer_authentication_setup']['device_data_collection_url'];
                    return array(
                        "status" => 2,
                        "text" => "3DS Verification Required",
                        "access_token" => $access_token,
                        "device_data_collection_url" => $device_data_collection_url,
                        "token" => $tracking_token, 
                        "order_id" => $order_id,
                        "success_url" => $success_url,
                        "fail_url" => $fail_url,
                        "verification_url" => $verification_url,
                        "not_enrolled_charge" => $not_enrolled_charge
                    );
                }
            }
            if(isset($tracker['tracker']) && isset($tracker['tracker']['state']) && $tracker['tracker']['state'] == 'TRACKER_ENDED') {
                return array(
                    "status" => 1,
                    "tracker" => $tracker['tracker']
                );
            } else {
                return array(
                    "status" => 0,
                    "message" => "There was an error charging customer.",
                    "error" => $e->getError()
                );
            }
        } catch (\Exception $e) {
            return array(
                "status" => 0,
                "message" => "There was an error charging customer.",
                "error" => $e->getError()
            );
        }
    }

    
    public function initiate3DSSecure($andaz_3ds_data){
        $page_html = '<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
            <style>
                .loader-overlay {
                    position: fixed;
                    top: 0;
                    left: 0;
                    width: 100%;
                    height: 100%;
                    background-color: grey;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    z-index: 999;
                }
                .loader {
                    border: 8px solid #f3f3f3;
                    border-top: 8px solid #02B6B0;
                    border-radius: 50%;
                    width: 50px;
                    height: 50px;
                    animation: spin 1s linear infinite;
                }
                @keyframes spin {
                    0% { transform: rotate(0deg); }
                    100% { transform: rotate(360deg); }
                }
                .content {
                padding: 20px;
                }
            </style>
            <div class="loader-overlay" id="loaderOverlay">
                <div class="loader"></div>
            </div>
            <iframe id="cardinal_collection_iframe" name="collectionIframe" height="10" width="10" style="display: none;"></iframe>
            <form id="cardinal_collection_form" method="POST" target="collectionIframe" action="' . $andaz_3ds_data['device_data_collection_url'] . '">
            <input id="cardinal_collection_form_input" type="hidden" name="JWT" value="' . $andaz_3ds_data['access_token'] . '">
            </form>
            <form id="session_verifications" method="POST" action="' . $andaz_3ds_data['verification_url'] . '">
            <input type="hidden" id="SessionId_tracker" name="SessionId" value="">
            <input type="hidden" id="token" name="token" value="' . $andaz_3ds_data['token'] . '">
            <input type="hidden" id="success_url" name="success_url" value="' . $andaz_3ds_data['success_url'] . '">
            <input type="hidden" id="fail_url" name="fail_url" value="' . $andaz_3ds_data['fail_url'] . '">
            <input type="hidden" id="order_id" name="order_id" value="' . $andaz_3ds_data['order_id'] . '">
            <input type="hidden" id="non_enrolled" name="non_enrolled" value="' . $andaz_3ds_data['not_enrolled_charge'] . '">
            </form> 
            <script>
                var three_session_url = \'' . $andaz_3ds_data['verification_url'] . '\';
                var timerExpired = false;
                function myFunction() {
                    if (!timerExpired) {
                        window.location.href = \'' . $andaz_3ds_data['fail_url'] . '?status=0&message="3DS Payment request timed out"\';
                    }
                }
                window.addEventListener("message", function(event) {
                    if(event.origin === "' . $this->three_ds_url . '") {
                        var receive_data = JSON.parse(event.data);
                        if(receive_data.Status == true && receive_data.MessageType == \'profile.completed\') {
                            var SessionId = receive_data.SessionId;   
                            $("#SessionId_tracker").val(SessionId);
                            var sessionVerificationForm = document.querySelector(\'#session_verifications\'); 
                            if(sessionVerificationForm) {
                                sessionVerificationForm.submit();
                            }
                            clearTimeout(timer);
                            timerExpired = true;      
                        } else {
                            window.location.href = \'' . $andaz_3ds_data['fail_url'] . '?status=0&message="3DS Payment invalid session ID"\';
                        }
                    }
                }, false);  
                var timer = setTimeout(myFunction, 100000);
                var cardinalCollectionForm = document.querySelector(\'#cardinal_collection_form\'); 
                if(cardinalCollectionForm) {
                    cardinalCollectionForm.submit();
                }
            </script>';
        return $page_html;
    }
    public function process3DSRequest($request){
        try {
            $url = parse_url($request['success_url']);
            if (isset($url['query'])) {
                $request['success_url'] .= '&tracker=' . urlencode($request['token']) . '&order_id=' . urlencode($request['order_id']);
            } else {
                $request['success_url'] .= '?tracker=' . urlencode($request['token']) . '&order_id=' . urlencode($request['order_id']);
            }
            $url = parse_url($request['fail_url']);
            if (isset($url['query'])) {
                $request['fail_url'] .= '&tracker=' . urlencode($request['token']) . '&order_id=' . urlencode($request['order_id']);
            } else {
                $request['fail_url'] .= '?tracker=' . urlencode($request['token']) . '&order_id=' . urlencode($request['order_id']);
            }
            $authorization = json_decode(json_encode($this->Safepay->order->charge($request['token'], [
                "payload" => [
                    "authorization" => [
                        "do_capture" => false
                    ],
                    "authentication_setup" => [
                        "success_url" => $request['success_url'],
                        "failure_url" => $request['fail_url'],
                        "device_fingerprint_session_id" => $request['SessionId']
                    ]
                ]
            ])),true);
            
            if(isset($authorization['tracker']['state']) && $authorization['tracker']['state'] == 'TRACKER_ENROLLED') {
                if(isset($authorization['tracker']['next_actions']['CYBERSOURCE']['kind']) && $authorization['tracker']['next_actions']['CYBERSOURCE']['kind'] == 'AUTHORIZATION') {
                    if(!(isset($authorization['action']['payer_authentication_enrollment']['veres_enrolled'])) || $authorization['action']['payer_authentication_enrollment']['veres_enrolled'] != 'Y') {
                        if($request['non_enrolled'] == 1){
                            $tracker_order_id = $authorization['tracker']['metadata']['data']['order_id'];
                            $payment_charge = json_decode(json_encode($this->Safepay->order->charge($request['token'], [
                                "payload" => [
                                "authorization" => [
                                    "do_capture" => true
                                ]
                                ]
                            ])),true);
                            if(isset($payment_charge['tracker']['state']) && $payment_charge['tracker']['state'] == 'TRACKER_ENDED') {
                                return array(
                                    "status" => 1,
                                    "message" => "Card successfully charged",
                                    "tracker" => $request['token'],
                                    "response" => $payment_charge
                                );
                            } else {
                                return array(
                                    "status" => 0,
                                    "message" => "There was an error processing your transaction",
                                    "tracker" => $request['token'],
                                    "response" => $payment_charge
                                );
                            }
                        } else {
                            return array(
                                "status" => 0,
                                "message" => "Card is not 3Ds enrolled",
                                "tracker" => $request['token'],
                                "response" => $authorization
                            );
                        }
                        
                    }
                    $tracker_order_id = $authorization['tracker']['metadata']['data']['order_id'];
                    $payment_charge = json_decode(json_encode($this->Safepay->order->charge($request['token'], [
                        "payload" => [
                          "authorization" => [
                            "do_capture" => false
                          ]
                        ]
                    ])),true);
                    if(isset($payment_charge['tracker']['state']) && $payment_charge['tracker']['state'] == 'TRACKER_ENDED') {
                        return array(
                            "status" => 1,
                            "message" => "Card successfully charged",
                            "tracker" => $request['token'],
                            "response" => $payment_charge
                        );
                    } else {
                        return array(
                            "status" => 0,
                            "message" => "There was an error processing your transaction",
                            "tracker" => $request['token'],
                            "response" => $payment_charge
                        );
                    }
                } else if(isset($authorization['tracker']['next_actions']['CYBERSOURCE']['kind']) && $authorization['tracker']['next_actions']['CYBERSOURCE']['kind'] == 'PAYER_AUTH_VALIDATION') {
                    $tracker_order_id = $authorization['tracker']['metadata']['data']['order_id'];
                    if(isset($authorization['action']['payer_authentication_enrollment']['access_token']) && $authorization['action']['payer_authentication_enrollment']['access_token'] != '') {
                        return array(
                            "status" => 2,
                            "message" => "3DS OTP Required",
                            "tracker" => $request['token'],
                            "response" => $authorization,
                            "authentication" => $authorization['action']['payer_authentication_enrollment']
                        );
                    } else {
                        return array(
                            "status" => 0,
                            "message" => "There was an error processing your transaction",
                            "tracker" => $request['token'],
                            "response" => $authorization
                        );
                    }
                } else {
                    return array(
                        "status" => 0,
                        "message" => "There was an error processing your transaction",
                        "tracker" => $request['token'],
                        "response" => $authorization
                    );
                }
            }
        } catch (\Exception $e) {
            return array(
                "status" => 0,
                "message" => "There was an error processing 3DS request.",
                "error" => $e->getError()
            );
        }
    }
    public function requestOTPCode3DS($andaz_3ds_data){
        $page_html = '<html lang="en">
            <style>
                html, body {
                    margin: 0;
                    padding: 0;
                    height: 100%;
                }
                .container {
                    width: 100%;
                    height: 100%;
                    position: relative;
                }
                iframe {
                    border: none;
                    width: 100%;
                    height: 100%;
                    position: absolute;
                    top: 0;
                    left: 0;
                }
            </style>
            <body>
                <div class="container">
                    <iframe name="step-up-iframe"></iframe>
                    <form id="step-up-form" target="step-up-iframe" method="post" action="' . $andaz_3ds_data['authentication']['step_up_url'] . '"> 
                        <input type="hidden" name="JWT" value="' . $andaz_3ds_data['authentication']['access_token'] . '" /> 
                        <input type="hidden" name="MD" value="{\'test\': \'1\'}"/> 
                    </form>
                </div>
            </body>
        </html>
        <script>
            window.onload = function() {   
                var stepUpForm = document.querySelector(\'#step-up-form\');
                if(stepUpForm){
                    stepUpForm.submit();
                }
            }
        </script>';
        return $page_html;
    }
    public function charge3DS($data)
    {
        $tracker = (isset($data['tracker']) && $data['tracker'] != "") ? $data['tracker'] : throw new SafePayEmbeddedException("Tracker is missing");
        try {
            $payment_charge = json_decode(json_encode($this->Safepay->order->charge($data['tracker'], new \stdClass())),true);
            if(isset($payment_charge['tracker']['state']) && $payment_charge['tracker']['state'] == 'TRACKER_ENDED') {
                return array(
                    "status" => 1,
                    "message" => "Card successfully charged",
                    "tracker" => $data['tracker'],
                    "response" => $payment_charge
                );
            } else {
                return array(
                    "status" => 0,
                    "error" => "There was an error processing your transaction",
                    "tracker" => $data['tracker'],
                    "response" => $payment_charge
                );
            }
        } catch (\Exception $e) {
            return array(
                "status" => 0,
                "message" => "There was an error charging customer.",
                "error" => $e->getError()
            );
        }
    }

    
    /**
    * SafePayEmbedded Charge Customer.
    * @param array $config.
    */
    public function getCardVaultURL($customer_token = "", $type = "redirect"){
        $token = (isset($customer_token) && $customer_token != "") ? $customer_token : throw new SafePayEmbeddedException("Customer Token is missing");
        try {
            $session = $this->Safepay->order->setup([
                "merchant_api_key" => $this->public_key,
                "intent" => $this->intent,
                "mode" => "instrument",
                "currency" => $this->currency
            ]);
            $tbt = $this->Safepay->passport->create();
            $params = array(
                "environment" => $this->environment,
                "tracker" => $session->tracker->token,
                "source" => $this->vault_source,
                "is_implicit" => $this->is_implicit,
                "tbt" => $tbt->token,
                "user_id" => $customer_token
            );
            $encoded = \http_build_query($params);
            $url = $this->card_vault_url . "/embedded?" . $encoded;
            if($type == "url"){
                return array(
                    "status" => 1,
                    "vault_url" => $url
                );
            } else if($type == "redirect"){
                header("Location: " . $url);
                die();
            }
        } catch (\Exception $e) {
            return array(
                "status" => 0,
                "message" => "There was an error getting card vault url.",
                "error" => $e->getError()
            );
        }
    }
    

    /**
    * SafePayEmbedded Verify Payment.
    * @param array $config.
    */
    public function verifyPayment()
    {
        $payload = @file_get_contents('php://input');
        if(empty($payload)){
            $payload = json_encode(array());
        }
        $event = null;
        try {
            $event = \Safepay\Event::constructFrom(json_decode($payload, true));
            http_response_code(200);
            switch ($event->type) {
                case 'payment.succeeded':
                    $payment = $event->data;
                    return array(
                        "status" => 1,
                        "data" => $event->data
                    );
                case 'payment.failed':
                    return array(
                        "status" => 0,
                        "message" => "Payment Failed.",
                        "error" => $event->data
                    );
                default:
                    return array(
                        "status" => 0,
                        "message" => "Recevied Unknown event type.",
                        "error" => $event->type
                    );
            }
        } catch (\Exception $e) {
            http_response_code(400);
            return array(
                "status" => 0,
                "message" => "Unauthorized Request.",
                "error" => $e->getError()
            );
        }
    }

    /**
    * SafePayEmbedded Secured Verify Payment.
    * @param array $config.
    */
    public function verifyPaymentSecured(){
        if($this->webhook_key == ""){
            throw new SafePayEmbeddedException("Webhook Key not set during initialization");
        }
        $payload = @file_get_contents('php://input');
        if(empty($payload)){
            $payload = json_encode(array());
        }
        $sig_header = isset($_SERVER['HTTP_X_SFPY_SIGNATURE']) ? $_SERVER['HTTP_X_SFPY_SIGNATURE'] : throw new SafePayEmbeddedException("Unauthenticated Request");;
        $webhook_secret = $this->webhook_key;
        $event = null;
        try {
            $event = \Safepay\Webhook::constructEvent($payload, $sig_header, $webhook_secret);
            http_response_code(200);
            return array(
                "status" => 1,
                "type" => $event->type,
                "data" => $event->data
            );
        } catch (\Exception $e) {
            http_response_code(400);
            return array(
                "status" => 0,
                "message" => "Unauthorized Request.",
                "error" => $e->getError()
            );
        }
    }
}
