<?php
class optAnalyticsHandler
{
    public $modx;
    public $sl;
    public $config;

    public function __construct(shopLogistic &$sl, modX &$modx)
    {
        $this->sl =& $sl;
        $this->modx =& $modx;
        $this->modx->lexicon->load('shoplogistic:default');
        // link ms2
        if (is_dir($this->modx->getOption('core_path') . 'components/minishop2/model/minishop2/')) {
            $ctx = 'web';
            $this->ms2 = $this->modx->getService('miniShop2');
            if ($this->ms2 instanceof miniShop2) {
                $this->ms2->initialize($ctx);
                return true;
            }
        }
    }

    /**
     * @param $action
     * @param $properties
     * @return mixed
     */
    public function handlePages($action, $properties = array()){
        switch ($action) {
            case 'get/mainpage':
                $response = $this->getMainPage($properties);
                break;
            case 'get/catalog':
                $response = $this->getCatalog($properties);
                break;
            case 'get/cart':
                $response = $this->getCart($properties);
                break;
            case 'get/vendors':
                $response = $this->getVendors($properties);
                break;
            case 'get/products':
                $response = $this->getProducts($properties);
                break;
            case 'basket/add':
                $response = $this->addBasket($properties);
                break;
            case 'basket/get':
                $response = $this->getBasket($properties);
                break;
            case 'basket/update':
                $response = $this->updateBasket($properties);
                break;
            case 'basket/clear':
                $response = $this->clearBasket($properties);
                break;
            case 'order/opt/submit':
                $response = $this->orderSubmit($properties);
                break;
            case 'get/orders/seller':
                $response = $this->getOrdersSeller($properties);
                break;

        }
        return $response;
    }

    /**
     * Получаем элементы главной страници
     * @return array
     */
    public function getMainPage($properties){
        $object = $this->modx->getObject("modResource", $this->modx->getOption("analytics_start_page"));
        if($object){
            $data = $object->toArray();

            $urlMain = $this->modx->getOption("site_url");

            //Слайдер "Готовимся к сезону"

            $season_slider = json_decode($object->getTVValue("season_slider"));
            foreach ($season_slider as $key => $value) {
                $data["season_slider"][$key]['id'] = $value->MIGX_id;
                $data["season_slider"][$key]['image'] = $urlMain . "assets/content/" . $value->image;
                $data["season_slider"][$key]['resource'] = $value->resource;
            }

            //Слайдер "Новинки"
            $new_slider = json_decode($object->getTVValue("new_slider"));
            foreach ($new_slider as $key => $value) {
                $data["new_slider"][$key]['id'] = $value->MIGX_id;
                $data["new_slider"][$key]['description'] = $value->description;
                $data["new_slider"][$key]['image'] = $urlMain . "assets/content/" . $value->image;
                $data["new_slider"][$key]['resource'] = $value->resource;
            }
        }
        return $data;
    }


    /**
     * Берем каталог и строим меню
     *
     * @param $properties
     * @return array
     */
    public function getCatalog($properties){
        $data = $this->modx->runSnippet('pdoMenu', array(
            "parents" => 4,
            "level" => 2,
            "where" => '{"class_key":"msCategory"}',
            "includeTVs" => "menu_image",
            "processTVs" => 1,
            "return" => "data",
            "context" => "web"
        ));

        $urlMain = $this->modx->getOption("site_url");

        foreach ($data as $key => $value) {
            $data[$key]['menu_image'] = $urlMain . $value['menu_image'];

            foreach ($value['children'] as $k => $v) {
                $data[$key]['children'][$k]['children'] = $urlMain . "assets/content/" . $v['menu_image'];
            }
        }

        return $data;
    }

    public function getCart($properties){

    }

