<?php
class cartDifficultHandler
{
	public $modx;
	public $sl;
	public $config;

	public function __construct(shopLogistic &$sl, modX &$modx)
	{
		$this->sl =& $sl;
		$this->modx =& $modx;
		$this->modx->lexicon->load('shoplogistic:default');
		$this->config['our_services'] = array('yandex', 'evening', 'postrf', 'cdek');
		// link ms2
		if(is_dir($this->modx->getOption('core_path').'components/minishop2/model/minishop2/')) {
			$ctx = 'web';
			$this->ms2 = $this->modx->getService('miniShop2');
			if ($this->ms2 instanceof miniShop2) {
				$this->ms2->initialize($ctx);
				return true;
			}
		}
	}

    /**
     * Берем доступные доставки. Позже будет упаковано.
     *
     * @return array
     */
    public function getDeliveries(){
        return array(
            "data" => array(
                "yandex" => array(
                    "name" => "Экспресс доставка",
                    "logo" => "/assets/content/images/delivery/yandex.png",
                    "pvz" => 0
                ),
                "evening" => array(
                    "name" => "Курьер с 17-00 до 22-00",
                    "logo" => "/assets/content/images/delivery/yandex.png",
                    "pvz" => 0
                ),
                "postrf" => array(
                    "name" => "Почта России",
                    "logo" => "/assets/content/images/delivery/post.png",
                    "pvz" => 0
                ),
                "cdek" => array(
                    "name" => "СДЭК",
                    "logo" => "/assets/content/images/delivery/cdek.svg",
                    "pvz" => 1
                )
            )
        );
    }

    /*
     * Обновляем корзину на фронте
     *
     */
    public function update(){
        $data = $this->modx->runSnippet("msCart", array('tpl' => '@FILE chunks/ms2_cart.tpl'));
        $data_order = $this->modx->runSnippet("sl.cart", array('cartTpl' => '@FILE chunks/ms2_order_cart.tpl'));
        $order = $this->modx->runSnippet("msOrder", array('tpl' => '@FILE chunks/ms2_order.tpl'));
        return $this->sl->success("Cart changer", array("cart" => $data, "cart_order" => $data_order, "order" => $order));
    }

    /**
     * Берем предложения по товару
     *
     * @param $product_id
     * @param $ctx
     * @return array
     */
    public function getOffers($product_id, $ctx = 'web'){
        $output = array(
            "location_store" => array(),
            "stores" => array(),
            "stores_city" => array(),
            "selected_store" => array(),
            "selected_store_warehouses" => array()
        );
        $location = $this->sl->getLocationData($ctx);
        $output['location'] = $location;
        $output['location_store'] = $location['store'];
        // проверяем остаток в выбранном магазине
        $query = $this->modx->newQuery("slStoresRemains");
        $query->leftJoin("slStores", "slStores", "slStores.id = slStoresRemains.store_id");
        $query->where(array(
            "product_id:=" => $product_id,
            "AND:remains:>=" => 1,
            "AND:price:>" => 0,
            'AND:store_id:=' => $location['store']['id']
        ));
        $query->select(array("slStoresRemains.*,slStores.name as store_name,slStores.address as store_address"));
        if($query->prepare() && $query->stmt->execute()){
            $remain = $query->stmt->fetch(PDO::FETCH_ASSOC);
            /*if($product_id == 4571 && $this->modx->user->id == 439){
                $this->modx->log(1, print_r($remain, 1));
            }*/
            if($remain){
                $action = $this->getSales($product_id, $remain["store_id"]);
                /*if($product_id == 4571 && $this->modx->user->id == 439){
                    $this->modx->log(1, print_r($action, 1));
                }*/
                if($action){
                    if($action["new_price"]){
                        $remain["price"] = $action["new_price"];
                    }
                    if($action["old_price"]){
                        $remain["old_price"] = $action["old_price"];
                    }
                }
                /*if($product_id == 4571 && $this->modx->user->id == 439){
                    $this->modx->log(1, print_r($remain, 1));
                }*/
                $output['selected_store'] = $remain;
            }else{
                // если остатка нет в магазине проверяем склад (если складов несколько, берем один с наименьшей ценой)
                $whs = array();
                $warehouses = $this->sl->store->getWarehouses($location['store']['id']);
                // $this->modx->log(1, print_r($warehouses, 1));
                // return $warehouses;
                foreach($warehouses as $wh) {
                    $whs[] = $wh['id'];
                }
                if($whs){
                    $q = $this->modx->newQuery("slStoresRemains");
                    $q->leftJoin("slStores", "slStores", "slStores.id = slStoresRemains.store_id");
                    $q->where(array(
                        "product_id:=" => $product_id,
                        "AND:remains:>=" => 1,
                        "AND:price:>" => 0,
                        'AND:store_id:IN' => $whs
                    ));
                    $q->sortby("price", "ASC");
                    $q->select(array("slStoresRemains.*,slStores.name as store_name,slStores.address as store_address"));
                    $q->prepare();
                    // echo '2 '.$q->toSQL();
                    if($q->prepare() && $q->stmt->execute()) {
                        $remains = $q->stmt->fetchAll(PDO::FETCH_ASSOC);
                        if($remains){
                            foreach($remains as $key => $remain){
                                $remains[$key]['type'] = "slWarehouse";
                                $action = $this->getSales($product_id, $remain["store_id"]);
                                if($action){
                                    if($action["new_price"]){
                                        $remains[$key]["price"] = $action["new_price"];
                                    }
                                    if($action["old_price"]){
                                        $remains[$key]["old_price"] = $action["old_price"];
                                    }
                                }
                            }
                            $output['selected_store_warehouses'] = $remains;
                        }
                    }
                }
            }
        }
        // ищем все предложения из города, в котором находимся
        $whs = array();
        $warehouses = $this->sl->store->getWarehouses($location['store']['id']);
        foreach($warehouses as $wh) {
            $whs[] = $wh['id'];
        }
        $criteria = array(
            "product_id:=" => $product_id,
            "AND:remains:>=" => 1,
            "AND:price:>" => 0,
            "AND:slStores.active:=" => 1,
            "AND:slStores.city:=" => $output['location_store']['city'],
            "AND:slStores.id:!=" => $output['location_store']['id']
        );
        if(count($whs)){
            $criteria["AND:slStores.id:NOT IN"] = $whs;
        }
        $query = $this->modx->newQuery("slStoresRemains");
        $query->leftJoin("slStores", "slStores", "slStores.id = slStoresRemains.store_id");
        $query->where($criteria);
        $query->sortby('price', 'ASC');
        $query->select(array(
            'slStoresRemains.*,slStores.name as store_name,slStores.address',
        ));
        if($query->prepare() && $query->stmt->execute()) {
            $remains = $query->stmt->fetchAll(PDO::FETCH_ASSOC);
            if($remains){
                foreach($remains as $key => $remain){
                    $action = $this->getSales($product_id, $remain["store_id"]);
                    if($action){
                        if($action["new_price"]){
                            $remains[$key]["price"] = $action["new_price"];
                        }
                        if($action["old_price"]){
                            $remains[$key]["old_price"] = $action["old_price"];
                        }
                    }
                }
                $output['stores_city'] = $remains;
            }
        }
        // ищем все предложения из других городов
        $criteria = array(
            "product_id:=" => $product_id,
            "AND:remains:>=" => 1,
            "AND:price:>" => 0,
            "AND:slStores.active:=" => 1,
            "AND:slStores.city:!=" => $output['location_store']['city'],
            "AND:slStores.id:!=" => $output['location_store']['id']
        );
        if(count($whs)){
            $criteria["AND:slStores.id:NOT IN"] = $whs;
        }
        $query = $this->modx->newQuery("slStoresRemains");
        $query->leftJoin("slStores", "slStores", "slStores.id = slStoresRemains.store_id");
        $query->where($criteria);
        $query->sortby('price', 'ASC');
        $query->select(array(
            'slStoresRemains.*,slStores.name as store_name,slStores.address',
        ));
        if($query->prepare() && $query->stmt->execute()) {
            $remains = $query->stmt->fetchAll(PDO::FETCH_ASSOC);
            if($remains){
                foreach($remains as $key => $remain){
                    $action = $this->getSales($product_id, $remain["store_id"]);
                    if($action){
                        if($action["new_price"]){
                            $remains[$key]["price"] = $action["new_price"];
                        }
                        if($action["old_price"]){
                            $remains[$key]["old_price"] = $action["old_price"];
                        }
                    }
                }
                $output['stores'] = $remains;
            }
        }
        $output['all_stores'] = array_merge($output['stores_city'], $output['stores']);
        if($output['selected_store']){
            array_unshift($output['all_stores'], $output['selected_store']);
        }
        // старое условие вывода.
        if(!isset($output['selected_store']) && !isset($output['selected_store_warehouses'])){

        }
        return $output;
    }

