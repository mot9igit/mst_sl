<?php

/**
 *  Обработчик действий с организацией
 *
 */

class storeHandler
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
     * Берем активные магазины
     *
     * @return array
     */
    public function getActiveStores(){
        $output = array();
        $query = $this->modx->newQuery("slStores");
        $query->where(array(
            "slStores.active:=" => 1
        ));
        $query->select(array("slStores.id"));
        if($query->prepare() && $query->stmt->execute()){
            $stores = $query->stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach($stores as $store){
                $output[] = $store["id"];
            }
        }
        return $output;
    }

    public function isStore($id){
        $store = $this->modx->getObject("slStores", $id);
        if($store){
            if($store->get("store")){
                return true;
            }else{
                return false;
            }
        }else{
            return false;
        }
    }

    public function isWarehouse($id){
        $store = $this->modx->getObject("slStores", $id);
        if($store){
            if($store->get("warehouse")){
                return true;
            }else{
                return false;
            }
        }else{
            return false;
        }
    }

    public function isVendor($id){
        $store = $this->modx->getObject("slStores", $id);
        if($store){
            if($store->get("vendor")){
                return true;
            }else{
                return false;
            }
        }else{
            return false;
        }
    }

    /**
     * Берем подключенные к магазину склады
     *
     * @param $id
     * @return array
     */
    public function getWarehouses($id){
        $query = $this->modx->newQuery("slWarehouseStores");
        $query->leftJoin("slStores", "slStores", "slStores.id = slWarehouseStores.warehouse_id");
        $query->select(array("slStores.*"));
        $query->where(array(
            "slWarehouseStores.store_id:=" => $id,
            "AND:slStores.active:=" => 1
        ));
        if($query->prepare() && $query->stmt->execute()) {
            $warehouses = $query->stmt->fetchAll(PDO::FETCH_ASSOC);
            return $warehouses;
        }
    }

    public function getStore($store_id){
        $query = $this->modx->newQuery("slStores");
        $query->where(array("id:=" => $store_id, "AND:active:=" => 1));
        $query->select(array("slStores.*"));
        if($query->prepare() && $query->stmt->execute()){
            $store = $query->stmt->fetch(PDO::FETCH_ASSOC);
            return $store;
        }
        return false;
    }

    public function setWork($properties){
        foreach($properties["dates"] as $key => $val){
            $criteria = array(
                "store_id" => $properties["id"],
                "week_day" => $key
            );
            $week = $this->modx->getObject("slStoresWeekWork", $criteria);
            if(!$week){
                $week = $this->modx->newObject("slStoresWeekWork");
                $week->set("store_id", $properties["id"]);
                $week->set("week_day", $key);
            }
            if($val["active"]){
                $week->set("weekend", 0);
                $week->set("date_from", $val["time_start"]);
                $week->set("date_to", $val["time_end"]);
            }else{
                $week->set("weekend", 1);
            }
            $week->set("timezone", $val["timezone"]);
            $week->save();
        }
    }

    public function setWorkDate($properties){
        if($properties["id"]){
            $week = $this->modx->getObject("slStoresWeekWork", $properties["data"]["id"]);
        }else{
            $criteria = array(
                "store_id:=" => $properties["id"],
                "week_day:=" => 0,
                "date:>=" => $properties["data"]["condition_date_from"],
                "date:<=" => $properties["data"]["condition_date_to"]
            );
            $week = $this->modx->getObject("slStoresWeekWork", $criteria);
        }
        if(!$week){
            $week = $this->modx->newObject("slStoresWeekWork");
            $week->set("store_id", $properties["id"]);
        }
        if($properties["data"]["type"] == "shortday"){
            $week->set("weekend", 0);
            $week->set("date_from", $properties["data"]["time_start"]);
            $week->set("date_to", $properties["data"]["time_end"]);
        }else{
            $week->set("weekend", 1);
        }
        $week->set("date", $properties["data"]["date_record"]);
        $week->set("week_day", 0);
        $week->set("timezone", $properties["data"]["timezone"]);
        $week->save();
    }

    /**
     * Берем оптовые заказы
     *
     * @param $data
     * @return void
     */
    public function getOrdersOpt($data){
        if($data["date"]){
            $customers = array();
            $orders = array();
            $output["customers"] = array();
            $date = strtotime($data["date"]);
            $start = new DateTime();
            $start->setTimestamp($date);
            $query = $this->modx->newQuery("slOrderOpt");
            $query->where(array(
                "warehouse_id:=" => $data["warehouse"]["id"],
                "date:>=" => $start->format('Y-m-d H:i:s')
            ));
            $query->select(array("slOrderOpt.*"));
            if($query->prepare() && $query->stmt->execute()){
                $orders = $query->stmt->fetchAll(PDO::FETCH_ASSOC);
                foreach($orders as $key => $order){
                    if(!in_array($order["store_id"], $customers)){
                        $customers[] = $order["store_id"];
                    }
                    $query = $this->modx->newQuery("slOrderOptProduct");
                    $query->leftJoin("slStoresRemains", "slStoresRemains", "slStoresRemains.id = slOrderOptProduct.remain_id");
                    $query->where(array(
                        "order_id:=" => $order["id"]
                    ));
                    $query->select(array("slStoresRemains.guid as guid, slOrderOptProduct.name as name, slOrderOptProduct.price as price, slOrderOptProduct.count as count, slOrderOptProduct.cost as cost"));
                    if($query->prepare() && $query->stmt->execute()){
                        $orders[$key]["products"] = $query->stmt->fetchAll(PDO::FETCH_ASSOC);
                    }
                }
            }
            foreach($customers as $customer){
                $store = $this->getStore($customer);
                unset($store["apikey"]);
                unset($store["balance"]);
                $output["customers"][] = $store;
            }
            foreach($orders as $key => $order){
                foreach($output["customers"] as $customer){
                    if($order["store_id"] == $customer["id"]){
                        $order["customer"] = $customer["inn"];
                    }
                }
                $orders[$key] = $order;
                ksort($orders[$key]);
            }
            $output["documents"] = $orders;
            return $output;
        }
    }

    /**
     * Берем доступны бонусы для организации
     *
     * @param $properties
     * @return array
     */
    public function getBonuses($properties){
        if($properties['bonus_id']){
            $query = $this->modx->newQuery("slBonuses");
            $query->leftJoin("msVendor", "msVendor", "slBonuses.brand_id = msVendor.id");
            $query->where(array("slBonuses.id:=" => $properties['bonus_id']));
            $query->select(array("slBonuses.*", "msVendor.name as brand", "msVendor.logo as brand_logo"));
            if($query->prepare() && $query->stmt->execute()) {
                $bonus = $query->stmt->fetch(PDO::FETCH_ASSOC);
                if($bonus){
                    $data = $bonus;
                    // $this->modx->log(1, print_r($data, 1));
                    $data['date_from'] = date('Y/m/d H:i:s', strtotime($data['date_from']));
                    $data['date_to'] = date('Y/m/d H:i:s', strtotime($data['date_to']));
                    $data['date_from_e'] = date('d.m.Y', strtotime($data['date_from']));
                    $data['date_to_e'] = date('d.m.Y', strtotime($data['date_to']));
                    $data['dates'] = array($data['date_from'],$data['date_to']);
                    $properties["sel_arr"] = explode(",", $data['store_ids']);
                    $data['fstores'] = boolval($data['stores']);
                    $data['fwarehouses'] = boolval($data['warehouses']);
                    $data['auto'] = boolval($data['auto_accept']);
                    $data['trigger_nocondition'] = 0;
                    if($data['conditions_programs']){
                        $query = $this->modx->newQuery("slBonuses");
                        $query->where(array("id:IN" => explode(",", $data['conditions_programs'])));
                        $query->select(array("slBonuses.id", "slBonuses.name"));
                        if($query->prepare() && $query->stmt->execute()){
                            $bonuses = $query->stmt->fetchAll(PDO::FETCH_ASSOC);
                            $data['trigger_programs'] = $bonuses;
                        }
                        if($data['store_id'] != $properties['id']) {
                            if ($data['trigger_programs']) {
                                foreach ($data['trigger_programs'] as $key => $program) {
                                    $connection = $this->modx->getObject("slBonusesConnection", array(
                                        "bonus_id" => $program['id'],
                                        "store_id" => $properties['id']
                                    ));
                                    if ($connection) {
                                        $data['trigger_programs'][$key]["nocondition"] = 0;
                                    } else {
                                        $data['trigger_nocondition'] = 1;
                                        $data['trigger_programs'][$key]["nocondition"] = 1;
                                    }
                                }
                            }
                        }
                    }
                    unset($data['stores']);
                    unset($data['auto_accept']);
                    unset($data['warehouses']);
                    if($data['banner']){
                        if($data['banner']){
                            $url = $data['banner'];
                        }else{
                            $url = "assets/content/img/nopic.png";
                        }
                        $image = $this->modx->getOption("base_path") . $url;
                        $small_file = $this->modx->runSnippet("phpThumbOn", array(
                            "input" => $image,
                            "options" => "w=286&h=160&q=99&zc=1"
                        ));
                        $big_file = $this->modx->runSnippet("phpThumbOn", array(
                            "input" => $image,
                            "options" => "w=742&h=420&q=99&zc=1"
                        ));
                        $data['thumb_big'] = $this->modx->getOption("site_url") . $big_file;
                        $data['files'][] = array(
                            "thumb" => str_replace("//a", "/a", $this->modx->getOption("site_url") . $small_file),
                            "thumb_big" => str_replace("//a", "/a", $this->modx->getOption("site_url") . $big_file),
                            "url" => str_replace("//a", "/a", $this->modx->getOption("site_url") . $url)
                        );
                    }
                    $data['brand_id'] = strval($data['brand_id']);
                    $connection = $this->modx->getObject("slBonusesConnection", array(
                        "bonus_id" => $data['id'],
                        "store_id" => $properties['id']
                    ));
                    if($connection){
                        if($connection->get("active")){
                            $data['connection'] = 1;
                        }else{
                            $data['connection'] = 0;
                        }
                    }else{
                        $data['connection'] = 0;
                    }
                    $data['stores'][] = $this->getAvailableStores($properties, 0);
                    $data['stores'][] = $this->getAvailableStores($properties, 1);
                    return $data;
                }
            }
            return array();
        }else{
            $results = array(
                "total" => 0,
                "items" => array()
            );
            $store = $this->modx->getObject("slStores", $properties['id']);
            if($store){
                $st = $store->toArray();
                $city = $this->modx->getObject('dartLocationCity', $st['city']);
                if($city){
                    $st['region'] = $city->get("region");
                }
                $q = $this->modx->newQuery("slBonuses");
                $q->leftJoin("msVendor", "msVendor", "slBonuses.brand_id = msVendor.id");
                $q->leftJoin("slStores", "slStores", "slBonuses.store_id = slStores.id");
                if($properties['our']){
                    // если только наши
                    $criteria = array(
                        "store_id:=" => $st['id']
                    );
                    $q->where($criteria);
                    if($properties['bonusid']){
                        $q->where(array("id:!=" => $properties['bonusid']));
                    }
                }else{
                    // выбираем только подходящие организации
                    $criteria = array();
                    if($this->isStore($st['id'])){
                        $criteria["stores:="] = 1;
                    }
                    if($this->isWarehouse($st['id'])){
                        $criteria["warehouses:="] = 1;
                    }
                    $criteria[] = "FIND_IN_SET({$st["id"]}, store_ids) > 0";
                    $q->where($criteria, xPDOQuery::SQL_OR);
                    $criteria = array();
                    $criteria[] = "(FIND_IN_SET({$st["city"]}, cities) > 0 OR FIND_IN_SET({$st["region"]}, regions) > 0 OR (cities = '' AND regions = ''))";
                    $q->where($criteria);
                    $today = new DateTime();
                    $date = $today->getTimestamp();
                    $criteria = array(
                        "date_from:<=" => date('Y-m-d H:i:s', $date),
                        "date_to:>=" => date('Y-m-d H:i:s', $date),
                    );
                    $q->where($criteria, xPDOQuery::SQL_AND);
                }
                if($properties['simple']){
                    $q->select(array("slBonuses.id", "slBonuses.name"));
                }else{
                    $q->select(array("slBonuses.*", "slStores.address as store_address", "slStores.name as store_name", "msVendor.name as brand", "msVendor.logo as brand_logo"));
                }
                $results['total'] = $this->modx->getCount('slBonuses', $q);

                if($properties['page'] && $properties['perpage']){
                    $limit = $properties['perpage'];
                    $offset = ($properties['page'] - 1) * $properties['perpage'];
                    $q->limit($limit, $offset);
                }

                if($properties['sort']){
                    // $this->modx->log(1, print_r($properties, 1));
                    $keys = array_keys($properties['sort']);
                    // нужно проверить какому объекту принадлежит поле
                    $q->sortby($keys[0], $properties['sort'][$keys[0]]['dir']);
                }
                $q->prepare();
                if($q->prepare() && $q->stmt->execute()){
                    $results['items'] = $q->stmt->fetchAll(PDO::FETCH_ASSOC);
                    foreach($results['items'] as $key => $val) {
                        if(!$properties['simple']) {
                            $date_from = strtotime($val['date_from']);
                            $results['items'][$key]['date_from'] = date("d.m.Y H:i", $date_from);
                            $date_to = strtotime($val['date_to']);
                            $results['items'][$key]['date_to'] = date("d.m.Y H:i", $date_to);
                            $results['items'][$key]['date_from_e'] = date('d.m.Y', $date_from);
                            $results['items'][$key]['date_to_e'] = date('d.m.Y', $date_to);
                            if ($results['items'][$key]['banner']) {
                                $url = $results['items'][$key]['banner'];
                            } else {
                                $url = "assets/content/img/nopic.png";
                            }
                            $image = $this->modx->getOption("base_path") . $url;
                            // $this->modx->log(1, $image);
                            $small_file = $this->modx->runSnippet("phpThumbOn", array(
                                "input" => $image,
                                "options" => "w=742&h=420=99&zc=1"
                            ));
                            $results['items'][$key]['banner'] = $small_file;
                            $connection = $this->modx->getObject("slBonusesConnection", array(
                                "bonus_id" => $results['items'][$key]['id'],
                                "store_id" => $properties['id']
                            ));
                            if ($connection) {
                                if ($connection->get("active")) {
                                    $results['items'][$key]['connection'] = 1;
                                } else {
                                    $results['items'][$key]['connection'] = 0;
                                }
                            } else {
                                $results['items'][$key]['connection'] = 0;
                            }
                        }
                    }
                }
            }
        }
        return $results;
    }

    public function getAvailableStores($properties, $include = 1){
        $results = array();
        $q = $this->modx->newQuery("slStores");
        $q->where(array(
            "slStores.store:=" => 1,
            "OR:slStores.warehouse:=" => 1,
        ));
        $q->select(array(
            'slStores.id',
            'slStores.name',
            'slStores.address'
        ));
        if($properties['sel_arr']){
            if($include){
                $q->where(array(
                    "slStores.id:IN" => $properties['sel_arr']
                ));
            }else{
                $q->where(array(
                    "slStores.id:NOT IN" => $properties['sel_arr']
                ));
            }
        }
        if($properties['filter']){
            $q->where(array(
                "slStores.name:LIKE" => "%{$properties['filter']}%",
                "OR:slStores.address:LIKE" => "%{$properties['filter']}%"
            ));
        }
        $q->where(array(
            "slStores.active:=" => 1
        ));
        if($q->prepare() && $q->stmt->execute()){
            $out = $q->stmt->fetchAll(PDO::FETCH_ASSOC);
            if($properties['sel_arr']){
                $results = $out;
            }else{
                $results['items'][] = $out;
                if($properties['selected']){
                    $results['items'][] = $properties['selected'];
                }else{
                    $results['items'][] = array();
                }
            }
            return $results;
        }
        return array();
    }

    /**
     * Берем колво заказов магазина
     *
     * @param $store_id
     * @return int
     */
    public function getOrdersCount($store_id){
        if($store_id){
            $query = $this->modx->newQuery("slOrder");
            $query->where(array(
                "slOrder.store_id:=" => $store_id
            ));
            $query->select(array("COUNT(*) as count"));
            if($query->prepare() && $query->stmt->execute()){
                $response = $query->stmt->fetch(PDO::FETCH_ASSOC);
                if($response){
                    return $response["count"];
                }
            }
        }
        return 0;
    }

    /**
     * Берем отгрузки
     *
     * @param $store_id
     * @return int
     */
    public function getShipsCount($store_id){
        if($store_id){
            $query = $this->modx->newQuery("slWarehouseShipment");
            $query->leftJoin("slWarehouseShip", "slWarehouseShip", "slWarehouseShip.id = slWarehouseShipment.ship_id");
            $query->where(array(
                "slWarehouseShip.warehouse_id:=" => $store_id,
                "slWarehouseShipment.date:>=" => time()
            ));
            $query->select(array("COUNT(*) as count"));
            if($query->prepare() && $query->stmt->execute()){
                $response = $query->stmt->fetch(PDO::FETCH_ASSOC);
                if($response){
                    return $response["count"];
                }
            }
        }
        return 0;
    }

    /**
     * Включение магазина через процессор
     *
     * @param $store_id
     * @return bool
     */
    public function enable($store_id){
        $processorProps = array(
            'ids' => json_encode(array($store_id))
        );
        $otherProps = array(
            'processors_path' => $this->modx->getOption('core_path') . 'components/shoplogistic/processors/'
        );
        $response = $this->modx->runProcessor('mgr/store/enable', $processorProps, $otherProps);
        if ($response->isError()) {
            $this->modx->log(modX::LOG_LEVEL_ERROR, 'STORE/Enable Error, Message: '.$response->getMessage());
            return false;
        }else{
            return true;
        }
    }

    /**
     * Отключение магазина через процессор
     *
     * @param $store_id
     * @return bool
     */
    public function disable($store_id){
        $processorProps = array(
            'ids' => json_encode(array($store_id))
        );
        $otherProps = array(
            'processors_path' => $this->modx->getOption('core_path') . 'components/shoplogistic/processors/'
        );
        $response = $this->modx->runProcessor('mgr/store/disable', $processorProps, $otherProps);
        if ($response->isError()) {
            $this->modx->log(modX::LOG_LEVEL_ERROR, 'STORE/Disable Error, Message: '.$response->getMessage());
            return false;
        }else{
            return true;
        }
    }

    public function getWorkWithTimezones($store_id, $date = "now"){
        $work = array();
        $timezone = '';
        $store = $this->sl->getObject($store_id);
        if($store){
            $city = $this->modx->getObject("dartLocationCity", $store["city"]);
            if($city){
                $carr = $city->toArray();
                $timezone = $this->sl->tools->getTimezone($carr['properties']['timezone']);
            }
        }
        $data['store'] = $store;
        $data['timezone'] = $timezone;
        // смотрим не забита ли какая дата
        $query = $this->modx->newQuery("slStoresWeekWork");
        $query->select(array(
            "`slStoresWeekWork`.*"
        ));
        //TODO Нужно выставлять часовой пояс пользователя
        $start = new DateTime($date);
        $start->setTime(00,00);
        if($timezone){
            $start->setTimezone(new DateTimeZone($timezone));
        }
        $timeStart = $start->getTimestamp();
        $end = new DateTime("now");
        if($timezone){
            $end->setTimezone(new DateTimeZone($timezone));
        }
        $start->setTime(23,59);
        $timeEnd = $end->getTimestamp();

        $query->where(array(
            "`slStoresWeekWork`.`store_id`:=" => $store_id,
            "`slStoresWeekWork`.`date`:>=" => date('Y-m-d H:i', $timeStart),
            "`slStoresWeekWork`.`date`:<=" => date('Y-m-d H:i', $timeEnd),
        ));
        if ($query->prepare() && $query->stmt->execute()) {
            $work = $query->stmt->fetch(PDO::FETCH_ASSOC);
            if(!$work){
                $q = $this->modx->newQuery("slStoresWeekWork");
                $q->select(array(
                    "`slStoresWeekWork`.*"
                ));
                $dateinfo = getdate();
                $wday = $dateinfo['wday'];
                $q->where(array(
                    "`slStoresWeekWork`.`store_id`:=" => $store_id,
                    "`slStoresWeekWork`.`week_day`:=" => $wday,
                ));
                if ($q->prepare() && $q->stmt->execute()) {
                    $work = $q->stmt->fetch(PDO::FETCH_ASSOC);
                }
            }
        }
        if ($work) {
            $usertimezone = $this->sl->getLocationData('web')['usertimezone'];

            $date_from = new DateTime($work['date_from']);
            $date_to = new DateTime($work['date_to']);

            $date_from->setTimezone(new DateTimeZone($work['timezone']));
            $date_to->setTimezone(new DateTimeZone($work['timezone']));
            $work["from"] = array(
                "hour" => $date_from->format("H"),
                "minute" => $date_from->format("i"),
            );
            $work["to"] = array(
                "hour" => $date_to->format("H"),
                "minute" => $date_to->format("i"),
            );

            $date = new DateTime();
            $date->setTimezone(new DateTimeZone($data['timezone']));
            $work["today"] = $date;

            $work_date_from = new DateTime();
            $work_date_from->setTimezone(new DateTimeZone($data['timezone']));
            $work_date_from->setTime($work["from"]['hour'],$work["from"]['minute']);
            $work["time_from"] = $work_date_from;

            $work_date_to = new DateTime();
            $work_date_to->setTimezone(new DateTimeZone($data['timezone']));
            $work_date_to->setTime($work["to"]['hour'],$work["from"]['to']);
            $work["time_to"] = $work_date_to;

            $data['work'] = $work;
            return $data;
        }
        return false;
    }

    /**
     * Получаем информацию о работе магазина
     *
     * @param $store_id
     * @return bool
     */
    public function getShoreWorkTime($store_id){
        $query = $this->modx->newQuery("slStoresWeekWork");

        $query->select(array(
            "`slStoresWeekWork`.*"
        ));

        //TODO Нужно выставлять часовой пояс пользователя
        $start = new DateTime("now");
        $start->setTime(00,00);
        $timeStart = $start->getTimestamp();
        $end = new DateTime("now");
        $start->setTime(23,59);
        $timeEnd = $end->getTimestamp();

        $query->where(array(
            "`slStoresWeekWork`.`store_id`:=" => $store_id,
            "`slStoresWeekWork`.`date`:>=" => date('Y-m-d H:i', $timeStart),
            "`slStoresWeekWork`.`date`:<=" => date('Y-m-d H:i', $timeEnd),
        ));
        $query->prepare();
        $this->modx->log(1, $query->toSQL());

        if ($query->prepare() && $query->stmt->execute()) {
            $work = $query->stmt->fetchAll(PDO::FETCH_ASSOC);

            if(!$work){
                $q = $this->modx->newQuery("slStoresWeekWork");
                $q->select(array(
                    "`slStoresWeekWork`.*"
                ));

                $dateinfo = getdate();
                $wday = $dateinfo['wday'];

                $q->where(array(
                    "`slStoresWeekWork`.`store_id`:=" => $store_id,
                    "`slStoresWeekWork`.`week_day`:=" => $wday,
                ));

                if ($q->prepare() && $q->stmt->execute()) {
                    $getWork = $q->stmt->fetchAll(PDO::FETCH_ASSOC);
                }

                if($getWork){
                    $usertimezone = $this->sl->getLocationData('web')['usertimezone'];


                    $date_to = new DateTime($getWork[0]['date_to']);
                    $date_from = new DateTime($getWork[0]['date_from']);

                    date_timezone_set($date_to, timezone_open($usertimezone));
                    date_timezone_set($date_from, timezone_open($usertimezone));

                    $getWork[0]['date_from'] = $date_from;
                    $getWork[0]['date_to'] = $date_to;

                    $getWork[0]['date_from_format'] = $date_from->format('H:i');
                    $getWork[0]['date_to_format'] = $date_to->format('H:i');

                    $data['work'] = $getWork;
                    return $data;
                }
            }else{
                $date_to = new DateTime($work[0]['date_to']);
                $date_from = new DateTime($work[0]['date_from']);

                $usertimezone = $this->sl->getLocationData('web')['usertimezone'];

                date_timezone_set($date_to, timezone_open($usertimezone));
                date_timezone_set($date_from, timezone_open($usertimezone));

                $work[0]['date_from'] = $date_from;
                $work[0]['date_to'] = $date_to;

                $work[0]['date_from_format'] = $date_from->format('H:i');
                $work[0]['date_to_format'] = $date_to->format('H:i');

                $data['work'] = $work;
                return $data;
            }
        }
        return null;
    }
}