    /**
     * Поставщики
     *
     * @param $properties
     * @return array|void
     */
    public function getVendors($properties){
        $data = array();
        $iids = array();
        $count = 0;
        $urlMain = $this->modx->getOption("site_url");
        // get selected
        $query = $this->modx->newQuery("slWarehouseStores");
        $query->leftJoin("slStores", "slStores", "slStores.id = slWarehouseStores.warehouse_id");
        $query->select(array(
            "`slStores`.*"
        ));
        $query->where(array(
            "`slWarehouseStores`.`store_id`:=" => $properties['id'],
            "`slStores`.`active`:=" => true
        ));
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
        $query = $this->modx->newQuery("slStores");
        $query->select(array(
            "`slStores`.*"
        ));
        $query->where(array(
            "`slStores`.`warehouse`:=" => true,
            "`slStores`.`active`:=" => true
        ));
        $data['available_count'] = $this->modx->getCount('slStores', $query);
        if($properties['filter']){
            $query->where(array(
                "`slStores`.`name`:LIKE" => "%".$properties['filter']."%",
                "OR:`slStores`.`address`:LIKE" => "%".$properties['filter']."%"
            ));
        }
        if($iids){
            $query->where(array(
                "`slStores`.`warehouse`:NOT IN" => $iids
            ));
        }
        if ($query->prepare() && $query->stmt->execute()) {
            $vendors = $query->stmt->fetchAll(PDO::FETCH_ASSOC);
            $count = 0;

            foreach ($vendors as $key => $value) {
                if(!in_array($value['id'], $iids)){
                    $vendors[$key]['image'] = $urlMain . "assets/content/" . $value['image'];
                    if($vendors[$key]['coordinats']){
                        $vendors[$key]['mapcoordinates'] = explode(",", $vendors[$key]['coordinats']);
                        foreach($vendors[$key]['mapcoordinates'] as $k => $coord){
                            $vendors[$key]['mapcoordinates'][$k] = floatval(trim($coord));
                        }
                        $vendors[$key]['mapcoordinates'] = array_reverse($vendors[$key]['mapcoordinates']);
                    }
                    unset($vendors[$key]['apikey']);
                }else{
                    unset($vendors[$key]);
                }
                $count++;
            }
            // $data['available_count'] = $count;
            $data['available'] = $vendors;
            return $data;
        }
    }