    /**
     * Считаем примерные сроки доставки
     *
     * @param $product_id
     * @param $type
     * @param $from_id
     * @return array|false
     */
    public function getDeliveryInfoDays($product_id, $type = 'slStores', $from_id = 0){
        $delivery_data = array();
        // $this->modx->log(1, $product_id.' | '.$type.' | '.$from_id.' | ');
        // Считаем примерные сроки доставки
        // Вариант 1: считаем самовывоз + доставку от поставщика
        $delivery_data['pickup'] = $this->getPickupPrice($product_id, $type, 1, $from_id);
        // TODO: контекст!
        $location = $this->sl->getLocationData('web');
        $delivery_data['location'] = $location;
        // игнорим второй режим
        /*
        if($this->modx->getOption('shoplogistic_cart_mode') == 2){
            $delivery_data = $this->getPickupPrice($product_id, 1, $from_id);
        }else{
            $stores = $this->getNearbyStores($product_id);
            if(count($stores)){
                // если в наличии
                $delivery_data['pickup']['store'] = $stores[0];
                $delivery_data['pickup']['price'] = 0;
                $delivery_data['pickup']['term'] = 'сегодня';
                $delivery_data['pickup']['term_default'] = 0;
            }else{
                $default_store = $this->modx->getOption("shoplogistic_default_store");
                if ($default_store) {
                    $delivery_data['pickup']['price'] = 0;
                    $delivery_data['pickup']['term'] = 'сегодня';
                    $delivery_data['pickup']['term_default'] = 0;
                    $def_store = $this->sl->getObject($default_store, "slStores");
                    if ($def_store) {
                        $delivery_data['pickup']['store'] = $def_store;
                    }
                }
            }
        }*/

        $delivery = $this->getTKPrice($product_id, 1, $type, $from_id);
        $delivery_data['delivery'] = $delivery['delivery'];
        $express = $this->getYaDeliveryPrice($product_id, 1, $type, $from_id);
        if($express['delivery']) {
            $delivery_data['express'] = $express['delivery'];
        }

        return $delivery_data;
    }

	/**
	 * Берем параметры товара, габариты в см, вес в кг, объем в куб.м.
	 *
	 * @param int $product_id
	 * @return array|bool
	 */
	public function getProductParams($product_id = 0, $num = 1){
		$tmp = array();
		$output = array();
		if($product_id){
			$query = $this->modx->newQuery("modResource");
			$query->leftJoin("msProductData", "Data");
			$query->where(array(
				"`modResource`.`id`:=" => $product_id
			));
			$query->select(array(
				"`modResource`.*",
				"`Data`.*"
			));
			$query->limit(1);
			if ($query->prepare() && $query->stmt->execute()) {
				$product = $query->stmt->fetch(PDO::FETCH_ASSOC);
				if(count($product)){
					$params = array();
					$tmp["id"] = $product['id'];
					$tmp["article"] = $product['article'];
					$tmp["name"] = $product['pagetitle'];
					$tmp['weight'] = (float)$product['weight']?:(float)$product['weight_brutto'];
					$tmp['weight_netto'] = (float)$product['weight_netto'];
					$tmp['volume'] = (float)$product['volume'];
					// TODO: fix price
					$tmp['price'] = (float)$product['price'];
					$tmp['count'] = $num;
					$params['dimensions'][0] = (int)$product['length'];
					$params['dimensions'][1] = (int)$product['width'];
					$params['dimensions'][2] = (int)$product['height'];
					$tmp['length'] = $params['dimensions'][0];
					$tmp['width'] = $params['dimensions'][1];
					$tmp['height'] = $params['dimensions'][2];
					$tmp['dimensions'] = implode('*', $params['dimensions']);
					$tmp['product'] = $product;
					$output[] = $tmp;
					return $output;
				}
			}
		}
		return false;
	}

	/**
	 * Берем координаты юзера
	 *
	 * @return array
	 */
	public function getUserPosition($ctx = 'web'){
		$geo = array();
		$geo['data'] = $_SESSION['dartlocation'][$ctx];
		$geo['lat'] = trim($_SESSION['dartlocation'][$ctx]['geo_lat']);
		$geo['lng'] = trim($_SESSION['dartlocation'][$ctx]['geo_lon']);
		return $geo;
	}

    public function getProductOffers($product_id, $ctx = 'web'){
        // проверяем выбранный магазин и склад
        $offers = $this->getLocationRemains($product_id, $ctx);
        // смотрим магазины
        if(!count($offers)){
            $offers = $this->getStoresRemains($product_id, 'slStores');
            $warehouses = $this->getStoresRemains($product_id, 'slWarehouse');
            $offers = array_merge($offers, $warehouses);
        }
        return $offers;
    }

    public function getStoresRemains($product_id, $type = 'slStores'){
        $output = array();
        if($type == 'slStores'){
            $object_r = "slStoresRemains";
            $object = 'slStores';
            $ob_key = 'store_id';
        }
        if($type == 'slWarehouse'){
            $object_r = "slWarehouseRemains";
            $object = 'slWarehouse';
            $ob_key = 'warehouse_id';
        }
        $query = $this->modx->newQuery($object_r);
        $query->where(
            array(
                "product_id:=" => $product_id,
                "AND:available:>=" => 1,
                "AND:price:>" => 0
            )
        );
        $query->sortby('price', 'ASC');
        $query->select(array(
            $object_r.'.*',
        ));
        $query->prepare();
        if($query->stmt->execute()) {
            $res = $query->stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($res as $key => $r) {
                $store = $this->modx->getObject($object, $r[$ob_key]);
                if ($store) {
                    if ($store->get('active')) {
                        $output[$object][] = array(
                            'type' => $object,
                            'data' => $store->toArray(),
                            'product' => $r
                        );
                    }
                }
            }
        }
        return $output;
    }

    public function getLocationRemains($product_id, $ctx = 'web'){
        $output = array();
        $location = $this->sl->getLocationData($ctx);
        // проверяем остаток в магазине
        $criteria = array(
            "product_id:=" => $product_id,
            "AND:available:>=" => 1,
            "AND:price:>" => 0
        );
        if($this->modx->getOption("shoplogistic_cart_mode") == 2){
            $criteria['AND:store_id:='] = $location['store']['id'];
        }
        // остатки магазина
        // TODO: предусмотреть работу по 1 режиму
        $remains = $this->modx->getObject("slStoresRemains", $criteria);
        if($remains){
            $output['slStores'][] = array(
                'type' => 'slStores',
                'data' => $location['store'],
                'product' => $remains->toArray()
            );
        }else{
            // проверяем склады
            if($this->modx->getOption("shoplogistic_cart_mode") == 2){
                // предусмотреть множество складов
                $warehouses = $this->getWarehouses($location['store']['id']);
                if(count($warehouses)){
                    $query = $this->modx->newQuery("slWarehouseRemains");
                    $query->where(
                        array(
                            "product_id:=" => $product_id,
                            "AND:available:>=" => 1,
                            "AND:price:>" => 0,
                            'AND:warehouse_id:IN' => $warehouses
                        )
                    );
                    $query->sortby('price', 'ASC');
                    $query->select(array(
                        'slWarehouseRemains.*',
                    ));
                    $query->prepare();
                    if($query->stmt->execute()) {
                        $res = $query->stmt->fetchAll(PDO::FETCH_ASSOC);
                        foreach($res as $key => $r){
                            $warehouse = $this->modx->getObject("slWarehouse",  $r['warehouse_id']);
                            if($warehouse) {
                                if ($warehouse->get('active')) {
                                    $output['slWarehouse'][] = array(
                                        'type' => 'slWarehouse',
                                        'data' => $warehouse->toArray(),
                                        'product' => $r
                                    );
                                }
                            }
                        }
                    }
                }else{
                    $this->sl->sendAlert("К магазину не привязаны оптовики", $location['store']);
                }
            }
        }
        return $output;
    }

    public function getWarehouses($store_id){
        $warehouses = array();
        $sql = "SELECT * FROM {$this->modx->getTableName('slWarehouseStores')} WHERE `store_id` = {$store_id}";
        $q = $this->modx->prepare($sql);
        $q->execute();
        $str = $q->fetchAll(PDO::FETCH_ASSOC);
        if(count($str)) {
            foreach($str as $s){
                $warehouses[] = $s['warehouse_id'];
            }
        }
        return $warehouses;
    }

