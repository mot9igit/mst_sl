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
			$product = $this->modx->getObject("msProduct", $product_id);
			if($product){
				$params = array();
				$tmp["id"] = $product->get('id');
				$tmp["article"] = $product->get('article');
     			$tmp["name"] = $product->get('pagetitle');
				$tmp['weight'] = (float)$product->get('weight_brutto');
				$tmp['weight_netto'] = (float)$product->get('weight_netto');
				$tmp['volume'] = (float)$product->get('volume');
				$tmp['price'] = (float)$product->get('price');
				$tmp['count'] = $num;
				$params['dimensions'][0] = (int)$product->get('length');
				$params['dimensions'][1] = (int)$product->get('width');
				$params['dimensions'][2] = (int)$product->get('height');
				$tmp['length'] = $params['dimensions'][0];
				$tmp['width'] = $params['dimensions'][1];
				$tmp['height'] = $params['dimensions'][2];
				$tmp['dimensions'] = implode('*', $params['dimensions']);
				$output[] = $tmp;
				return $output;
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
	 * 	Проверка корзины и разбивка на отправлениям
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
							$new_cart['slStores_' . $result[0]['id']]['products'][$key] = $item;
						}
					}
				}
			}

		}else{
			foreach($cart as $key => $item){
				$item['key'] = $key;
				$product_data = $this->getProductData($item['id']);
				$item = array_merge($item, $product_data);
				$position = $this->getUserPosition();
				$store = $position['data']['store'];
				if($store){
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
							$new_cart['slWarehouse_'.$remains['object_id']]['products'][$key] = $item;
						}
					}else{
						$new_cart['slStores_'.$store['id']]['object'] = $store['id'];
						$new_cart['slStores_'.$store['id']]['type'] = 'slStores';
						$new_cart['slStores_'.$store['id']]['products'][$key] = $item;
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
		$link = $this->modx->getObject('slWarehouseStores', array("store_id" => $store_id));
		if($link){
			// если связь нашлась, то мы можем проверить, есть ли товар у дистра
			$criteria = array(
				"warehouse_id" => $link->get("warehouse_id"),
				"product_id" => $product_id
			);
			$remain = $this->modx->getObject('slWarehouseRemains', $criteria);
			if($remain){
				$count = $remain->get("available");
				if($count >= $count) {
					// если есть необходимое количество товара у дистра
					$query = $this->modx->newQuery("slWarehouseShipment");
					$query->leftJoin('slWarehouseShip', 'slWarehouseShip', '`slWarehouseShipment`.`ship_id` = `slWarehouseShip`.`id`');
					$query->where(array(
						"date:>=" => date('Y-m-d H:i:s'),
						"FIND_IN_SET({$store_id}, `slWarehouseShipment`.`ship_id`) > 0"
					));
					$query->sortby('date', 'ASC');
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
		}else{
			$this->modx->log(xPDO::LOG_LEVEL_ERROR, '[shopLogistic] Store link with warehouse not found.');
			return false;
		}
	}

	public function getStoreRemain($product_id, $store_id){
		$object = "slStoresRemains";
		$key = "store_id";
		$object = $this->modx->getObject($object, array($key => $store_id, 'product_id' => $product_id));
		if($object){
			return $object->toArray();
		}
		return false;
	}

	public function getDeliveryInfoDays($product_id){
		$delivery_data = array();
		$main_store = array();
		$stores = array();
		$loc = $this->sl->getLocationData();
		// TODO: учесть контекст
		$location = $loc['web'];
		if($this->modx->getOption('shoplogistic_cart_mode') == 2){
			// проверяем остаток к выбранного магазина
			$remain = $this->getStoreRemain($product_id, $location['store']['id']);
			if($remain['available'] > 0){
				$stores[] = $location['store'];
			}
		}else{
			// Список магазинов у кого есть в наличии товар (дистры нам тут не нужны)
			$stores = $this->getNearbyStores($product_id);
		}
		$products = $this->getProductParams($product_id);
		if(count($stores)){
			// если в наличии
			$main_store = $stores[0];
			$delivery_data['pickup']['price'] = 0;
			$delivery_data['pickup']['term'] = 'сегодня';
			$delivery_data['pickup']['term_default'] = 0;
		}else{
			if($this->modx->getOption('shoplogistic_cart_mode') == 2){
				// TODO: проверить на остатке на складе и ближайшую отгрузку
				$shipment = $this->getNearShipment($product_id, $location['store']['id']);
				$newDate = new DateTime();
				$interval = 'P'.$shipment.'D';
				$newDate->add(new DateInterval($interval));
				$delivery_data['pickup']['term_default'] = $newDate->format('Y-m-d H:i:s');
				$delivery_data['pickup']['term'] = $newDate->format('Y-m-d H:i:s');
				$this->modx->log(1, print_r($shipment, 1));
			}else{
				// если же нет в наличии, проверяем подключен ли магазин по умолчанию
				$default_store = $this->modx->getOption("shoplogistic_default_store");
				if ($default_store) {
					$delivery_data['pickup']['price'] = 0;
					$delivery_data['pickup']['term'] = 'сегодня';
					$delivery_data['pickup']['term_default'] = 0;
					$def_store = $this->modx->getObject("slStores", $default_store);
					if ($def_store) {
						$main_store = $def_store->toArray();
					}
				}
			}
		}
		// смотрим доставку
		$this->sl->esl = new eShopLogistic($this->sl, $this->modx);
		$position = $this->getUserPosition();
		// get cost pvz (SDEK or DPD)
		// Проверяем сразу СДЭК
		$to_data = array(
			"target" => $position['data']['city_fias_id']? : $position['data']['fias_id']
		);

		$resp = $this->sl->esl->query("search", $to_data);
		$to = $resp['data'][0]['services']['sdek'];

		$city = $this->modx->getObject("dartLocationCity", $location['store']['city']);
		if($city){
			$properties = $city->get("properties");
			$pos = array((float) $properties["geo_lat"], (float) $properties["geo_lon"]);
			$dt = json_decode($this->sl->getGeoData($pos), 1);
			$data = $dt['suggestions'][0];

			$from = $data['data']['city_fias_id']? : $data['data']['fias_id'];
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
		}

		// get cost delivery (Yandex or DPD)
		$from = array((float) $city->get("lat"), (float) $city->get("lng"));
		$to = array(
			(float)$position['data']['geo_lat'],
			(float)$position['data']['geo_lon'],
		);
		$arr = $this->sl->esl->getYandexDeliveryPrice($product_id, $from, $to);
		// проверяем наличие Я.Доставки
		if($arr){
			$delivery_data['delivery']['price'] = $arr['price'];
			$newDate = new DateTime();
			$delivery_data['delivery']['term_default'] = $newDate->format('Y-m-d H:i:s');
			$delivery_data['delivery']['term'] = 0;
			$delivery_data['delivery']['service'] = 'Яндекс.Доставка';
		}else{
			// иначе выставляем postrf
			// $this->modx->log(1, print_r($position, 1));
			$to = $position['data']['postal_code'];
			$city = $this->modx->getObject("dartLocationCity", $location['store']['city']);
			// $this->modx->log(1, print_r($city->toArray(), 1));
			if($city){
				$props = $city->get("properties");
				$from = $props['postal_code'];
			}
			$arr = $this->sl->esl->getPostRfPrice("card", $to, $from, $products);
			$this->modx->log(1, print_r($arr, 1));
			$delivery_data['delivery']['price'] = round($arr['door']['price']);
			$newDate = new DateTime();
			$newDate->add(new DateInterval('P7D'));
			$delivery_data['delivery']['term_default'] = $newDate->format('Y-m-d H:i:s');
			$delivery_data['delivery']['term'] = $newDate->format('Y-m-d H:i:s');
			$delivery_data['delivery']['service'] = 'Почта России';
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
						$object = $this->modx->getObject($item['type'], $item['object']);
						if($object) {
							$city = $object->getOne("City");
							if ($city) {
								$properties = $city->get('properties');
								$this->modx->log(1, print_r($properties, 1));
								if($properties['postal_code']){
									$out = $this->sl->esl->getPostRfPrice('cart', $properties['postal_code'], $data['location']['postal_code'], $item['products']);
									$s = array('door', 'terminal');
									foreach($s as $key){
										if(isset($services['postrf']['price'][$key]['price'])){
											$services['postrf']['price'][$key]['price'] += $out['postrf']['price'][$key]['price'];
										}else{
											$services['postrf']['price'][$key]['price'] = $out['postrf']['price'][$key]['price'];
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
							$from_resp = $this->esl->query("search", $search_data);
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
							$this->modx->log(1, print_r($data['service'], 1));
							$this->modx->log(1, print_r($esl_rdata, 1));
							$resp = $this->sl->esl->query("delivery/" . $data['service'], $esl_rdata);
							$this->modx->log(1, print_r($resp, 1));
							$types = array('terminal','door');
							foreach($types as $type){
								$d = explode("-", $resp['data'][$type]['time']);
								$days = (int) preg_replace('/[^0-9]/', '', $d[0]) + 1 + $offset;
								if($services[$data['service']]['price'][$type]['time'] < $days){
									$services[$data['service']]['price'][$type]['time'] = $days;
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
						$pos = $this->sl->cart->getProductParams($pr['id']);
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
				$pos = $this->sl->cart->getProductParams($pr['id']);
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