    /**
     * Берем товары из категории
     *
     * @param $properties
     * @return array
     */
    public function getProducts($properties) {
        // $this->modx->log(1, print_r($properties, 1));
        // TODO: выбранные поставщики
        $data = array();
        $urlMain = $this->modx->getOption("site_url");
        $warehouses = $this->sl->store->getWarehouses($properties['id']);
        $av = array();
        foreach($warehouses as $wh){
            $av[] = $wh['id'];
        }

        // нашли поставщиков
        if(count($av)){
            $remains = array();
            if($properties['search']){
                foreach($av as $warehouse){
                    $res = $this->sl->search->getOptBigResults($properties['search'], array("store_id" => array($warehouse)), 99000, 0);
                    if($res["matches"]){
                        foreach($res["matches"] as $key => $v){
                            $remains[] = $key;
                        }
                    }
                }
            }
            if(count($remains)){
                $properties['category_id'] = 'all';
            }
            $query = $this->modx->newQuery("slStoresRemains");
            $query->leftJoin("msProductData", "msProductData", "msProductData.id = slStoresRemains.product_id");
            $query->leftJoin("modResource", "modResource", "modResource.id = slStoresRemains.product_id");
            if($properties['category_id'] != "all"){
                $query->where(array(
                    "`msProductData`.`available`:=" => 1,
                ));
            }
            $query->where(array(
                "`slStoresRemains`.`store_id`:IN" => $av,
                "`slStoresRemains`.`remains`:>" => 0,
                "`slStoresRemains`.`price`:>" => 0
            ));
            $this->modx->log(1, print_r($remains, 1));
            if(count($remains)){
                $query->where(array(
                    "`slStoresRemains`.`id`:IN" => $remains,
                ));
            }
            if($properties['category_id'] != "all"){
                $query->where(array(
                    "`modResource`.`parent`:=" => $properties['category_id']
                ));
            }

            $query->select(array(
                "`slStoresRemains`.*",
                "`slStoresRemains`.`id` as remain_id",
                "`msProductData`.*",
                "`modResource`.*",
                "COALESCE(`modResource`.pagetitle, `slStoresRemains`.name) as pagetitle",
                "COALESCE(`msProductData`.vendor_article, `slStoresRemains`.article) as article"
            ));
            if($properties['category_id'] != "all") {
                $query->groupby("slStoresRemains.product_id");
            }else{
                $query->groupby("slStoresRemains.id");
            }
            // Подсчитываем общее число записей
            $data['total'] = $this->modx->getCount('slStoresRemains', $query);

            // Устанавливаем лимит 1/10 от общего количества записей
            // со сдвигом 1/20 (offset)
            if($properties['page'] && $properties['perpage']) {
                $limit = $properties['perpage'];
                $offset = ($properties['page'] - 1) * $properties['perpage'];
                $query->limit($limit, $offset);
            }
            // $query->prepare();
            // $this->modx->log(1, $query->toSQL());
            if ($query->prepare() && $query->stmt->execute()) {
                $data['items'] = $query->stmt->fetchAll(PDO::FETCH_ASSOC);
            }

            foreach ($data['items'] as $key => $value) {
                if($value['image']){
                    $data['items'][$key]['image'] = $urlMain . $value['image'];
                }else{
                    $data['items'][$key]['image'] = $urlMain . $this->modx->getPlaceholder("+conf_noimage");
                }

                $q = $this->modx->newQuery("slStoresRemains");
                $q->leftJoin("slStores", "slStores", "slStores.id = slStoresRemains.store_id");

//                $q->leftJoin("slActionsProducts", "slActionsProducts", "slStoresRemains.product_id = slActionsProducts.product_id");
                $q->select(array(
                    "`slStoresRemains`.*",
                    "`slStores`.name_short as store_name"
                ));

                if($properties['category_id'] != "all") {
                    $q->where(array("`slStoresRemains`.`product_id`:=" => $data['items'][$key]['id']));
                }else{
                    $q->where(array("`slStoresRemains`.`id`:=" => $data['items'][$key]['remain_id']));
                }
                $q->where(array(
                    "`slStoresRemains`.`remains`:>" => 0,
                    "`slStoresRemains`.`guid`:!=" => "",
                    "`slStoresRemains`.`store_id`:IN" => $av
                ));

                if ($q->prepare() && $q->stmt->execute()) {
                    $data['items'][$key]['stores'] = $q->stmt->fetchAll(PDO::FETCH_ASSOC);

                    foreach ($data['items'][$key]['stores'] as $key_store => $value_store) {
                        $q_a = $this->modx->newQuery("slActions");
                        $q_a->leftJoin("slActionsProducts", "slActionsProducts", "slActions.id = slActionsProducts.action_id");
                        $q_a->select(array(
                            "`slActions`.*",
                            "`slActionsProducts`.*",
                        ));

                        $q_a->where(array(
                            "`slActionsProducts`.`product_id`:=" => $data['items'][$key]['id'],
                            "`slActions`.`store_id`:=" => $data['items'][$key]['stores'][$key_store]['store_id'],
                            "`slActions`.`active`:=" => 1,
                            "`slActions`.`type`:=" => 1,
                        ));

                        if ($q_a->prepare() && $q_a->stmt->execute()) {
                            $actions = $q_a->stmt->fetchAll(PDO::FETCH_ASSOC);

                            $min_price = $data['items'][$key]['stores'][$key_store]['price'];
                            $data['items'][$key]['stores'][$key_store]['old_price'] = $data['items'][$key]['stores'][$key_store]['price'];
                            $data['items'][$key]['stores'][$key_store]['remain_id'] = $data['items'][$key]['remain_id'];
                            $mainAction = array();
                            foreach ($actions as $key_action => $value_action) {
                                if($value_action['new_price'] < $min_price) {
                                    $min_price = $value_action['new_price'];
                                    $mainAction = $value_action;
                                }
                            }

                            $data['items'][$key]['stores'][$key_store]['price'] = $min_price;
                            $data['items'][$key]['stores'][$key_store]['action'] = $mainAction;

                            foreach ($actions as $key_a => $value_a) {
                                if($value_a['icon']){
                                    $actions[$key_a]['icon'] = "assets/content/" . $actions[$key_a]['icon'];
                                }
                            }

                            $data['items'][$key]['stores'][$key_store]['actions'] = $actions;
                            $data['items'][$key]['stores'][$key_store]['delivery'] = $this->sl->cart->getNearShipment($data['items'][$key]['remain_id'], $data['items'][$key]['stores'][$key_store]['store_id']);
                            $data['items'][$key]['stores'][$key_store]['delivery_day'] = date("Y-m-d", time()+60*60*24*$data['items'][$key]['stores'][$key_store]['delivery']);
                        }
                    }
//
//
//
//
                    // Подсчитываем общее число записей
                    $data['items'][$key]['total_stores'] = count($data['items'][$key]['stores']);
                }
            }
        }else{
            $data['total'] = 0;
            $data['items'] = array();
        }

        $object = $this->modx->getObject("modResource", $properties['category_id']);
        if($object) {
            $data['page'] = $object->toArray();
        }
        return $data;
    }

