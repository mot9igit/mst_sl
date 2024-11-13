<?php

/**
 * Класс огранизации
 */

class slOrganization
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
     * @param $action
     * @param $properties
     * @return mixed
     */
    public function handlePages($action, $properties = array()){
        switch ($action) {
            case 'get/orgs':
                $response = $this->getOrgs($properties);
                break;
            case 'get/stores':
                $response = $this->getStoresOrg($properties);
                break;
            case 'get/org/store':
                $response = $this->getOrgStore($properties);
                break;
            case 'get/org/profile':
                $response = $this->getOrgProfile($properties);
                break;
            case 'set/org/profile':
                $response = $this->setOrgProfile($properties);
                break;
            case 'set/org/virtual_profile':
                $response = $this->setOrgVirtualProfile($properties);
                break;
            case 'delete/org/virtual_profile':
                $response = $this->deleteOrgVirtualProfile($properties);
                break;
            case 'set/request/profile':
                $response = $this->requestChangeRequisite($properties);
                break;
            case 'get/individual/discount':
                $response = $this->getIndividualDiscount($properties);
                break;
        }
        return $response;
    }

    /**
     * Розничные заказы
     *
     * @param $properties
     * @return array
     */
    public function getOrders($properties){
        // берем доступные склады
        $stores = $this->getStoresOrg($properties, 0);
        foreach($stores["items"] as $store){
            $warehouses[] = $store["id"];
        }
        // берем заказы
        $q = $this->modx->newQuery('slOrder');
        $q->leftJoin('msOrder', 'msOrder', 'msOrder.id = slOrder.order_id');
        $q->leftJoin('msOrderAddress', 'msOrderAddress', 'msOrderAddress.id = msOrder.id');
        $q->leftJoin('modUser', 'User', 'User.id = msOrder.user_id');
        $q->leftJoin('modUserProfile', 'UserProfile', 'UserProfile.id = msOrder.user_id');
        $q->leftJoin('slCRMStage', 'slCRMStage', 'slCRMStage.id = slOrder.status');
        $q->leftJoin('msDelivery', 'Delivery', 'Delivery.id = msOrder.delivery');
        $q->leftJoin('msPayment', 'Payment', 'Payment.id = msOrder.payment');
        if($properties['order_id']){
            $q->where(array(
                'slOrder.id' => $properties['order_id']
            ));
            $q->limit(1);
            $result['total'] = 1;
        }else{
            $criteria = array(
                'slOrder.store_id:IN' => $warehouses,
                'OR:slOrder.warehouse_id:IN' => $warehouses
            );
            $q->where(array('slCRMStage.show_in_analytics' => true));
            $q->where($criteria);
            if($properties['filter']){
                $criteria = array();
                $criteria['comment:LIKE'] = '%'.$properties['filter'].'%';
                $criteria['OR:User.username:LIKE'] = '%'.$properties['filter'].'%';
                $criteria['OR:UserProfile.fullname:LIKE'] = '%'.$properties['filter'].'%';
                $criteria['OR:UserProfile.email:LIKE'] = '%'.$properties['filter'].'%';
                $q->where($criteria);
            }

            $result['total'] = $this->modx->getCount('slOrder', $q);

            if($properties['page'] && $properties['perpage']){
                $limit = $properties['perpage'];
                $offset = ($properties['page'] - 1) * $properties['perpage'];
                $q->limit($limit, $offset);
            }

            $q->groupby('slOrder.id');

            if($properties['sort']){
                $keys = array_keys($properties['sort']);
                $q->sortby('slOrder.'.$keys[0], $properties['sort'][$keys[0]]['dir']);
            }else{
                $q->sortby('slOrder.createdon', "DESC");
            }
        }
        $q->select(
            $this->modx->getSelectColumns('slOrder', 'slOrder', '', array('status', 'delivery', 'payment'), true) . ', ' . $this->modx->getSelectColumns('msOrder', 'msOrder', '', array('id', 'status', 'delivery', 'payment'), true) . ',
            msOrder.id as order_id, msOrder.status as status_id, msOrder.delivery as delivery_id, msOrder.payment as payment_id,
            msOrderAddress.receiver as customer, msOrderAddress.text_address as user_address, User.username as customer_username, msOrderAddress.phone as customer_phone, 
            msOrderAddress.email as customer_email, slCRMStage.name as status_name, slCRMStage.color as status_color, slCRMStage.transition_anchor, 
            slCRMStage.description as stage_description, slCRMStage.check_code as stage_check_code, slCRMStage.stores_available,
            Delivery.name as delivery, Payment.name as payment, slOrder.id as id, slOrder.num as num'
        );
        if($q->prepare() && $q->stmt->execute()){
            $result['orders'] = $q->stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach($result['orders'] as $index => $order){
                $result['orders'][$index]['createdon'] = date('d.m.Y H:i', strtotime($order['createdon']));
                $result['orders'][$index]['cost'] = number_format($order['cost'], 2, ',', ' ');
                $result['orders'][$index]['stage_check_code'] = (bool) $result['orders'][$index]['stage_check_code'];
                $result['orders'][$index]['properties'] = json_decode($order['properties'], 1);
                $store = $this->modx->getObject("slStores", array('id' => $order["store_id"]));
                if($store){
                    $result['orders'][$index]['store'] = $store->get("name_short");
                }
                if($order["tk"]){
                    if($order["tk"] == "cdek"){
                        $result['orders'][$index]['tk'] = "СДЭК";
                    }
                    if($order["tk"] == "postrf"){
                        $result['orders'][$index]['tk'] = "Почта России";
                    }
                    if($order["tk"] == "yandex"){
                        $result['orders'][$index]['tk'] = "Экспресс доставка (Я.Такси)";
                    }
                    if($order["tk"] == "evening"){
                        $result['orders'][$index]['tk'] = "Вечерняя доставка (Я.Такси)";
                    }
                }
                if($properties['order_id']){
                    $q = $this->modx->newQuery('slOrderProduct');
                    $q->leftJoin('msProductData', 'msProductData', 'msProductData.id = slOrderProduct.product_id');
                    $q->select(array(
                        'msProductData.*',
                        'slOrderProduct.*'
                    ));
                    $q->where(array(
                        'order_id' => $properties['order_id']
                    ));
                    if($q->prepare() && $q->stmt->execute()){
                        $products = $q->stmt->fetchAll(PDO::FETCH_ASSOC);
                        $result['products'] = $products;
                        foreach($result['products'] as $key => $v){
                            if($v['image']){
                                $images = $this->sl->tools->prepareImage($v['image'], "", 0);
                                $result['products'][$key]["image"] = $images["image"];
                            }
                        }
                    }
                }
            }
        }
        $q = $this->modx->newQuery('slCRMStage');
        $q->select(array(
            'slCRMStage.*'
        ));
        if($q->prepare() && $q->stmt->execute()) {
            $result['statuses'] = $q->stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        return $result;
    }

    /**
     * Берем доп. свойства организации
     *
     * @param $key
     * @param $org_id
     * @return false
     */
    public function getProperties($key, $org_id){
        $org = $this->modx->getObject("slOrg", $org_id);
        if($org){
            $result = $org->get("properties");
            if(isset($result[$key])){
                return $result[$key];
            }else{
                return false;
            }
        }else{
            return false;
        }
    }

    /**
     * Выставляем доп. свойства организации
     *
     * @param $key
     * @param $org_id
     * @return false
     */
    public function setProperties($key, $org_id, $val){
        $org = $this->modx->getObject("slOrg", $org_id);
        if($org){
            $props = $org->get("properties");
            if(isset($props[$key])){
                $props[$key] = array_merge($props[$key], $val);
            }else{
                $props[$key] = $val;
            }
            $props[$key] = array_unique($props[$key]);
            $org->set("properties", json_encode($props));
            if($org->save()){
                return $props;
            }else{
                return false;
            }
        }else{
            return false;
        }
    }

    /**
     * Удаляем элементы доп. свойства организации
     *
     * @param $key
     * @param $org_id
     * @return false
     */
    public function removePropertiesValue($key, $org_id, $val){
        $org = $this->modx->getObject("slOrg", $org_id);
        if($org){
            $props = $org->get("properties");
            if(isset($props[$key])){
                foreach($val as $v){
                    if (($k = array_search($v, $props[$key])) !== false) {
                        unset($props[$key][$k]);
                    }
                }
                $props[$key] = array_unique($props[$key]);
                $org->set("properties", json_encode($props));
                if($org->save()){
                    return $props;
                }else{
                    return false;
                }
            }else{
                return false;
            }
        }else{
            return false;
        }
    }

    /**
     * Информация о складе
     *
     * @param $store_id
     * @return array|false
     */
    public function getOrgStore($properties){
        $store_id = $properties["store_id"];
        $user_id = $this->sl->userHandler->getUserId();
        if($this->sl->userHandler->checkUserPermission($user_id, $store_id, 'org_store_view')){
            $organization = $this->modx->getObject('slStores', $store_id);
            if($organization) {
                $out = $organization->toArray();
                // чекаем роль для меню
                if($out["store"]){
                    $out["type"] = 1;
                }
                if($out["warehouse"]){
                    $out["type"] = 2;
                }
                if($out["vendor"]){
                    $out["type"] = 3;
                }
                if($out['image']){
                    $out["images"] = $this->sl->tools->prepareImage($out['image']);
                    $out["image"] = $out["images"]["image"];
                }
                // время работы на неделе
                $query = $this->modx->newQuery("slStoresWeekWork");
                $query->where(array(
                    "store_id" => $store_id,
                    "week_day:>" => 0
                ));
                $query->select(array("slStoresWeekWork.*"));
                $query->sortby("slStoresWeekWork.week_day", "ASC");
                if($query->prepare() && $query->stmt->execute()){
                    $data = $query->stmt->fetchAll(PDO::FETCH_ASSOC);
                    foreach($data as $key => $item){
                        $timestamp_from = strtotime($item["date_from"]);
                        $timestamp_to = strtotime($item["date_to"]);
                        $data[$key]["timestamp_from"] = $timestamp_from;
                        $data[$key]["timestamp_to"] = $timestamp_to;
                        $date = new DateTime();
                        $date->setTimestamp($timestamp_from);
                        if($properties['timezone']){
                            $date->setTimezone(new DateTimeZone($properties['timezone']));
                        }
                        $data[$key]["time_from"] = $date->format('H:i');
                        $date->setTimestamp($timestamp_to);
                        if($properties['timezone']){
                            $date->setTimezone(new DateTimeZone($properties['timezone']));
                        }
                        $data[$key]["time_to"] = $date->format('H:i');
                    }
                    $out["worktime"] = $data;
                }
                // время работы на конкретные дни
                $out["workdays"] = array(
                    array(
                        "dot" => "red",
                        "type" => "weekend",
                        "dates" => array()
                    ),
                    array(
                        "dot" => "blue",
                        "type" => "shortdays",
                        "dates" => array()
                    )
                );
                $query = $this->modx->newQuery("slStoresWeekWork");
                $query->where(array(
                    "store_id" => $store_id,
                    "week_day:=" => 0
                ));
                $query->select(array("slStoresWeekWork.*"));
                $query->sortby("slStoresWeekWork.week_day", "ASC");
                if($query->prepare() && $query->stmt->execute()){
                    $data = $query->stmt->fetchAll(PDO::FETCH_ASSOC);
                    foreach($data as $key => $item){
                        $timestamp_from = strtotime($item["date_from"]);
                        $timestamp_to = strtotime($item["date_to"]);
                        $timestamp = strtotime($item["date"]);
                        $data[$key]["timestamp_from"] = $timestamp_from;
                        $data[$key]["timestamp_to"] = $timestamp_to;
                        $date = new DateTime();
                        $date->setTimestamp($timestamp_from);
                        if($properties['timezone']){
                            $date->setTimezone(new DateTimeZone($properties['timezone']));
                        }
                        $data[$key]["time_from"] = $date->format('H:i');
                        $date->setTimestamp($timestamp_to);
                        if($properties['timezone']){
                            $date->setTimezone(new DateTimeZone($properties['timezone']));
                        }
                        $data[$key]["time_to"] = $date->format('H:i');
                        $date = new DateTime();
                        $date->setTimestamp($timestamp);
                        if($properties['timezone']){
                            $date->setTimezone(new DateTimeZone($properties['timezone']));
                        }
                        $data[$key]["date"] = $date->format('Y-m-d');
                        if($item['weekend']){
                            $out["workdays"][] = array(
                                "dot" => "red",
                                "dates" => array($data[$key]["date"]),
                                "popover" => array(
                                    "label" => "Выходной"
                                )
                            );
                        }else{
                            $out["workdays"][] = array(
                                "dot" => "blue",
                                "dates" => array($data[$key]["date"]),
                                "popover" => array(
                                    "label" => $data[$key]["time_from"].' - '.$data[$key]["time_to"]
                                )
                            );
                        }
                    }
                    $out["workdays_source"] = $data;
                }
                // берем товары организации
                $query = $this->modx->newQuery("slStoresRemains");
                $query->where(array("slStoresRemains.store_id" => $store_id));
                $query->select(array("slStoresRemains.*"));
                $out["products"]["count"] = $this->modx->getCount("slStoresRemains", $query);
                $query->where(array("slStoresRemains.product_id:>" => 0));
                $out["products"]["copo_count"] = $this->modx->getCount("slStoresRemains", $query);
                if($out["products"]["count"]){
                    $out["products"]["no_copo_percent"] = (($out["products"]["count"] - $out["products"]["copo_count"]) * 100) / $out["products"]["count"];
                    $out["products"]["no_copo_percent"] = round($out["products"]["no_copo_percent"], 2);
                    $out["products"]["copo_percent"] = 100 - $out["products"]["no_copo_percent"];
                }
                $summ = 0;
                $query = $this->modx->newQuery("slStoresRemains");
                $query->where(array("slStoresRemains.store_id" => $store_id));
                $query->select(array("SUM(slStoresRemains.price * slStoresRemains.remains) as price, SUM(slStoresRemains.remains) as count"));
                if($query->prepare() && $query->stmt->execute()){
                    $data = $query->stmt->fetch(PDO::FETCH_ASSOC);
                    $summ = $data["price"];
                    $out["products"]["summ"] = number_format($data["price"], 2, ',', ' ');
                    $out["products"]["count_all"] = number_format($data["count"], 0, '', ' ');
                }

                $query = $this->modx->newQuery("slStoresRemains");
                $query->where(array("slStoresRemains.store_id" => $store_id));
                $query->select(array("SUM(slStoresRemains.price * slStoresRemains.remains) as price, SUM(slStoresRemains.remains) as count"));
                $query->where(array("slStoresRemains.product_id:>" => 0));
                if($query->prepare() && $query->stmt->execute()){
                    $data = $query->stmt->fetch(PDO::FETCH_ASSOC);
                    if($summ > 0 && $data["price"]){
                        $out["products"]["summ_copo"] = number_format($data["price"], 2, ',', ' ');
                        $out["products"]["count_copo"] = number_format($data["count"], 0, '', ' ');
                        $perc = ($data["price"] / $summ) * 100;
                        $out["products"]["copo_money_percent"] = round($perc, 2);
                        $out["products"]["no_copo_money_percent"] = 100 - $out["products"]["copo_money_percent"];
                    }else{
                        $out["products"]["summ_copo"] = 0;
                        $out["products"]["count_copo"] = 0;
                        $out["products"]["copo_money_percent"] = 0;
                        $out["products"]["no_copo_money_percent"] = 0;
                    }
                }
                $out["settings"] = $this->sl->store->getStoreSettings($store_id);
            }

            return $out;
        }else{
            return false;
        }
    }

    /**
     * Берем информацию по организации
     *
     * @param $id
     * @return false|array
     */
    public function getOrganization($id){
        $user_id = $this->sl->userHandler->getUserId();
        if($this->sl->userHandler->checkUserPermission($user_id, $id, 'org_view')){
            $organization = $this->modx->getObject('slOrg', $id);
            if($organization) {
                $out = $organization->toArray();
                // берем товары организации
                $ids = array();
                $stores = $this->getStoresOrg(array("id" => $id));
                foreach($stores["items"] as $store){
                    $ids[] = $store["id"];
                }
                // чекаем роль для меню
                if(count($stores['items'])){
                    if($out['warehouse']){
                        $out["type"] = 3;
                    }else{
                        $out["type"] = 2;
                    }
                }else{
                    $out["type"] = 1;
                }
                // заказы за 7 дней
                $newDate = new DateTime('7 days ago');
                $date = $newDate->format('Y-m-d H:i:s');
                $query = $this->modx->newQuery("slOrder");
                $query->select(array("SUM(slOrder.cart_cost) as summ, COUNT(*) as count"));
                $query->where(array(
                    "org_id:=" => $id
                    ),
                );
                $query->where(array(
                    "createdon:>=" => $date
                ));
                if($query->prepare() && $query->stmt->execute()){
                    $data = $query->stmt->fetch(PDO::FETCH_ASSOC);
                    $out["orders"]["summ"] = number_format($data["summ"], 2, ',', ' ');
                    $out["orders"]["count"] = number_format($data["count"], 0, '', ' ');
                }
                if($out['image']){
                    $out["images"] = $this->sl->tools->prepareImage($out['image']);
                    $out["image"] = $out["images"]["image"];
                }

                $query = $this->modx->newQuery("slStoresRemains");
                $query->where(array("slStoresRemains.store_id:IN" => $ids));
                $query->select(array("slStoresRemains.*"));
                $out["products"]["count"] = $this->modx->getCount("slStoresRemains", $query);
                $query->where(array("slStoresRemains.product_id:>" => 0));
                $out["products"]["copo_count"] = $this->modx->getCount("slStoresRemains", $query);
                if($out["products"]["count"]){
                    $out["products"]["no_copo_percent"] = (($out["products"]["count"] - $out["products"]["copo_count"]) * 100) / $out["products"]["count"];
                    $out["products"]["no_copo_percent"] = round($out["products"]["no_copo_percent"], 2);
                    $out["products"]["copo_percent"] = 100 - $out["products"]["no_copo_percent"];
                }
                $summ = 0;

                $query = $this->modx->newQuery("slStoresRemains");
                $query->where(array("slStoresRemains.store_id:IN" => $ids));
                $query->select(array("SUM(slStoresRemains.price * slStoresRemains.remains) as price, SUM(slStoresRemains.remains) as count"));
                if($query->prepare() && $query->stmt->execute()){
                    $data = $query->stmt->fetch(PDO::FETCH_ASSOC);
                    $summ = $data["price"];
                    $out["products"]["summ"] = number_format($data["price"], 2, ',', ' ');
                    $out["products"]["count_all"] = number_format($data["count"], 0, '', ' ');
                }

                $query = $this->modx->newQuery("slStoresRemains");
                $query->where(array("slStoresRemains.store_id:IN" => $ids));
                $query->select(array("SUM(slStoresRemains.price * slStoresRemains.remains) as price, SUM(slStoresRemains.remains) as count"));
                $query->where(array("slStoresRemains.product_id:>" => 0));
                if($query->prepare() && $query->stmt->execute()){
                    $data = $query->stmt->fetch(PDO::FETCH_ASSOC);
                    if($summ > 0 && $data["price"]){
                        $out["products"]["summ_copo"] = number_format($data["price"], 2, ',', ' ');
                        $out["products"]["count_copo"] = number_format($data["count"], 0, '', ' ');
                        $perc = ($data["price"] / $summ) * 100;
                        $out["products"]["copo_money_percent"] = round($perc, 2);
                        $out["products"]["no_copo_money_percent"] = 100 - $out["products"]["copo_money_percent"];
                    }else{
                        $out["products"]["summ_copo"] = 0;
                        $out["products"]["count_copo"] = 0;
                        $out["products"]["copo_money_percent"] = 0;
                        $out["products"]["no_copo_money_percent"] = 0;
                    }
                }
                // топ товаров по прогнозу упущенной выручки
                $today = date_create();
                $month_ago = date_create("-1 MONTH");
                date_time_set($month_ago, 00, 00);

                $date_from = date_format($month_ago, 'Y-m-d H:i:s');
                $date_to = date_format($today, 'Y-m-d H:i:s');

                $query = $this->modx->newQuery("slStoresRemains");
                $query->leftJoin("msProductData", "msProductData", "msProductData.id = slStoresRemains.product_id");
                $query->leftJoin("modResource", "modResource", "modResource.id = slStoresRemains.product_id");
                $query->where(array(
                    "slStoresRemains.store_id:IN" => $ids,
                    "slStoresRemains.purchase_speed:>" => 0,
                    "slStoresRemains.remains:=" => 0
                ));
                $query->select(array("slStoresRemains.*,msProductData.image,msProductData.vendor_article,modResource.pagetitle"));
                $query->limit(5);
                $query->sortby('no_money', 'DESC');
                if($query->prepare() && $query->stmt->execute()){
                    $out["no_money"]['top'] = $query->stmt->fetchAll(PDO::FETCH_ASSOC);
                    foreach($out["no_money"]['top'] as $key => $item){
                        $out["no_money"]['top'][$key]["no_money"] = number_format($item["no_money"], 2, ',', ' ');
                    }
                }
                // топ товаров по прогнозу остатков
                $query = $this->modx->newQuery("slStoresRemains");
                $query->leftJoin("msProductData", "msProductData", "msProductData.id = slStoresRemains.product_id");
                $query->leftJoin("modResource", "modResource", "modResource.id = slStoresRemains.product_id");
                $query->where(array(
                    "slStoresRemains.store_id:=" => $ids,
                    "slStoresRemains.purchase_speed:>" => 0,
                    "slStoresRemains.remains:>" => 0
                ));
                $query->select(array("slStoresRemains.*,msProductData.image,msProductData.vendor_article,modResource.pagetitle,FLOOR((slStoresRemains.remains - slStoresRemains.purchase_speed)) as forecast,FLOOR((slStoresRemains.remains - (7 * slStoresRemains.purchase_speed))) as forecast_7, CONCAT(FLOOR((slStoresRemains.remains - slStoresRemains.purchase_speed)), ' / ', FLOOR((slStoresRemains.remains - (7 * slStoresRemains.purchase_speed)))) as forecast_all"));
                $query->limit(5);
                $query->sortby('forecast', 'ASC');
                // $query->prepare();
                // $this->modx->log(1, $query->toSQL());
                if($query->prepare() && $query->stmt->execute()){
                    $out["forecast"]['top'] = $query->stmt->fetchAll(PDO::FETCH_ASSOC);
                    foreach($out["forecast"]['top'] as $key => $item){
                        // $out["forecast"]['top'][$key]["no_money"] = number_format($item["no_money"], 2, ',', ' ');
                    }
                }
            }
            return $out;
        }else{
            return false;
        }
    }

    /**
     * Берем подключенные к магазину склады
     *
     * @param $id
     * @return array
     * */
    public function getDilers($id, $properties){
        $urlMain = $this->sl->config["urlMain"];
        $results = array();
        $query = $this->modx->newQuery("slOrg");
        $query->leftJoin("slWarehouseStores", "slWarehouseStores", "slWarehouseStores.org_id = slOrg.id");
        $query->select(array(
            "slOrg.*,
            slWarehouseStores.warehouse_id,
            slOrg.name as warehouse,
            slWarehouseStores.id as obj_id"
        ));
        $query->where(array(
            "slWarehouseStores.warehouse_id:=" => $id,
            "AND:slOrg.active:=" => 1
        ));

        if($properties['filter']){
            $query->where(array(
                "slOrg.name:LIKE" => "%{$properties['filter']}%"
            ));
        }

        if($properties["filtersdata"]){
            if($properties['filtersdata']['our']){
                $query->where(array(
                    "slOrg.owner_id:=" => $properties['id']
                ));
            }
        }

        $results['total'] = $this->modx->getCount('slOrg', $query);
        if($properties['page'] && $properties['perpage']){
            $limit = $properties['perpage'];
            $offset = ($properties['page'] - 1) * $properties['perpage'];
            $query->limit($limit, $offset);
        }
        if($properties['sort']){
            $keys = array_keys($properties['sort']);
            $query->sortby($keys[0], $properties['sort'][$keys[0]]['dir']);
        }
        if($query->prepare() && $query->stmt->execute()) {
            $results['items'] = $query->stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($results['items'] as $k => $item){
                $req = $this->modx->getObject("slOrgRequisites", array("org_id:=" => $item["id"]));
                if($req){
                    $results['items'][$k]['req'] = $req->toArray();
                }
                if($item['image']){
                    $images = $this->sl->tools->prepareImage($item['image']);
                    $results['items'][$k]['image'] = $images['image'];
                }else{
                    $results['items'][$k]['image'] = $urlMain . $this->modx->getPlaceholder("+conf_noimage");
                }
                $results['items'][$k]['image'] = str_replace("//assets", "/assets", $results['items'][$k]['image']);
            }
            return $results;
        }
    }

    /**
     * Берем индивидуальные скидки
     *
     * @param $id
     * @return array
     * */
    public function getIndividualDiscount($properties){
        $results = array();
        $query = $this->modx->newQuery("slOrg");
        $query->leftJoin("slWarehouseStores", "slWarehouseStores", "slWarehouseStores.org_id = slOrg.id");
        $query->leftJoin("slOrgStores", "slOrgStores", "slOrgStores.org_id = slWarehouseStores.warehouse_id");
        $query->leftJoin("slStores", "slStores", "slStores.id = slOrgStores.store_id");
        $query->select(array(
            "`slOrg`.name as warehouse",
            "`slOrg`.image",
            "`slOrg`.address",
            "`slStores`.id as store_id",
            "`slOrg`.id as client_id",
            "`slStores`.name as store_name"
        ));
        $query->where(array(
            "slWarehouseStores.warehouse_id:=" => $properties['id']
        ));



        if($properties['filter']){
            $criteria = array();
            $criteria['slOrg.name:LIKE'] = '%'.$properties['filter']['filter'].'%';
            $criteria['OR:slOrg.address:LIKE'] = '%'.$properties['filter']['filter'].'%';
            $criteria['OR:slStores.name:LIKE'] = '%'.$properties['filter']['filter'].'%';
            $query->where($criteria);

            if($properties['filter']['filtersdata'] && $properties['filter']['filtersdata']['store'] != null){
                $query->where(array(
                    "`slStores`.`id`:=" => $properties['filter']['filtersdata']['store']
                ));
            }
        }



        $results['total'] = $this->modx->getCount('slStores', $query);
        if($properties['page'] && $properties['perpage']){
            $limit = $properties['perpage'];
            $offset = ($properties['page'] - 1) * $properties['perpage'];
            $query->limit($limit, $offset);
        }
        if($properties['sort']){
            $keys = array_keys($properties['sort']);
            $query->sortby($keys[0], $properties['sort'][$keys[0]]['dir']);
        }else{
            $query->sortby("`slOrg`.name");
        }
        if($query->prepare() && $query->stmt->execute()) {
            $results['items'] = $query->stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($results['items'] as $k => $item){
                if($item['image']){
                    $results['items'][$k]['image'] = $this->sl->tools->prepareImage($item['image'])['image'];
                }else{
                    $urlMain = $this->modx->getOption("site_url");
                    $results['items'][$k]['image'] =  $urlMain . $this->modx->getPlaceholder("+conf_noimage");
                }


                $q = $this->modx->newQuery("slActions");
                $q->select(array(
                    "slActions.*"
                ));
                $q->where(array(
                    "type" => 3,
                    "org_id" => $properties['id'],
                    "client_id" => $item['client_id'],
                    "store_id" => $item['store_id']
                ));

                if($q->prepare() && $q->stmt->execute()) {
                    $action = $q->stmt->fetch(PDO::FETCH_ASSOC);
                    if($action){

                        $q_p = $this->modx->newQuery("slActionsProducts");
                        $q_p->select(array(
                            "slActionsProducts.*"
                        ));
                        $q_p->where(array(
                            'action_id' => $action['id'],
                        ));

                        $count = $this->modx->getCount('slActionsProducts', $q_p);


                        if($action['payer'] == 0){
                            $results['items'][$k]['payer'] = "Покупатель";
                        }else {
                            $results['items'][$k]['payer'] = "Бесплатная доставка";
                        }

                        if($action['condition_min_sum'] > 0){
                            $results['items'][$k]['condition_min_sum'] = $action['condition_min_sum'];
                        }else {
                            $results['items'][$k]['condition_min_sum'] = "";
                        }

                        if($action['delay'] > 0){
                            $results['items'][$k]['delay'] = $action['delay'];
                        } else {
                            $results['items'][$k]['delay'] = "Предоплата";
                        }

                        if($action['type_all_sale'] !== null){
                            if($action['type_all_sale'] == 0){
                                if($action['type_all_sale_symbol'] == 0){
                                    $results['items'][$k]['sale'] = $action['all_sale_value'] . " ₽";

                                    if($count > 0){
                                        $results['items'][$k]['sale'] = $results['items'][$k]['sale'] . " / Скидка на группы товаров";
                                    }
                                } else{
                                    $results['items'][$k]['sale'] = $action['all_sale_value'] . " %";
                                    if($count > 0){
                                        $results['items'][$k]['sale'] = $results['items'][$k]['sale'] . " / Скидка на группы товаров";
                                    }
                                }
                            }
                        } else {
                            if($count > 0){
                                $results['items'][$k]['sale'] = $results['items'][$k]['sale'] . "Скидка на группы товаров";
                            }
                        }


                    } else{
                        $results['items'][$k]['payer'] = "";
                        $results['items'][$k]['condition_min_sum'] = "";
                        $results['items'][$k]['delay'] = "";
                        $results['items'][$k]['sale'] = "";
                    }
                }



//                if($action){
//                    $results['items'][$k]['action'] = $action->toArray();
//                } else{
//                    $results['items'][$k]['action'] = array("type" => "discounts", "org_id" => $properties['id'], "client_id" => $item['client_id'], "store_id" => $item['store_id']);
//                }

//                $results['items'][$k]['stores'] = $this->getStoresOrg($properties);
            }

            return $results;
        }
    }

    /**
     * Чекаем оптовиков
     *
     * @param $properties
     * @return mixed
     */
    public function toggleOpts($properties)
    {
        // Собираем данные для писем
        $mail_data = array();
        $query = $this->modx->newQuery("slOrg");
        $query->where(array("slOrg.id:=" => $properties["store"]));
        $query->select(array("slOrg.*"));
        if($query->prepare() && $query->stmt->execute()) {
            $mail_data["who"] = $query->stmt->fetch(PDO::FETCH_ASSOC);
            $query = $this->modx->newQuery("slOrgRequisites");
            $query->where(array("slOrgRequisites.org_id:=" => $properties["store"]));
            $query->select(array("slOrgRequisites.*"));
            if ($query->prepare() && $query->stmt->execute()) {
                $mail_data["who"]['req'] = $query->stmt->fetch(PDO::FETCH_ASSOC);
            }
        }
        $query = $this->modx->newQuery("slOrg");
        $query->where(array("slOrg.id:=" => $properties["id"]));
        $query->select(array("slOrg.*"));
        if($query->prepare() && $query->stmt->execute()) {
            $mail_data["to"] = $query->stmt->fetch(PDO::FETCH_ASSOC);
            $query = $this->modx->newQuery("slOrgRequisites");
            $query->where(array("slOrgRequisites.org_id:=" => $properties["id"]));
            $query->select(array("slOrgRequisites.*"));
            if ($query->prepare() && $query->stmt->execute()) {
                $mail_data["to"]['req'] = $query->stmt->fetch(PDO::FETCH_ASSOC);
            }
        }
        if($properties['action']){
            // установить
            if(!$object = $this->modx->getObject("slWarehouseStores", array("org_id" => $properties["store"], "warehouse_id" => $properties["id"]))){
                $object = $this->modx->newObject("slWarehouseStores");
            }
            $object->set("org_id", $properties["store"]);
            $object->set("warehouse_id", $properties["id"]);
            $object->set("description", "Установлено через ЛК магазина");
            $object->set("date", time());
            $object->save();

            // Включить в закупках
            // Берем все склады оптовика
            // toggleOptsVisible
            $query = $this->modx->newQuery("slOrgStores");
            $query->leftJoin("slStores", "slStores", "slStores.id = slOrgStores.store_id");
            $query->where(array(
                "slOrgStores.org_id:=" => $properties["store"],
                "slStores.active" => 1
            ));
            $query->select(array("slStores.*"));
            if($query->prepare() && $query->stmt->execute()){
                $stores = $query->stmt->fetchAll(PDO::FETCH_ASSOC);
                if($stores){
                    foreach($stores as $store){
                        $props = array(
                            "id" => $properties["id"],
                            "action" => 1,
                            "store" => $store["id"]
                        );
                        $props = $this->toggleOptsVisible($props);
                    }
                }
            }

            //уведомление продавцу
            $notification = array(
                "org_id" => $properties["id"],
                "namespace" => 7,
                "store_id" => $properties["store"]
            );
            $this->sl->notification->setNotification(array("data" => $notification));


            // Письмо, что подписались
            if($properties["id"] != 39) {
                $emails = $this->sl->notification->getEmailManagers($properties["id"], $properties["store"], 7);
                if(count($emails) && $this->sl->config["alert_mode"] == 1){
                    $result = $this->sl->tools->sendMail("@FILE chunks/email_new_store.tpl", $mail_data, $emails, "Подключился новый клиент {$mail_data["who"]["name"]}! МС Закупки.");
                }
            }


            return $this->sl->success("Объект создан", $object->toArray());
        }else{
            // удалить
            $object = $this->modx->getObject("slWarehouseStores", array("org_id" => $properties["store"], "warehouse_id" => $properties["id"]));
            if($object){
                $data = $object->toArray();
                $object->remove();
            }

            //уведомление продавцу
            $notification = array(
                "org_id" => $properties["id"],
                "namespace" => 8,
                "store_id" => $properties["store"]
            );
            $this->sl->notification->setNotification(array("data" => $notification));

            // Письмо, что отписались
            if($properties["id"] != 39) {
                $emails = $this->sl->notification->getEmailManagers($properties["id"], $properties["store"], 8);
                if(count($emails) && $this->sl->config["alert_mode"] == 1){
                    $result = $this->sl->tools->sendMail("@FILE chunks/email_new_store.tpl", $mail_data, $emails, "Отключился новый клиент {$mail_data["who"]["name"]}! МС Закупки.");
                }
            }

            return $this->sl->success("Объект удален", $data);
        }

    }

    /**
     * Управляем подключением к Оптовикам
     *
     * @param $properties
     * @return array|void
     */
    public function getOpts($properties){
        $query = $this->modx->newQuery("slOrg");
        $query->leftJoin("slWarehouseStores", "slWarehouseStores", "slWarehouseStores.org_id = {$properties["id"]} AND slWarehouseStores.warehouse_id = slOrg.id");
        // $query->leftJoin("dartLocationCity", "dartLocationCity", "dartLocationCity.id = slStores.city");
        // $query->leftJoin("dartLocationRegion", "dartLocationRegion", "dartLocationRegion.id = dartLocationCity.region");
        // фильтруем по флагу Активности и Оптового склада
        $query->where(array(
            "slOrg.active:=" => 1,
            "slOrg.warehouse:=" => 1
        ));
        /*
        if($properties['filtersdata']['region']){
            $geo_data = $this->parseRegions($properties['filtersdata']['region']);
            $criteria = array();
            if ($geo_data["cities"]) {
                $criteria["dartLocationCity.id:IN"] = $geo_data["cities"];
            }
            if ($geo_data["regions"]) {
                $criteria["dartLocationRegion.id:IN"] = $geo_data["regions"];
            }
            if($criteria){
                $query->where($criteria);
            }
        }
        */
        if ($properties['filtersdata']['our']) {
            $query->where(array("slWarehouseStores.org_id:=" => $properties["id"]));
        }

        if ($properties['filter']) {
            $words = explode(" ", $properties['filter']);
            foreach ($words as $word) {
                $criteria = array();
                $criteria['slOrg.name:LIKE'] = '%' . trim($word) . '%';
                $criteria['OR:slOrg.address:LIKE'] = '%' . trim($word) . '%';
                $query->where($criteria);
            }
        }

        $query->select(array(
            "slOrg.*,
            IF(slWarehouseStores.warehouse_id = slOrg.id, 1, 0) as connection,
            slWarehouseStores.date as connection_date"
        ));
        $result['total'] = $this->modx->getCount('slOrg', $query);

        // Устанавливаем лимит 1/10 от общего количества записей
        // со сдвигом 1/20 (offset)
        if($properties['page'] && $properties['perpage']){
            $limit = $properties['perpage'];
            $offset = ($properties['page'] - 1) * $properties['perpage'];
            $query->limit($limit, $offset);
        }

        // И сортируем по ID в обратном порядке
        if($properties['sort']){
            $keys = array_keys($properties['sort']);
            $query->sortby($keys[0], $properties['sort'][$keys[0]]['dir']);
        }else{
            $query->sortby('connection', 'desc');
        }

        $query->prepare();
        $this->modx->log(1, "MOT9I :: ".print_r($query->toSQL(), 1));

        if ($query->prepare() && $query->stmt->execute()) {
            $result['items'] = $query->stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach($result['items'] as $key => $val){
                $req = $this->modx->getObject("slOrgRequisites", array("org_id:=" => $val["id"]));
                if($req){
                    $result['items'][$key]['req'] = $req->toArray();
                }
                if($val["image"]){
                    $result['items'][$key]['image'] = $this->modx->getOption("site_url")."assets/content/".$val["image"];
                }else{
                    $result['items'][$key]['image'] = $this->modx->getOption("site_url").$this->modx->getOption("shoplogistic_blank_image");
                }

                $result['items'][$key]['connection_date'] = date('d.m.Y H:i', strtotime($val['connection_date']));
            }
            return $result;
        }
    }

    /**
     * Чекаем оптовиков
     *
     * @param $properties
     * @return void
     */
    public function checkOpts($properties){
        $outputs = array();
        if($properties['action'] == 'set'){
            if(count($properties['vendors'])){
                $vendors = array();
                foreach($properties['vendors'] as $key => $v){
                    if($v){
                        $vendors[] = $key;
                    }
                }
                if(count($vendors)){
                    $this->setProperties("opt_selected_stores", $properties["id"], $vendors);
                }
            }
        }
        return $this->sl->success("Объекты создан", $outputs);
    }

    /**
     * Чекаем оптовиков (видимость)
     *
     * @param $properties
     * @return mixed
     */
    public function toggleOptsVisible($properties)
    {
        $vendors = array($properties["id"]);
        if ($properties['action']) {
            if(count($vendors)){
                $props = $this->setProperties("opt_selected_stores", $properties["store"], $vendors);
                return $this->sl->success("Объект создан", $props);
            }
        } else {
            if(count($vendors)){
                $props = $this->removePropertiesValue("opt_selected_stores", $properties["store"], $vendors);
                return $this->sl->success("Объект удален", $props);
            }
        }
        return $this->sl->tools->error("Объект не найден", $properties);
    }

    /**
     * Поставщики для отображения закупок
     *
     * @param $properties
     * @return array|void
     */
    public function getVendorsStores($properties){
        $data = array(
            'selected_count' => 0,
            'selected' => array(),
            "available_count" => 0,
            "available" => array()
        );
        $iids = array();
        $count = 0;
        $urlMain = $this->modx->getOption("site_url");

        // Берем подключенных оптовиков
        $warehouses = array();
        $query = $this->modx->newQuery("slWarehouseStores");
        $query->where(array(
            "`slWarehouseStores`.`org_id`:=" => $properties['id']
        ));
        $query->select(array("slWarehouseStores.warehouse_id"));
        if($query->prepare() && $query->stmt->execute()){
            $results = $query->stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach($results as $warehouse){
                $warehouses[] = $warehouse["warehouse_id"];
            }
        }

        $selected_stores = $this->getProperties("opt_selected_stores", $properties['id']);
        if($selected_stores){
            // get selected
            $query = $this->modx->newQuery("slStores");
            $query->leftJoin("slOrgStores", "slOrgStores", "slOrgStores.store_id = slStores.id");
            $query->leftJoin("slOrg", "slOrg", "slOrg.id = slOrgStores.org_id");
            $query->where(array(
                "slOrgStores.org_id:IN" => $warehouses,
                "`slStores`.`id`:IN" => $selected_stores,
                "`slStores`.`opt_marketplace`:=" => 1,
                "`slStores`.`active`:=" => 1,
                "`slOrg`.`active`:=" => 1,
                "`slOrg`.`warehouse`:=" => 1
            ));
            $query->select(array("slStores.*"));
            $query->groupby("slStores.id");
            if ($query->prepare() && $query->stmt->execute()) {
                $selected = $query->stmt->fetchAll(PDO::FETCH_ASSOC);
                foreach ($selected as $key => $value) {
                    $iids[] = $value['id'];
                    $selected[$key]['image'] = $urlMain . "assets/content/" . $value['image'];
                    if($selected[$key]['coordinats']){
                        $selected[$key]['mapcoordinates'] = explode(",", $selected[$key]['coordinats']);
                        foreach($selected[$key]['mapcoordinates'] as $k => $coord){
                            $selected[$key]['mapcoordinates'][$k] = floatval(trim($coord));
                        }
                        $selected[$key]['mapcoordinates'] = array_reverse($selected[$key]['mapcoordinates']);
                    }
                    unset($selected[$key]['apikey']);
                    $count++;
                }

                $data['selected_count'] = $count;
                $data['selected'] = $selected;
            }
        }else{
            $data['selected_count'] = 0;
            $data['selected'] = array();
        }

        // берем доступные для выбора склады
        $query = $this->modx->newQuery("slStores");
        $query->leftJoin("slOrgStores", "slOrgStores", "slOrgStores.store_id = slStores.id");
        $query->leftJoin("slOrg", "slOrg", "slOrg.id = slOrgStores.org_id");
        $query->where(array(
            "slOrgStores.org_id:IN" => $warehouses,
            "`slStores`.`active`:=" => 1,
            "`slStores`.`opt_marketplace`:=" => 1,
            "`slOrg`.`warehouse`:=" => 1,
            "`slOrg`.`active`:=" => 1
        ));
        if($selected_stores){
            $query->where(array(
                "`slStores`.`id`:NOT IN" => $selected_stores,
            ));
        }
        if($properties['filter']){
            $query->where(array(
                "`slStores`.`name`:LIKE" => "%".$properties['filter']."%",
                "OR:`slStores`.`address`:LIKE" => "%".$properties['filter']."%"
            ));
        }
        $query->select(array("slStores.*"));
        $query->groupby("slStores.id");
        if($query->prepare() && $query->stmt->execute()){
            $vendors = $query->stmt->fetchAll(PDO::FETCH_ASSOC);
            $count = 0;
            if($vendors) {
                foreach ($vendors as $key => $value) {
                    if (!in_array($value['id'], $iids)) {
                        if($value['image']){
                            $vendors[$key]['image'] = $urlMain . "assets/content/" . $value['image'];
                        } else{
                            $vendors[$key]['image'] = $urlMain . "/assets/files/img/nopic.png";
                        }
                        if ($vendors[$key]['coordinats']) {
                            $vendors[$key]['mapcoordinates'] = explode(",", $vendors[$key]['coordinats']);
                            foreach ($vendors[$key]['mapcoordinates'] as $k => $coord) {
                                $vendors[$key]['mapcoordinates'][$k] = floatval(trim($coord));
                            }
                            $vendors[$key]['mapcoordinates'] = array_reverse($vendors[$key]['mapcoordinates']);
                        }
                        unset($vendors[$key]['apikey']);
                    } else {
                        unset($vendors[$key]);
                    }
                    $count++;
                }
                $data["available_count"] = count($vendors) + $data['selected_count'];
                $data['available'] = $vendors;
            }else{
                $data["available_count"] = 0;
                $data['available'] = array();
            }
        }
        return $data;
    }

    /**
     * Берем подключенные к магазину склады
     *
     * @param $id
     * @return array
     */
    public function getWarehouses($id, $visible = 0){
        // Берем подключенных оптовиков
        $warehouses = array();
        $query = $this->modx->newQuery("slWarehouseStores");
        $query->where(array(
            "`slWarehouseStores`.`org_id`:=" => $id
        ));
        $query->select(array("slWarehouseStores.warehouse_id"));
        if($query->prepare() && $query->stmt->execute()){
            $results = $query->stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach($results as $warehouse){
                $warehouses[] = $warehouse["warehouse_id"];
            }
        }

        $selected_stores = $this->getProperties("opt_selected_stores", $id);
        if($selected_stores){
            // get selected
            $query = $this->modx->newQuery("slStores");
            $query->leftJoin("slOrgStores", "slOrgStores", "slOrgStores.store_id = slStores.id");
            $query->leftJoin("slOrg", "slOrg", "slOrg.id = slOrgStores.org_id");
            $query->where(array(
                "slOrgStores.org_id:IN" => $warehouses,
                "`slStores`.`id`:IN" => $selected_stores,
                "`slStores`.`opt_marketplace`:=" => 1,
                "`slStores`.`active`:=" => 1,
                "`slOrg`.`active`:=" => 1,
                "`slOrg`.`warehouse`:=" => 1
            ));
            $query->select(array("slStores.*"));
            if ($query->prepare() && $query->stmt->execute()) {
                $warehouses = $query->stmt->fetchAll(PDO::FETCH_ASSOC);
                return $warehouses;
            }
        }
    }

    /**
     * Получаем все организации пользователя
     * @return array|null
     */
    public function getOrgs(){
        $user_id = $this->sl->userHandler->getUserId();
        if($user_id){
            $query = $this->modx->newQuery("slOrgUsers");
            $query->leftJoin("slOrg", "slOrg", "slOrg.id = slOrgUsers.org_id");
            $query->where(array(
                "slOrgUsers.user_id:=" => $user_id
            ));
            $query->select(array(
                "slOrg.*"
            ));
            if($query->prepare() && $query->stmt->execute()) {
                $urlMain = $this->modx->getOption("site_url");
                $orgs = $query->stmt->fetchAll(PDO::FETCH_ASSOC);
                foreach ($orgs as $key => $org){
                    // заказы за 7 дней
                    $newDate = new DateTime('7 days ago');
                    $date = $newDate->format('Y-m-d H:i:s');
                    $query = $this->modx->newQuery("slOrder");
                    $query->select(array("SUM(slOrder.cart_cost) as summ, COUNT(*) as count"));
                    $query->where(array(
                        "org_id:=" => $org['id']
                        )
                    );
                    $query->where(array(
                        "createdon:>=" => $date
                    ));
                    if($query->prepare() && $query->stmt->execute()){
                        $data = $query->stmt->fetch(PDO::FETCH_ASSOC);
                        $orgs[$key]["orders"]["summ"] = number_format($data["summ"], 2, ',', ' ');
                        $orgs[$key]["orders"]["count"] = number_format($data["count"], 0, '', ' ');
                    }
                    if($org["image"]){
                        $out["images"] = $this->sl->tools->prepareImage($org['image']);
                        $orgs[$key]['image'] = $out["images"]["image"];
                    }
                    // Берем адрес
                    $query = $this->modx->newQuery("slOrgRequisites");
                    $query->where(array(
                        array(
                            "org_id:=" => $org['id']
                        )
                    ));
                    $query->select(array("slOrgRequisites.*"));
                    if($query->prepare() && $query->stmt->execute()){
                        $req = $query->stmt->fetch(PDO::FETCH_ASSOC);
                        if($req){
                            $orgs[$key]['description'] = $req["fact_address"];
                        }
                    }
                }
                return $orgs;
            }
        }
    }

    /**
     *
     * Получаем все склады пользователя
     * @return
     */
    public function getStoresOrg($properties, $pagination = 1){
        if($properties['id']) {
            $query = $this->modx->newQuery("slOrgStores");
            $query->leftJoin("slStores", "slStores", "slStores.id = slOrgStores.store_id");
            $query->where(array(
                "slOrgStores.org_id:=" => $properties['id'],
            ));
            $query->select(array(
                "slStores.*"
            ));
            $result['total'] = $this->modx->getCount('slOrgStores', $query);

            if ($pagination) {
                // Устанавливаем лимит 1/10 от общего количества записей
                // со сдвигом 1/20 (offset)
                if ($properties['page'] && $properties['perpage']) {
                    $limit = $properties['perpage'];
                    $offset = ($properties['page'] - 1) * $properties['perpage'];
                    $query->limit($limit, $offset);
                }
                // И сортируем по ID в обратном порядке
                if($properties['sort']){
                    $keys = array_keys($properties['sort']);
                    $query->sortby($keys[0], $properties['sort'][$keys[0]]['dir']);
                }
            }

            if($query->prepare() && $query->stmt->execute()) {
                $result['items'] = $query->stmt->fetchAll(PDO::FETCH_ASSOC);
                foreach ($result['items'] as $key => $store){
                    if($result['items'][$key]['name_short'] == ''){
                        $result['items'][$key]['name_short'] = $result['items'][$key]['name'];
                    }
                    if($result['items'][$key]['address_short'] == ''){
                        $result['items'][$key]['address_short'] = $result['items'][$key]['address'];
                    }
                    $query = $this->modx->newQuery("slStoresRemains");
                    $query->where(array("slStoresRemains.store_id:=" => $store["id"]));
                    $result['items'][$key]["remains"] = number_format($this->modx->getCount("slStoresRemains", $query), 0, "", " ");
                    if($store["image"]) {
                        $out["images"] = $this->sl->tools->prepareImage($store['image']);
                        $result['items'][$key]['image'] = $out["images"]["image"];
                    }
                }
                return $result;
            }
        }
    }

    /**
     *
     * Получаем настройки огранизации
     * @return
     */
    public function getOrgProfile($properties){
        $user_id = $this->sl->userHandler->getUserId();
        if($properties['id']){
            // проверяем связь юзера и организации
            $criteria = array(
                "org_id:=" => $properties['id'],
                "user_id:=" => $user_id
            );
            $ulink = $this->modx->getObject("slOrgUsers", $criteria);
            $org = false;
            if($properties['owner_id']) {
                // проверяем связь владельца и организации
                $criteria = array(
                    "id:=" => $properties['id'],
                    "owner_id:=" => $properties['owner_id']
                );
                $org = $this->modx->getObject("slOrg", $criteria);
            }
            if($ulink || $org){
                $urlMain = $this->modx->getOption("site_url");
                $query = $this->modx->newQuery("slOrg");
                $query->where(array(
                    "`slOrg`.id:=" => $properties['id'],
                ));
                $query->select(array(
                    "`slOrg`.*"
                ));
                if($query->prepare() && $query->stmt->execute()){
                    $org = $query->stmt->fetch(PDO::FETCH_ASSOC);
                    if($org['image']){
                        $org['image'] = $urlMain . "assets/content/" .  $org['image'];
                    }
                    $q = $this->modx->newQuery("slOrgRequisites");
                    $q->where(array(
                        "`slOrgRequisites`.`org_id`:=" => $org['id'],
                    ));
                    $q->select(array(
                        "`slOrgRequisites`.*"
                    ));
                    if($q->prepare() && $q->stmt->execute()){
                        $orgRequisites = $q->stmt->fetchAll(PDO::FETCH_ASSOC);
                        $org['requisites'] = $orgRequisites;
                        foreach ($org['requisites'] as $key => $requisite){
                            $queryBank = $this->modx->newQuery("slOrgBankRequisites");
                            $queryBank->where(array(
                                "`slOrgBankRequisites`.`org_requisite_id`:=" => $requisite['id'],
                            ));
                            $queryBank->select(array(
                                "`slOrgBankRequisites`.*"
                            ));
                            if($queryBank->prepare() && $queryBank->stmt->execute()) {
                                $bankRequisites = $queryBank->stmt->fetchAll(PDO::FETCH_ASSOC);
                                $org['requisites'][$key]['banks'] = $bankRequisites;
                            }
                        }
                    }
                    $q = $this->modx->newQuery("slOrgStores");
                    $q->leftJoin("slStores", "slStores", "slStores.id = slOrgStores.store_id");
                    $q->where(array(
                        "`slOrgStores`.`org_id`:=" => $org['id'],
                    ));
                    $q->select(array(
                        "`slStores`.*"
                    ));
                    if($q->prepare() && $q->stmt->execute()){
                        $stores = $q->stmt->fetchAll(PDO::FETCH_ASSOC);
                        $org['warehouses'] = $stores;
                    }

                    $q_m = $this->modx->newQuery("slNotificationManagers");
                    $q_m->where(array(
                        "`slNotificationManagers`.`org_id`:=" => $org['id'],
                    ));
                    $q_m->select(array(
                        "`slNotificationManagers`.*"
                    ));

                    if($q_m->prepare() && $q_m->stmt->execute()){
                        $managers = $q_m->stmt->fetchAll(PDO::FETCH_ASSOC);
                        $data_managers = array();
                        $mapping = [
                            1 => "order_status_changes",
                            2 => "new_opt_order",
                            3 => "company_enabled",
                            4 => "company_connected",
                            5 => "new_vendor",
                            7 => "added_to_my_vendors",
                            8 => "deleted_from_my_vendors"
                        ];
                        foreach ($managers as $key => $manager){


                            // Преобразуем строку с числами в массив
                            $typesArray = explode(",", $manager['type']);

                            // Создаём пустой объект для результата
                            $notif = [];

                            // Перебираем все числа и добавляем соответствующие ключи в объект
                            foreach ($typesArray as $type) {
                                $type = (int)$type; // Преобразуем строку в целое число
                                if (isset($mapping[$type])) {
                                    $notif[$mapping[$type]] = true;
                                }
                            }

                            if(!$manager['global']){
                                $clients = array();

                                $q_r = $this->modx->newQuery("dartLocationRegion");
                                $q_r->select(array(
                                    "dartLocationRegion.id",
                                    "dartLocationRegion.name",
                                ));

                                $numbersArray = explode(",", $manager['region']);
                                $q_r->where(array(
                                    "`dartLocationRegion`.`id`:IN" => $numbersArray,
                                ));

                                if ($q_r->prepare() && $q_r->stmt->execute()) {
                                    $regions = $q_r->stmt->fetchAll(PDO::FETCH_ASSOC);

                                    foreach ($regions as $kr => $region){
                                        $regions[$kr]['id'] = "r_".$region['id'];
                                    }
                                }

                                $results = array();
                                $q_o = $this->modx->newQuery("slOrg");
                                $q_o->select(array(
                                    "slOrg.id,
                                    slOrg.name as name"
                                ));
                                $numbersArray = explode(",", $manager['org']);
                                $q_o->where(array(
                                    "AND:slOrg.active:=" => 1,
                                    "`slOrg`.`id`:IN" => $numbersArray
                                ));
                                if ($q_o->prepare() && $q_o->stmt->execute()) {
                                    $orgs = $q_o->stmt->fetchAll(PDO::FETCH_ASSOC);

                                    foreach ($orgs as $ko => $org){
                                        $orgs[$ko]['id'] = "o_".$org['id'];
                                    }
                                }

                                $q_c = $this->modx->newQuery("dartLocationCity");
                                $q_c->select(array(
                                    "dartLocationCity.id",
                                    "dartLocationCity.city as name",
                                ));
                                $numbersArray = explode(",", $manager['city']);
                                $q_c->where(array(
                                    "`dartLocationCity`.`id`:IN" => $numbersArray,
                                ));
                                if ($q_c->prepare() && $q_c->stmt->execute()) {
                                    $city = $q_c->stmt->fetchAll(PDO::FETCH_ASSOC);

                                    foreach ($city as $kc => $cit){
                                        $city[$kc]['id'] = "c_".$cit['id'];
                                    }

                                    $clients = array_merge($regions, $city, $orgs);
                                }
                            }

                            $data_managers[] = array(
                                "id" => $manager['id'],
                                "name" => $manager['receiver'],
                                "email" => $manager['email'],
                                "phone" => $manager['phone'],
                                "unlimitied_clients" => $manager['global'] == 1? true : false,
                                "notifications" => $notif,
                                "clients" => $clients
                            );
                        }
                    }


                    $org['managers'] = $data_managers;
                    $org["success"] = true;
                    return $org;
                }
            }else{
                return $this->sl->tools->error("У вас нет доступа к этой организации");
            }
        }
    }

    /**
     * Берем приватные организации по ИНН
     *
     * @param $inn
     * @return array
     */
    public function getPrivateClients($inn){
        $clients = array();
        $query = $this->modx->newQuery("slOrgRequisites");
        $query->leftJoin("slOrg", "slOrg", "slOrg.id = slOrgRequisites.org_id");
        $query->where(array(
            'slOrgRequisites.inn:=' => $inn,
            "slOrg.owner_id:>" => 0
        ));
        $query->select(array("slOrgRequisites.*"));
        if($query->prepare() && $query->stmt->execute()){
            $clients = $query->stmt->fetchAll(PDO::FETCH_ASSOC);
            return $clients;
        }
    }

    public function getRegionsAndCity($id){
        //Получаем все города и регионы организации
        $result = array();

        $q = $this->modx->newQuery("slOrgStores");
        $q->leftJoin("slStores", "slStores", "slStores.id = slOrgStores.store_id");
        $q->where(array(
            "`slOrgStores`.`org_id`:=" => $id,
        ));
        $q->select(array(
            "`slStores`.city"
        ));
        if($q->prepare() && $q->stmt->execute()){
            $stores = $q->stmt->fetchAll(PDO::FETCH_ASSOC);


            foreach ($stores as $store){
                $result['city'][] = $store['city'];
            }

            $query = $this->modx->newQuery("dartLocationCity");
            $query->where(array(
                "`dartLocationCity`.`id`:IN" => $result['city'],
            ));
            $query->select(array(
                "dartLocationCity.region",
            ));

            if($query->prepare() && $query->stmt->execute()) {
                $regions = $query->stmt->fetchAll(PDO::FETCH_ASSOC);

                foreach ($regions as $region){
                    $result['region'][] = $region['region'];
                }
            }

            return $result;
        }
    }

    /**
     * Удаление организации
     *
     * @param $properties
     * @return mixed
     */
    public function deleteOrgVirtualProfile($properties){
        $user_id = $this->sl->userHandler->getUserId();
        if($properties["client_id"]){
            $org = $this->modx->getObject("slOrg", $properties["client_id"]);
            if($org){
                $owner_id = $org->get("owner_id");
                if($owner_id != $properties["id"]){
                    return $this->sl->tools->error("У вас нет доступа к этой организации");
                }
            }else{
                return $this->sl->tools->error("Организация не найдена");
            }
            $org_id = $org->get("id");
            // удаляем связь родительской организации и виртуальной
            $criteria = array(
                "org_id:=" => $org_id,
                "warehouse_id:=" => $properties["id"]
            );
            $uslink = $this->modx->getObject("slWarehouseStores", $criteria);
            if($uslink){
                $uslink->remove();
            }
            // удаляем связь юзера и организации
            $criteria = array(
                "org_id:=" => $org_id,
                "user_id:=" => $user_id
            );
            $ulink = $this->modx->getObject("slOrgUsers", $criteria);
            if($ulink){
                $ulink->remove();
            }
            // удаляем склады
            $query = $this->modx->newQuery("slOrgStores");
            $query->where(array(
                "org_id:=" => $org_id
            ));
            $query->select(array("slOrgStores.*"));
            if($query->prepare() && $query->stmt->execute()){
                $stores = $query->stmt->fetchAll(PDO::FETCH_ASSOC);
                foreach($stores as $store){
                    $store_id = $store["store_id"];
                    // удаляем связь склада и организации
                    $criteria = array(
                        "org_id:=" => $org_id,
                        "store_id:=" => $store_id
                    );
                    $link = $this->modx->getObject("slOrgStores", $criteria);
                    if($link){
                        $link->remove();
                    }
                    // удаляем связь юзера и склада
                    $criteria = array(
                        "user_id:=" => $user_id,
                        "store_id:=" => $store_id
                    );
                    $uslink = $this->modx->getObject("slStoreUsers", $criteria);
                    if($uslink){
                        $uslink->remove();
                    }
                    // удаляем склад
                    $store = $this->modx->getObject("slStores", $store_id);
                    if($store){
                        $store->remove();
                    }
                }
            }
            // удаляем реквизиты
            $org_reqs = $this->modx->getCollection('slOrgRequisites', array("org_id:=" => $org_id));
            foreach($org_reqs as $reqs){
                $reqs->remove();
            }
            // удаляем организацию
            $org = $this->modx->getObject("slOrg", $org_id);
            if($org){
                $org->remove();
            }
            return $this->sl->tools->success("Организация успешно удалена", array("org_id" => $org_id));
        }else{
            return $this->sl->tools->error("Организация не найдена");
        }
    }

    /**
     * Создание виртуальной организации
     *
     * @param $properties
     * @return mixed
     */
    public function setOrgVirtualProfile($properties, $force = 0){
        $user_id = $this->sl->userHandler->getUserId();
        if(($properties["id"] && $user_id) || ($properties["id"] && $force)){
            $userdata = array();
            if($properties["client_id"]){
                // проверяем доступ
                $org = $this->modx->getObject("slOrg", $properties["client_id"]);
                if($org){
                    $owner_id = $org->get("owner_id");
                    if($owner_id != $properties["id"]){
                        return $this->sl->tools->error("У вас нет доступа к этой организации");
                    }
                }else{
                    return $this->sl->tools->error("Организация не найдена");
                }
            }else{
                $query = $this->modx->newQuery("slOrgRequisites");
                $query->leftJoin("slOrg", "slOrg", "slOrg.id = slOrgRequisites.org_id");
                $query->where(array(
                    'slOrgRequisites.inn:=' => $properties['data']['org']['inn'],
                    "slOrg.owner_id:=" => 0
                ));
                $count = $this->modx->getCount('slOrgRequisites', $query);
                if($count > 0){
                    return $this->sl->tools->error("Организация с ИНН {$properties['org']['inn']} уже прошла процедуру интеграции!");
                }
                $query = $this->modx->newQuery("slOrgRequisites");
                $query->leftJoin("slOrg", "slOrg", "slOrg.id = slOrgRequisites.org_id");
                $query->where(array(
                    'slOrgRequisites.inn:=' => $properties['data']['org']['inn'],
                    "slOrg.owner_id:=" => $properties["id"]
                ));
                $count = $this->modx->getCount('slOrgRequisites', $query);
                if($count > 0){
                    $query->select(array("slOrg.*"));
                    if($query->prepare() && $query->stmt->execute()){
                        $userdata["organization"] = $query->stmt->fetch(PDO::FETCH_ASSOC);
                    }
                    return $this->sl->tools->error("Организация с ИНН {$properties['org']['inn']} уже существует!", $userdata);
                }
                $org = $this->modx->newObject("slOrg");
                $org->set("owner_id", $properties["id"]);
            }
            // Получили все объекты
            if($properties['data']['upload_image']){
                if ($properties['data']['image']) {
                    $avatar = $this->modx->getOption('base_path') . "assets/content/avatars/" . $properties['data']['image']['name'];

                    if (rename($this->modx->getOption('base_path') . $properties['data']['image']['original'], $avatar)) {
                        $org->set("image", "avatars/" . $properties['data']['image']['name']);
                    }
                }
            }
            if($properties['data']['contact']){
                $org->set('contact', $properties['data']['contact']);
            }
            if($properties['data']['email']){
                $org->set('email', $properties['data']['email']);
            }
            if($properties['data']['phone']){
                $org->set('phone', $properties['data']['phone']);
            }
            $org->set("name", $properties['data']['org']['name']["value"]);
            $org->set("store", 0);
            if($org->save()) {
                $userdata["organization"] = $org->toArray();
                // Создаем реквизиты
                $org_id = $org->get("id");
                if($properties["client_id"]){
                    $org_req = $this->modx->getObject('slOrgRequisites', array("org_id:=" => $org_id));
                }else{
                    $org_req = $this->modx->newObject('slOrgRequisites');
                    $org_req->set("org_id", $org_id);
                }
                if($org_req){
                    $org_req->set("name", $properties['data']['org']['name']["value"]);
                    $org_req->set("inn", $properties['data']['org']['inn']);
                    $org_req->save();
                    $userdata["organization"]['requizites'] = $org_req->toArray();
                }
                foreach($properties['data']['org']["warehouses"] as $index => $address){
                    if($address['address']["value"]){
                        if(!isset($address['address']["data"])){
                            if (!class_exists('Dadata')) {
                                require_once dirname(__FILE__) . '/dadata.class.php';
                            }
                            $token = $this->modx->getOption('shoplogistic_api_key_dadata');
                            $secret = $this->modx->getOption('shoplogistic_secret_key_dadata');
                            $dadata = new Dadata($token, $secret);
                            $dadata->init();
                            $res = $dadata->clean('address', $address["address"]["value"]);
                            $address['address']["data"] = $res[0];
                        }
                        if($index == 0) {
                            $org_req->set("fact_address", $address["address"]["value"]);
                            $org_req->save();
                        }
                        if($address["id"]){
                            $store = $this->modx->getObject("slStores", $address["id"]);
                        }else{
                            $store = $this->modx->newObject("slStores");
                        }
                        $store->set("name", $properties['data']['org']['name']["value"].' || '.$address["address"]["value"]);
                        $city = $this->sl->tools->getCity($address["address"]);
                        $store->set("city", $city);
                        if($index == 0) {
                            $userdata["organization"]["city"] = $city;
                        }
                        $store->set("address", $address["address"]["value"]);
                        $store->set("contact", $properties['data']['contact']);
                        $store->set("email", $properties['data']['email']);
                        $store->set("phone", $properties['data']['phone']);
                        $store->set("integration", 0);
                        $store->set("marketplace", 0);
                        $store->set("opt_marketplace", 0);
                        $store->set("check_remains", 0);
                        $store->set("check_docs", 0);
                        $store->set("active", 0);
                        $store->set("coordinats", $address["address"]["data"]["geo_lat"].','.$address["address"]["data"]["geo_lon"]);
                        $store->set("lat", $address["address"]["data"]["geo_lat"]);
                        $store->set("lng", $address["address"]["data"]["geo_lon"]);
                        if($store->save()){
                            $userdata["organization"]['stores'][] = $store->toArray();
                            if($address["id"]){

                            }else{
                                // связь склада и организации
                                $store_id = $store->get("id");
                                $link = $this->modx->newObject("slOrgStores");
                                $link->set("org_id", $org_id);
                                $link->set("store_id", $store_id);
                                $link->save();

                                // связь юзера и склада
                                $uslink = $this->modx->newObject("slStoreUsers");
                                $uslink->set("store_id", $store_id);
                                $uslink->set("user_id", $user_id);
                                $uslink->save();
                            }
                        }
                    }
                }
                if(!$properties["client_id"]) {
                    // связь родительской организации и виртуальной
                    $uslink = $this->modx->newObject("slWarehouseStores");
                    $uslink->set("org_id", $org_id); 
                    $uslink->set("warehouse_id", $properties["id"]);
                    $uslink->save();
                    // связь юзера и организации
                    $ulink = $this->modx->newObject("slOrgUsers");
                    $ulink->set("org_id", $org_id);
                    $ulink->set("user_id", $user_id);
                    $ulink->save();
                }
                return $this->sl->tools->success("Организация успешно создана", $userdata);
            }else{
                return $this->sl->tools->error("Ошибка при создании/редактировании организации");
            }
        }else{
            return $this->sl->tools->error("У вас нет прав на создание/редактирование организаций");
        }
    }

    /**
     *
     * Настройки огранизации
     * @return
     */
    public function setOrgProfile($properties){
        if($properties['id'] && $properties['data']){
            $org = $this->modx->getObject('slOrg', $properties['id']);

//            if($properties['data']['contact']){
//                $org->set('contact', $properties['data']['contact']);
//            }
//
//            if($properties['data']['email']){
//                $org->set('email', $properties['data']['email']);
//            }
//
//            if($properties['data']['phone']){
//                $org->set('phone', $properties['data']['phone']);
//            }

            $sql = "DELETE FROM {$this->modx->getTableName('slNotificationManagers')} WHERE `org_id` = {$properties['id']}";
            $q_del = $this->modx->query($sql);

            if($properties['data']['managers']){
//                $crit = array(
//                    "org_id" => $properties['id']
//                );
//                $this->modx->removeCollection("slNotificationManagers", $crit);

                foreach($properties['data']['managers'] as $manager){
                    $manager_new = $this->modx->newObject("slNotificationManagers");
                    $manager_new->set("org_id", $properties['id']);
                    $manager_new->set("receiver", $manager['name']);
                    $manager_new->set("phone", $manager['phone']);
                    $manager_new->set("email", $manager['email']);
                    $manager_new->set("global", $manager['unlimitied_clients']);

                    if($manager['unlimitied_clients'] == false){
                        $region = [];
                        $city = [];
                        $orgs = [];

                        foreach ($manager['clients'] as $item) {
                            if (strpos($item['id'], 'r_') === 0) {
                                $region[] = substr($item['id'], 2); // Удаляем 'r_' и добавляем только число
                            } elseif (strpos($item['id'], 'c_') === 0) {
                                $city[] = substr($item['id'], 2); // Удаляем 'c_' и добавляем только число
                            } elseif (strpos($item['id'], 'o_') === 0) {
                                $orgs[] = substr($item['id'], 2); // Удаляем 'o_' и добавляем только число
                            }
                        }

                        $regionString = implode(",", $region);
                        $cityString = implode(",", $city);
                        $orgString = implode(",", $orgs);

//                        echo "$regionString\n";
//                        echo "$cityString\n";
//                        echo "$orgString\n";

                        $manager_new->set("city", $cityString);
                        $manager_new->set("region", $regionString);
                        $manager_new->set("org", $orgString);
                    }

                    $types = []; // Перемещаем сюда, чтобы массив был одним для всех уведомлений

                    foreach ($manager['notifications'] as $key => $notif) {
                        if ($notif) { // Проверяем только на true
                            if ($key == "added_to_my_vendors") {
                                $types[] = 7;
                            } else if ($key == "company_connected") {
                                $types[] = 4;
                            } else if ($key == "company_enabled") {
                                $types[] = 3;
                            } else if ($key == "deleted_from_my_vendors") {
                                $types[] = 8;
                            } else if ($key == "new_opt_order") {
                                $types[] = 2;
                            } else if ($key == "new_vendor") {
                                $types[] = 5;
                            } else if ($key == "order_status_changes") {
                                $types[] = 1;
                            }
                        }
                    }

                    $typesString = implode(",", $types);

                    $manager_new->set("type", $typesString);

                    $manager_new->save();
                }
            }

            if($properties['data']['upload_image']){
                if ($properties['data']['image']) {
                    $avatar = $this->modx->getOption('base_path') . "assets/content/avatars/" . $properties['data']['image']['name'];

                    if (rename($this->modx->getOption('base_path') . $properties['data']['image']['original'], $avatar)) {
                        $org->set("image", "avatars/" . $properties['data']['image']['name']);
                    }
                }
            }

            $org->save();
            return array(
                "status" => true,
                "message" => "Данные успешно сохранены!",
                "test" => $types
            );
        }
    }

    /**
     *
     * Отправка запроса на изменение/добавление реквизитов или банковских реквизитов
     * @return
     */
    public function requestChangeRequisite ($properties) {
        if($properties['id'] && $properties['data']){
            $pdo = $this->modx->getService('pdoFetch');
            $chunk = "@FILE chunks/send_email_request_org.tpl";
            //$this->modx->log(1, "{$chunk}");
            if($pdo) {
                $data = $properties['data'];
                $data['date'] = date('d.m.Y H:i');
                $message = $pdo->getChunk($chunk, $data);
                $emailsender = $this->modx->getOption("emailsender");

                $this->modx->getService('mail', 'mail.modPHPMailer');
                $this->modx->mail->set(modMail::MAIL_BODY, $message);
                $this->modx->mail->set(modMail::MAIL_FROM, $emailsender);
                $this->modx->mail->set(modMail::MAIL_FROM_NAME,'MST Аналитика');
                $this->modx->mail->set(modMail::MAIL_SUBJECT,'Новый запрос редактирования/добавляния реквизитов огранизации');
                $this->modx->mail->address('to','info@dart.agency');
                $this->modx->mail->address('to','artpetropavlovskij@gmail.com');
                $this->modx->mail->address('to','info@mst.tools');
                $this->modx->mail->address('reply-to', $emailsender);
                $this->modx->mail->setHTML(true);
                if (!$this->modx->mail->send()) {
                    $this->modx->log(1, 'An error occurred while trying to send the email: '.$this->modx->mail->mailer->ErrorInfo);
                }
                $this->modx->mail->reset();


                if($properties['data']['id']){
                    $org = $this->modx->getObject('slOrgRequisites', $properties['data']['id']);
                    if($org){
                        $org->set("send_request", true);
                        $org->save();
                    }
                }
            }
            return array(
                "status" => true,
                "message" => "Запрос успешно отправлен!"
            );
        }
    }
}