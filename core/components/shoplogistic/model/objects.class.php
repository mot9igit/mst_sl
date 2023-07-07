<?php
class objectsHandler
{
    public $modx;
    public $sl;
    public $config;

    public function __construct(shopLogistic &$sl, modX &$modx)
    {
        $this->config = array(
            "loaddata" => "demo"
        );
        $this->sl =& $sl;
        $this->modx =& $modx;
        $this->modx->lexicon->load('shoplogistic:default');
    }

    public function getObject($class, $id = 0, $criteria = array()){
        $query = $this->modx->newQuery($class);
        if($id){
            $criteria = array(
                "id:=" => $id
            );
        }
        if($criteria){
            $query->where($criteria);
        }
        $query->select(array(
            "`{$class}`.*"
        ));
        $query->limit(1);
        if ($query->prepare() && $query->stmt->execute()) {
            $object = $query->stmt->fetch(PDO::FETCH_ASSOC);
            return $object;
        }
        return false;
    }

    public function getObjects($properties){
        // $this->modx->log(1, print_r($properties, 1));
        if($properties['type'] == 'bonuses'){
            return $this->getBonuses($properties);
        }
        if($properties['type'] == 'available_stores'){
            return $this->getAvailableStores($properties, 1);
        }
        if($properties['type'] == 'bonus'){
            return $this->getBonuses($properties);
        }
        if($properties['type'] == 'akbpunkts'){
            return $this->getAkbPunkts($properties);
        }
        if($properties['type'] == 'akbsettlements'){
            return $this->getAkbSettlements($properties);
        }
        if($properties['type'] == 'akbdotsplan'){
            return $this->getAkbDotsPlan($properties);
        }
        if($properties['type'] == 'akbdata'){
            return $this->getAkbData($properties);
        }
        return array(
            "total" => 0,
            "items" => array()
        );
    }