    /**
     * Добавление товаров в корзину
     *
     * @param $properties
     * @return array
     */
    public function addBasket($properties) {
        //Проверяем, есть ли нужное количество товаров в магазине
        // unset($_SESSION['analytics_user']['basket']);
        if($properties['id_remain']){
            //Проверяем, есть ли у пользователя в сесии такой товар в нужном магазине
            if($_SESSION['basket'][$properties['id']][$properties['store_id']][$properties['id_remain']]){

                //Проверяем, есть ли нужное количество товаров в магазине
                $q = $this->modx->newQuery("slStoresRemains");
                $q->select(array(
                    "`slStoresRemains`.*"
                ));
                $q->where(array(
                    "`slStoresRemains`.`id`:=" => $properties['id_remain'],
                    "`slStoresRemains`.`remains`:>" => 0,
                    "`slStoresRemains`.`guid`:!=" => "",
                    "`slStoresRemains`.`store_id`:=" => $properties['store_id']
                ));

                if ($q->prepare() && $q->stmt->execute()) {
                    $stores = $q->stmt->fetch(PDO::FETCH_ASSOC);


                    $valueBasket = $_SESSION['basket'][$properties['id']][$properties['store_id']][$properties['id_remain']];
                    //Проверяем, хватает ли товаров на складе
                    //Если хватает, кладём в корзину. Если не хватает, кладём всё, что осталось на складе
                    if($valueBasket['count'] + $properties['value'] <= $stores['remains']){
                        $_SESSION['basket'][$properties['id']][$properties['store_id']][$properties['id_remain']] = array(
                            "count" => $valueBasket['count'] + $properties['value'],
                            "price" => $this->getPrice($properties['store_id'], $properties['id_remain'], $valueBasket['count'] + $properties['value'])
                        );
                    }else{
                        $_SESSION['basket'][$properties['id']][$properties['store_id']][$properties['id_remain']] = array(
                            "count" => (int) $stores['remains'],
                            "price" => $this->getPrice($properties['store_id'], $properties['id_remain'], (int) $stores['remains'])
                        );
                    }
                }
            }else{
                //Проверяем, есть ли нужное количество товаров в магазине
                $q = $this->modx->newQuery("slStoresRemains");
                $q->select(array(
                    "`slStoresRemains`.*"
                ));
                $q->where(array(
                    "`slStoresRemains`.`id`:=" => $properties['id_remain'],
                    "`slStoresRemains`.`remains`:>" => 0,
                    "`slStoresRemains`.`guid`:!=" => "",
                    "`slStoresRemains`.`store_id`:=" => $properties['store_id']
                ));


                if ($q->prepare() && $q->stmt->execute()) {
                    $stores = $q->stmt->fetch(PDO::FETCH_ASSOC);

                    //Проверяем, хватает ли товаров на складе
                    //Если хватает, кладём в корзину. Если не хватает, кладём всё, что осталось на складе
                    if($properties['value'] <= $stores['remains']){
                        $_SESSION['basket'][$properties['id']][$properties['store_id']][$properties['id_remain']] = array(
                            "count" => $properties['value'],
                            "price" => $this->getPrice($properties['store_id'], $properties['id_remain'], $properties['value'])
                        );
                    }else{
                        $_SESSION['basket'][$properties['id']][$properties['store_id']][$properties['id_remain']] = array(
                            "count" => (int) $stores['remains'],
                            "price" => $this->getPrice($properties['store_id'], $properties['id_remain'], (int) $stores['remains'])
                        );
                    }
                }
            }

            return $this->getBasket(array(
                "id" => $properties['id'],
            ));
        }
    }

