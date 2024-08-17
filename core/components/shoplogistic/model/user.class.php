<?php

/**
 * Класс инструментов
 */

class slUser
{
    public $modx;
    public $sl;

    public $config;

    public function __construct(shopLogistic &$sl, modX &$modx)
    {
        $this->sl =& $sl;
        $this->modx =& $modx;
        $this->modx->lexicon->load('shoplogistic:default');
    }

    /**
     *  Проверка прав доступа
     * @param $user_id
     * @param $org_id
     * @param $action
     * @return bool
     */
    public function checkUserPermission($user_id, $org_id, $action){
        return true;
    }

    /**
     * Берем ID юзера
     *
     * @return mixed
     */
    public function getUserId(){
        // $this->modx->log(1, print_r($_SESSION["analytics_user"], 1));
        return $_SESSION['analytics_user']['id'];
    }

    /**
     * @param $action
     * @param $properties
     * @return mixed
     */
    public function handlePages($action, $properties = array()){
        switch ($action) {
            case 'get/count/bonuses':
                $response = $this->getCountBonuses($properties);
                break;
            case 'get/history/bonuses':
                $response = $this->getHistoryBonuses($properties);
                break;

        }
        return $response;
    }

    /**
     * Получаем количество бонусов у пользователя
     * @param $properties
     * @return mixed
     */
    public function getCountBonuses($properties){
        if($properties['user_id']){
            $query = $this->modx->newQuery("slBonusAccount");
            $query->where(array(
                "slBonusAccount.user_id:=" => $properties['user_id'],
            ));
            $query->select(array("slBonusAccount.*"));
            if($query->prepare() && $query->stmt->execute()){
                $bonuses = $query->stmt->fetch(PDO::FETCH_ASSOC);

                return $bonuses;
            }
        }
    }

    /**
     * Получаем историю бонусов
     * @param $properties
     * @return mixed
     */
    public function getHistoryBonuses($properties){
        if($properties['user_id']){

            $query = $this->modx->newQuery("slBonusAccount");
            $query->where(array(
                "slBonusAccount.user_id:=" => $properties['user_id'],
            ));
            $query->select(array("slBonusAccount.*"));
            if($query->prepare() && $query->stmt->execute()){
                $result['bonuses'] = $query->stmt->fetch(PDO::FETCH_ASSOC);
            }

            $q = $this->modx->newQuery("slBonusOperations");
            $q->leftJoin("msOrder", "msOrder", "msOrder.id = slBonusOperations.order_id");

            $q->where(array(
                "slBonusOperations.bonus_id:=" => $result['bonuses']['id'],
            ));

            $q->select(array(
                "slBonusOperations.*",
                "msOrder.id as order_id"
            ));
            $q->sortby("slBonusOperations.date", "DESC");
//            $q->prepare();
//            $this->modx->log(1, $q->toSQL());
            if($q->prepare() && $q->stmt->execute()){
                $history = $q->stmt->fetchAll(PDO::FETCH_ASSOC);

                foreach ($history as $key => $his){

                    $q_order = $this->modx->newQuery("slOrder");
                    $q_order->leftJoin("slStores", "slStores", "slStores.id = slOrder.store_id");

                    $q_order->where(array(
                        "slOrder.order_id:=" => $his['order_id'],
                    ));

                    $q_order->select(array(
                        "slStores.name_short",
                    ));

                    if($q_order->prepare() && $q_order->stmt->execute()) {
                        $store = $q_order->stmt->fetch(PDO::FETCH_ASSOC);

                        if($store['name_short']){
                            $history[$key]['store_name'] = $store['name_short'];
                        } else if($his['store_id']) {
                            $q_store = $this->modx->newQuery("slStores");

                            $q_store->where(array(
                                "slStores.id:=" => $his['store_id'],
                            ));

                            $q_store->select(array(
                                "slStores.name_short",
                            ));

                            if($q_store->prepare() && $q_store->stmt->execute()) {
                                $store = $q_store->stmt->fetch(PDO::FETCH_ASSOC);

                                $history[$key]['store_name'] = $store['name_short'];
                            }
                        }
                    }

                    $q_price = $this->modx->newQuery("msOrderProduct");

                    $q_price->where(array(
                        "msOrderProduct.order_id:=" => $his['order_id'],
                        "msOrderProduct.product_id:=" => $his['product_id'],
                    ));

                    $q_price->select(array(
                        "msOrderProduct.price",
                    ));

                    $q_price->prepare();
                    $this->modx->log(1, $q_price->toSQL());

                    if($q_price->prepare() && $q_price->stmt->execute()) {
                        $order = $q_price->stmt->fetch(PDO::FETCH_ASSOC);

                        $history[$key]['price'] = $order['price'];
                        $this->modx->log(1, $order['price']);
                    }
                }

                foreach ($history as $key => $his){
                    if($his['type'] == 'plus'){
                        foreach ($history as $k => $h){
                            if($h['type'] == 'minus' && $h['order_id'] == $his['order_id']){

                                $history[$key]['minus'] = $h;
                                $history[$key]['price'] = $h['price'];

                            }
                        }
                    } else{
                        $isPlus = false;
                        foreach ($history as $k => $h){
                            if($h['type'] == 'plus' && $h['order_id'] == $his['order_id']){
                                $isPlus = true;
                            }
                        }

                        if(!$isPlus){
                            $waiting = array(
                                'type' => 'waiting',
                                'date' => $his['date'],
                                'store_name' => $his['store_name'],
                                'minus' => $his,
                                'price' => $his['price']
                            );
                            $history[] = $waiting;
                        }
                    }

                }



                $new_history = [];
                $monthes = array(
                    "01" => 'Январь',
                    "02" => 'Февраль',
                    "03" => 'Март',
                    "04" => 'Апрель',
                    "05" => 'Май',
                    "06" => 'Июнь',
                    "07" => 'Июль',
                    "08" => 'Август',
                    "09" => 'Сентябрь',
                    "10" => 'Октябрь',
                    "11" => 'Ноябрь',
                    "12" => 'Декабрь'
                );

                $monthes_two = array(
                    "01" => 'Января',
                    "02" => 'Февраля',
                    "03" => 'Марта',
                    "04" => 'Апреля',
                    "05" => 'Мая',
                    "06" => 'Июня',
                    "07" => 'Июля',
                    "08" => 'Августа',
                    "09" => 'Сентября',
                    "10" => 'Октября',
                    "11" => 'Ноября',
                    "12" => 'Декабря'
                );

                //Собераем все года
                foreach ($history as $k => $his){
                    $timestamp = strtotime($his['date']);
                    if(!$new_history[date('Y', $timestamp)]){
                        $new_history[date('Y', $timestamp)] = array();
                    }
                    if(!$new_history[date('Y', $timestamp)][$monthes[date('m', $timestamp)]]){
                        $new_history[date('Y', $timestamp)][$monthes[date('m', $timestamp)]] = array();
                    }
                    if($his['type'] == 'plus' || $his['type'] == 'waiting'){
                        $history[$k]['date_format'] = date('d', $timestamp) . " " . $monthes_two[date('m', $timestamp)] . " " . date('Y', $timestamp);
                        $new_history[date('Y', $timestamp)][$monthes[date('m', $timestamp)]][] = $history[$k];
                    }


                }

                $result['history'] = $new_history;
            }



            return $result;
        }
    }
}