	/**
	 * Ищем ближайший магазин с товаром
	 *
	 * @param $product_id
	 * @return array, bool
	 */
	public function getNearbyStores($product_id){
		$sts = array();
		$default_store = $this->modx->getOption("shoplogistic_default_store");
		if($default_store){
			$sts[] = $default_store;
		}
		// ищем пока в два запроса, позже нужно оптимизировать
		$query = $this->modx->newQuery('slStoresRemains');
		$query->rightJoin('slStores','slStores', array( 'slStoresRemains.store_id = slStores.id'));
		$query->select(array('slStores.id'));
		$query->where(array('slStoresRemains.product_id' => $product_id));
		$query->limit(999);

		if ($query->prepare() && $query->stmt->execute()) {
			$result = $query->stmt->fetchAll(PDO::FETCH_ASSOC);
			foreach ($result as $res) {
				$sts[] = $res['id'];
			}
			$stores = implode(',', $sts);
			$geo = $this->getUserPosition();
			$lat = trim($geo['lat']);
			$lng = trim($geo['lng']);

			$sql = "SELECT 
        			*,
        			coordinats,
        			(
        			   6371 *
        			   acos(cos(radians({$lat})) * 
        			   cos(radians(lat)) * 
        			   cos(radians(lng) - 
        			   radians({$lng})) + 
        			   sin(radians({$lat})) * 
        			   sin(radians(lat)))
        			) AS distance 
        			FROM {$this->modx->getTableName('slStores')} WHERE `active` = 1 AND `id` IN ({$stores}) ORDER BY distance LIMIT 999";

			$query = $this->modx->query($sql);
			if($query){
				$result = $query->fetchAll(PDO::FETCH_ASSOC);
				return $result;
			}else{
				return false;
			}
		}
		return false;
	}

	public function getProductData($product_id){
		$resource = $this->modx->getObject("modResource", $product_id);
		if($resource){
			$item = $resource->toArray();
		}
		$product_data = $this->modx->getObject("msProductData", $product_id);
		if($product_data){
			$item = array_merge($item, $product_data->toArray());
		}
		$options = $this->modx->call('msProductData', 'loadOptions', array($this->modx, $item['id']));
		if($product_data){
			$item = array_merge($item, $options);
		}
		$item['old_price'] = $this->ms2->formatPrice($item['old_price']);
		$item['price'] = $this->ms2->formatPrice($item['price']);
		$item['weight'] = $this->ms2->formatWeight($item['weight']);
		// $item['cost'] = $this->ms2->formatPrice($item['count'] * $item['price']);
		// $item['discount_price'] = $this->ms2->formatPrice($item['discount_price']);
		// $item['discount_cost'] = $this->ms2->formatPrice($item['count'] * $item['discount_price']);
		return $item;
	}

	/**
	 * 	Проверка корзины и разбивка на отправления
	 */
	public function checkCart($fias = 0){
		$tmp = array();
		$new_cart = array();
		$cart = $this->ms2->cart->get();
		// $cart_mode = $this->modx->getOption('shoplogistic_cart_mode');
        // echo $cart_mode;
		// если режим глобальный, то ищем среди всех магазинов. В данном случае, игнорируются дистрибьюторы.
        // print_r($warehouse);
        $warehouse = array();
        $store = array();
        foreach($cart as $key => $item){
            $item['key'] = $key;
            $product_data = $this->getProductData($item['id']);
            $item = array_merge($product_data, $item);
            $location = $this->sl->getLocationData('web');
            //if(isset($item['options']['type'])){
                if($item['options']['type'] == 'slWarehouse'){
                    if(isset($item['options']['warehouse'])){
                        $warehouse = $this->sl->getObject($item['options']['warehouse']);
                        if($warehouse){
                            $remains = $this->getRemains('slStores', $warehouse['id'], $item['id'], $item['count']);
                            $new_cart['stores'][] = $warehouse['id'];
                            $new_cart['slWarehouse_'.$warehouse['id']]['object'] = $remains['object_id'];
                            $new_cart['slWarehouse_'.$warehouse['id']]['type'] = 'slWarehouse';
                            $new_cart['slWarehouse_'.$warehouse['id']]['data'] = $warehouse;
                            $new_cart['slWarehouse_'.$warehouse['id']]['products'][$key] = $item;
                        }
                    }
                }else{
                    if(isset($item['options']['store'])) {
                        // проверяем остаток в магазине
                        $store = $this->sl->getObject($item['options']['store']);
                        if($store){
                            $new_cart['stores'][] = $store['id'];
                            // проверяем остаток у магазина
                            $remains = $this->getRemains('slStores', $store['id'], $item['id'], $item['count']);
                            if ($remains) {
                                $new_cart['slStores_' . $store['id']]['object'] = $store['id'];
                                $new_cart['slStores_' . $store['id']]['type'] = 'slStores';
                                $new_cart['slStores_' . $store['id']]['data'] = $store;
                                $new_cart['slStores_' . $store['id']]['products'][$key] = $item;
                            }
                        }
                    }
                }
            //}
        }
		return $new_cart;
	}

    /**
     * Ищем активные акции
     *
     * @param $product_id
     * @return array|void
     */
    public function getSales($product_id, $store_id = 0){
        $city = 0;
        $region = 0;
        $location = $this->sl->getLocationData('web');
        if($location['city_id']){
            $city = $this->sl->getObject($location['city_id'], "dartLocationCity");
            if($city){
                $region = $city['region'];
            }
        }
        if($location['region_type_full'] && $location['region']){
            // сначала чекаем fias
            $criteria = array(
                "fias_id:=" => $location['region_fias_id']
            );
            $object = $this->modx->getObject("dartLocationRegion", $criteria);
            if(!$object){
                $criteria = array(
                    "name:LIKE" => "%{$location['region_type_full']} {$location['region']}%",
                    "OR:name:LIKE" => "%{$location['region']} {$location['region_type_full']}%"
                );
                $object = $this->modx->getObject("dartLocationRegion", $criteria);
                if($object){
                    if(!$object->get("fias_id") && $location['region_fias_id']){
                        $object->set("fias_id", $location['region_fias_id']);
                        $object->save();
                    }
                }
            }
            if($object){
                $region = $object->get("id");
            }else{
                $region = 44;
            }
        }
        // регион должен 100% определиться
        if($region){
            $query = $this->modx->newQuery("slActionsProducts");
            $query->leftJoin("slActions", "slActions", "slActions.id = slActionsProducts.action_id");
            if($store_id) {
                $query->leftJoin("slActionsStores", "slActionsStores", "slActionsStores.action_id = slActionsProducts.action_id AND slActionsStores.store_id = " . $store_id);
            }
            $criteria = array(
                "slActions.global:=" => 1,
                "FIND_IN_SET({$region}, REPLACE(REPLACE(REPLACE(slActions.regions, '\"', ''),'[', ''),']','')) > 0"
            );
            if($city){
                $criteria[] = "FIND_IN_SET({$city['id']}, REPLACE(REPLACE(REPLACE(slActions.cities, '\"', ''),'[', ''),']','')) > 0";
            }
            $query->where($criteria, xPDOQuery::SQL_OR);
            if($store_id){
                // TODO: добавить проверку на участников
                $query->where(array(
                    "slActions.store_id:=" => $store_id,
                    "OR:slActionsStores.active:=" => 1
                ));
            }
            $query->where(array(
                "slActions.date_from:<=" => date('Y-m-d H:i:s'),
                "slActions.date_to:>=" => date('Y-m-d H:i:s'),
                "slActions.active:=" => 1,
                "slActionsProducts.product_id:=" => $product_id
            ), xPDOQuery::SQL_AND);

            $query->select(array("slActions.*,slActionsProducts.*"));
            $query->prepare();
            if($product_id == 4571){
                $this->modx->log(1, $query->toSQL());
            }
            if($query->prepare() && $query->stmt->execute()){
                $results = $query->stmt->fetch(PDO::FETCH_ASSOC);
                if($results){
                    if($results["force"]){
                        return $results;
                    }else{
                        return array(
                            "old_price" => $results['old_price']
                        );
                    }
                }
                return array();
            }
        }
    }


    /**
     * Берем остатки
     *
     * @param $type
     * @param $object_id
     * @param $product_id
     * @param $count
     * @return array|false
     */

	public function getRemains($type, $object_id, $product_id, $count = 1){
        $query = $this->modx->newQuery("slStoresRemains");
        $query->leftJoin("slStores", "slStores", "slStores.id = slStoresRemains.store_id");
        $query->where(array(
            "product_id:=" => $product_id,
            "AND:available:>=" => 1,
            "AND:price:>" => 0,
            'AND:store_id:=' => $object_id
        ));
        $query->select(array("slStoresRemains.*,slStores.name as store_name,slStores.address as store_address"));
        if($query->prepare() && $query->stmt->execute()){
            $remains = $query->stmt->fetch(PDO::FETCH_ASSOC);
            $tmp = array();
            $action = $this->getSales($product_id, $object_id);
            if($product_id == 7021){
                $this->modx->log(1, print_r($action, 1));
            }
            if($action){
                if($action["new_price"]){
                    $remains["price"] = $action["new_price"];
                }
                if($action["old_price"] && $action["new_price"] != $action["old_price"]){
                    $remains["old_price"] = $action["old_price"];
                }
            }
            $tmp['remains'] = $remains;
            $tmp['object_id'] = $object_id;
            $tmp['type'] = $type;
            return $tmp;
        }
		return false;
	}