    public function getPrice($store_id, $remain_id, $count) {
        $q = $this->modx->newQuery("slStoresRemains");
        $q->select(array(
            "`slStoresRemains`.*",
        ));
        $q->where(array(
            "`slStoresRemains`.`id`:=" => $remain_id,
            "`slStoresRemains`.`remains`:>" => 0,
            "`slStoresRemains`.`guid`:!=" => "",
            "`slStoresRemains`.`store_id`:=" => $store_id
        ));


        if ($q->prepare() && $q->stmt->execute()) {
            $storeRemains = $q->stmt->fetch(PDO::FETCH_ASSOC);


            $q_a = $this->modx->newQuery("slActions");
            $q_a->leftJoin("slActionsProducts", "slActionsProducts", "slActions.id = slActionsProducts.action_id");
            $q_a->select(array(
                "`slActions`.*",
                "`slActionsProducts`.*",
            ));

            $q_a->where(array(
                "`slActionsProducts`.`remain_id`:=" => $remain_id,
                "`slActions`.`store_id`:=" => $store_id,
                "`slActions`.`active`:=" => 1,
                "`slActions`.`type`:=" => 1
            ));

            if ($q_a->prepare() && $q_a->stmt->execute()) {
                $actions = $q_a->stmt->fetchAll(PDO::FETCH_ASSOC);

                $min_price = $storeRemains['price'];
                foreach ($actions as $key_action => $value_action) {
                    //Расчёт цены с кратность
                    //Количество товаров, на которое не действует скидка
                    $remain_multiplicity = $count % $value_action['multiplicity'];
                    //Количество товаров, на которое действует скидка
                    $sale_multiplicity = $count - $remain_multiplicity;

                    $calc_price = ($remain_multiplicity * $value_action['old_price'] + $sale_multiplicity * $value_action['new_price']) / $count;
                    $this->modx->log(1, "calc_price" . $calc_price);


                    if($calc_price < $min_price) {
                        $min_price = $calc_price;
                    }
                }

                $storeRemains['price'] = $min_price;

            }

            return $storeRemains['price'];
        }
    }

