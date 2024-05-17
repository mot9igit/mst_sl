<?php

/**
 * yandex Logistic Class
 *
 * @package shoplogistic
 */
class Yandex
{
    function __construct(shopLogistic &$sl, modX &$modx, array $config = array())
    {
        $this->sl =& $sl;
        $this->modx =& $modx;
        $this->modx->lexicon->load('shoplogistic:default');

        $corePath = $this->modx->getOption('shoplogistic_core_path', $config, $this->modx->getOption('core_path') . 'components/shoplogistic/');
        $assetsUrl = $this->modx->getOption('shoplogistic_assets_url', $config, $this->modx->getOption('assets_url') . 'components/shoplogistic/');
        $assetsPath = $this->modx->getOption('shoplogistic_assets_path', $config, $this->modx->getOption('base_path') . 'assets/components/shoplogistic/');
        $token = $this->modx->getOption('shoplogistic_yandex_oauth_token', $config, '');
        $delivery_url = $this->modx->getOption('shoplogistic_yandex_delivery_url', $config, '');
        $delivery_url_test = $this->modx->getOption('shoplogistic_yandex_delivery_url_test', $config, '');
        $express_url = $this->modx->getOption('shoplogistic_yandex_express_url', $config, '');
        $express_url_test = $this->modx->getOption('shoplogistic_yandex_express_url_test', $config, '');
        $delivery_log = $this->modx->getOption('shoplogistic_yandex_delivery_log', $config, true);

        $this->config = array_merge([
            'corePath' => $corePath,
            'modelPath' => $corePath . 'model/',
            'processorsPath' => $corePath . 'processors/',

            'connectorUrl' => $assetsUrl . 'connector.php',
            'assetsUrl' => $assetsUrl,
            'assetsPath' => $assetsPath,
            'cssUrl' => $assetsUrl . 'css/',
            'jsUrl' => $assetsUrl . 'js/',

            'delivery_log' => 1,

            'token' => $token,
            'delivery_url' => $delivery_url,
            'delivery_url_test' => $delivery_url_test,
            'express_url' => $express_url,
            'express_url_test' => $express_url_test,

            'callback_url' => $this->modx->getOption("site_url").'assets/components/shoplogistic/yandex_handler.php'

        ], $config);
    }

    /**
     * Проверка статусов заявок
     *
     * @return void
     */
    public function checkDelivery () {
        $url = $this->config["express_url"]."claims/search";
        $data = array("claim_id" => "018ee06ca79b85eb98890dc2ba490100");
        $query = $this->modx->newQuery("slOrder");
        $query->leftJoin("slCRMStage", "slCRMStage", "slCRMStage.id = slOrder.status");
        $query->where(array(
            "slCRMStage.check_deal" => 1,
            "slOrder.tk:=" => "yandex"
        ));
        $query->select(array("slOrder.*, slCRMStage.transition_fail"));
        if($query->prepare() && $query->stmt->execute()){
            $orders = $query->stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach($orders as $order){
                $response = $this->getRequest($order["tk_id"]);
                if($response[0]){
                    $success_statuses = array(
                        "delivered",
                        "delivered_finish"
                    );
                    if(in_array($response[0]['status'], $success_statuses)){
                        $this->sl->cart->setDeliveryStage($order['claim_id']);
                    }
                    $error_statuses = array(
                        "failed",
                        "estimating_failed",
                        "performer_not_found",
                        "returned",
                        "returned_finish"
                    );
                    if(in_array($response[0]['status'], $error_statuses)){
                        $this->sl->cart->changeOrderStage($order["id"], $order["transition_fail"], 1);
                    }
                }
            }
        }
    }