	/**
	 * Узнаем про ближайшую отгрузку от дистра, если она будет
	 *
	 * @param $product_id
	 * @param $store_id
	 * @param int $count
	 * @return bool|int|string
	 * @throws Exception
	 */
	public function getNearShipment($product_id, $store_id, $count = 1){
		$links = $this->modx->getCollection('slWarehouseStores', array("store_id" => $store_id));
		$offset = 999;
		if(count($links)){
			foreach($links as $link){
				$off = $this->getNearShipWh($product_id, $count, $link->get('warehouse_id'), $store_id);
				if($off < $offset){
					$offset = $off;
				}
			}
			return $offset;
		}else{
			$this->modx->log(xPDO::LOG_LEVEL_ERROR, '[shopLogistic] Store link with warehouse not found.');
			return 10;
		}
	}

    /**
     * Проверяем ближайшие отгрузки
     *
     * @param $whs_id
     * @param $store_id
     * @param $create
     * @return array
     * @throws Exception
     */
    public function findNearShipment($whs_id = 0, $store_id = 0, $create = 1){
        if($whs_id && $store_id) {
            $ship = array();
            $newDate = new DateTime();
            $interval = 'P1D';
            $newDate->add(new DateInterval($interval));
            $newDate->setTime(0, 0, 0);
            $date = $newDate->format('Y-m-d H:i:s');
            $query = $this->modx->newQuery("slWarehouseShipment");
            $query->leftJoin('slWarehouseShip', 'slWarehouseShip', '`slWarehouseShipment`.`ship_id` = `slWarehouseShip`.`id`');
            $query->where(array(
                "date:>=" => $date,
                "store_id" => $store_id
            ));
            $query->sortby('date', 'ASC');
            // $query->prepare();
            // $this->modx->log(1, print_r($query->toSQL(), 1));
            $obj = $this->modx->getObject("slWarehouseShipment", $query);
            if ($obj) {
                // $this->modx->log(1, print_r($obj->toArray(), 1));
                $nowDate = new DateTime();
                $newDate = new DateTime($obj->get('date'));
                $newDate->add(new DateInterval('P1D'));
                $interval = $nowDate->diff($newDate);
                $offset = $interval->format('%a');
                $ship = $obj->toArray();
            } else {
                // если вдруг отгрузка не назначена берем 10 дней и создаем отгрузку
                $offset = 10;
                if ($create) {
                    // создаем отгрузку
                    $start = new DateTime();
                    $start->add(new DateInterval('P10D'));
                    $start->setTime(00, 00);

                    $ship = $this->modx->newObject('slWarehouseShip');
                    $ship->set("warehouse_id", $whs_id);
                    $ship->set("store_ids", $store_id);
                    $ship->set("timing", '');
                    $ship->set("date_from", $start->format('Y-m-d H:i:s'));
                    $ship->set("date_to", $start->format('Y-m-d H:i:s'));
                    $ship->set("createdon", time());
                    $ship->set("active", 1);
                    $ship->save();
                    if ($ship->get('id')) {
                        $shipping = $this->modx->newObject("slWarehouseShipment");
                        $shipping->set("ship_id", $ship->get('id'));
                        $shipping->set("store_id", $store_id);
                        $shipping->set("date", $start->format('Y-m-d H:i:s'));
                        if ($shipping->save()) {
                            $ship = $shipping->toArray();
                        }
                    }
                }
            }
            return $ship;
        }
    }

    /**
     * Смотрим ближайшие отгрузки и смещения по товарам
     *
     * @param $product_id
     * @param $count
     * @param $whs_id
     * @param $store_id
     * @return int|string
     * @throws Exception
     */
	public function getNearShipWh($product_id, $count = 1, $whs_id = 0, $store_id = 0, $return = 'offsets', $create = 0)
    {
        $ship = array();
        $criteria = array(
            "store_id" => $whs_id,
            "id" => $product_id
        );
        $remain = $this->modx->getObject('slStoresRemains', $criteria);
        if ($remain) {
            $available = $remain->get("remains");
            if ($available >= $count) {
                // если есть необходимое количество товара у дистра, доставка возможна на след рабочий день
                $newDate = new DateTime();
                $interval = 'P1D';
                $newDate->add(new DateInterval($interval));
                $newDate->setTime(0, 0, 0);
                $date = $newDate->format('Y-m-d H:i:s');
                $query = $this->modx->newQuery("slWarehouseShipment");
                $query->leftJoin('slWarehouseShip', 'slWarehouseShip', '`slWarehouseShipment`.`ship_id` = `slWarehouseShip`.`id`');
                $query->where(array(
                    "date:>=" => $date,
                    "store_id" => $store_id
                ));
                $query->sortby('date', 'ASC');
                // $query->prepare();
                // $this->modx->log(1, print_r($query->toSQL(), 1));
                $obj = $this->modx->getObject("slWarehouseShipment", $query);
                if ($obj) {
                    // $this->modx->log(1, print_r($obj->toArray(), 1));
                    $nowDate = new DateTime();
                    $newDate = new DateTime($obj->get('date'));
                    $newDate->add(new DateInterval('P1D'));
                    $interval = $nowDate->diff($newDate);
                    $offset = $interval->format('%a');
                    $ship = $obj->toArray();
                } else {
                    // если вдруг отгрузка не назначена берем 10 дней и создаем отгрузку
                    $offset = 10;
                    if ($create) {
                        // создаем отгрузку
                        $start = new DateTime();
                        $start->add(new DateInterval('P10D'));
                        $start->setTime(00, 00);

                        $ship = $this->modx->newObject('slWarehouseShip');
                        $ship->set("warehouse_id", $whs_id);
                        $ship->set("store_ids", $store_id);
                        $ship->set("timing", '');
                        $ship->set("date_from", $start->format('Y-m-d H:i:s'));
                        $ship->set("date_to", $start->format('Y-m-d H:i:s'));
                        $ship->set("createdon", time());
                        $ship->set("active", 1);
                        $ship->save();
                        if ($ship->get('id')) {
                            $shipping = $this->modx->newObject("slWarehouseShipment");
                            $shipping->set("ship_id", $ship->get('id'));
                            $shipping->set("store_id", $store_id);
                            $shipping->set("date", $start->format('Y-m-d H:i:s'));
                            if ($shipping->save()) {
                                $ship = $shipping->toArray();
                            }
                        }
                    }
                }
            }else{
                // остаток 0, закладываем 999 дней?
                $offset = 999;
            }
        } else {
            // остаток 0, закладываем 999 дней?
            $offset = 999;
        }
        if ($return == 'offsets') {
            return $offset;
        } else {
            return $ship;
        }
    }