    /**
     * Получить корзину
     *
     * @param $properties
     * @return stdClass
     */
    public function getBasket($properties) {
        $result = array();
        $total_cost = 0;
        $total_weight = 0;
        $total_volume = 0;

//        return $_SESSION['basket'][$properties['id']];

        if($properties['id']){
            if($_SESSION['basket'][$properties['id']]){
                $urlMain = $this->modx->getOption("site_url");
                foreach ($_SESSION['basket'][$properties['id']] as $key => $value){
                    $q = $this->modx->newQuery("slStores");
                    $q->select(array(
                        "`slStores`.*"
                    ));
                    $q->where(array(
                        "`slStores`.`id`:=" => $key
                    ));

                    //$q->prepare();
                    //$this->modx->log(1, $q->toSQL());

                    if ($q->prepare() && $q->stmt->execute()) {
                        $store = $q->stmt->fetch(PDO::FETCH_ASSOC);

                        $colors = $this->modx->getOption('shoplogistic_store_colors');
                        $colors = trim($colors);
                        $colorsArray = explode(",", $colors);

                        if(($store['id'] % 4) == 0){
                            if($colorsArray[0]){
                                $store['color'] = $colorsArray[0];
                            }
                            $store['color'] = "#50C0E6";
                        }elseif($store['id'] % 3){
                            if($colorsArray[1]){
                                $store['color'] = $colorsArray[1];
                            }
                            $store['color'] = "#6CA632";
                        }elseif($store['id'] % 2){
                            if($colorsArray[2]){
                                $store['color'] = $colorsArray[2];
                            }
                            $store['color'] = "#3237A6";
                        }else{
                            if($colorsArray[3]){
                                $store['color'] = $colorsArray[3];
                            }
                            $store['color'] = "#A63232";
                        }

                        $result['stores'][$key] = $store;
                        $cost = 0;
                        $weight = 0;
                        $volume = 0;

                        foreach ($value as $k => $v){
                            $query = $this->modx->newQuery("slStoresRemains");
                            $query->leftJoin("modResource", "modResource", "modResource.id = slStoresRemains.product_id");
                            $query->leftJoin("msProductData", "msProductData", "msProductData.id = slStoresRemains.product_id");

                            $query->select(array(
                                "`slStoresRemains`.*",
                                "`slStoresRemains`.id as id_remain",
                                "`msProductData`.*"
                            ));
                            $query->where(array(
                                "`slStoresRemains`.`id`:=" => $k,
                                "`slStoresRemains`.`remains`:>" => 0,
                                "`slStoresRemains`.`guid`:!=" => "",
                                "`slStoresRemains`.`store_id`:=" => $key
                            ));

                            if ($query->prepare() && $query->stmt->execute()) {
                                $product = $query->stmt->fetch(PDO::FETCH_ASSOC);

                                if($product['image']){
                                    $product['image'] = $urlMain . $product['image'];
                                }else{
                                    $product['image'] = $urlMain . $this->modx->getPlaceholder("+conf_noimage");
                                }

                                $total_cost = $total_cost + $v['count'] * $v['price'];
                                $cost = $cost + $v['count'] * $v['price'];

                                $params = $this->sl->product->getProductParams($product['id']);
                                $weight += $params[0]['product']["weight_brutto"] * $v['count'];
                                $volume += $params[0]['product']["length"] * $params[0]['product']["width"] * $params[0]['product']["height"] * $v['count'];

                                $total_weight += $params[0]['product']["weight_brutto"] * $v['count'];
                                $total_volume += $params[0]['product']["length"] * $params[0]['product']["width"] * $params[0]['product']["height"] * $v['count'];

                                $product['info'] = $v;
                                $result['stores'][$key]['products'][$k] = $product;
                            }
                        }

                        $result['stores'][$key]['cost'] = $cost;
                        $result['stores'][$key]['weight'] = $weight;
                        $result['stores'][$key]['volume'] = $volume;
                    }
                }

                $result['cost'] = $total_cost;
                $result['weight'] = $total_weight;
                $result['volume'] = $total_volume;

                return $result;
            }else{
                return null;
            }
        }
    }