    /**
     * Запрос стоимости доставки на Я.Доставку (Откуда, Куда, Товар)
     *
     * @param $type
     * @param int $product_id
     * @param array $sdata
     * @return bool|mixed
     */
    public function getYaDeliveryPrice($type, $product_id = 0, $sdata = array()){
        $this->modx->log(1, print_r($sdata, 1));
        if($sdata['location']){
            $position = $sdata['location'];
        }else{
            $position = $this->sl->cart->getUserPosition();
        }
        $url = $this->config["express_url"]."check-price";
        $start_coodinats = array();
        if($sdata['cart']){
            if($sdata['cart']['object'] && $sdata['cart']['type']){
                $start_coodinats = explode(",", $sdata['cart']['data']['coordinats']);
                foreach($start_coodinats as $key => $val){
                    $start_coodinats[$key] = (float) trim($val);
                }
           }
        }
        if(isset($position['geo_lon'])){
            $to = array(
                (float) $position['geo_lon'],
                (float) $position['geo_lat']
            );
        }else{
            $to = array(
                (float) $position['data']['geo_lon'],
                (float) $position['data']['geo_lat'] 
            );
        }
        $data = array();
        $data['route_points'] = array(
            0 => array(
                "id" => 1,
                "coordinates" => array_reverse($start_coodinats)
            ),
            1 => array(
                "id" => 2,
                "coordinates" => $to
            )
        );
        // TODO: check this function
        if($type == 'card'){
            if($product_id){
                $product = $this->modx->getObject("modResource", $product_id);
                if($product){
                    $pos = $this->sl->cart->getProductParams($product_id);

                    $data['items'][] = array(
                        "quantity" => 1,
                        "size" => array(
                            "length" => (float) $pos[0]['length'] * 0.01,
                            "width" => (float) $pos[0]['width'] * 0.01,
                            "height" => (float) $pos[0]['height'] * 0.01,
                        ),
                        "weight" => (float) $pos[0]['weight']
                    );
                    $ya_delivery_data = $this->yaDeliveryRequest($url, $data);
                    if(isset($ya_delivery_data['code'])){
                        $this->yaDeliveryReport($url.' '.$ya_delivery_data['code'].' '.$ya_delivery_data['message']);
                        $this->yaDeliveryReport($data);
                        return false;
                    }else{
                        return $ya_delivery_data;
                    }
                }
            }
        }
        if($type == 'cart'){
            $cart = $sdata['cart']['products'];
            $price_data = array();
            $price_data['route_points'] = $data['route_points'];
            foreach ($cart as $pr) {
                $product = $this->modx->getObject("modResource", $pr['id']);
                if($product) {
                    $pos = $this->sl->cart->getProductParams($pr['id']);
                    $price_data['items'][] = array(
                        "quantity" => 1,
                        "size" => array(
                            "length" => (float)$pos[0]['length'] * 0.01,
                            "width" => (float)$pos[0]['width'] * 0.01,
                            "height" => (float)$pos[0]['height'] * 0.01,
                        ),
                        "weight" => (float)$pos[0]['weight']
                    );
                }
            }
            // УДАЛЯЕМ ITEMS некорректно считает размеры
            unset($price_data['items']);
            $this->modx->log(1, print_r($price_data, 1));
            $ya_delivery_data = $this->yaDeliveryRequest($url, $price_data);
            if(isset($ya_delivery_data['code'])){
                $this->yaDeliveryReport($url.' '.$ya_delivery_data['code'].' '.$ya_delivery_data['message']);
                $this->yaDeliveryReport($ya_delivery_data);
                $this->yaDeliveryReport($data);
                return false;
            }else{
                return $ya_delivery_data;
            }
        }
    }


    /**
     * Берем информацию о заявке
     *
     * @param $id
     * @return void
     */
    public function getRequest($id){
        $url = $this->config["express_url"]."claims/search";
        if($id){
            $data = array(
                "claim_id" => $id,
                "limit" => 1
            );
            $response = $this->yaDeliveryRequest($url, $data);
            return $response;
        }
        return false;
    }

