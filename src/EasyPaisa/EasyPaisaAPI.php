<?php

namespace ShaqiLabs\EasyPaisa;

class EasyPaisaAPI
{
    private $EasyPaisaClient;
    private $callback_url;

    public function __construct(EasyPaisaClient $EasyPaisaClient){
        $this->EasyPaisaClient = $EasyPaisaClient;
    }
    public function initiateHostedCheckout($order, $response_type = "form"){
        if((!isset($order['amount']) || $order['amount'] == "")){
            throw new EasyPaisaException("Transaction Amount is missing.");
        }
        if (!is_numeric($order['amount']) || filter_var($order['amount'], FILTER_VALIDATE_FLOAT) === false) {
            throw new EasyPaisaException("Transaction Amount must be a number or float.");
        }
        if($this->EasyPaisaClient->return_url == "" && (!isset($order['return_url']) || $order['return_url'] == "")){
            throw new EasyPaisaException("Return URL is missing. It can either be set once for all transactions or provided against each order or both.");
        }
        $currentDate = new \DateTime();
        $currentDate->modify('+2 day');
        $expiryDate = $currentDate->format('Ymd His');
        
        $data = array(
            "amount" => number_format($order['amount'],2),
            "autoRedirect" => 1,
            "bankIdentifier" => (isset($order['bank_id']) && $order['bank_id'] != "") ? $order['bank_id'] : "",
            "emailAddr" => (isset($order['email']) && $order['email'] != "") ? $order['email'] : "",
            "expiryDate" => (isset($order['expiry_datetime']) && $order['expiry_datetime'] != "") ? $order['expiry_datetime'] : $expiryDate,
            "mobileNum" => (isset($order['phone']) && $order['phone'] != "") ? $order['phone'] : "",
            "orderRefNum" => (isset($order['order_id']) && $order['order_id'] != "") ? $order['order_id'] : uniqid(),
            "paymentMethod" => (isset($order['payment_method']) && in_array($order['payment_method'], ["OTC_PAYMENT_METHOD","MA_PAYMENT_METHOD","CC_PAYMENT_METHOD","QR_PAYMENT_METHOD","DD_PAYMENT_METHOD"])) ? $order['payment_method'] : "",
            "postBackURL" => (isset($order['return_url']) && $order['return_url'] != "") ? $order['return_url'] : $this->EasyPaisaClient->return_url,
            "storeId" => $this->EasyPaisaClient->store_id,
        );
        $filteredData = array_filter($data, function($value) {
            return !empty($value);
        });
        $mapString = '';
		foreach ($filteredData as $key => $val) {
			$mapString .=  $key.'='.$val.'&';
		}
		$url_string  = substr($mapString , 0, -1);
        $crypttext = openssl_encrypt($url_string, "aes-128-ecb", $this->EasyPaisaClient->hash_key, OPENSSL_RAW_DATA);
        $encrypted_hash = base64_encode($crypttext);
        $data['merchantHashedReq'] = $encrypted_hash;

        if($response_type == "form"){
            return $this->generateForm($data);
        } else if($response_type == "redirect"){
            $form = $this->generateForm($data);
            $form .= '<script>
                document.addEventListener("DOMContentLoaded", function() {
                    document.getElementById("payment_form_easypay").submit();
                });
            </script>';
            return $form;
        } else if($response_type == "follow"){
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $this->EasyPaisaClient->api_url . 'easypay/Index.jsf');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
            curl_setopt($ch, CURLOPT_TIMEOUT, 90);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            if (curl_errno($ch)) {
                $return_data = array(
                    "status" => 0,
                    "error" => curl_error($ch)
                );
            } else if ($httpCode >= 400) {
                $return_data = array(
                    "status" => 0,
                    "error" => "HTTP request failed with status {$httpCode}",
                    "data" => $response
                );
            } else {
                $redirected_url = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
                $parsedUrl  = parse_url($redirected_url);
                $queryString = isset($parsedUrl['query']) ? $parsedUrl['query'] : '';
                parse_str($queryString, $queryParams);
                if(isset($queryParams['auth_token']) && $queryParams['auth_token'] != ""){
                    $return_data = array(
                        "status" => 1,
                        "auth_token" => $queryParams['auth_token']
                    );
                } else {
                    $return_data = array(
                        "status" => 0,
                        "error" => "Unable to get Auth Token",
                        "data" => $response
                    );
                }
            }
            curl_close($ch);
            return $return_data;
        } else {
            return array(
                "status" => 0,
                "error" => "Unknown Response Type"
            );
        }
    }
    public function generateForm($data){
        $form = '<form action="' . $this->EasyPaisaClient->api_url . 'easypay/Index.jsf " method="POST" id = "payment_form_easypay"  style = "display:none;">
            <input type ="hidden" name="storeId" value="' . $data['storeId'] . '"/>
            <input type ="hidden" name="amount" value="' . $data['amount'] . '"/>
            <input type ="hidden" name="postBackURL" value="' . $data['postBackURL'] . '"/>
            <input type ="hidden" name="orderRefNum" value="' . $data['orderRefNum'] . '"/>
            <input type ="hidden" name="expiryDate" value="' . $data['expiryDate'] . '">
            <input type ="hidden" name="autoRedirect" value="' . $data['autoRedirect'] . '">
            <input type ="hidden" name="emailAddr" value="' . $data['emailAddr'] . '">
            <input type ="hidden" name="mobileNum" value=' . $data['mobileNum'] . '>
            <input type ="hidden" name="merchantHashedReq" value=' . $data['merchantHashedReq'] . '>
            <input type ="hidden" name="paymentMethod" value="' . $data['paymentMethod'] . '">
            <input type ="hidden" name="bankIdentifier" value="' . $data['bankIdentifier'] . '">
            <input type="SUBMIT" value="SUBMIT">      
            </form>
        ';
        return $form;
    }
    public function processHostedCheckout($data, $response_type = "form"){
        if((!isset($data['auth_token']) || $data['auth_token'] == "")){
            throw new EasyPaisaException("Auth Token is missing.");
        }
        if($this->EasyPaisaClient->return_url == "" && (!isset($data['redirect_url']) || $data['redirect_url'] == "")){
            throw new EasyPaisaException("Return URL is missing. It can either be set once for all transactions or provided against each order or both.");
        }
        $data['redirect_url'] = (isset($data['redirect_url']) && $data['redirect_url'] != "") ? $data['redirect_url'] : $this->EasyPaisaClient->return_url;

        $form = '<form action="' . $this->EasyPaisaClient->api_url . 'easypay/Confirm.jsf" method="POST" id = "payment_form_easypay" style = "display:none;">
            <input type ="hidden" name="auth_token" value="' . $data['auth_token'] . '"/>
            <input type ="hidden" name="postBackURL" value="' . $data['redirect_url'] . '"/>
            <input type="SUBMIT" value="SUBMIT">      
            </form>
        ';

        if($response_type == "form"){
            return $form;
        } else if($response_type == "redirect"){
            $form .= '<script>
                document.addEventListener("DOMContentLoaded", function() {
                    document.getElementById("payment_form_easypay").submit();
                });
            </script>';
            return $form;
        }
    }
    public function transactionStatus($orderID){
        $endpoint = 'easypay-service/rest/v4/inquire-transaction';
        $method = 'POST';
        $requestData = [
            'orderId' => $orderID,
            'storeId' => $this->EasyPaisaClient->store_id,
            'accountNum' => $this->EasyPaisaClient->ewp_account_number,
        ];
        $payload = $this->EasyPaisaClient->makeRequest($endpoint, $method, $requestData);
        return $payload;
    }
    public function performWalletTransaction($order){
        if((!isset($order['amount']) || $order['amount'] == "")){
            throw new EasyPaisaException("Transaction Amount is missing.");
        }
        if (!is_numeric($order['amount']) || filter_var($order['amount'], FILTER_VALIDATE_FLOAT) === false) {
            throw new EasyPaisaException("Transaction Amount must be a number or float.");
        }
        if((!isset($order['account_number']) || $order['account_number'] == "")){
            throw new EasyPaisaException("Account Number is missing.");
        }
        if((!isset($order['email_address']) || $order['email_address'] == "")){
            throw new EasyPaisaException("Email Address is missing.");
        }
        
        $endpoint = 'easypay-service/rest/v4/initiate-ma-transaction';
        $method = 'POST';
        $requestData = array(
            "orderId" => (isset($order['order_id']) && $order['order_id'] != "") ? $order['order_id'] : uniqid(),
            "storeId" => $this->EasyPaisaClient->store_id,
            "transactionAmount" => number_format($order['amount'],2),
            "transactionType" => "MA",
            "mobileAccountNo" => $order['account_number'],
            "emailAddress" => $order['email_address'],
            "optional1" => (isset($order['metafield_1']) && $order['metafield_1'] != "") ? $order['metafield_1'] : "",
            "optional2" => (isset($order['metafield_2']) && $order['metafield_2'] != "") ? $order['metafield_2'] : "",
            "optional3" => (isset($order['metafield_3']) && $order['metafield_3'] != "") ? $order['metafield_3'] : "",
            "optional4" => (isset($order['metafield_4']) && $order['metafield_4'] != "") ? $order['metafield_4'] : "",
            "optional5" => (isset($order['metafield_5']) && $order['metafield_5'] != "") ? $order['metafield_5'] : "",
        );
        $payload = $this->EasyPaisaClient->makeRequest($endpoint, $method, $requestData);
        return $payload;
    }
}