    public function updateBasket($properties)
    {
        //Проверяем, есть ли нужное количество товаров в магазине
        // unset($_SESSION['basket']);
        if($properties['id_remain']){
            //Проверяем, есть ли у пользователя в сесии такой товар в нужном магазине
            if($_SESSION['basket'][$properties['id']][$properties['store_id']][$properties['id_remain']]){
                //Проверяем, есть ли нужное количество товаров в магазине
                $q = $this->modx->newQuery("slStoresRemains");

                $q->select(array(
                    "`slStoresRemains`.*"
                ));
                $q->where(array(
                    "`slStoresRemains`.`id`:=" => $properties['id_remain'],
                    "`slStoresRemains`.`remains`:>" => 0,
                    "`slStoresRemains`.`guid`:!=" => "",
                    "`slStoresRemains`.`store_id`:=" => $properties['store_id']
                ));


                if ($q->prepare() && $q->stmt->execute()) {
                    $stores = $q->stmt->fetch(PDO::FETCH_ASSOC);


                    //Проверяем, хватает ли товаров на складе
                    //Если хватает, кладём в корзину. Если не хватает, кладём всё, что осталось на складе
                    if($properties['value'] <= $stores['remains']){
                        $_SESSION['basket'][$properties['id']][$properties['store_id']][$properties['id_remain']] = array(
                            "count" => $properties['value'],
                            "price" => $this->getPrice($properties['store_id'], $properties['id_remain'], $properties['value'])
                        );
                    }
                }

                return $this->getBasket(array(
                    "id" => $properties['id'],
                ));
            }

        }
    }

    public function clearBasket($properties)
    {
        if($properties['id']){
            if($properties['store_id'] && $properties['id_remain']){
                unset($_SESSION['basket'][$properties['id']][$properties['store_id']][$properties['id_remain']]);
            }else{
                unset($_SESSION['basket'][$properties['id']]);
            }
        }

        return $this->getBasket(array(
            "id" => $properties['id'],
        ));
    }

    public function orderSubmit($properties){
        $this->modx->log(1, print_r($properties, 1));
        $this->modx->log(1, print_r($_SESSION['basket'], 1));
        if($properties["id"]){
            if($properties["store_id"] != 'all'){
                $order_data['products'] = $_SESSION['basket'][$properties["id"]][$properties["store_id"]];
                $order_data["warehouse_id"] = $properties["store_id"];
                $order_data["store_id"] = $properties["id"];
                $response[] = $this->orderSave($order_data);
            }else{
                foreach($_SESSION['basket'][$properties["id"]] as $key => $val){
                    $order_data['products'] = $_SESSION['basket'][$properties["id"]][$properties["store_id"]];
                    $order_data["warehouse_id"] = $properties["store_id"];
                    $order_data["store_id"] = $properties["id"];
                    $response[] = $this->orderSave($order_data);
                }
            }
        }
        $this->clearBasket($properties);
        return $this->sl->tools->success("", $response);
    }

    public function orderSave($data){
        $order_data = $this->orderGetCost($data);
        $order = $this->modx->newObject("slOrderOpt");
        $order->set("warehouse_id", $data["warehouse_id"]);
        $order->set("store_id", $data["store_id"]);
        $order->set("weight", $order_data["weight"]);
        $order->set("volume", $order_data["volume"]);
        $order->set("cost", $order_data["cost"]);
        $order->set("cart_cost", $order_data["cost"]);
        $order->set("createdon", time());
        $order->set("date", time());
        $order->save();
        foreach($data['products'] as $k => $item){
            $remain = $this->modx->getObject("slStoresRemains", $k);
            if($remain){
                $product_id = $remain->get("product_id");
                if($product_id){
                    $params = $this->sl->product->getProductParams($product_id);
                }else{
                    $params = array(
                        array(
                            "weight_brutto" => 0,
                            "name" => $remain->get("name"),
                            "length" => 0,
                            "width" => 0,
                            "height" => 0
                        )
                    );
                }
                $product = $this->modx->newObject("slOrderOptProduct");
                $product->set("remain_id", $k);
                $product->set("order_id", $order->get("id"));
                $product->set("name", $params[0]['name']);
                $product->set("count", $item['count']);
                $product->set("price", $item['price']);
                $product->set("weight", $params[0]['product']["weight_brutto"]);
                $product->set("cost", ($item['count'] * $item['price']));
                $product->save();
            }

        }

        return $order->toArray();
    }