    /**
     * Создание заявки в ТК
     *
     * @param $order_id
     * @return array
     */
    public function createRequest($order_id){
        $result = array();
        $result = $this->sl->cart->getOrderData($order_id);
        // $request_id = md5($result['order']['id'].time());
        $request_id = md5($result['order']['id']);
        $url = $this->config["express_url"]."claims/create?request_id={$request_id}";
        if($result["order"]["code"]){
            $message = "При заборе груза скажите код выдачи: ".$result["order"]["code"];
        }
        $data = array(
            "auto_accept" => false,
            "callback_properties" => array(
                "callback_url" => $this->config["callback_url"]
            ),
            "client_requirements" => array(
                "assign_robot" => false,
                "cargo_loaders" => 0,
                "pro_courier" => false,
                "taxi_class" => 'courier'
            ),
            "comment" => $message,
            "optional_return" => true,
            "referral_source" => "mst.tools",
            "emergency_contact" => array(
                "name" => $result["store"]["contact"],
                "phone" => $result["store"]["phone"]
            ),
            "route_points" => array(
                array(
                    "address" => array(
                        "coordinates" => array((float) $result["store"]["lng"], (float) $result["store"]["lat"]),
                        "shortname" => $result["store"]["address"],
                        "fullname" => $result["store"]["address"],
                        "comment" => "Доставка из магазина {$result["store"]["name"]}. Сообщите менеджеру, что заказ для Яндекс.Доставки из маркетплейса МСТ. Назовите код выдачи {$result["order"]["code"]} и заберите посылку. Заказ оплачен безналично, при передаче заказа нельзя требовать с получателя деньги за доставку."
                    ),
                    "contact" => array(
                        "name" => $result["store"]["contact"],
                        "phone" => $result["store"]["phone"]
                    ),
                    "point_id" => 1,
                    "type" => "source",
					"skip_confirmation" => true,
                    "visit_order" => 1
                ),
                array(
                    "address" => array(
                        "coordinates" => array((float) $result["address"]["properties"]["geo_data"]["geo_lon"], (float) $result["address"]["properties"]["geo_data"]["geo_lat"]),
                        "city" => $result["address"]["city"],
                        "street" => $result["address"]["street"],
                        "building" => $result["address"]["building"],
                        "shortname" => "{$result["address"]["city"]}, {$result["address"]["street"]}, {$result["address"]["building"]}",
                        "fullname" => "{$result["address"]["city"]}, {$result["address"]["street"]}, {$result["address"]["building"]}, {$result["address"]["room"]}",
                        "comment" => $result["address"]["comment"]
                    ),
                    "contact" => array(
                        "name" => $result["address"]["receiver"],
                        "phone" => $result["address"]["phone"],
                        "email" => $result["user"]["email"]
                    ),
                    "point_id" => 2,
					"skip_confirmation" => true,
                    "type" => "destination",
                    "visit_order" => 2
                )
            )
        );
        if($result["products"]){
            foreach($result["products"] as $product){
                $tmp = array(
                    "cost_currency" => "RUB",
                    "cost_value" => $product['price'],
                    "quantity" => (int)$product['count'],
                    "title" => $product["name"],
                    "weight" => (float)$product["weight_brutto"],
                    "size" => array(
                        "height" => $product["height"] * 0.01,
                        "length" => $product["length"] * 0.01,
                        "width" => $product["width"] * 0.01,
                    ),
                    "droppof_point" => 2,
                    "pickup_point" => 1
                );
                $data["items"][] = $tmp;
            }
        }
        $response = $this->yaDeliveryRequest($url, $data);
        return $response;
    }

    /**
     * Запрос к серверам Яндекса
     *
     * @param $url
     * @param $data
     * @return mixed
     */
    public function yaDeliveryRequest($url, $data = array()){
        $ch = curl_init();

        $this->modx->log(1, print_r(json_encode($data, JSON_UNESCAPED_UNICODE), 1));
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data, JSON_UNESCAPED_UNICODE));
        curl_setopt($ch, CURLOPT_POST, 1);

        $headers = array();
        $headers[] = "Content-Type: application/json";
        $headers[] = "Accept: application/json";
        $headers[] = "Authorization: Bearer ".$this->config['token'];
        $headers[] = "Accept-Language: ru";
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $result = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if($this->config["delivery_log"]){
            $this->yaDeliveryReport($http_code);
        }
        if (curl_errno($ch)) {
            $this->modx->log(xPDO::LOG_LEVEL_ERROR,  'YA Delivery Error:' . curl_error($ch));
        }
        curl_close ($ch);
        if($this->config["delivery_log"]){
            $this->modx->log(1, print_r($http_code, 1));
            $this->modx->log(1, print_r(json_decode($result, 1), 1));
            $this->yaDeliveryReport($http_code);
            $this->yaDeliveryReport(json_decode($result, 1));
        }
        return json_decode($result, 1);
    }

    public function yaDeliveryReport($text){
        $this->modx->log(xPDO::LOG_LEVEL_ERROR, print_r($text, 1), array(
            'target' => 'FILE',
            'options' => array(
                'filename' => 'delivery_yandex.log'
            )
        ));
    }

}