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
		$this->config['our_services'] = array('postrf', 'yandex');
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
					$tmp['weight'] = (float)$product['weight']?:(float)$product['weight_netto'];
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

    public function getStoresRemains($product_id, $type){
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
		$cart_mode = $this->modx->getOption('shoplogistic_cart_mode');
		// если режим глобальный, то ищем среди всех магазинов. В данном случае, игнорируются дистрибьюторы.
		if($cart_mode == 1){
			// 1 - проверить все товары в наличии в магазине
			// 2 - все товары у дистрибьютора
			// 3 - отправить магазину по умолчанию
			$product_ids = array();
			$stores_ids = array();
			$warehouse_ids = array();
			// узнаем где товары есть в наличии
			foreach($cart as $key => $item){
				$product_ids[] = $item['id'];
				$criteria = array(
					"product_id:=" => $item['id'],
					"AND:available:>=" => $item['count']
				);
				// остатки магазина
				$remains = $this->modx->getCollection("slStoresRemains", $criteria);
				foreach($remains as $remain){
					$stores_ids[$remain->get('store_id')] = $item['id'];
				}
				// остатки склада
				$remains = $this->modx->getCollection("slWarehouseRemains", $criteria);
				foreach($remains as $remain){
					$warehouse_ids[$remain->get('warehouse_id')] = $item['id'];
				}
			}
			// отсечем лишнее в магазинах
			foreach($stores_ids as $key => $store) {
				if (count($cart) != count($store)) {
					unset($stores_ids[$key]);
				}
			}
			// если магазинов не нашлось ищем у дистров
			if(!count($stores_ids)){
				foreach($warehouse_ids as $key => $warehouse) {
					if (count($cart) != count($warehouse)) {
						unset($warehouse_ids[$key]);
					}
				}
				// если разрешено отправлять к дистру
				if($this->modx->getOption("shoplogistic_cart_to_warehouse") && count($warehouse_ids)){
					$sts = array_keys($warehouse_ids);
					$whs = implode(',', $sts);
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
        			FROM {$this->modx->getTableName('slWarehouse')} WHERE `active` = 1 AND `id` IN ({$whs}) ORDER BY distance LIMIT 999";

					$query = $this->modx->query($sql);
					if($query) {
						$result = $query->fetchAll(PDO::FETCH_ASSOC);
						if($result){
							foreach($cart as $key => $item) {
								$item['key'] = $key;
								$product_data = $this->getProductData($item['id']);
								$item = array_merge($item, $product_data);
								$new_cart['slWarehouse_' . $result[0]['id']]['object'] = $result[0]['id'];
								$new_cart['slWarehouse_' . $result[0]['id']]['type'] = 'slWarehouse';
								$new_cart['slWarehouse_' . $result[0]['id']]['data'] = $result[0];
								$new_cart['slWarehouse_' . $result[0]['id']]['products'][$key] = $item;
							}
						}
					}
				}else{
					$this->modx->log(1, $this->modx->getOption("shoplogistic_default_store"));
					$this->modx->log(1, print_r($warehouse_ids, 1));
					// отправляем в магазин по умолчанию
					$default_store = $this->modx->getOption("shoplogistic_default_store");
					if($default_store){
						$store = $this->modx->getObject("slStores", $default_store);
						if($store){
							$store_d = $store->toArray();
							foreach($cart as $key => $item) {
								$item['key'] = $key;
								$product_data = $this->getProductData($item['id']);
								$item = array_merge($item, $product_data);
								$new_cart['slStores_' . $store_d['id']]['object'] = $store_d['id'];
								$new_cart['slStores_' . $store_d['id']]['type'] = 'slStores';
								$new_cart['slStores_' . $store_d['id']]['data'] = $store_d;
								$new_cart['slStores_' . $store_d['id']]['products'][$key] = $item;
							}
						}
					}
				}
			}else{
				// отправляем в ближайший магазин
				$sts = array_keys($stores_ids);
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
				if($query) {
					$result = $query->fetchAll(PDO::FETCH_ASSOC);
					if($result){
						foreach($cart as $key => $item) {
							$item['key'] = $key;
							$product_data = $this->getProductData($item['id']);
							$item = array_merge($item, $product_data);
							$new_cart['slStores_' . $result[0]['id']]['object'] = $result[0]['id'];
							$new_cart['slStores_' . $result[0]['id']]['type'] = 'slStores';
							$new_cart['slStores_' . $result[0]['id']]['data'] = $result[0];
							$new_cart['slStores_' . $result[0]['id']]['products'][$key] = $item;
						}
					}
				}
			}

		}else{
			// print_r($warehouse);
			$warehouse = array();
			$store = array();
			foreach($cart as $key => $item){
				$item['key'] = $key;
				$product_data = $this->getProductData($item['id']);
				$item = array_merge($product_data, $item);
				$position = $this->getUserPosition();
				if(isset($item['options']['type'])){
					if($item['options']['type'] == 'slWarehouse'){
						if(isset($item['options']['warehouse'])){
							$warehouse = $this->sl->getObject($item['options']['warehouse'], 'slWarehouse');
						}
					}
				}
				if(isset($item['options']['store'])){
					$store = $this->sl->getObject($item['options']['store']);
				}else{
					$store = $position['data']['store'];
				}
				if($store){
					$new_cart['stores'][] = $store['id'];
					// проверяем остаток у магазина
					$remains = $this->getRemains('slStores', $store['id'], $item['id'], $item['count']);
					if(!$remains){
						// если остаток не пришел проверяем у дистра
						$remains = $this->getRemains('slWarehouse', $store['id'], $item['id'], $item['count']);
						if(!$remains){
							// TODO: решить вопрос, если остатков нет в магазине и у дистра
							$new_cart['not_found']['products'][$key] = $item;
						}else{
							$new_cart['slWarehouse_'.$remains['object_id']]['object'] = $remains['object_id'];
							$new_cart['slWarehouse_'.$remains['object_id']]['type'] = 'slWarehouse';
							$new_cart['slWarehouse_'.$remains['object_id']]['data'] = $warehouse;
							$new_cart['slWarehouse_'.$remains['object_id']]['products'][$key] = $item;
						}
					}else{
						$new_cart['slStores_'.$store['id']]['object'] = $store['id'];
						$new_cart['slStores_'.$store['id']]['type'] = 'slStores';
						$new_cart['slStores_'.$store['id']]['data'] = $store;
						$new_cart['slStores_'.$store['id']]['products'][$key] = $item;
					}
				}
                if($warehouse){
                    $remains = $this->getRemains('slWarehouse', $warehouse['id'], $item['id'], $item['count']);
                    if(!$remains){
                        // TODO: решить вопрос, если остатков нет в магазине и у дистра
                        $new_cart['not_found']['products'][$key] = $item;
                    }else{
                        $new_cart['slWarehouse_'.$remains['object_id']]['object'] = $remains['object_id'];
                        $new_cart['slWarehouse_'.$remains['object_id']]['type'] = 'slWarehouse';
                        $new_cart['slWarehouse_'.$remains['object_id']]['data'] = $warehouse;
                        $new_cart['slWarehouse_'.$remains['object_id']]['products'][$key] = $item;
                    }
                }
			}
		}
		return $new_cart;
	}

	public function getRemains($type, $object_id, $product_id, $count = 1){
		if($type == 'slStores'){
			$object = "slStoresRemains";
			$key = "store_id";
		}
		if($type == 'slWarehouse'){
			$object = "slWarehouseRemains";
			$key = "warehouse_id";
			// находим дистра магазина
			$criteria = array(
				"store_id" => $object_id
			);
			$wh = $this->modx->getObject("slWarehouseStores", $criteria);
			if($wh){
				$object_id = $wh->get("warehouse_id");
			}else{
				return false;
			}
		}
		$criteria = array(
			"product_id" => $product_id,
			$key => $object_id
		);
		$remains = $this->modx->getObject($object, $criteria);
		if($remains){
			$available = $remains->get("available");
			$this->modx->log(1, print_r($remains->toArray(), 1));
			if($count <= $available){
				$tmp = array();
				$tmp['remains'] = $remains->toArray();
				$tmp['object_id'] = $object_id;
				$tmp['type'] = $type;
				return $tmp;
			}
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
			return false;
		}
	}

	public function getNearShipWh($product_id, $count = 1, $whs_id = 0, $store_id = 0){
		$criteria = array(
			"warehouse_id" => $whs_id,
			"product_id" => $product_id
		);
		$remain = $this->modx->getObject('slWarehouseRemains', $criteria);
		if($remain){
			$available = $remain->get("available");
			if($available >= $count) {
				// если есть необходимое количество товара у дистра
				$query = $this->modx->newQuery("slWarehouseShipment");
				$query->leftJoin('slWarehouseShip', 'slWarehouseShip', '`slWarehouseShipment`.`ship_id` = `slWarehouseShip`.`id`');
				$query->where(array(
					"date:>=" => date('Y-m-d H:i:s'),
					"FIND_IN_SET({$store_id}, `slWarehouseShip`.`store_ids`) > 0"
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
					return $offset;
				}else{
					// если вдруг отгрузка не назначена берем 10 дней
					$offset = 10;
					return $offset;
				}
			}else{
				// остаток 0, закладываем 30 дней?
				$offset = 30;
				return $offset;
			}
		}else{
			// остаток 0, закладываем 30 дней?
			$offset = 30;
			return $offset;
		}
	}

	public function getStoreRemain($product_id, $count = 1, $store_id = 0, $type = 'slStores'){
		if($type == 'slStores'){
			$object = "slStoresRemains";
			$key = "store_id";
		}else{
			$object = "slWarehouseRemains";
			$key = "warehouse_id";
		}
		$object = $this->modx->getObject($object, array($key => $store_id, 'product_id' => $product_id, 'available:>=' => $count));
		if($object){
			return $object->toArray();
		}
		return false;
	}

	public function getPickupPrice($product_id, $count = 1, $type = 'slStores', $from_id = 0){
		$delivery_data = array();
		$loc = $this->getUserPosition();
		if(!$from_id){
			$from['store'] = $loc['data']['store'];
		}else{
			$from['store'] = $this->sl->getObject($from_id, $type);
		}
		$from['store']['type'] = $type;
		// $this->modx->log(1, print_r($from, 1));
		$remain = $this->getStoreRemain($product_id, $count, $from['store']['id'], $from['store']['type']);
		if($remain){
			if($type == 'slStores'){
				// если тип магазин возращаем остаток
				$delivery_data['pickup']['store'] = $from['store'];
				$delivery_data['pickup']['price'] = 0;
				$delivery_data['pickup']['term'] = 'сегодня';
				$delivery_data['pickup']['term_default'] = 0;
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
					// смотрим отгрузки
					$shipment = $this->getNearShipWh($product_id, $count, $from['store']['id'], $loc['data']['store']['id']);
					if($shipment){
						$newDate = new DateTime();
						$interval = 'P'.$shipment.'D';
						$newDate->add(new DateInterval($interval));
						$delivery_data['pickup']['term_default'] = $newDate->format('Y-m-d H:i:s');
						$delivery_data['pickup']['term'] = $newDate->format('Y-m-d H:i:s');
						$delivery_data['pickup']['price'] = 0;
						$delivery_data['pickup']['store'] = $from['store'];
					}
				}
			}
			return $delivery_data;
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
		$loc = $this->getUserPosition();
		if(!$from_id){
			$from_id = $loc['data']['store']['id'];
		}
		$remain = $this->getStoreRemain($product_id, $count, $from_id, $type);
		// print_r($remain);
		if($remain){
			// $sdek_price = $this->getSdekPrice($product_id, $count, $type, $from_id, $to_id);
			// $yandex_price = $this->getYaDeliveryPrice($product_id, $count, $type, $from_id, $to_id);
			$postrf_price = $this->getPostRfPrice($product_id, $count, $type, $from_id, $to_id);
            return $postrf_price;
		}
		return false;
	}

	public function getPostRfPrice($product_id, $count = 1, $type = 'slStores', $from_id = 0, $to_id = 0){
		// $this->modx->log(1, $product_id.', '.$count.', '.$type.', '.$from_id.', '.$to_id);
		$delivery_data = array();
		$loc = $this->getUserPosition();
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
			$properties = $this->getCityData($object['city']);
			$from = $properties['postal_code'];
		}else{
			$object = $this->sl->getObject($loc['data']['store']['id'], $type);
			$properties = $this->getCityData($object['city']);
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
        $this->modx->log(1, $to.' '.$from.' '.print_r($prs, 1));
		$arr = $this->sl->postrf->getPrice($to, $from, $prs, 0);
		$this->modx->log(1, print_r($arr, 1));
		$delivery_data['delivery']['price'] = round($arr['terminal']['price']);
		$newDate = new DateTime();
		// Смещение относительно доставки
		if($type == 'slStores'){
            // проверим остаток на складе
            $remains = $this->getStoreRemain($product_id, 1, $from_id, 'slStores');
            $this->modx->log(1, $loc['data']['store']['id'].' '.print_r($remains, 1));
            if(!$remains){
                // иначе
                $offset = $this->getNearShipment($product_id, $count, $loc['data']['store']['id']);
            }else{
                $offset = 0;
            }
		}
		// TODO: решить door или terminal
		if($offset){
			$offset = $offset + $arr['terminal']['time'];
		}else{
			$offset = $arr['terminal']['time'];
		}
        $this->modx->log(1, $offset);
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
		$position = $this->getUserPosition();
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
			$object = $this->sl->getObject($from_id, $type);
		}else{
			$object = $this->sl->getObject($position['data']['store']['id'], $type);
		}
		$from = array(
			(float)$object['lng'],
			(float)$object['lat']
		);
		$arr = $this->sl->esl->getYandexDeliveryPrice($product_id, $from, $to);
		// проверяем наличие Я.Доставки
		if($arr){
			$delivery_data['delivery']['price'] = $arr['price'];
			$newDate = new DateTime();
			$delivery_data['delivery']['term_default'] = $newDate->format('Y-m-d H:i:s');
			$delivery_data['delivery']['term'] = 0;
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

	public function getDeliveryInfoDays($product_id, $type = 'slStores', $from_id = 0){
		$delivery_data = array();
		$main_store = array();
		$stores = array();
		$loc = $this->sl->getLocationData();
		$location = $loc['web'];
		$checked_store = $location['store'];

		if($this->modx->getOption('shoplogistic_cart_mode') == 2){
			$delivery_data = $this->getPickupPrice($product_id, 1, $type, $from_id);
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
		}

		$delivery = $this->getTKPrice($product_id, 1, $type, $from_id);
        $delivery_data['delivery'] = $delivery['delivery'];
        $express = $this->getYaDeliveryPrice($product_id, 1, $type, $from_id);
        if($express['delivery']) {
            $delivery_data['express'] = $express['delivery'];
        }
		return $delivery_data;
	}

	public function getDeliveryPrice($data){
		$services = array("main_key" => $data['service']);
		$data['location'] = json_decode($data['address'], 1);
		//$this->modx->log(1, print_r($data['location'], 1));
		if ($data) {
			$offset = $this->getDeliveryDateOffset('cart');
			$cart = $this->checkCart();
			if (in_array($data['service'], $this->config['our_services'])) {
				if($data['service'] == 'yandex'){
					if($offset){
						$days = $this->decl($offset, "день|дня|дней", true);
					}else{
						$days = "сегодня";
					}
					$services['yandex'] = false;
					foreach($cart as $item){
						// магазины считаем сразу
						if($item['type'] == 'slStores'){
							$data['cart'] = $item;
							$ya_data = $this->sl->esl->getYaDeliveryPrice('cart', 0, $data);
							if(isset($ya_data['price'])){
								// складываем цену
								if(isset($services['yandex']['door']['price'])){
									$services['yandex']['door']['price'] = $services['yandex']['door']['price'] + $ya_data['price'];
								}
								$services['yandex'] = array(
									"price" => array(
										"door" => array(
											"price" => $ya_data['price'],
											"time" => $days
										)
									)
								);
							}else{
								$services['yandex'] = false;
							}
						}else{
							// если у нас склад, отправляем пока отправляем как из магазина
							$data['cart'] = $item;
							$ya_data = $this->sl->esl->getYaDeliveryPrice('cart', 0, $data);
							if(isset($ya_data['price'])){
								// складываем цену
								if(isset($services['yandex']['door']['price'])){
									$services['yandex']['door']['price'] = $services['yandex']['door']['price'] + $ya_data['price'];
								}
								$services['yandex'] = array(
									"price" => array(
										"door" => array(
											"price" => $ya_data['price'],
											"time" => $days
										)
									)
								);
							}else{
								$services['yandex'] = false;
							}
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
								$this->modx->log(1, print_r($properties, 1));
								if($properties['postal_code']){
									$out = $this->sl->postrf->getPrice($properties['postal_code'], $data['location']['postal_code'], $item['products'], 0);
                                    $this->modx->log(1, print_r($out, 1));
									$s = array('door', 'terminal');
									foreach($s as $key){
										if(isset($services['postrf']['price'][$key]['price'])){
											$services['postrf']['price'][$key]['price'] += $out[$key]['price'];
                                            $services['postrf']['price'][$key]['time'] = $this->sl->num_word($out[$key]['time'], array('день', 'дня', 'дней'), 1);
										}else{
											$services['postrf']['price'][$key]['price'] = $out[$key]['price'];
                                            $services['postrf']['price'][$key]['time'] = $this->sl->num_word($out[$key]['time'], array('день', 'дня', 'дней'), 1);
										}
									}
								}
							}
						}
					}
				}
			}else {
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

	public function decl($amount, $variants, $number = false, $delimiter = '|') {
		$variants = explode($delimiter, $variants);
		if (count($variants) < 2) {
			$variants = array_fill(0, 3, $variants[0]);
		} elseif (count($variants) < 3) {
			$variants[2] = $variants[1];
		}
		$modulusOneHundred = $amount % 100;
		switch ($amount % 10) {
			case 1:
				$text = $modulusOneHundred == 11
					? $variants[2]
					: $variants[0];
				break;
			case 2:
			case 3:
			case 4:
				$text = ($modulusOneHundred > 10) && ($modulusOneHundred < 20)
					? $variants[2]
					: $variants[1];
				break;
			default:
				$text = $variants[2];
		}

		return $number
			? $amount . ' ' . $text
			: $text;
	}
}