    /**
     * Ищем остаток у ближайшего оптовика, которы может доставить с помощью ТК в магазин
     *
     * @param $product_id
     * @param $count
     * @return false
     */
    public function getRemainsWarehouseTK($product_id, $count = 1){
        $location = $this->sl->getLocationData('web');
        $lat = $location['store']['lat'];
        $lng = $location['store']['lng'];
        $query = $this->modx->newQuery("slStoresRemains");
        $query->leftJoin("slStores", "slStores", "slStores.id = slStoresRemains.store_id");
        $query->where(array(
            'slStores.delivery_tk:=' => 1,
            'AND:slStoresRemains.product_id:=' => $product_id,
            'AND:slStoresRemains.remains:>=' => $count,
            "AND:slStores.active:=" => 1
        ));
        $query->select(array("slStores.*,
			(
			   6371 *
			   acos(cos(radians({$lat})) * 
			   cos(radians(lat)) * 
			   cos(radians(lng) - 
			   radians({$lng})) + 
			   sin(radians({$lat})) * 
			   sin(radians(lat)))
			) AS distance"));
        $query->sortby("distance", "ASC");
        $query->prepare();
        echo $query->toSQL();
        if($query->prepare() && $query->stmt->execute()){
            $store = $query->stmt->fetch(PDO::FETCH_ASSOC);
            return $store;
        }
        return false;
    }

    /**
     * Ищем остаток у магазина
     *
     * @param $product_id
     * @param $count
     * @param $store_id
     * @return array|false
     */
	public function getStoreRemain($product_id, $count = 1, $store_id = 0){
        $query = $this->modx->newQuery("slStoresRemains");
        $query->leftJoin("slStores", "slStores", "slStores.id = slStoresRemains.store_id");
        $query->where(array(
            "slStoresRemains.store_id:=" => $store_id,
            'AND:slStoresRemains.product_id:=' => $product_id,
            'AND:slStoresRemains.remains:>=' => $count,
            "AND:slStores.active:=" => 1
        ));
        $query->select(array("slStoresRemains.*"));
        if($query->prepare() && $query->stmt->execute()){
            $remain = $query->stmt->fetch(PDO::FETCH_ASSOC);
            return $remain;
        }
        return false;
	}

    /**
     * Узнаем сроки и стоимость доставки до магазина при самовывозе (в том числе учитывая отгрузки оптовика)
     *
     * @param $product_id
     * @param $count
     * @param $from_id
     * @return array|false
     * @throws Exception
     */
	public function getPickupPrice($product_id, $type = "slStores", $count = 1, $from_id = 0){
		$delivery_data = array();
        $location = $this->sl->getLocationData('web');
		if(!$from_id || $type == "slWarehouse"){
			$from['store'] = $location['store'];
		}else{
			$from['store'] = $this->sl->store->getStore($from_id);
		}
        $city = $this->modx->getObject("dartLocationCity", $from['store']['city']);
        if($city){
            $city_data = $city->toArray();
            // $this->modx->log(1, print_r($city_data, 1));
            // $this->modx->log(1, print_r($location, 1));
            if($city_data['properties']['postal_code'] == $location["postal_code"]){
                $remain = $this->getStoreRemain($product_id, $count, $from['store']['id']);
                if($remain){
                    // если нашли возращаем остаток
                    $delivery_data['pickup']['store'] = $from['store'];
                    $delivery_data['pickup']['price'] = 0;
                    $delivery_data['pickup']['term'] = 'сегодня';
                    $delivery_data['pickup']['term_default'] = 0;

                    /*
                    if($type == 'slStores'){

                    }else{
                        // меняем адрес самовывоза
                        $from['store']['address'] = $loc['data']['store']['address'];
                        if($from['store']['delivery_tk']){
                            // проверем склад, если стоит галка "Доставка ТК", игнорируем поле отгрузок
                            // считаем доставку ТК
                            // $this->modx->log(1, $product_id.', 1, slWarehouse, '.$from['store']['id'].', '.$loc['data']['store']['id']);
                            $delivery_data = $this->getTKPrice($product_id, 1, 'slWarehouse', $from['store']['id'], $loc['data']['store']['id']);
                            $delivery_data['pickup'] = $delivery_data['delivery'];
                            $delivery_data['pickup']['store'] = $from['store'];
                            unset($delivery_data['delivery']);
                        }else{

                        }
                    }*/
                    return $delivery_data;
                }else{
                    // ищем у поставщиков, в приоритете поставщики, подключенные к магазину
                    // смотрим отгрузки
                    $warehouses = $this->sl->store->getWarehouses($from['store']['id']);
                    $shipment = 999;
                    foreach($warehouses as $warehouse){
                        $ship = $this->getNearShipWh($product_id, $count, $warehouse['id'], $from['store']['id']);
                        // $this->modx->log(1, $ship);
                        if($ship < $shipment){
                            $newDate = new DateTime();
                            $interval = 'P'.$ship.'D';
                            $newDate->add(new DateInterval($interval));
                            $delivery_data['pickup']['term_default'] = $newDate->format('Y-m-d H:i:s');
                            $delivery_data['pickup']['term'] = $newDate->format('Y-m-d H:i:s');
                            $delivery_data['pickup']['price'] = 0;
                            $delivery_data['pickup']['store'] = $from['store'];
                            // $this->modx->log(1, print_r($delivery_data, 1));
                        }
                    }

                    if($shipment == 999){
                        // смотрим поставщиков, которые могу доставить с помощью ТК
                        // TODO: прописать условия
                    }
                    return $delivery_data;
                }
            }
        }
		return false;
	}

	/**
	 * Считаем доставку транспортной компанией
	 *
	 * @param $product_id
	 * @param int $count
	 * @param string $type
	 * @param int $from_id
	 * @param int $to_id
	 */
	public function getTKPrice($product_id, $count = 1, $type = 'slStores', $from_id = 0, $to_id = 0){
		$loc = $this->getUserPosition('web');
		if(!$from_id){
			$from_id = $loc['data']['store']['id'];
		}
		$remain = $this->getStoreRemain($product_id, $count, $from_id);
        // TODO: перебивается значение типа
        $type = 'slStores';
		// print_r($remain);
		if($remain){
            $city = $this->getCityByFiasId($loc['data']['city_fias_id']);
            if(!$city){
                $city['id'] = 0;
            }
            $times = $this->sl->store->getWorkWithTimezones($from_id);
            if($times) {
                if ($times['work']['time_to']) {
                    $to = array(
                        "hour" => $times['work']['time_to']->format("H"),
                        "minute" => $times['work']['time_to']->format("i")
                    );
                    $today = array(
                        "hour" => $times['work']['today']->format("H"),
                        "minute" => $times['work']['today']->format("i")
                    );
                    if (in_array($city['id'], $this->sl->evening->config["cities"]) && $to["hour"] >= 19) {
                        $price = $this->sl->evening->getPrice($from_id, 0, array());
                        // $this->modx->log(1, print_r($price, 1));
                        if ($price) {
                            if ($today["hour"] > 16 || ($today["hour"] == 16 && $today["minute"] >= 30)) {
                                $price['term'] = $price['term'];
                            } else {
                                $price['term'] = $price['term'] - 1;
                            }
                            return array('delivery' => $price);
                        }
                    }
                }
            }
			// $sdek_price = $this->getSdekPrice($product_id, $count, $type, $from_id, $to_id);
			// $yandex_price = $this->getYaDeliveryPrice($product_id, $count, $type, $from_id, $to_id);
			$postrf_price = $this->getPostRfPrice($product_id, $count, $type, $from_id, $to_id);
            if(!$postrf_price['delivery']['price']){
                $products = array($product_id);
                $store = $this->modx->getObject("slStores", $from_id);
                if($store){
                    $city_id = $store->get("city");
                    $city = $this->modx->getObject("dartLocationCity", $city_id);
                    if($city){
                        // $prods = $this->sl->cdek->prepareProducts($products);
                        $out = $this->sl->cdek->getCalcPrice($city->get("postal_code"), $loc['data']['postal_code'], $products);
                        $offset = $out['door']['time']? : 7;
                        $offset = $offset + 2;
                        $interval = 'P'.$offset.'D';
                        $newDate = new DateTime();
                        $newDate->add(new DateInterval($interval));
                        $postrf_price['delivery']['price'] = $out['door']['price'];
                        $postrf_price['delivery']['term_default'] = $newDate->format('Y-m-d H:i:s');
                        $postrf_price['delivery']['term'] = $newDate->format('Y-m-d H:i:s');
                        $postrf_price['delivery']['service'] = 'СДЭК';
                        // $sdek_price = $this->sl->getCalcPrice($product_id, $count, $type, $from_id, $to_id);
                        // $this->modx->log(1, "СДЭК СЮДА!!! ".print_r($sdek_price, 1));
                        // return $sdek_price;
                    }
                }
            }
            $this->modx->log(1, "СДЭК СЮДА!!! ".print_r($postrf_price, 1));
            return $postrf_price;
		}
		return false;
	}

	public function getPostRfPrice($product_id, $count = 1, $type = 'slStores', $from_id = 0, $to_id = 0){
		// $this->modx->log(1, $product_id.', '.$count.', '.$type.', '.$from_id.', '.$to_id);
		$delivery_data = array();
		$loc = $this->getUserPosition('web');
        // $this->modx->log(1, print_r($loc, 1));
		if($to_id){
			$object = $this->sl->getObject($to_id);
			if($object){
				$properties = $this->getCityData($object['city']);
				if($properties){
					$to = $properties['postal_code'];
				}else{
					return false;
				}
			}else{
				return false;
			}
		}else{
			$to = $loc['data']['postal_code'];
		}
		if($from_id){
			$object = $this->sl->getObject($from_id, $type);
            //$this->modx->log(1, print_r($object, 1));
			$properties = $this->getCityData($object['city']);
            //$this->modx->log(1, print_r($properties, 1));
			$from = $properties['postal_code'];
		}else{
			$object = $this->sl->getObject($loc['data']['store']['id'], $type);
			$properties = $this->getCityData($object['city']);
            //$this->modx->log(1, print_r($properties, 1));
			$from = $properties['postal_code'];
		}
		$prs = array(
			0 => array(
				"id" => $product_id,
				"count" => $count,
				"price" => 0
			)
		);
		//echo $from.' '.$to.' ';
		// fix
		//$to = $to + 10;
		//$from = $from + 10;
        // $this->modx->log(1, $to.' '.$from.' '.print_r($prs, 1));
		$arr = $this->sl->postrf->getPrice($from, $to, $prs, 0);
		// $this->modx->log(1, print_r($arr, 1));
		$delivery_data['delivery']['price'] = round($arr['terminal']['price']);
		$newDate = new DateTime();
		// Смещение относительно доставки
		if($type == 'slStores'){
            // проверим остаток на складе
            $remains = $this->getStoreRemain($product_id, 1, $from_id, 'slStores');
            // $this->modx->log(1, $loc['data']['store']['id'].' '.print_r($remains, 1));
            if(!$remains){
                // иначе
                $offset = $this->getNearShipment($product_id, $count, $loc['data']['store']['id']);
            }else{
                $offset = 0;
            }
		}
		// TODO: решить door или terminal
		if($offset){
			$offset = $offset + $arr['door']['time'];
		}else{
			$offset = $arr['door']['time'];
		}
        // $this->modx->log(1, $offset);
		$interval = 'P'.$offset.'D';
		$newDate->add(new DateInterval($interval));
		$delivery_data['delivery']['term_default'] = $newDate->format('Y-m-d H:i:s');
		$delivery_data['delivery']['term'] = $newDate->format('Y-m-d H:i:s');
		$delivery_data['delivery']['service'] = 'Почта России';
		return $delivery_data;
	}

	public function getYaDeliveryPrice($product_id, $count = 1, $type = 'slStores', $from_id = 0, $to_id = 0){
		$this->sl->esl = new eShopLogistic($this->sl, $this->modx);
		$delivery_data = array();
		$position = $this->getUserPosition('web');
		if($to_id){
			$object = $this->sl->getObject($to_id);
			if($object){
				$properties = $this->getCityData($object['city']);
				if($properties){
					$to = array(
						(float)$properties['geo_lon'],
						(float)$properties['geo_lat']
					);
				}else{
					return false;
				}
			}else{
				return false;
			}
		}else{
			$to = array(
				(float)$position['data']['geo_lon'],
				(float)$position['data']['geo_lat']
			);
		}
		if($from_id){
			$object = $this->sl->getObject($from_id);
		}else{
			$object = $this->sl->getObject($position['data']['store']['id']);
		}
		$from = array(
			(float)$object['lng'],
			(float)$object['lat']
		);
        //$this->modx->log(1, print_r($from, 1));
        //$this->modx->log(1, print_r($to, 1));
		$arr = $this->sl->esl->getYandexDeliveryPrice($product_id, $from, $to);
		// проверяем наличие Я.Доставки
		if($arr){
			$delivery_data['delivery']['price'] = round($arr['price']);
			$newDate = new DateTime();
			$delivery_data['delivery']['term_default'] = $newDate->format('Y-m-d H:i:s');
            $times = $this->sl->store->getWorkWithTimezones($from_id);
            if($times) {
                if ($times['work']['time_to']) {
                    $to = array(
                        "hour" => $times['work']['time_to']->format("H"),
                        "minute" => $times['work']['time_to']->format("i")
                    );
                    $today = array(
                        "hour" => $times['work']['today']->format("H"),
                        "minute" => $times['work']['today']->format("i")
                    );
                    if ($to["hour"] >= 19) {
                        if ($today["hour"] > 16 || ($today["hour"] == 16 && $today["minute"] >= 30)) {
                            $delivery_data['delivery']['term'] = 1;
                        }else{
                            $delivery_data['delivery']['term'] = 0;
                        }
                    }else{
                        $delivery_data['delivery']['term'] = 1;
                    }
                }
            }

			$delivery_data['delivery']['service'] = 'Яндекс.Доставка';
			return $delivery_data;
		}
		return false;
	}

	/**
	 * WILL BE DEPRECATED
	 * TODO: решить вопрос с кешированием ID городов
	 * @param $product_id
	 * @param int $count
	 * @param string $type
	 * @param int $from_id
	 * @param int $to_id
	 * @return array|bool
	 * @throws Exception
	 */
	public function getSdekPrice($product_id, $count = 1, $type = 'slStores', $from_id = 0, $to_id = 0){
		$this->sl->esl = new eShopLogistic($this->sl, $this->modx);
		$from = false;
		$to = false;
		$delivery_data = array();
		$position = $this->getUserPosition();
		if($to_id){
			$object = $this->sl->getObject($to_id);
			if($object){
				$properties = $this->getCityData($object['city']);
				if($properties){
					$to = $properties['city_fias_id']? : $properties['fias_id'];
					$to_data = array(
						"target" => $to
					);
					$resp = $this->sl->esl->query("search", $to_data);
					$to = $resp['data'][0]['services']['sdek'];
				}else{
					return false;
				}
			}else{
				return false;
			}
		}else{
			$to_data = array(
				"target" => $position['data']['city_fias_id']? : $position['data']['fias_id']
			);
			$resp = $this->sl->esl->query("search", $to_data);
			$to = $resp['data'][0]['services']['sdek'];
		}
		if($from_id){
			$object = $this->sl->getObject($from_id, $type);
			$city = $object['city'];
		}else{
			$city = $position['data']['store']['city'];
		}
		$properties = $this->getCityData($city);
		if($properties){
			$from = $properties['city_fias_id']? : $properties['fias_id'];
			$from_data = array(
				"target" => $from
			);
			$resp = $this->sl->esl->query("search", $from_data);
			$from = $resp['data'][0]['services']['sdek'];
		}
		$sdek_data = array(
			"from" => $from,
			"to" => $to,
		);
		$products = $this->sl->product->getProductParams($product_id);
		$sdek_data['offers'] = json_encode($products, JSON_UNESCAPED_UNICODE);
		$resp = $this->sl->esl->query("delivery/sdek", $sdek_data);
		if($resp['success']){
			$delivery_data['pvz']['price'] = $resp['data']['terminal']['price'];
			$newDate = new DateTime();
			$d = explode("-", $resp['data']['terminal']['time']);
			$days = (int) preg_replace('/[^0-9]/', '', $d[0]);
			$interval = 'P'.$days.'D';
			$newDate->add(new DateInterval($interval));
			$delivery_data['pvz']['term_default'] = $newDate->format('Y-m-d H:i:s');
			$delivery_data['pvz']['term'] = $newDate->format('Y-m-d H:i:s');
			$delivery_data['pvz']['service'] = 'СДЭК';
			return $delivery_data;
		}else{
			return false;
		}
	}

	public function getCityData($id){
        $city = $this->modx->getObject("dartLocationCity", $id);
        if($city) {
            $properties = $city->get("properties");
            if($city->get("postal_code")){
                $properties["postal_code"] = $city->get("postal_code");
            }
            return $properties;
        }
	}

    public function getCityByFiasId($fias_id){
        $city = $this->modx->getObject("dartLocationCity", array("fias_id" => $fias_id));
        if($city) {
            $properties = $city->get("properties");
            if($city->get("postal_code")){
                $properties["postal_code"] = $city->get("postal_code");
            }
            return $city->toArray();
        }
    }


    /**
     * Считаем стоимость доставки определенной ТК
     *
     * @param $data
     * @return array
     */
	public function getDeliveryPrice($data){
		$services = array("main_key" => $data['service']);
		$data['location'] = json_decode($data['address'], 1);
        $city = $this->getCityByFiasId($data['location']['city_fias_id']);
        if(!$city){
            $city['id'] = 0;
        }
        $this->modx->log(1, print_r($city, 1));
		$this->modx->log(1, print_r($data, 1));
        // чекаем индекс на всякий случай
        $postrf_data = array_values($this->sl->postrf->getNearPVZ($data['location']['geo_lon'], $data['location']['geo_lat']));
        // $this->modx->log(1, print_r($postrf_data, 1));
        if($postrf_data){
            $data['location']['postal_code'] = $postrf_data[0]['postal-code'];
        }
        $this->modx->log(1, print_r($data, 1));
		if ($data) {
			$offset = $this->getDeliveryDateOffset('cart');
			$cart = $this->checkCart();
			if (in_array($data['service'], $this->config['our_services'])) {
                // $this->modx->log(1, "Наша система");
				if($data['service'] == 'yandex'){
                    $this->modx->log(1, print_r($cart, 1));
					if($offset){
						$days = $this->sl->tools->decl($offset, "день|дня|дней", true);
					}else{
						$days = "сегодня";
					}
					$services['yandex'] = false;
					foreach($cart as $item){
                        $all_price = 0;
						// магазины считаем сразу
						if(isset($item['type']) || $item['type'] == 'slStores'){
							$data['cart'] = $item;
							$ya_data = $this->sl->yandex->getYaDeliveryPrice('cart', 0, $data);
							if(isset($ya_data['price'])){
								// складываем цену
								if(isset($services['yandex']['door']['price'])){
									$services['yandex']['door']['price'] = round($services['yandex']['door']['price'] + $ya_data['price']);
								}else{
                                    $services['yandex'] = array(
                                        "price" => array(
                                            "door" => array(
                                                "price" => round($ya_data['price']),
                                                "time" => $days,
                                            )
                                        )
                                    );
                                }
                                $all_price += $ya_data['price'];
                                // ADD price
                                $services['delivery'][$item['object']]['yandex'] = round($all_price);

							}else{
								$services['yandex'] = false;
							}
						}else{
                            /*
                            // TODO: will be corrected
							// если у нас склад, отправляем пока отправляем как из магазина
							$data['cart'] = $item;
							$ya_data = $this->sl->yandex->getYaDeliveryPrice('cart', 0, $data);
							if(isset($ya_data['price'])){
								// складываем цену
								if(isset($services['yandex']['door']['price'])){
									$services['yandex']['door']['price'] = round($services['yandex']['door']['price'] + $ya_data['price']);
								}
								$services['yandex'] = array(
									"price" => array(
										"door" => array(
											"price" => round($ya_data['price']),
											"time" => $days
										)
									)
								);
							}else{
								$services['yandex'] = false;
							}
                            */
						}
					}
				}
				if($data['service'] == 'postrf'){
					foreach($cart as $item) {
                        // $this->modx->log(1, print_r($item, 1));
						$object = $this->modx->getObject($item['type'], $item['object']);
						if($object) {
							$city = $object->getOne("City");
							if ($city) {
								$properties = $city->get('properties');
								// $this->modx->log(1, print_r($properties, 1));
								if($properties['postal_code']){
									$out = $this->sl->postrf->getPrice($properties['postal_code'], $data['location']['postal_code'], $item['products']);
                                    // $out = $this->sl->postrf->getPrice($properties['postal_code'], $data['location']['postal_code'], $item['products'], 0);
                                    $this->modx->log(1, print_r($out, 1));
									$s = array('door', 'terminal');
									foreach($s as $key){
										if(isset($services['postrf']['price'][$key]['price'])){
											$services['postrf']['price'][$key]['price'] += round($out[$key]['price']);
                                            $services['postrf']['price'][$key]['time'] = $this->sl->num_word($out[$key]['time'], array('день', 'дня', 'дней'), 1);
										}else{
											$services['postrf']['price'][$key]['price'] = round($out[$key]['price']);
                                            $services['postrf']['price'][$key]['time'] = $this->sl->num_word($out[$key]['time'], array('день', 'дня', 'дней'), 1);
										}
                                        $services['delivery'][$item['object']]['postrf'][$key] = array(
                                            'price' => round($out[$key]['price']),
                                            'time' => $this->sl->num_word($out[$key]['time'], array('день', 'дня', 'дней'), 1)
                                        );
									}
								}
							}
						}
					}
				}
                if($data['service'] == 'cdek'){
                    // $this->modx->log(1, print_r($cart, 1));
                    unset($cart['stores']);
                    foreach($cart as $item) {
                        if($item['object']){
                            $object = $this->modx->getObject("slStores", $item['object']);
                            if ($object) {
                                $city = $object->getOne("City");
                                if ($city) {
                                    $properties = $city->get('properties');
                                    if ($properties['postal_code']) {
                                        $products = array();
                                        foreach ($item['products'] as $product) {
                                            // TODO: учесть кол-во
                                            $tmp = array(
                                                "id" => $product['id'],
                                                "count" => $product['count']
                                            );
                                            $products[] = $product['id'];
                                        }
                                        $prods = $this->sl->cdek->prepareProducts($products);
                                        $out = $this->sl->cdek->getCalcPrice($properties['postal_code'], $data['location']['postal_code'], $prods);
                                        $s = array('door', 'terminal');
                                        foreach($s as $key){
                                            if(isset($services['cdek']['price'][$key]['price'])){
                                                $services['cdek']['price'][$key]['price'] += $out[$key]['price'];
                                                $services['cdek']['price'][$key]['time'] = $this->sl->num_word($out[$key]['time'], array('день', 'дня', 'дней'), 1);
                                            }else{
                                                $services['cdek']['price'][$key]['price'] = $out[$key]['price'];
                                                $services['cdek']['price'][$key]['time'] = $this->sl->num_word($out[$key]['time'], array('день', 'дня', 'дней'), 1);
                                            }
                                            $services['delivery'][$item['object']]['cdek'][$key] = array(
                                                'price' => $out[$key]['price'],
                                                'time' => $this->sl->num_word($out[$key]['time'], array('день', 'дня', 'дней'), 1)
                                            );
                                        }
                                        if($out['terminals']){
                                            $services['cdek']['price']['terminals'] = $out['terminals'];
                                        }
                                        // $this->modx->log(1, print_r($out, 1));
                                    }else{
                                        // $this->modx->log(1, print_r($properties, 1));
                                    }
                                }else{
                                    // $this->modx->log(1, "Не нашел город '{$item['object']}' '{$item['type']}'");
                                }
                            }else{
                                // $this->modx->log(1, "Не нашел объект '{$item['object']}' '{$item['type']}'");
                            }
                        }else{
                            // $this->modx->log(1, "Пустые значения: '{$item['object']}' '{$item['type']}'");
                        }

                    }
                }
                if($data['service'] == 'evening'){
                    if(in_array($city['id'], $this->sl->evening->config["cities"])) {
                        foreach ($cart as $item) {
                            if ($item['object']) {
                                $price = $this->sl->evening->getPrice(0, 0, $data);
                                $services['evening']['price']['door']['price'] += $price['price'];
                                if($price['offset'] > 1 && $price['offset'] != 0){
                                    $days = $this->sl->tools->decl($offset, "день|дня|дней", true);
                                }else{
                                    $days = "сегодня";
                                }
                                $services['evening']['price']['door']['time'] = $days;
                                $services['delivery'][$item['object']]['evening']['door'] = array(
                                    'price' => $price
                                );
                                $services['delivery'][$item['object']]['evening']['door']['time'] = $days;
                            }
                        }
                    }else{
                        $services['evening']['price']['door']['price'] = 0;
                        $services['evening']['price']['door']['time'] = 0;
                        $services['evening']['price']['terminal']['price'] = 0;
                        $services['evening']['price']['terminal']['time'] = 0;
                    }
                }
			}else {
                // $this->modx->log(1, "Не наша система");
				$this->esl = new eShopLogistic($this->sl, $this->modx);
				$init = $this->esl->init();
				$search_data = array(
					"target" => $data['location']['city_fias_id']? : $data['location']['fias_id']
				);
				$to_resp = $this->sl->esl->query("search", $search_data);
				//$this->modx->log(1, print_r($to_resp, 1));
				foreach($cart as $item){
					$esl_data = array();
					$object = $this->modx->getObject($item['type'], $item['object']);
					if($object){
						$city = $object->getOne("City");
						if($city){
							$fias_id = $city->get("fias_id");
							$search_data = array(
								"target" => $fias_id
							);
                            $this->modx->log(1, print_r($search_data, 1));
							$from_resp = $this->esl->query("search", $search_data);
                            $this->modx->log(1, print_r($from_resp, 1));
							foreach ($init['data']['services'] as $key => $val) {
								$tmp = array(
									"name" => $val["name"],
									"from" => $from_resp["data"][0]["services"][$key],
									"to" => $to_resp["data"][0]["services"][$key],
									"logo" => $val["logo"]
								);
								$svrs[$key] = $tmp;
							}
							$cart = $this->prepareProductsList('eshoplogistic', $item['products']);
							$esl_rdata = array(
								"from" => $svrs[$data['service']]['from'],
								"to" => $svrs[$data['service']]['to']? : $data['location']['fias_id'],
							);
							$esl_rdata['offers'] = json_encode($cart['offers']);
							// $this->modx->log(1, print_r($data['service'], 1));
							// $this->modx->log(1, print_r($esl_rdata, 1));
							$resp = $this->sl->esl->query("delivery/" . $data['service'], $esl_rdata);
							$this->modx->log(1, print_r($resp, 1));
							$types = array('terminal','door');
							foreach($types as $type){
								$d = explode("-", $resp['data'][$type]['time']);
								$days = (int) preg_replace('/[^0-9]/', '', $d[0]) + 1 + $offset;
								if($services[$data['service']]['price'][$type]['time'] < $days){
									$services[$data['service']]['price'][$type]['time'] = $this->sl->num_word($days, array('день', 'дня', 'дней'), 1);
								}
								if(isset($services[$data['service']]['price'][$type]['price'])){
									$services[$data['service']]['price'][$type]['price'] = $services[$data['service']][$type]['price'] + $resp['data'][$type]['price'];
								}else{
									$services[$data['service']]['price'][$type]['price'] = $resp['data'][$type]['price'];
								}
							}
							$services[$data['service']]['price']['terminals'] = $resp['data']['terminals'];
						}
					}
				}
			}
		}
		$this->modx->log(1, print_r($services, 1));
		return $services;
	}

    /**
     * Собираем информацию о заказе
     *
     * @param $order_id
     * @return array
     */
    public function getOrderData($order_id){
        $order = $this->modx->getObject("slOrder", $order_id);
        if($order){
            $result['order'] = $order->toArray();
            $store_id = $order->get("store_id");
            $store = $this->modx->getObject("slStores", $store_id);
            if($store){
                $result['store'] = $store->toArray();
            }
            $ms_order_id = $order->get("order_id");
            $msOrder = $this->modx->getObject("msOrder", $ms_order_id);
            if($msOrder){
                $result['ms_order'] = $msOrder->toArray();
                $msAddress = $msOrder->getOne('Address');
                $result['address'] = $msAddress->toArray();
                $msUserProfile = $msOrder->getOne('UserProfile');
                $result['user'] = $msUserProfile->toArray();
                $msStatus = $msOrder->getOne('Status');
                $result['status'] = $msStatus->toArray();
                $msDelivery = $msOrder->getOne('Delivery');
                $result['delivery'] = $msDelivery->toArray();
                $msPayment = $msOrder->getOne('Payment');
                $result['payment'] = $msPayment->toArray();
            }
            $q = $this->modx->newQuery('slOrderProduct');
            $q->leftJoin('msProductData', 'msProductData', 'msProductData.id = slOrderProduct.product_id');
            $q->select(array(
                'slOrderProduct.*',
                'msProductData.*'
            ));
            $q->where(array(
                'order_id' => $order_id
            ));
            if($q->prepare() && $q->stmt->execute()){
                $products = $q->stmt->fetchAll(PDO::FETCH_ASSOC);
                $result['products'] = $products;
            }
        }
        return $result;
    }

    /**
     * Берем ID сделки
     *
     * @param $crm_id
     * @return void
     */
    public function getStageId($crm_id){
        $st = $this->modx->getObject("slCRMStage", array("crm_id" => $crm_id));
        if($st){
            return $st->toArray();
        }
        return false;
    }

    /**
     * Изменение стадии заказа (сделки)
     *
     * @param $order_id
     * @param $stage_id
     * @return void
     */
    public function changeOrderStage($order_id, $stage_id, $deal_update = true){
        $order = $this->modx->getObject("slOrder", array("id" => $order_id));
        if($order){
            $order_data = $this->getOrderData($order_id);
            $st = $this->modx->getObject("slCRMStage", array("id" => $stage_id));
            if($st){
                $order->set('status', $st->get("id"));
                $order->save();
                // обновляем сделку, если необходимо
                if($deal_update){
                    $this->sl->b24->initialize();
                    $data = array(
                        "STAGE_ID" => $st->get('crm_id')
                    );
                    $crm_id = $order->get('crm_id');
                    $response = $this->sl->b24->updateDeal($crm_id, $data);
                }
                // генерируем код выдачи, если необходимо
                if($st->get("check_code")){
                    /*
                    $code = $this->sl->tools->generate_code();
                    $order->set("code", $code['code']);
                    if($code['date_until']){
                        $order->set("code_until", $code['date_until']);
                    }
                    $order->save();
                    */
                }
                // обработка заявки в ТК
                if($st->get("to_tk")){
                    // TODO: предусмотреть обновление заявки в ТК
                    $code = $this->sl->tools->generate_code();
                    $order->set("code", $code['code']);
                    if($code['date_until']){
                        $order->set("code_until", $code['date_until']);
                    }
                    $order->save();
                    $delivery = $order_data["ms_order"]["properties"]["sl"]["key"];
                    // TODO: process shipping order
                    if($delivery == "yandex"){
                        $response = $this->sl->yandex->createRequest($order_data["order"]["id"]);
                        if($response['id']){
                            $order->set("tk_id", $response['id']);
                            $properties = $order->get("properties");
                            $properties["delivery_shipping_data"] = $response;
                            $order->set("properties", $properties);
                            $order->save();
                            $to = $st->get("transition_to");
                            if($to){
                                $order->set('status', $to);
                                $order->save();
                                // обновляем сделку, если необходимо
                                $to_st = $this->modx->getObject("slCRMStage", array("id" => $to));
                                if($to_st){
                                    $this->sl->b24->initialize();
                                    $data = array(
                                        "STAGE_ID" => $to_st->get('crm_id')
                                    );
                                    $crm_id = $order->get('crm_id');
                                    $response = $this->sl->b24->updateDeal($crm_id, $data);
                                }
                            }
                        }
                    }
                }
                // выплата на баланс
                if($st->get("pay")){
                    $tax = $this->modx->getOption('shoplogistic_tax_percent') / 100;
                    $cost = $order_data["order"]["cart_cost"] * (1 - $tax);
                    $store_id = $order_data["store"]['id'];

                    // add log
                    $balance = $this->modx->newObject("slStoreBalance");
                    $balance->set("store_id", $store_id);
                    $balance->set("type", 1);
                    $balance->set("value", $cost);
                    $balance->set("createdon", date('Y-m-d H:i:s'));
                    $balance->set("description", "Начисление за заказ №".$order_data["order"]['num']);
                    $balance->save();

                    //add money to store
                    $store = $this->modx->getObject("slStores", $store_id);
                    if($store){
                        $b = $store->get('balance');
                        $store->set('balance', $b + $cost);
                        $store->save();
                    }
                }
            }
        }
    }

    /**
     * Смена статуса доставки
     *
     * @param $service
     * @param $stage_id
     * @return false|void
     */
    public function setDeliveryStage($tk_id){
        $order = $this->modx->getObject("slOrder", array("tk_id" => $tk_id));
        if($order){
            $order_data = $order->toArray();
            $stage = $this->sl->getObject($order_data["status"], "slCRMStage");
            if($stage){
                $response = $this->changeOrderStage($order_data["id"], $stage["transition_to"], true);
                return $response;
            }
        }
        return false;
    }

	public function prepareProductsList($service, $products){
		$output = array();
		if($service == 'eshoplogistic'){
			foreach($products as $pr) {
				$product = $this->modx->getObject("modResource", $pr['id']);
				if ($product) {
					$par = json_decode($product->getTVValue("delivery_attributes"), true);
					if ($par) {
						foreach ($par as $key => $p) {
							$output['offers'][] = array(
								'article' => $pr['id'],
								'name' => $pr['id'],
								"count" => $pr['count'],
								"dimensions" => $p['dimensions'] ?: '',
								"weight" => $p['weight']
							);
						}
					} else {
						$pos = $this->sl->product->getProductParams($pr['id']);
						$output['offers'][] = array(
							'article' => $pr['id'],
							'name' => $pr['id'],
							"count" => $pr['count'],
							"dimensions" => implode("*", array((float)$pos[0]['length'], (float)$pos[0]['width'], $pos[0]['height'])),
							"weight" => (float)$pos[0]['weight']
						);
					}
				}
			}
		}
		if($service == 'postrf'){
			$all = array();
			foreach($products as $pr) {
				$product = $this->modx->getObject("modResource", $pr['id']);
				$pos = $this->sl->product->getProductParams($pr['id']);
				if ($product) {
					$par = json_decode($product->getTVValue("delivery_attributes"), true);
					if ($par) {
						foreach ($par as $key => $p) {
							$output['offers'][] = array(
								'article' => $pr['id'],
								'name' => $pr['id'],
								"count" => $pr['count'],
								'price' => str_replace(" ", "", $pr['price']),
								"dimensions" => $p['dimensions'] ?: '',
								"weight" => $p['weight']
							);
						}
					} else {
						$output['offers'][] = array(
							'article' => $pr['id'],
							'name' => $pr['id'],
							"count" => $pr['count'],
							'price' => str_replace(" ", "", $pr['price']),
							"dimensions" => implode("*", array((float)$pos[0]['length'], (float)$pos[0]['width'], $pos[0]['height'])),
							"weight" => (float)$pos[0]['weight']
						);
					}
					$output['price'] += ((float)$pos[0]['price'] * $pr['count']);
					$output['weight'] += ((float)$pos[0]['weight'] * $pr['count']);
				}
			}
		}
		return $output;
	}

	public function getDeliveryDateOffset($mode, $id = 0){
		$offset = 0;
		return $offset;
	}

	public function getNearbyWarehouse($product_id){

	}

	public function getLocationData($fias)
	{
		$token = $this->modx->getOption("shoplogistic_api_key_dadata");
		$ch = curl_init('https://suggestions.dadata.ru/suggestions/api/4_1/rs/findById/address');
		$dt = array(
			"query" => $fias
		);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($dt, JSON_UNESCAPED_UNICODE));
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			'Authorization: Token ' . $token,
			'Content-Type: application/json',
			'Accept: application/json'
		));
		$res = curl_exec($ch);
		curl_close($ch);
		$res = json_decode($res, true);
		return $res;
	}
}