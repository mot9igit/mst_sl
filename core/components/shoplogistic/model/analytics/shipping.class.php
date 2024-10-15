<?php
class shippingHandler
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
     * Получаем отгрузки
     *
     * @param $warehouse_id
     * @param $properties
     * @return array
     */
    public function getShipments($properties = array()){
        $result = array();
        $q = $this->modx->newQuery('slWarehouseShipment');
        $q->leftJoin('slWarehouseShip', 'slWarehouseShip', "slWarehouseShipment.ship_id = slWarehouseShip.id");
        $q->leftJoin('slWarehouseShipmentStatus', 'slWarehouseShipmentStatus', "slWarehouseShipmentStatus.id = slWarehouseShipment.status");
        // $q->leftJoin('dartLocationCity', 'dartLocationCity', "dartLocationCity.id = slWarehouseShipment.city_id");
        // $q->leftJoin('dartLocationRegion', 'dartLocationRegion', "dartLocationRegion.id = dartLocationCity.region");
        $q->leftJoin('slStores', 'slStores', "slStores.id = slWarehouseShip.warehouse_id");
        $q->select(array(
            'slWarehouseShip.*',
            'slStores.name_short',
            'slStores.address',
            'slWarehouseShipment.*',
            'slWarehouseShipmentStatus.name as status_name',
            'slWarehouseShipmentStatus.color as status_color',
            // 'dartLocationCity.city as city_name',
            // 'dartLocationRegion.name as region_name',
        ));

        $orgs = $this->sl->orgHandler->getStoresOrg(array("id" => $properties['id']));
        $ids = [];
        foreach($orgs['items'] as $org){
            $ids[] = $org['id'];
        }
        $q->where(array(
            'slWarehouseShip.warehouse_id:IN' => $ids,
            'slWarehouseShip.org_id' => $properties['id']
        ));
        if(isset($properties['filtersdata'])){
            if($properties['filtersdata']['status']){
                $q->where(array(
                    "slWarehouseShipment.status:=" => $properties['filtersdata']['status']
                ));
            }
            if(isset($properties['filtersdata']['dates'])){
                if($properties['filtersdata']['dates'][0] && $properties['filtersdata']['dates'][1]){
                    $from = date('Y-m-d H:i:s', strtotime($properties['filtersdata']['dates'][0]));
                    $timestamp = strtotime($properties['filtersdata']['dates'][1]);
                    $date_to = new DateTime();
                    $date_to->setTimestamp($timestamp);
                    $date_to->setTime(23,59,59);
                    $to = $date_to->format('Y-m-d H:i:s');
                    $q->where(array("`slWarehouseShipment`.`date`:>=" => $from, "`slWarehouseShipment`.`date`:<=" => $to));
                }
                if($properties['filtersdata']['dates'][0] && !$properties['filtersdata']['dates'][1]){
                    $from = date('Y-m-d H:i:s', strtotime($properties['filtersdata']['dates'][0]));
                    $q->where(array("`slWarehouseShipment`.`date`:>=" => $from));
                }
            }
            if($properties['filtersdata']['store']){
                $q->where(array("slWarehouseShipment.store_id:=" => $properties['filtersdata']['store']));
            }
            if(isset($properties['filtersdata']['region'])){
                $cities = array();
                $regions = array();
                foreach($properties['filtersdata']['region'] as $key => $val){
                    if($val['checked']){
                        $k_r = explode("_", $key);
                        if($k_r[0] == 'region'){
                            $regions[] = $k_r[1];
                        }
                        if($k_r[0] == 'city') {
                            $cities[] = $k_r[1];
                        }
                    }
                }
                if(count($regions)){
                    $q->where(array(
                        "`dartLocationCity`.`region`:IN" => $regions
                    ));
                }
                if(count($cities)){
                    $q->where(array(
                        "`dartLocationCity`.`id`:IN" => $cities
                    ));
                }
            }
        }
        // И сортируем по ID в обратном порядке
        if($properties['sort']){
            // $this->modx->log(1, print_r($properties, 1));
            $keys = array_keys($properties['sort']);
            // нужно проверить какому объекту принадлежит поле
            $prefixes = array(
                "id" => "slWarehouseShipment.",
                "name_short" => "slStores.",
                "date" => "slWarehouseShipment.",
                "date_order_end" => "slWarehouseShipment.",
                "city" => "slWarehouseShipment."
            );
            $q->sortby($prefixes[$keys[0]].$keys[0], $properties['sort'][$keys[0]]['dir']);
        }else{
            $q->sortby('slWarehouseShip.id', "DESC");
        }
        $q->prepare();
        $this->modx->log(1, $q->toSQL());
        $result['total'] = $this->modx->getCount('slWarehouseShipment', $q);
        if($q->prepare() && $q->stmt->execute()){
            $result['shipment'] = $q->stmt->fetchAll(PDO::FETCH_ASSOC);
            /* -------------- HIGHLIGHT --------------- */
            $dates = array();
            $result['dates'] = array(
                'highlight' => array(
                    'highlight' => 'teal',
                    'fillMode' => 'solid',
                    'contentStyle' => array(
                        'color' => 'white',
                    ),
                ),
                'dates' => array()
            );
            $start = 0;
            foreach($result['shipment'] as $index => $res){
                $result['dates']['dates'][] = date("Y-m-d", strtotime($res['date']));
                $result['shipment'][$index]['date'] = date("d.m.Y", strtotime($res['date']));
                $result['shipment'][$index]['date_order_end'] = date("d.m.Y", strtotime($res['date_order_end']));
                //if($start == 0){
                //    $yep = $result['shipment'][$index]["id"]-$result['shipment'][$index]["id"] + 1;
                //    $start = $result['shipment'][$index]["id"]-$result['shipment'][$index]["id"];
                //}else{
                //    $yep =  $result['shipment'][$index]["id"]-$result['shipment'][$index]["id"] - $start - 1;
                //    $start = $result['shipment'][$index]["id"]-$result['shipment'][$index]["id"];
                //}

                //$result['shipment'][$index]["id"] = $res["ship_id"].'_'.$yep;
                if($result['shipment'][$index]["address"]){
                    $result['shipment'][$index]["name_short"] = $result['shipment'][$index]["name_short"].', '.$result['shipment'][$index]["address"];
                }
                // TODO: цепануть заказы закупок
                /*
                $query = $this->modx->newQuery("slOrderProduct");
                $query->leftJoin("msProductData", "msProductData", "msProductData.id = slOrderProduct.product_id");
                $query->leftJoin("slOrder", "slOrder", "slOrder.id = slOrderProduct.order_id");
                $query->select(array(
                    "COALESCE(SUM(msProductData.weight_brutto), 0) as weight, COALESCE(SUM(slOrderProduct.count), 0) as count"
                ));
                $query->where(array(
                    "slOrder.ship_id:=" => $res['id']
                ));
                if($query->prepare() && $query->stmt->execute()){
                    $out_data = $query->stmt->fetch(PDO::FETCH_ASSOC);
                    $result['shipment'][$index]['weight'] = $out_data["weight"];
                    $result['shipment'][$index]['count'] = $out_data["count"];
                }
                */
            }
            /* ------------ / HIGHLIGHT --------------- */
            return $result;
        }

        return array(
            "total" => 0,
            "shipment" => array()
        );
    }

    /**
     * Сохраняем элемент отгрузки
     *
     * @param $data
     * @return int
     */
    public function setShippingElement($data){
        // $this->modx->log(1, print_r($data, 1));
        $shipping = $this->modx->newObject("slWarehouseShipment");
        $shipping->set("ship_id", $data["ship_id"]);
        $shipping->set("city", $data["city"]['value']);
        $shipping->set("city_fias", $data["city"]['data']['fias_id']);
        $shipping->set("date", $data["city"]['date']->format('Y-m-d H:i:s'));
        $shipping->set("date_order_end", $data["city"]['date_order_end']->format('Y-m-d H:i:s'));
        if($data["cities"]){
            $shipping->set("properties", json_encode($data["cities"]));
        }
        if ($shipping->save()) {
            return $shipping->get("id");
        }
        return 0;
    }

    /**
     * Удаление отгрузки
     *
     * @param $properties
     * @return bool|void
     */
    public function deleteShipping($properties){
        if(isset($properties['shipping'])){
            $shipping = $this->modx->getObject("slWarehouseShipment", $properties['shipping']["id"]);
            if($shipping){
                $value = $shipping->get("city");
                $ship_id = $shipping->get("ship_id");
                $ship = $this->modx->getObject("slWarehouseShip", $ship_id);
                if($ship){
                    // базовая проверка прав
                    if($properties["id"] == $ship->get("org_id")){
                        if ($shipping->remove() !== false) {
                            // чекаем не осталось ли объектов у основного блока отгрузок
                            $query = $this->modx->newQuery("slWarehouseShipment");
                            $query->leftJoin("slWarehouseShip", "slWarehouseShip", "slWarehouseShip.id = slWarehouseShipment.ship_id");
                            $query->where(array(
                                "slWarehouseShip.org_id" => $properties["id"],
                                "slWarehouseShipment.ship_id" => $ship_id
                            ));
                            $query->select(array("slWarehouseShipment.*"));
                            if($query->prepare() && $query->stmt->execute()){
                                $ships = $query->stmt->fetchAll(PDO::FETCH_ASSOC);
                                if(!count($ships)){
                                    if ($ship->remove() !== false) {
                                        $this->sl->tools->log("slWarehouseShip :: Проверьте удаление отгрузки (Основной) ". $properties['shipping']["id"], "objects/delete");
                                    }
                                }else{
                                    foreach($ships as $ship_elem){
                                        $sh = $this->modx->getObject("slWarehouseShipment", $ship_elem['id']);
                                        $props = $sh->get("properties");
                                        foreach($props as $key => $prop){
                                            if($prop["value"] == $value){
                                                unset($props[$key]);
                                            }
                                        }
                                        $sh->set("properties", json_encode($props));
                                        $sh->save();
                                    }
                                }
                            }
                            return true;
                        }else{
                            $this->sl->tools->log("slWarehouseShipment :: Проверьте удаление отгрузки ". $properties['shipping']["id"], "objects/delete");
                            return false;
                        }
                    }
                }else{
                    // объект не найден
                    return false;
                }
            }else{
                // объект не найден
                return false;
            }
        }
    }

    /**
     * Установка отгрузки
     *
     * @param $properties
     * @return void
     * @throws Exception
     */
    public function setShipping ($properties) {
        $warehouse_id = $properties['data']['store_id'];
        $org_id = $properties['id'];
        $timing = $properties['data']['timeSelected'];
        $cities = array();

        // dates
        $date_start = new DateTime($properties['data']['dateStart']);
        $date_order_end = new DateTime($properties['data']['dateEnd']);
        $diff_for_repeat = $date_order_end->getTimestamp() - $date_start->getTimestamp();

        // Города, по которым идет отгрузка
        foreach ($properties['data']['selectedCities'] as $key => $city){
            $tmp = $city;
            if($properties['data']['citiesDates'][$city["value"]]){
                $tmp['date'] = new DateTime($properties['data']['citiesDates'][$city["value"]]);
                $tmp['date_diff'] = $tmp['date']->getTimestamp() - $date_start->getTimestamp();
            }else{
                $tmp['date'] = $date_start;
                $tmp['date_diff'] = 0;
            }
            $cities[] = $tmp;
        }

        // Range for repeat
        $start = new DateTime($timing['range']['start']);
        $start->setTime(00,00);
        if($timing['repeater'] == '0'){
            $end = new DateTime($timing['range']['start']);
        }else{
            $end = new DateTime($timing['range']['end']);
        }
        $interval = new DateInterval('P1D');
        $end->setTime(23,59);

        $period = new DatePeriod($start, $interval, $end);

        // Создаем объект отгрузки
        $ship_id = 0;
        if($properties['data']["id"]){
            $obj = $this->modx->getObject("slWarehouseShipment", $properties['data']["id"]);
            if($obj){
                $ship_id = $obj->get("ship_id");
                $ship = $this->modx->newObject('slWarehouseShip', $ship_id);
            }else{
                // TODO: уведомление, что не найдено
            }
        }else{
            $ship = $this->modx->newObject('slWarehouseShip');
        }
        if($ship){
            // удаляем старые объекты
            if($ship_id) {
                $result = $this->modx->removeCollection('slWarehouseShipment', array("ship_id:=" => $ship_id));
            }
            $ship->set("warehouse_id", $warehouse_id);
            $ship->set("org_id", $org_id);
            $ship->set("timing", json_encode($timing, JSON_UNESCAPED_UNICODE));
            $ship->set("date_from", $date_start->format('Y-m-d H:i:s'));
            $ship->set("date_order_end", $date_order_end->format('Y-m-d H:i:s'));
            // если повторений нет используем первую дату
            if($timing['repeater'] == '0'){
                $ship->set("date_to", $date_start->format('Y-m-d H:i:s'));
            }else{
                $ship->set("date_to", $end->format('Y-m-d H:i:s'));
            }
            $ship->set("createdon", time());
            $ship->set("active", 1);
            $ship->save();

            // Главный объект отгрузки создан
            if($ship->get('id')) {
                if ($timing['repeater'] === 0) {
                    // если не повторяем, дата одна создаем отдельные отгрузки на каждый город
                    $start->modify('+1 day');
                    foreach ($cities as $city) {
                        $city['date_order_end'] = $date_order_end;
                        $city_date = array(
                            "cities" => $cities,
                            "ship_id" => $ship->get('id'),
                            "city" => $city
                        );
                        $ship_id = $this->setShippingElement($city_date);
                    }
                }
                if ($timing['repeater'] === 'day') {
                    // если повторяем ежедневно, то каждый день наш
                    foreach ($period as $date) {
                        foreach ($cities as $city) {
                            $timestamp = $date->getTimestamp() + $diff_for_repeat;
                            $date_order_off = new DateTime();
                            $date_order_off->setTimestamp($timestamp);

                            $timestamp_to = $date->getTimestamp() + $city['date_diff'];
                            $date_to = new DateTime();
                            $date_to->setTimestamp($timestamp_to);

                            $city['date_order_end'] = $date_to;
                            $city['date'] = $date_order_off;
                            $city_date = array(
                                "cities" => $cities,
                                "ship_id" => $ship->get('id'),
                                "city" => $city
                            );
                            $ship_id = $this->setShippingElement($city_date);
                        }
                    }
                }
                if ($timing['repeater'] === 'week') {
                    // Повтор по неделям
                    $weekInterval = $timing['weeks'];
                    if ($weekInterval) {
                        $fakeWeek = 0;
                        $currentWeek = $start->format('W');
                        foreach ($period as $date) {
                            if ($date->format('W') !== $currentWeek) {
                                $currentWeek = $date->format('W');
                                $fakeWeek++;
                            }
                            if ($fakeWeek % $weekInterval !== 0) {
                                continue;
                            }
                            $dayOfWeek = $date->format('N');
                            if (in_array($dayOfWeek, $timing['days'])) {
                                foreach ($cities as $city) {
                                    $timestamp = $date->getTimestamp() + $diff_for_repeat;
                                    $date_order_off = new DateTime();
                                    $date_order_off->setTimestamp($timestamp);

                                    $timestamp_to = $date->getTimestamp() + $city['date_diff'];
                                    $date_to = new DateTime();
                                    $date_to->setTimestamp($timestamp_to);

                                    $city['date_order_end'] = $date_to;
                                    $city['date'] = $date_order_off;
                                    $city_date = array(
                                        "cities" => $cities,
                                        "ship_id" => $ship->get('id'),
                                        "city" => $city
                                    );
                                    $ship_id = $this->setShippingElement($city_date);
                                }
                            }
                        }
                    }
                }
            }
        }else{
            // TODO: уведомление, что не найдено
        }
    }
}