    /**
     * Формируем данные по заказу
     *
     * @param $data
     * @return int[]
     */
    public function orderGetCost($data){
        $output = array(
            "cost" => 0,
            "count" => 0,
            "weight" => 0,
            "volume" => 0
        );
        foreach($data['products'] as $k => $item){
            $output['cost'] += $item["price"] * $item['count'];
            $output['count'] += $item['count'];
            $params = $this->sl->product->getProductParams($k);
            $output['weight'] += $params[0]['product']["weight_brutto"] * $item['count'];
            $output['volume'] += $params[0]['product']["length"] * $params[0]['product']["width"] * $params[0]['product']["height"] * $item['count'];
        }
        $output['volume'] = $output['volume'] / 1000000;
        return $output;
    }

    public function getOrdersSeller($properties) {
        if($properties["store_id"]){
            $result = array();

            if($properties["order_id"]){
                $query = $this->modx->newQuery("slOrderOpt");
                $query->rightJoin("slStores", "slStores", "slStores.id = slOrderOpt.store_id");
                $query->select(array(
                    "`slOrderOpt`.*",
                    "`slStores`.name_short",
                    "`slStores`.phone",
                ));
                $query->where(array(
                    "`slOrderOpt`.`warehouse_id`:=" => $properties['store_id'],
                    "`slOrderOpt`.`id`:=" => $properties['order_id']
                ));

//                $query->prepare();
//                $this->modx->log(1, $query->toSQL());

                if ($query->prepare() && $query->stmt->execute()) {
                    $result["order"] = $query->stmt->fetch(PDO::FETCH_ASSOC);

                    $query_p = $this->modx->newQuery("slOrderOptProduct");
                    $query_p->leftJoin("slStoresRemains", "slStoresRemains", "slStoresRemains.id = slOrderOptProduct.remain_id");
                    $query_p->leftJoin("msProductData", "msProductData", "msProductData.id = slStoresRemains.product_id");
                    $query_p->leftJoin("modResource", "modResource", "modResource.id = slStoresRemains.product_id");
                    $query_p->select(array(
                        "`slOrderOptProduct`.*",
                        "`msProductData`.*",
                        "`modResource`.*",
                        "COALESCE(msProductData.image, '/assets/files/img/nopic.png') as image",
                        "`slOrderOptProduct`.price as price",
                    ));
                    $query_p->where(array(
                        "`slOrderOptProduct`.`order_id`:=" => $result["order"]['id']
                    ));

                    if ($query_p->prepare() && $query_p->stmt->execute()) {
                        $result["order"]['products'] = $query_p->stmt->fetchAll(PDO::FETCH_ASSOC);
                    }
                }
            }
            else{
                $query = $this->modx->newQuery("slOrderOpt");
                $query->leftJoin("slStores", "slStores", "slStores.id = slOrderOpt.store_id");

                $query->select(array(
                    "`slOrderOpt`.*",
                    "`slStores`.name_short as store_name"
                ));
                $query->where(array(
                    "`slOrderOpt`.`warehouse_id`:=" => $properties['store_id']
                ));

                if($properties['filter']){
                    $criteria = array();
                    $criteria['slStores.name_short:LIKE'] = '%'.$properties['filter'].'%';
                    $criteria['OR:slOrderOpt.date:LIKE'] = '%'.$properties['filter'].'%';
                    $query->where($criteria);
                }

                // Подсчитываем общее число записей
                $result['total'] = $this->modx->getCount('slOrderOpt', $query);

                // Устанавливаем лимит 1/10 от общего количества записей
                // со сдвигом 1/20 (offset)
                if($properties['page'] && $properties['perpage']){
                    $limit = $properties['perpage'];
                    $offset = ($properties['page'] - 1) * $properties['perpage'];
                    $query->limit($limit, $offset);
                }

                if ($query->prepare() && $query->stmt->execute()) {
                    $result["orders"] = $query->stmt->fetchAll(PDO::FETCH_ASSOC);
                }
            }

            return $result;

        }
    }


}