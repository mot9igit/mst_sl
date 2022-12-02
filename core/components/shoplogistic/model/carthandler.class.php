<?php
class cartDifficultHandler
{
	public function __construct(shopLogistic &$sl, modX &$modx)
	{
		$this->sl =& $sl;
		$this->modx =& $modx;
		$this->modx->lexicon->load('shoplogistic:default');
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
	public function getUserPosition(){
		$geo = array();
		$geo['data'] = $_SESSION['sl_location']['data'];
		$geo['lat'] = trim($_SESSION['sl_location']['data']['geo_lat']);
		$geo['lng'] = trim($_SESSION['sl_location']['data']['geo_lon']);
		return $geo;
	}

	/**
	 * Ищем ближайший магазин с товаром
	 *
	 * @param $product_id
	 * @return array, bool
	 */
	public function getNearbyStores($product_id){
		// ищем пока в два запроса, позже нужно оптимизировать
		$query = $this->modx->newQuery('slStoresRemains');
		$query->rightJoin('slStores','slStores', array( 'slStoresRemains.store_id = slStores.id'));
		$query->select(array('slStores.id'));
		$query->where(array('slStoresRemains.product_id' => $product_id));
		$query->limit(999);

		if ($query->prepare() && $query->stmt->execute()) {
			$result = $query->stmt->fetchAll(PDO::FETCH_ASSOC);
			$sts = array();
			foreach ($result as $res) {
				$sts[] = $res['id'];
			}
			$default_store = $this->modx->getOption("shoplogistic_default_store");
			if($default_store){
				$sts[] = $default_store;
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
			$result = $query->fetchAll(PDO::FETCH_ASSOC);
			return $result;
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
					$query->where(array(
						"date:>=" => date('Y-m-d H:i:s'),
						"FIND_IN_SET({$store_id}, store_ids) > 0"
					));
					$query->sortby('date', 'ASC');
					$obj = $this->modx->getObject("slWarehouseShipment", $query);
					if ($obj) {
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

	public function getDeliveryInfoDays($product_id){
		$delivery_data = array();
		$main_store = array();
		// Список магазинов у кого есть в наличии товар (дистры нам тут не нужны)
		$stores = $this->getNearbyStores($product_id);
		$products = $this->getProductParams($product_id);
		if(count($stores)){
			// если в наличии
			$main_store = $stores[0];
			$delivery_data['pickup']['price'] = 0;
			$delivery_data['pickup']['term'] = 'сегодня';
			$delivery_data['pickup']['term_default'] = 0;
		}else{
			// если же нет в наличии, проверяем подключен ли магазин по умолчанию
			$default_store = $this->modx->getOption("shoplogistic_default_store");
			if($default_store){
				$delivery_data['pickup']['price'] = 0;
				$delivery_data['pickup']['term'] = 'сегодня';
				$delivery_data['pickup']['term_default'] = 0;
				$def_store = $this->modx->getObject("slStores", $default_store);
				if($def_store){
					$main_store = $def_store->toArray();
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

		$city = $this->modx->getObject("slCityCity", $main_store['city']);
		if($city){
			$pos = array((float) $city->get("lat"), (float) $city->get("lng"));
			$dt = json_decode($this->sl->getGeoData($pos), 1);
			$data = $dt['suggestions'][0];
			//$this->modx->log(1, print_r($dt, 1));
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
			$to = $position['data']['postal_code'];
			$city = $this->modx->getObject("slCityCity", $main_store['city']);
			if($city){
				$pos = array((float) $city->get("lat"), (float) $city->get("lng"));
				$dt = json_decode($this->sl->getGeoData($pos), 1);
				$data = $dt['suggestions'][0];
				$from = $data['data']['postal_code'];
			}
			$arr = $this->sl->esl->getPostRfPrice("card", $to, $from, $products);
			$delivery_data['delivery']['price'] = round($arr['door']['price']);
			$newDate = new DateTime();
			$newDate->add(new DateInterval('P7D'));
			$delivery_data['delivery']['term_default'] = $newDate->format('Y-m-d H:i:s');
			$delivery_data['delivery']['term'] = $newDate->format('Y-m-d H:i:s');
			$delivery_data['delivery']['service'] = 'Почта России';
		}
		return $delivery_data;
	}

	public function getDeliveryDateOffset($mode, $id = 0){
		$offset = 0;
		if($mode == 'cart'){
			// не тестировалось, придется переписывать
			$cart = $this->sl->ms2->cart->get();
			foreach ($cart as $product) {
				$id = $product['id'];
				$remains = $this->modx->getObject("slStoresRemains", array("product_id" => $id, "store_id" => $_SESSION['sl_location']['store']['id']));
				if (!$remains) {
					// если нет в наличии проверяем ближайшую отгрузку +1 день
					$query = $this->modx->newQuery("slWarehouseShipment");
					$query->where(array(
						"date:>=" => date('Y-m-d H:i:s'),
						"FIND_IN_SET({$_SESSION['sl_location']['store']['id']}, store_ids) > 0"
					));
					$query->sortby('date', 'ASC');
					$obj = $this->modx->getObject("slWarehouseShipment", $query);
					if ($obj) {
						$nowDate = new DateTime();
						$newDate = new DateTime($obj->get('date'));
						$newDate->add(new DateInterval('P1D'));
						$interval = $nowDate->diff($newDate);
						$offset = $interval->format('%a');
					}
				}
			}
		}
		if($mode == 'card'){
			$remains = $this->modx->getObject("slStoresRemains", array("product_id" => $id, "store_id" => $_SESSION['sl_location']['store']['id']));
			if (!$remains) {
				// если нет в наличии проверяем ближайшую отгрузку +1 день
				$query = $this->modx->newQuery("slWarehouseShipment");
				$query->where(array(
					"date:>=" => date('Y-m-d H:i:s'),
					"FIND_IN_SET({$_SESSION['sl_location']['store']['id']}, store_ids) > 0"
				));
				$query->sortby('date', 'ASC');
				//$query->prepare();
				//$this->modx->log(1, $query->toSQL());
				$obj = $this->modx->getObject("slWarehouseShipment", $query);
				if ($obj) {
					$nowDate = new DateTime();
					$newDate = new DateTime($obj->get('date'));
					$newDate->add(new DateInterval('P1D'));
					$interval = $nowDate->diff($newDate);
					$offset = $interval->format('%a');
				}
			}
		}
		return $offset;
	}

	public function getNearbyWarehouse($product_id){

	}
}