    public function getAkbDotsPlan($properties) {
        $query = $this->modx->newQuery("slStoresAkbDotsPlan");
        $query->where(array("store_id" => $properties["id"]));
        $query->select(array("slStoresAkbDotsPlan.*"));
        $result['total'] = $this->modx->getCount('slStoresAkbDotsPlan', $query);

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
            $query->sortby('id', 'desc');
        }
        if ($query->prepare() && $query->stmt->execute()) {
            $result['items'] = $query->stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach($result['items'] as $key => $value){
                $name = array();
                $regions = explode(",", $value["region"]);
                $cities = explode(",", $value["city"]);
                foreach($regions as $region){
                    $r = $this->modx->getObject("dartLocationRegion", $region);
                    if($r){
                        $name[] = $r->get("name");
                    }
                }
                foreach($cities as $city){
                    $c = $this->modx->getObject("dartLocationCity", $city);
                    if($c){
                        $name[] = $c->get("city");
                    }
                }
                $dt = new DateTime();
                $dt->setTimestamp(strtotime($value['date']));
                $from = $dt->modify('first day of this month')->setTime(00,00)->format('Y/m/d H:i:s');
                $to = $dt->modify('last day of this month')->setTime(23,59)->format('Y/m/d H:i:s');
                // get plan
                $criteria = array(
                    "slStoresConnection.vendor_id:IN" => array($properties['id']),
                    "AND:slStoresConnection.date:>=" => $from,
                    "AND:slStoresConnection.date:<=" => $to,
                    "AND:slStoresConnection.active:=" => 1,
                    "AND:slStores.active:=" => 1
                );
                if($value["city"]){
                    $criteria["AND:dartLocationCity.id:IN"] = explode(",", $value["city"]);
                }
                if($value["region"]){
                    $criteria["AND:dartLocationRegion.id:IN"] = explode(",", $value["region"]);
                }
                $q = $this->modx->newQuery('slStores');
                $q->leftJoin("slStoresConnection", "slStoresConnection", "slStoresConnection.store_id = slStores.id");
                $q->leftJoin("dartLocationCity", "dartLocationCity", "dartLocationCity.id = slStores.city");
                $q->leftJoin("dartLocationRegion", "dartLocationRegion", "dartLocationRegion.id = dartLocationCity.region");
                $q->where($criteria);
                $q->select(array("COUNT(*) as count"));
                $q->prepare();
                $this->modx->log(1, $q->toSQL());
                if($q->prepare() && $q->stmt->execute()){
                    $res = $q->stmt->fetch(PDO::FETCH_ASSOC);
                    if($res["count"]){
                        $result['items'][$key]['fact_akb'] = $res["count"];
                    }else{
                        $result['items'][$key]['fact_akb'] = 0;
                    }
                }
                $result['items'][$key]['date'] = date('m.Y', strtotime($value['date']));
                $result['items'][$key]["name"] = implode(", ", $name);
            }
            return $result;
        }
    }

    public function getAkbData ($properties) {
        // надо взять общее кол-во точек
        // надо взять общее кол-во населенных пунктов
        $output = array();
        if($properties["id"]){
            $query = $this->modx->newQuery("slStoresConnection");
            $query->where(array("vendor_id" => $properties["id"]));
            $query->select(array("COUNT(*) as dots"));
            if($query->prepare() && $query->stmt->execute()){
                $res = $query->stmt->fetch(PDO::FETCH_ASSOC);
                $output['dots'] = $res['dots'];
            }
            $query = $this->modx->newQuery("slStoresConnection");
            $query->leftJoin("slStores", "slStores", "slStores.id = slStoresConnection.store_id");
            $query->leftJoin("dartLocationCity", "dartLocationCity", "dartLocationCity.id = slStores.city");
            $query->where(array("vendor_id" => $properties["id"]));
            $query->select(array("COUNT(*) as cities"));
            $query->groupby(array("slStores.city"));
            if($query->prepare() && $query->stmt->execute()){
                $res = $query->stmt->fetch(PDO::FETCH_ASSOC);
                $output['cities'] = $res['cities'];
            }
        }
        return $output;
    }

    public function getLastMonth () {
        // get 12 months
        $arr = [
            'январь',
            'февраль',
            'март',
            'апрель',
            'май',
            'июнь',
            'июль',
            'август',
            'сентябрь',
            'октябрь',
            'ноябрь',
            'декабрь'
        ];
        $dateTime = new DateTime();
        $months = array();
        for($i = 1; $i < 13; $i++){
            $dt = new DateTime();
            $dt->setTimestamp($dateTime->getTimestamp());
            $month = $dateTime->format('n')-1;
            $months[$arr[$month].$dateTime->format(', Y')] = array(
                $dt->modify('first day of this month')->setTime(00,00)->format('Y/m/d H:i:s'),
                $dt->modify('last day of this month')->setTime(23,59)->format('Y/m/d H:i:s')
            );
            $dateTime->modify("-1 month");
        }
        $months = array_reverse($months);
        return $months;
    }

    public function getAkbSettlements($properties) {
        $output = array(
            "items" => array(),
            "total" => 0
        );
        $query = $this->modx->newQuery("slStoresConnection");
        $query->leftJoin("slStores", "slStores", "slStores.id = slStoresConnection.store_id");
        $query->leftJoin("dartLocationCity", "dartLocationCity", "dartLocationCity.id = slStores.city");
        $query->where(array("vendor_id" => $properties["id"]));
        $query->groupby("slStores.city");
        $query->select(array("dartLocationCity.*"));
        if($properties['page'] && $properties['perpage']){
            $limit = $properties['perpage'];
            $offset = ($properties['page'] - 1) * $properties['perpage'];
            $query->limit($limit, $offset);
        }
        if($properties['sort']){
            $keys = array_keys($properties['sort']);
            $query->sortby($keys[0], $properties['sort'][$keys[0]]['dir']);
        }else{
            $query->sortby('dartLocationCity.city', 'ASC');
        }
        if($query->prepare() && $query->stmt->execute()){
            $res = $query->stmt->fetchAll(PDO::FETCH_ASSOC);
            $output['items'] = $res;
            // TODO: change fix
            $output['total'] = count($output['items']);
            $months = $this->getLastMonth();
            foreach($output['items'] as $key => $val){

                foreach($months as $k => $v) {
                    $timestamp = strtotime($v[0]);
                    $mon = date("m", $timestamp);
                    // collect connection count
                    $criteria = array(
                        "slStoresConnection.vendor_id:IN" => array($properties['id']),
                        "AND:slStoresConnection.date:>=" => $v[0],
                        "AND:slStoresConnection.date:<=" => $v[1],
                        "AND:slStoresConnection.active:=" => 1,
                        "AND:slStores.active:=" => 1,
                        "AND:slStores.city:=" => $val['id']
                    );
                    $query = $this->modx->newQuery("slStores");
                    $query->leftJoin("slStoresConnection", "slStoresConnection", "slStoresConnection.store_id = slStores.id");
                    $query->where($criteria);
                    $query->select(array("COUNT(*) as count"));
                    $query->prepare();
                    $this->modx->log(1, $query->toSQL());
                    if ($query->prepare() && $query->stmt->execute()) {
                        $stores = $query->stmt->fetch(PDO::FETCH_ASSOC);
                        if($stores['count']){
                            $output['items'][$key]["month_".$mon] = $stores["count"];
                        }else{
                            $output['items'][$key]["month_".$mon] = 0;
                        }
                    }
                }
            }
        }
        return $output;
    }

    public function getAkbPunkts($properties) {
        $output = array(
            "items" => array(),
            "total" => 0
        );
        $months = $this->getLastMonth();
        foreach($months as $key => $val){
            // collect connection count stores
            $criteria = array(
                "slStoresConnection.vendor_id:IN" => array($properties['id']),
                "AND:slStoresConnection.date:>=" => $val[0],
                "AND:slStoresConnection.date:<=" => $val[1],
                "AND:slStoresConnection.active:=" => 1,
                "AND:slStores.active:=" => 1
            );
            $query = $this->modx->newQuery("slStores");
            $query->leftJoin("slStoresConnection", "slStoresConnection", "slStoresConnection.store_id = slStores.id");
            $query->leftJoin("dartLocationCity", "dartLocationCity", "dartLocationCity.id = slStores.city");
            $query->where($criteria);
            $query->select(array("slStores.*, dartLocationCity.city as city"));
            if($query->prepare() && $query->stmt->execute()){
                $stores = $query->stmt->fetchAll();
                $months[$key]["stores"] = array();
                foreach($stores as $store){
                    $months[$key]["stores"][] = $store['name'];
                    $months[$key]["cities"][] = $store['city'];
                }
            }
            $cities = array_unique($months[$key]["cities"]);
            $output['total'] += count($months[$key]["stores"]);
            $output['city_total'] += count($cities);

            $output["items"][] = array(
              "month" => $key,
              "stores" => implode("; ", $months[$key]["stores"]),
              "cities" => implode("; ", $cities)
            );
        }
        // collect all connections
        $criteria = array(
            "slStoresConnection.vendor_id:IN" => array($properties['id']),
            "AND:slStoresConnection.active:=" => 1,
            "AND:slStores.active:=" => 1
        );
        $query = $this->modx->newQuery("slStores");
        $query->leftJoin("slStoresConnection", "slStoresConnection", "slStoresConnection.store_id = slStores.id");
        $query->leftJoin("dartLocationCity", "dartLocationCity", "dartLocationCity.id = slStores.city");
        $query->where($criteria);
        $query->select(array("slStores.*, dartLocationCity.city as city_name, dartLocationCity.properties as city_properties"));
        if($query->prepare() && $query->stmt->execute()){
            $stores = $query->stmt->fetchAll(PDO::FETCH_ASSOC);
            $output["all_stores"] = array();
            $output["all_cities"] = array();
            foreach($stores as $store){
                $tmp = $store;
                $tmp['coords'] = array(
                    $tmp['lat'],
                    $tmp['lng']
                );
                $tmp['city_properties'] = json_decode($store['city_properties'], 1);
                $this->modx->log(1, print_r($tmp, 1));
                $output["all_stores"][] = $tmp;
                $output["all_cities"][$store['city']] = array(
                    "name" => $store['city_name'],
                    "coords" => array(
                        $tmp['city_properties']["geo_lat"],
                        $tmp['city_properties']["geo_lon"]
                    )
                );
            }
        }
        // $output['total'] = count($output["items"]);
        return $output;
    }

    public function loadData($properties){
        $output = array();
        if($this->config['loaddata'] == 'demo'){
            $stores[] = 5;
            $stores[] = 6;
        }
        if($properties['stores']){
            $stores = $properties['store'];
        }
        if($stores){
            // берем магазины
            $q = $this->modx->newQuery("slStores");
            $q->leftJoin("dartLocationCity", "dartLocationCity", "dartLocationCity.id = slStores.city");
            $q->leftJoin("dartLocationRegion", "dartLocationRegion", "dartLocationRegion.id = dartLocationCity.region");
            $q->select(array("slStores.id as id, slStores.name as name, slStores.address as address, slStores.coordinats as coordinats, dartLocationCity.city as city, dartLocationRegion.name as region"));
            $q->where(array(
                "id:IN" => $stores
            ));
            $q->prepare();
            $this->modx->log(1, $q->toSQL());
            if($q->prepare() && $q->stmt->execute()){
                $stores = $q->stmt->fetchAll(PDO::FETCH_ASSOC);
                foreach($stores as $key => $store) {
                    $output['stores'][] = $store;
                }
                // продажи
                foreach ($output['stores'] as $key => $str) {
                    $query = $this->modx->newQuery("slStoreDocsProducts");
                    $query->leftJoin("slStoreDocs", "slStoreDocs", "slStoreDocs.id = slStoreDocsProducts.doc_id");
                    $query->leftJoin("slStoresRemains", "slStoresRemains", "slStoresRemains.id = slStoreDocsProducts.remain_id");
                    $query->leftJoin("msProductData", "msProductData", "msProductData.id = slStoresRemains.product_id");
                    $query->leftJoin("msVendor", "msVendor", "msVendor.id = msProductData.vendor");
                    $query->leftJoin("modResource", "modResource", "modResource.id = slStoresRemains.product_id");
                    $query->select(array("msProductData.vendor_article as article, modResource.pagetitle as name, msVendor.name as vendor_name, slStoreDocs.guid as doc_guid, slStoreDocs.date as date, slStoreDocsProducts.count as count, slStoreDocsProducts.price as price"));
                    $query->where(array(
                        "slStoreDocs.store_id:=" => $str['id'],
                        "AND:slStoresRemains.product_id:!=" => 0,
                        "AND:msProductData.vendor_article:!=" => ""
                    ));
                    if ($query->prepare() && $query->stmt->execute()) {
                        $sales = $query->stmt->fetchAll(PDO::FETCH_ASSOC);
                        foreach ($sales as $sale) {
                            $sale = array_merge(array('store' => $str['name']), $sale);
                            $output['sales'][] = $sale;
                        }
                    }
                }
                // остатки
                foreach ($output['stores'] as $key => $str) {
                    $query = $this->modx->newQuery("slStoresRemains");
                    $query->leftJoin("msProductData", "msProductData", "msProductData.id = slStoresRemains.product_id");
                    $query->leftJoin("msVendor", "msVendor", "msVendor.id = msProductData.vendor");
                    $query->leftJoin("modResource", "modResource", "modResource.id = slStoresRemains.product_id");
                    $query->select(array("msProductData.vendor_article as article, modResource.pagetitle as name, msVendor.name as vendor_name, slStoresRemains.remains, slStoresRemains.reserved, slStoresRemains.available, slStoresRemains.price"));
                    $query->where(array(
                        "slStoresRemains.store_id:=" => $str['id'],
                        "AND:slStoresRemains.product_id:!=" => 0,
                        "AND:msProductData.vendor_article:!=" => ""
                    ));
                    if ($query->prepare() && $query->stmt->execute()) {
                        $remains = $query->stmt->fetchAll(PDO::FETCH_ASSOC);
                        foreach ($remains as $remain) {
                            $sale = array_merge(array('store' => $str['name']), $remain);
                            $output['remains'][] = $sale;
                        }
                    }
                }
                foreach($output['stores'] as $key => $store) {
                    unset($output['stores'][$key]['id']);
                }
            }
        }
        return $output;
    }

    public function fileUpload($properties){
        // $this->modx->log(1, print_r($properties, 1));
        // $this->modx->log(1, print_r($_FILES, 1));
        $output = array();
        if(count($_FILES)){
            // сначала грузим во временное хранилище
            if($properties['store_id']){
                $tmp_path = $this->modx->getOption('base_path').'assets/components/shoplogistic/tmp/'.$properties['store_id'].'/';
                $tmp_url = 'assets/components/shoplogistic/tmp/'.$properties['store_id'].'/';
                if(!file_exists($tmp_path)){
                    mkdir($tmp_path, 0777, true);
                }else{
                    // удаляем все временное
                    $files = glob($tmp_path.'*');
                    foreach($files as $file){
                        if(is_file($file)) {
                            unlink($file);
                        }
                    }
                }
                foreach($_FILES as $key => $file){
                    foreach($file['name'] as $k => $v){
                        $target = $tmp_path . basename($file['name'][$k]);
                        if (move_uploaded_file($file['tmp_name'][$k], $target)) {
                            $small_file = $this->modx->runSnippet("phpThumbOn", array(
                                "input" => $tmp_path . basename($file['name'][$k]),
                                "options" => "w=300&zc=1"
                            ));
                            $output['files'][] = array(
                                "original" => $tmp_url . basename($file['name'][$k]),
                                "thumb" => str_replace("//assets", "/assets", $this->modx->getOption('site_url') . $small_file),
                                "path" => $properties['path']
                            );
                        }
                    }
                }
            }else{
                // уведомление, что не указана организация
            }
        }else{
            // уведомление, что файлов нет
            return false;
        }
        return $output;
    }

    public function setObjects($properties){
        $response = array();
        if($properties['type'] == 'bonus' && $properties['action'] == 'set'){
            $response = $this->setBonus($properties);
        }
        if($properties['type'] == 'organization' && $properties['action'] == 'set'){
            $response = $this->setOrganization($properties);
        }
        if($properties['type'] == 'bonus_connection' && $properties['action'] == 'set') {
            $response = $this->setBonusConnection($properties);
        }
        if($properties['type'] == 'akbdotsplan' && $properties['action'] == 'set') {
            $response = $this->setAkbDotsPlan($properties);
        }
        return $response;
    }

    public function parseRegions($data){
        $output = array(
            "regions" => array(),
            "cities" => array()
        );
        foreach($data as $key => $val){
            if($val['checked'] && !$val["partialChecked"]){
                $k_r = explode("_", $key);
                if($k_r[0] == 'region'){
                    $output['regions'][] = $k_r[1];
                }
                if($k_r[0] == 'city') {
                    $output['cities'][] = $k_r[1];
                }
            }
            // Убираем лишние города
            foreach($output['cities'] as $key => $val){
                $city = $this->modx->getObject("dartLocationCity", $val);
                $region = $city->get("region");
                if(in_array($region, $output['regions'])){
                    unset($output['cities'][$key]);
                }
            }
        }
        return $output;
    }

    public function setAkbDotsPlan ($properties) {
        if($properties['action'] == 'set' && $properties['type'] == 'akbdotsplan'){
            $store_id = $properties['id'];
            $date = new DateTime($properties['data']['month']);
            $one_day = new DateInterval('P1D');
            $date->add($one_day);
            $date->setTime(00,00);
            $geo_data = $this->parseRegions($properties['data']['city']);
            $akbdotsplan = $this->modx->newObject('slStoresAkbDotsPlan');
            $akbdotsplan->set('store_id', $store_id);
            if(count($geo_data['regions'])){
                $akbdotsplan->set('region', implode(",", $geo_data['regions']));
            }
            if(count($geo_data['cities'])){
                $akbdotsplan->set('city', implode(",", $geo_data['cities']));
            }
            $akbdotsplan->set('date', $date->format('Y-m-d H:i:s'));
            $akbdotsplan->set("createdon", time());
            $akbdotsplan->set('count', $properties['data']['dots']);
            $akbdotsplan->set('properties', json_encode($properties['data'], JSON_UNESCAPED_UNICODE));
            if($akbdotsplan->save()){
                return $akbdotsplan->toArray();
            }else{
                // TODO: ADD TO TELEGRAM ALERT
                return false;
            }
        }
        return false;
    }

    public function setBonusConnection($properties){
        $output = array();
        $criteria = array(
            "bonus_id" => $properties['bonus_id'],
            "store_id" => $properties['id'],
        );
        $connection = $this->modx->getObject("slBonusesConnection", $criteria);
        if($connection){
            // уже есть объект
        }else{
            $connection = $this->modx->newObject("slBonusesConnection");
            $connection->set("bonus_id", $properties['bonus_id']);
            $connection->set("store_id", $properties['id']);
            $connection->set("date", time());
            $connection->set("active", 1);
            $connection->save();
            $output = $connection->toArray();
        }
        // first connection
        $bonus = $this->modx->getObject("slBonuses", $properties['bonus_id']);
        if($bonus){
            $vendor = $bonus->get("store_id");
            $criteria = array(
                "vendor_id" => $vendor,
                "store_id" => $properties['id'],
            );
            $connection = $this->modx->getObject("slStoresConnection", $criteria);
            if($connection){
                // уже есть объект
            }else{
                $connection = $this->modx->newObject("slStoresConnection");
                $connection->set("vendor_id", $vendor);
                $connection->set("store_id", $properties['id']);
                $connection->set("date", time());
                $connection->set("active", 1);
                $connection->save();
            }
        }
        return $output;
    }

    public function setOrganization($properties){
        if($properties['action'] == 'set'){
            $store_id = $properties['id'];
            $store = $this->modx->getObject('slStores', $store_id);
            $store->set("contact", $properties['contact']);
            $store->set("phone", $properties['phone']);
            $store->set("email", $properties['email']);
            $store->set("updatedon", time());
            if($properties['files']){
                if($file = $store->get("image")){
                    $full_path = $this->modx->getOption("base_path").$file;
                    if(is_file($full_path)) {
                        unlink($full_path);
                    }
                }
                $source = $this->modx->getOption("base_path").$properties['files'][0]["original"];
                // грузим новый
                if($properties['files'][0]['path']){
                    $target_path = $this->modx->getOption("base_path")."assets/files/organizations/{$store_id}/{$properties['files'][0]['path']}/";
                    $target_file = $target_path.basename($source);
                    $url = "assets/files/organizations/{$store_id}/{$properties['files'][0]['path']}/".basename($source);
                }else{
                    $target_path = $this->modx->getOption("base_path")."assets/files/organizations/{$store_id}/";
                    $target_file = $target_path.basename($source);
                    $url = "assets/files/organizations/{$store_id}/".basename($source);
                }
                if(!file_exists($target_path)){
                    mkdir($target_path, 0777, true);
                }
                if (copy($source, $target_file)) {
                    if(is_file($source)) {
                        unlink($source);
                    }
                    $store->set("image", $url);
                }
            }
            $store->save();
            return $store->toArray();
        }
    }

    public function setBonus($properties){
        if($properties['action'] == 'set'){
            $store_id = $properties['id'];
            $start = new DateTime($properties['dates'][0]);
            $start->setTime(00,00);
            $end = new DateTime($properties['dates'][1]);
            $end->setTime(23,59);

            if($properties['bonus_id']){
                $bonus = $this->modx->getObject('slBonuses', $properties['bonus_id']);
                $bonus->set("updatedon", time());
            }else{
                $bonus = $this->modx->newObject('slBonuses');
                $bonus->set("createdon", time());
            }
            $bonus->set("store_id", $store_id);
            $bonus->set("name", $properties['name']);
            $bonus->set("stores", $properties['stores']);
            $bonus->set("warehouses", $properties['warehouses']);
            $bonus->set("brand_id", $properties['brand']);
            $bonus->set("date_from", $start->format('Y-m-d H:i:s'));
            $bonus->set("date_to", $end->format('Y-m-d H:i:s'));
            if($properties['files']){
                if($file = $bonus->get("banner")){
                    $full_path = $this->modx->getOption("base_path").$file;
                    if(is_file($full_path)) {
                        unlink($full_path);
                    }
                }
                $source = $this->modx->getOption("base_path").$properties['files'][0]["original"];
                // грузим новый
                if($properties['files'][0]['path']){
                    $target_path = $this->modx->getOption("base_path")."assets/files/organizations/{$store_id}/{$properties['files'][0]['path']}/";
                    $target_file = $target_path.basename($source);
                    $url = "assets/files/organizations/{$store_id}/{$properties['files'][0]['path']}/".basename($source);
                }else{
                    $target_path = $this->modx->getOption("base_path")."assets/files/organizations/{$store_id}/";
                    $target_file = $target_path.basename($source);
                    $url = "assets/files/organizations/{$store_id}/".basename($source);
                }
                if(!file_exists($target_path)){
                    mkdir($target_path, 0777, true);
                }
                if (copy($source, $target_file)) {
                    if(is_file($source)) {
                        unlink($source);
                    }
                    $bonus->set("banner", $url);
                }
            }
            if($properties['store_ids']){
                $ids = array();
                foreach($properties['store_ids'] as $store){
                    $ids[] = $store['id'];
                }
                $bonus->set("store_ids", implode(',', $ids));
            }
            if($properties['reward']){
                $bonus->set("reward", $properties['reward']);
            }
            if($properties['conditions']){
                $bonus->set("conditions", $properties['conditions']);
            }
            $bonus->set("active", 1);
            $bonus->save();
            return $bonus->toArray();
        }
        return false;
    }

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
                    $this->modx->log(1, print_r($data, 1));
                    $data['date_from'] = date('Y/m/d H:i:s', strtotime($data['date_from']));
                    $data['date_to'] = date('Y/m/d H:i:s', strtotime($data['date_to']));
                    $data['date_from_e'] = date('d.m.Y', strtotime($data['date_from']));
                    $data['date_to_e'] = date('d.m.Y', strtotime($data['date_to']));
                    $data['dates'] = array($data['date_from'],$data['date_to']);
                    $properties["sel_arr"] = explode(",", $data['store_ids']);
                    $data['fstores'] = boolval($data['stores']);
                    $data['fwarehouses'] = boolval($data['warehouses']);
                    unset($data['stores']);
                    unset($data['warehouses']);
                    if($data['banner']){
                        $url = $data['banner'];
                        $image = $this->modx->getOption("base_path") . $url;
                        $small_file = $this->modx->runSnippet("phpThumbOn", array(
                            "input" => $image,
                            "options" => "w=300&zc=1"
                        ));
                        $big_file = $this->modx->runSnippet("phpThumbOn", array(
                            "input" => $image,
                            "options" => "w=1920&h=860&zc=1"
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
                $q = $this->modx->newQuery("slBonuses");
                $q->leftJoin("msVendor", "msVendor", "slBonuses.brand_id = msVendor.id");
                $criteria = array();
                if($st['type'] == 3){
                    // если мы производитель, то только свои можем просматривать
                    $criteria = array(
                        "store_id:=" => $st['id']
                    );
                }
                // TODO: сделать выборку по флагам для магазинов и складов
                $q->where($criteria);
                $q->select(array("slBonuses.*", "msVendor.name as brand", "msVendor.logo as brand_logo"));
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
                if($q->prepare() && $q->stmt->execute()){
                    $results['items'] = $q->stmt->fetchAll(PDO::FETCH_ASSOC);
                    foreach($results['items'] as $key => $val) {
                        $date_from = strtotime($val['date_from']);
                        $results['items'][$key]['date_from'] = date("d.m.Y H:i", $date_from);
                        $date_to = strtotime($val['date_to']);
                        $results['items'][$key]['date_to'] = date("d.m.Y H:i", $date_to);
                        $results['items'][$key]['date_from_e'] = date('d.m.Y', $date_from);
                        $results['items'][$key]['date_to_e'] = date('d.m.Y', $date_to);
                        if ($results['items'][$key]['banner']){
                            $image = $this->modx->getOption("base_path") . $results['items'][$key]['banner'];
                            //$this->modx->log(1, $image);
                            $small_file = $this->modx->runSnippet("phpThumbOn", array(
                                "input" => $image,
                                "options" => "w=100&zc=1"
                            ));
                            $results['items'][$key]['banner'] = $small_file;
                        }
                        $connection = $this->modx->getObject("slBonusesConnection", array(
                            "bonus_id" => $results['items'][$key]['id'],
                            "store_id" => $properties['id']
                        ));
                        if($connection){
                            if($connection->get("active")){
                                $results['items'][$key]['connection'] = 1;
                            }else{
                                $results['items'][$key]['connection'] = 0;
                            }
                        }else{
                            $results['items'][$key]['connection'] = 0;
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
            "slStores.type:IN" => array(1,2)
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
        $q->prepare();
        $this->modx->log(1, $q->toSQL());
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

    public function getAvailableProducts($store_id, $properties = array(), $include = 1){
        $results = array();
        $criteria = array(
            "store_id" => $store_id
        );
        $vs = array();
        $vendors = $this->modx->getCollection("slStoresBrands", $criteria);
        foreach($vendors as $v){
            $vs[] = $v->get("brand_id");
        }
        $q = $this->modx->newQuery("modResource");
        $q->leftJoin('msProductData', 'msProduct', 'msProduct.id = modResource.id');
        $q->where(array(
            "modResource.class_key:=" => "msProduct",
            "msProduct.vendor:IN" => $vs
        ));
        if($properties['sel_arr']){
            if($include){
                $q->where(array(
                    "modResource.id:IN" => $properties['sel_arr']
                ));
            }else{
                $q->where(array(
                    "modResource.id:NOT IN" => $properties['sel_arr']
                ));
            }
        }
        $q->select(array(
            'modResource.id',
            'modResource.pagetitle as name',
            'msProduct.image',
            'msProduct.vendor_article as article'
        ));
        if($properties['filter']){
            $q->where(array(
                "modResource.pagetitle:LIKE" => "%{$properties['filter']}%",
                "OR:msProduct.vendor_article:LIKE" => "%{$properties['filter']}%"
            ));
        }
        // Подсчитываем общее число записей
        // $result['total'] = $this->modx->getCount('slStoresRemains', $q);
        $q->prepare();
        $this->modx->log(1, $q->toSQL());
        if($q->prepare() && $q->stmt->execute()){
            $out = $q->stmt->fetchAll(PDO::FETCH_ASSOC);
            if($properties['sel_arr']){
                $results = $out;
            }else{
                $results['products'][] = $out;
                if($properties['selected']){
                    $results['products'][] = $properties['selected'];
                }else{
                    $results['products'][] = array();
                }
            }
            return $results;
        }
        return array();
    }

    public function setMatrix($properties){
        if($properties['action'] == 'set'){
            $store_id = $properties['id'];
            $start = new DateTime($properties['dates'][0]);
            $start->setTime(00,00);
            $end = new DateTime($properties['dates'][1]);
            $end->setTime(23,59);

            if($properties['matrix_id']){
                $matrix = $this->modx->getObject('slStoresMatrix', $properties['matrix_id']);
            }else{
                $matrix = $this->modx->newObject('slStoresMatrix');
            }
            if($matrix){
                $matrix->set("store_id", $store_id);
                $matrix->set("name", $properties['name']);
                $matrix->set("percent", $properties['percent']);
                $matrix->set("date_from", $start->format('Y-m-d H:i:s'));
                $matrix->set("date_to", $end->format('Y-m-d H:i:s'));
                $matrix->set("createdon", time());
                $matrix->set("active", 1);
                $matrix->save();
                if($matrix->get('id')){
                    if($properties['matrix_id']){
                        $crit = array(
                            "matrix_id" => $properties['matrix_id']
                        );
                        $this->modx->removeCollection("slStoresMatrixProducts", $crit);
                    }
                    foreach($properties['products'] as $product){
                        $matrix_p = $this->modx->newObject("slStoresMatrixProducts");
                        $matrix_p->set("matrix_id", $matrix->get('id'));
                        $matrix_p->set("product_id", $product['id']);
                        if($product['count']){
                            $matrix_p->set("count", $product['count']);
                        }else{
                            $matrix_p->set("count", 1);
                        }
                        if($product['days']){
                            $matrix_p->set("days", $product['days']);
                        }else{
                            $matrix_p->set("days", 1);
                        }
                        $matrix_p->save();
                    }
                    return $matrix->toArray();
                }
            }
        }
        return false;
    }

    public function getMatrix($properties){
        // $this->modx->log(1, print_r($properties, 1));
        if($properties['matrix_id']){
            $matrix = $this->modx->getObject("slStoresMatrix", $properties['matrix_id']);
            if($matrix){
                $data = $matrix->toArray();
                $data['date_from'] = date('Y/m/d H:i:s', strtotime($data['date_from']));
                $data['date_to'] = date('Y/m/d H:i:s', strtotime($data['date_to']));
                $products = $this->modx->getCollection("slStoresMatrixProducts", array("matrix_id" => $data['id']));
                $properties["sel_arr"] = array();
                foreach($products as $product){
                    $properties["sel_arr"][] = $product->get("product_id");
                }
                $data['products'][] = $this->getAvailableProducts($data['store_id'], $properties, 0);
                $data['products'][] = $this->getAvailableProducts($data['store_id'], $properties, 1);
                return $data;
            }
        }else{
            $q = $this->modx->newQuery("slStoresMatrix");
            $q->select(array(
                'slStoresMatrix.*'
            ));
            if($properties['filtersdata']){
                if(isset($properties['filtersdata']['range'])){
                    if($properties['filtersdata']['range'][0] && $properties['filtersdata']['range'][1]){
                        $from = date('Y-m-d H:i:s', strtotime($properties['filtersdata']['range'][0]));
                        $to = date('Y-m-d H:i:s', strtotime($properties['filtersdata']['range'][1]));
                        $q->where(array("`slStoresMatrix`.`date_from`:<=" => $from, "`slStoresMatrix`.`date_to`:>=" => $to));
                    }
                    if($properties['filtersdata']['range'][0] && !$properties['filtersdata']['range'][1]){
                        $from = date('Y-m-d H:i:s', strtotime($properties['filtersdata']['range'][0]));
                        $q->where(array("`slStoresMatrix`.`date_from`:<=" => $from));
                    }
                }
                if($properties['filter']){
                    $words = explode(" ", $properties['filter']);
                    foreach($words as $word){
                        $criteria = array();
                        $criteria['slStoresMatrix.name:LIKE'] = '%'.trim($word).'%';
                        $q->where($criteria);
                    }
                }
            }
            $result = array();
            // Подсчитываем общее число записей
            $result['total'] = $this->modx->getCount("slStoresMatrix", $q);

            // Устанавливаем лимит 1/10 от общего количества записей
            // со сдвигом 1/20 (offset)
            if($properties['page'] && $properties['perpage']){
                $limit = $properties['perpage'];
                $offset = ($properties['page'] - 1) * $properties['perpage'];
                $q->limit($limit, $offset);
            }

            // И сортируем по ID в обратном порядке
            if($properties['sort']){
                // $this->modx->log(1, print_r($properties, 1));
                $keys = array_keys($properties['sort']);
                // нужно проверить какому объекту принадлежит поле
                $q->sortby($keys[0], $properties['sort'][$keys[0]]['dir']);
            }else{
                $q->sortby('id', "DESC");
            }
            $q->prepare();
            $this->modx->log(1, $q->toSQL());
            if ($q->prepare() && $q->stmt->execute()) {
                $output = array();
                $result['items'] = $q->stmt->fetchAll(PDO::FETCH_ASSOC);
                foreach($result['items'] as $key => $val){
                    $date_from = strtotime($val['date_from']);
                    $result['items'][$key]['date_from'] = date("d.m.Y H:i", $date_from);
                    $date_to = strtotime($val['date_to']);
                    $result['items'][$key]['date_to'] = date("d.m.Y H:i", $date_to);
                }
                $this->modx->log(1, print_r($output, 1));
                return $result;
            }
        }
    }

    public function deleteObject($properties){
        $this->modx->log(1, print_r($properties, 1));
        // проверка доступа должна быть в процессоре
        if($properties['type'] == 'bonus'){
            if(isset($properties['bonus_id'])){
                $bonus = $this->modx->getObject("slBonuses", $properties['bonus_id']['id']);
                if($bonus){
                    if ($bonus->remove() !== false) {
                        return true;
                    }else{
                        $this->modx->log(1, "Проверьте удаление Ретро-бонуса ". $properties['bonus_id']['id']);
                        return false;
                    }
                }
            }
        }
        if($properties['type'] == 'akbdotsplan'){
            if(isset($properties['plan_id'])){
                $plan = $this->modx->getObject("slStoresAkbDotsPlan", $properties['plan_id']['id']);
                if($plan){
                    if ($plan->remove() !== false) {
                        return true;
                    }else{
                        $this->modx->log(1, "Проверьте удаление плана ". $properties['plan_id']['id']);
                        return false;
                    }
                }
            }
        }
        if($properties['type'] == 'report'){
            if(isset($properties['report_id'])){
                $report = $this->modx->getObject("slReports", $properties['report_id']['id']);
                if($report){
                    if($report->get("type") == 1){
                        if($this->removeTopsReport($report->get("id"))){
                            if ($report->remove() !== false) {
                                return true;
                            }else{
                                $this->modx->log(1, "Проверьте удаление отчета ". $properties['report_id']['id']);
                                return false;
                            }
                        }else{
                            return false;
                        }
                    }
                    if($report->get("type") == 2){
                        if($this->removePresentReport($report->get("id"))){
                            if ($report->remove() !== false) {
                                return true;
                            }else{
                                $this->modx->log(1, "Проверьте удаление отчета ". $properties['report_id']['id']);
                                return false;
                            }
                        }else{
                            return false;
                        }
                    }
                    if($report->get("type") == 3){
                        if($this->removeRRCReport($report->get("id"))){
                            if ($report->remove() !== false) {
                                return true;
                            }else{
                                $this->modx->log(1, "Проверьте удаление отчета ". $properties['report_id']['id']);
                                return false;
                            }
                        }else{
                            return false;
                        }
                    }
                    if($report->get("type") == 4){
                        if($this->removeWeekSalesReport($report->get("id"))){
                            if ($report->remove() !== false) {
                                return true;
                            }else{
                                $this->modx->log(1, "Проверьте удаление отчета ". $properties['report_id']['id']);
                                return false;
                            }
                        }else{
                            return false;
                        }
                    }
                }
            }
        }
    }

    public function removeTopsReport($id){
        $result = false;
        // удаляем все магазины
        $criteria = array(
            "report_id" => $id
        );
        $stores = $this->modx->getCollection("slReportsTopSales", $criteria);
        if(count($stores)){
            foreach($stores as $store){
                if ($store->remove() !== false) {
                    $result = true;
                }
            }
        }else{
            $result = true;
        }
        if(!$result){
            $this->modx->log(1, "Проверьте удаление отчета Топов продаж ". $id);
        }
        return $result;
    }

    public function removePresentReport($id){
        $result = false;
        // удаляем все магазины
        $criteria = array(
            "report_id" => $id
        );
        $stores = $this->modx->getCollection("slReportsPresent", $criteria);
        if(count($stores)){
            foreach($stores as $store){
                if ($store->remove() !== false) {
                    $result = true;
                }
            }
        }else{
            $result = true;
        }
        if(!$result){
            $this->modx->log(1, "Проверьте удаление отчета Первичной представленности ". $id);
        }
        return $result;
    }

    public function removeRRCReport($id){
        $result = false;
        // удаляем все магазины
        $criteria = array(
            "report_id" => $id
        );
        $stores = $this->modx->getCollection("slReportsRRCStores", $criteria);
        if(count($stores)){
            foreach($stores as $store){
                if ($store->remove() !== false) {
                    $result = true;
                }
            }
        }else{
            $result = true;
        }
        if(!$result){
            $this->modx->log(1, "Проверьте удаление отчета РРЦ ". $id);
        }
        return $result;
    }

    public function removeWeekSalesReport($id){
        $result = false;
        // удаляем все магазины
        $criteria = array(
            "report_id" => $id
        );
        $stores = $this->modx->getCollection("slReportsWeeks", $criteria);
        if(count($stores)){
            foreach($stores as $store){
                if ($store->remove() !== false) {
                    $result = true;
                }
            }
        }else{
            $result = true;
        }
        if(!$result){
            $this->modx->log(1, "Проверьте удаление отчета Недельных продаж ". $id);
        }
        return $result;
    }
}