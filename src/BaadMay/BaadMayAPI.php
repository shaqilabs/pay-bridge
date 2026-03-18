<?php

namespace ShaqiLabs\BaadMay;

class BaadMayAPI
{
    private $BaadMayClient;

    public function __construct(BaadMayClient $BaadMayClient){
        $this->BaadMayClient = $BaadMayClient;
    }
    public function createCheckoutLink($order, $response_type = "redirect"){
        if((!isset($order['amount']) || $order['amount'] == "")){
            throw new BaadMayException("Transaction Amount is missing.");
        }
        if (!is_numeric($order['amount']) || filter_var($order['amount'], FILTER_VALIDATE_FLOAT) === false) {
            throw new BaadMayException("Transaction Amount must be a number or float.");
        }
        if($this->BaadMayClient->success_url == "" && (!isset($order['success_url']) || $order['success_url'] == "")){
            throw new BaadMayException("Success URL is missing. It can either be set once for all transactions or provided against each order or both.");
        }
        if($this->BaadMayClient->failure_url == "" && (!isset($order['failure_url']) || $order['failure_url'] == "")){
            throw new BaadMayException("Failure URL is missing. It can either be set once for all transactions or provided against each order or both.");
        }
        $customer = array(
            "firstname" => (isset($order['customer']['first_name']) && $order['customer']['first_name'] != "") ? $order['customer']['first_name'] : "",
            "lastname" => (isset($order['customer']['last_name']) && $order['customer']['last_name'] != "") ? $order['customer']['last_name'] : "",
            "address" => array(
                (isset($order['customer']['address'][0]) && $order['customer']['address'][0] != "") ? $order['customer']['address'][0] : "",
                (isset($order['customer']['address'][1]) && $order['customer']['address'][1] != "") ? $order['customer']['address'][1] : "",
            ),
            "city" => (isset($order['customer']['city']) && $order['customer']['city'] != "") ? $order['customer']['city'] : "",
            "state" => (isset($order['customer']['state']) && $order['customer']['state'] != "") ? $order['customer']['state'] : "",
            "postcode" => (isset($order['customer']['postcode']) && $order['customer']['postcode'] != "") ? $order['customer']['postcode'] : "",
            "phone" => (isset($order['customer']['phone']) && $order['customer']['phone'] != "") ? $order['customer']['phone'] : "",
            "email" => (isset($order['customer']['email']) && $order['customer']['email'] != "") ? $order['customer']['email'] : "",
        );
        $billing = array(
            "firstname" => (isset($order['billing']['first_name']) && $order['billing']['first_name'] != "") ? $order['billing']['first_name'] : "",
            "lastname" => (isset($order['billing']['last_name']) && $order['billing']['last_name'] != "") ? $order['billing']['last_name'] : "",
            "address" => array(
                (isset($order['billing']['address'][0]) && $order['billing']['address'][0] != "") ? $order['billing']['address'][0] : "",
                (isset($order['billing']['address'][1]) && $order['billing']['address'][1] != "") ? $order['billing']['address'][1] : "",
            ),
            "city" => (isset($order['billing']['city']) && $order['billing']['city'] != "") ? $order['billing']['city'] : "",
            "state" => (isset($order['billing']['state']) && $order['billing']['state'] != "") ? $order['billing']['state'] : "",
            "postcode" => (isset($order['billing']['postcode']) && $order['billing']['postcode'] != "") ? $order['billing']['postcode'] : "",
            "phone" => (isset($order['billing']['phone']) && $order['billing']['phone'] != "") ? $order['billing']['phone'] : "",
            "email" => (isset($order['billing']['email']) && $order['billing']['email'] != "") ? $order['billing']['email'] : "",
        );
        $shipping = array(
            "method" => (isset($order['shipping']['method']) && $order['shipping']['method'] != "") ? $order['shipping']['method'] : "",
            "cost" => (isset($order['shipping']['cost']) && $order['shipping']['cost'] != "") ? (float)number_format($order['shipping']['cost'],2, '.', '') : 0.00,
            "firstname" => (isset($order['shipping']['first_name']) && $order['shipping']['first_name'] != "") ? $order['shipping']['first_name'] : "",
            "lastname" => (isset($order['shipping']['last_name']) && $order['shipping']['last_name'] != "") ? $order['shipping']['last_name'] : "",
            "address" => array(
                (isset($order['shipping']['address'][0]) && $order['shipping']['address'][0] != "") ? $order['shipping']['address'][0] : "",
                (isset($order['shipping']['address'][1]) && $order['shipping']['address'][1] != "") ? $order['shipping']['address'][1] : "",
            ),
            "city" => (isset($order['shipping']['city']) && $order['shipping']['city'] != "") ? $order['shipping']['city'] : "",
            "state" => (isset($order['shipping']['state']) && $order['shipping']['state'] != "") ? $order['shipping']['state'] : "",
            "postcode" => (isset($order['shipping']['postcode']) && $order['shipping']['postcode'] != "") ? $order['shipping']['postcode'] : "",
            "phone" => (isset($order['shipping']['phone']) && $order['shipping']['phone'] != "") ? $order['shipping']['phone'] : "",
            "email" => (isset($order['shipping']['email']) && $order['shipping']['email'] != "") ? $order['shipping']['email'] : "",
        );
        $items = array();
        foreach($order['items'] as $item){
            array_push($items, array(
                "itemId" => (isset($item['item_id']) && $item['item_id'] != "") ? $item['item_id'] : uniqid(),
                "sku" => (isset($item['sku']) && $item['sku'] != "") ? $item['sku'] : uniqid(),
                "name" => (isset($item['name']) && $item['name'] != "") ? $item['name'] : "",
                "qty" => (isset($item['qty']) && $item['qty'] != "") ? $item['qty'] : 1,
                "price" => (float)number_format((float)$item['price'],2, '.', '')
            ));
        }

        $order_data = array(
            "apiKey" => $this->BaadMayClient->api_key,
            "orderId" => (isset($order['order_id']) && $order['order_id'] != "") ? $order['order_id'] : uniqid(),
            "createdAt" => date("Y-m-d H:i:s"),
            "totalAmount" => (float)number_format((float)$order['amount'],2, '.', ''),
            "items" => $items,
            "customer" => $customer,
            "billing" => $billing,
            "shipping" => $shipping,
            "success_url" => (isset($order['success_url']) && $order['success_url'] != "") ? $order['success_url'] : $this->BaadMayClient->success_url,
            "failure_url" => (isset($order['failure_url']) && $order['failure_url'] != "") ? $order['failure_url'] : $this->BaadMayClient->failure_url,
        );
        $url = $this->BaadMayClient->api_url . "?q=" . base64_encode(json_encode($order_data, JSON_PRESERVE_ZERO_FRACTION));
        if($response_type == "response"){
            return array(
                "status" => 1,
                "url" => $url
            );
        } else if($response_type == "url"){
            return $url;
        } else if($response_type == "redirect"){
            if(headers_sent()){
                throw new BaadMayException("Unable to redirect because headers have already been sent.");
            }
            header('Location: '. $url);
            return;
        }
    }
    public function getOrderStatus($order_id){
        if((!isset($order_id) || $order_id == "")){
            throw new BaadMayException("Order ID is missing.");
        }
        $endpoint = $this->BaadMayClient->status_url . "orders/" . $order_id;
        $method = 'GET';
        $payload = $this->BaadMayClient->makeRequest($endpoint, $method);
        return $payload;
    }
}
