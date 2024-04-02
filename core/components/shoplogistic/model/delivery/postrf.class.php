<?php

class postrf{

	function __construct(shopLogistic &$sl, modX &$modx, array $config = array())
	{
		$this->sl =& $sl;
		$this->modx =& $modx;
		$this->modx->lexicon->load('shoplogistic:default');

		$corePath = $this->modx->getOption('shoplogistic_core_path', $config, $this->modx->getOption('core_path') . 'components/shoplogistic/');
		$assetsUrl = $this->modx->getOption('shoplogistic_assets_url', $config, $this->modx->getOption('assets_url') . 'components/shoplogistic/');
		$assetsPath = $this->modx->getOption('shoplogistic_assets_path', $config, $this->modx->getOption('base_path') . 'assets/components/shoplogistic/');
        $token = $this->modx->getOption('shoplogistic_postrf_token', $config, '');
        $key = $this->modx->getOption('shoplogistic_postrf_key', $config, '');
        $url = $this->modx->getOption('shoplogistic_postrf_url', $config, '');


		$this->config = array_merge([
			'corePath' => $corePath,
			'modelPath' => $corePath . 'model/',
			'processorsPath' => $corePath . 'processors/',
            'log_file_name' => "delivery_postrf",
            "delivery_log" => false,

			'connectorUrl' => $assetsUrl . 'connector.php',
			'assetsUrl' => $assetsUrl,
			'assetsPath' => $assetsPath,
			'cssUrl' => $assetsUrl . 'css/',
			'jsUrl' => $assetsUrl . 'js/',

            'token' => $token,
            'key' => $key,
            'url' => $url,
		], $config);
	}

    /**
     * Подготовка адреса
     *
     * @param $address
     * @return array
     */
    public function prepareAddress($address, $to = false){
        $output = array();
        $address = $this->cleanAddress($address);
        $needle_fields = array(
            "area",
            "building",
            "corpus",
            "hotel",
            "house",
            "index",
            "letter",
            "location",
            "num-address-type",
            "office",
            "place",
            "region",
            "room",
            "slash",
            "street",
            "vladenie"
        );
        if($to){
            foreach($needle_fields as $key => $val){
                $needle_fields[$key] = $val."-to";
            }
        }
        foreach($address[0] as $key => $val){
            if(in_array($key, $needle_fields)){
                $output[$key] = $val;
            }
        }
        return $output;
    }

	/**
	 * Подготовка данных для запроса
	 *
	 * @param $products
	 * @return mixed
	 */
	public function prepareProducts($products){
        // $this->modx->log(1, print_r($products, 1));
		$output = array();
		foreach($products as $pr) {
			$tmp = array(
				'price' => 0,
				'weight' => 0,
				"count" => $pr['count'],
			);
			$pos = $this->sl->cart->getProductParams($pr['id']);
			if ($pos) {
				$pos = $pos[0];
				if (!$pos['product']['places']) {
					$pos['product']['places'] = 1;
				}
				for ($i = 0; $i < $pos['product']['places']; $i++){
					$tmp['offers'][] = array(
						'article' => $pr['id'],
						'name' => $pr['id'],
						"count" => $pr['count'],
						'price' => str_replace(" ", "", $pr['price']),
						"dimensions" => implode("*", array((float)$pos['length'], (float)$pos['width'], $pos['height'])),
						"weight" => (float)$pos['weight']
					);
				}
				$tmp['product'] = $pos['product'];
				$tmp['places'] = $pos['product']['places'];
				$tmp['price'] += ((float)$pos['price'] * $pr['count']);
				$tmp['weight'] += ((float)$pos['weight'] * $pr['count']);
				$output[] = $tmp;
			}
		}
		return $output;
	}

	/**
     * Расчет стоимсти бесплатный
     *
	 * @param $to
	 * @param $from
	 * @param array $products
	 * @return array
	 */

	public function getFreePrice($to, $from, $products = array(), $scache = 1){
		// кеш
		$cache_id = md5($from.' '.$to.' '.json_encode($products));
		$cache = $this->modx->getCacheManager();
		$cache_options = array( xPDO::OPT_CACHE_KEY => 'default/delivery/postrf/' );
        if($out = $cache->get($cache_id, $cache_options) && $scache) {
            //$this->modx->log(1, print_r($out, 1));
            //$this->modx->log(1, print_r($scache, 1));
			return $out;
		}else{
			$out = array(
				'service' => 'postrf',
				'door' => array(),
				'terminal' => array()
			);
			$all = array(
				"weight" => 0,
				"places" => 0,
				"price" => 0,
				"door" => array(
					"price" => 0,
					"time" => 0
				),
				"terminal" => array(
					"price" => 0,
					"time" => 0
				),
				'delivery' => ''
			);
			$products = $this->prepareProducts($products);
            //$this->modx->log(1, print_r($products, 1));
			foreach ($products as $product) {
				if($product['weight'] > 10){
					if($product['weight'] < 20){
						// Посылка (до отделения)
						// EMS PT (курьер)
						$tariffs = array(
							'terminal' => 23020,
							'door' => 41020
						);
					}else{
						$tariffs = array(
							'terminal' => 23020,
							'door' => 41020
						);
					}
				}else{
					// курьер онлайн (курьер) и посылка онлайн (до отделения)
					$tariffs = array(
						'terminal' => 23020,
						'door' => 24020
					);
				}
				// $this->modx->log(1, print_r($tariffs, 1));
				foreach($tariffs as $key => $tarif) {
                    // TODO: сделать заплатку для упаковки
					$params = array(
						'object' => $tarif,
						'from' => $from,
						'to' => $to,
                        'pack' => 21,
						'weight' => $product['weight'] * 1000,
						'sumoc' => $product['price'] * 100,
						'countinpack' => count($product['places'])
					);
                    $this->modx->log(1, print_r($params, 1));
					$prf_data = $this->post_request($params);
					// $out['product'] = $product;
                    // TODO: сделать заплатку для курьера
                    if($prf_data['errors']){
                        $out['debug'][] = $prf_data['errors'];
                    }
					//$this->modx->log(1, print_r($params, 1));
					$this->modx->log(1, print_r($prf_data, 1));
					if (!empty($prf_data['paymoneynds'])) {
						$all[$key]['price'] = $all[$key]['price'] + ($prf_data['paymoneynds'] * $product['count']);
					}
					if (!empty($prf_data['delivery'])) {
						if($prf_data['delivery']['max'] > $all[$key]['time']){
							$all[$key]['time'] = $prf_data['delivery']['max'];
						}
					} else {
						$all[$key]['time'] = '7';
					}
				}
			}
			if(!empty($all['door']['price'])){
				$out['door']['price'] = round(((($all['door']['price']  / 100) * 1.13) + 15));
			}
			if(!empty($all['terminal']['price'])){
				$out['terminal']['price'] = round(((($all['terminal']['price']  / 100) * 1.13) + 15));
			}
			if(!empty($all['terminal']['time'])){
				$out['terminal']["time"] = $all['terminal']['time'];
			}else{
				$out['terminal']["time"] = '7';
			}
			if(!empty($all['door']['time'])){
				$out['door']["time"] = $all['door']['time'];
			}else{
				$out['door']["time"] = '7';
			}
			$cache->set($cache_id, $out, 604800, $cache_options);
			return $out;
		}
		return $out;
	}

    /**
     * Узнаем стоимость доставки
     *
     * @param $from
     * @param $to
     * @param $products
     * @return array
     */
    public function getPrice($from, $to, $products = array()){
        $out = array(
            'service' => 'postrf',
            'door' => array(
                'price' => 0,
                'time' => 0
            ),
            'terminal' => array(
                'price' => 0,
                'time' => 0
            )
        );
        $url = '/1.0/tariff';
        $data = array(
            "completeness-checking" => false,
            "contents-checking" => false,
            "courier" => false,
            // "dimension-type" => "OVERSIZED",
            "fragile" => "false",
            "declared-value" => 999,
            "index-from" => $from,
            "index-to" => $to,
            "inventory" => false,
            "mass" => 5000,
            "entries-type" => "SALE_OF_GOODS",
            "mail-category" => "WITH_DECLARED_VALUE",
            "mail-type" => "BUSINESS_COURIER",
            "transport-type" => "SURFACE"
        );
        if($products){
            $products = $this->prepareProducts($products);
            foreach($products as $product){
                // TODO: GET_TARIFFS
                if($product['weight'] > 10){
                    if($product['weight'] < 20){
                        // Посылка (до отделения)
                        // EMS PT (курьер)
                        $tariffs = array(
                            'terminal' => 'POSTAL_PARCEL',
                            'door' => 'EMS_RT'
                        );
                    }else{
                        $tariffs = array(
                            'terminal' => 'POSTAL_PARCEL',
                            'door' => 'EMS_RT'
                        );
                    }
                }else{
                    $tariffs = array(
                        'terminal' => 'POSTAL_PARCEL',
                        'door' => 'EMS_RT'
                    );
                }
                foreach($tariffs as $key => $tariff) {
                    $data["declared-value"] = $product['price'];
                    $data["mass"] = $product['weight'] * 1000;
                    $data["mail-type"] = $tariff;
                    $prf_data = $this->request($url, $data);
                    $this->sl->tools->log(print_r($prf_data, 1), $this->config["log_file_name"]);
                    if (!empty($prf_data['total-rate'])) {
                        $out[$key]['price'] = $out[$key]['price'] + round(($prf_data['total-rate'] * $product['count'] * 0.01));
                    }
                    if (!empty($prf_data['delivery-time']['max-days'])) {
                        if($prf_data['delivery-time']['max-days'] > $out[$key]['time']){
                            $out[$key]['time'] = $prf_data['delivery-time']['max-days'];
                        }
                    } else {
                        $out[$key]['time'] = '7';
                    }
                }
            }
        }
        return $out;
    }

    /**
     * Нормализация имени
     *
     * @param $name
     * @return void
     */
    public function cleanName($name){
        $url = "1.0/clean/physical";
        $data = array(array(
            "id" => md5($name),
            "original-fio" => $name
        ));
        $response = $this->request($url, $data, "POST");
        return $response;
    }

    /**
     * Нормализация адреса
     *
     * @param $address
     * @return void
     */
    public function cleanAddress($address){
        $url = "1.0/clean/address";
        $data = array(array(
            "id" => md5($address),
            "original-address" => $address
        ));
        $response = $this->request($url, $data, "POST");
        return $response;
    }

    /**
     * Создание заявки в ТК
     *
     * @param $order_id
     * @return array
     */
    public function createRequest($order_id){
        $result = $this->sl->cart->getOrderData($order_id);
        // $request_id = md5($result['order']['id'].time());
        $request_id = md5($result['order']['id']);
        $address_from = $this->prepareAddress($result["store"]["address"]);
        $address_to = $this->prepareAddress($result["address"]["text_address"], true);
        $goods = array(
            "items" => array()
        );
        $weight = 0;
        foreach($result['products'] as $product){
            $tmp = array(
                "description" => $product["vendor_article"],
                "goods-type"=> "GOODS",
                "insr-value"=> $product["price"] * $product["count"] * 100,
                "item-number"=> $product["vendor_article"],
                "quantity"=> $product["count"],
                "supplier-inn"=> $result['store']["inn"],
                "supplier-name"=> $result['store']["name"],
                "supplier-phone"=> $result['store']["phone"],
                "value"=> $product["price"] * 100,
                "vat-rate" => 20,
                "weight" => $product["weight_brutto"] * 1000,
            );
            $goods["items"][] = $tmp;
            $weight += $product["weight_brutto"] * 1000;
        }
        $data = array(
            "add-to-mmo" => false,
            "address-from" => $address_from,
            "address-type-to" => "DEFAULT",
            "comment" => "",
            "completeness-checking" => false,
            "courier" => false,
            "easy-return" => true,
            "given-name" => $result["address"]["receiver"],
            "inner-num" => $result["order"]["id"],
            "insr-value" => $result["order"]["cart_cost"] * 1000,
            "goods" => $goods,
            "mass" => $weight,
            "mail-category" => "WITH_DECLARED_VALUE",
            "mail-type" => "EMS_RT",
            "transport-type" => "SURFACE",
            "recipient-name" => $result["address"]["receiver"],
        );
        // "delivery-to-door" => true, если курьер
        if($result["delivery"]["id"] == 4){
            $data["delivery-to-door"] = true;
        }
        $request = array_merge($data, $address_to);
        // запрос
    }

    /**
     *  Берем ближайшие отделения (что ВАЖНО - с функцией отправки)
     *
     */
    public function getNearPVZ($latitude, $longitude){
        $url = "postoffice/1.0/nearby?latitude={$latitude}&longitude={$longitude}&filter=ALL";
        $response = $this->request($url, array(), "GET");
        foreach($response as $key => $resp){
            if($resp["type-code"] == "ПОЧТОМАТ"){
                unset($response[$key]);
            }
        }
        return $response;
    }

    /**
     * Запрос к API
     *
     * @param $url
     * @param $data
     * @param $method
     * @return mixed
     */
    public function request($url, $data = array(), $method = "POST"){
        $ch = curl_init();
        $this->sl->tools->log($method.' '.print_r($data, 1), $this->config["log_file_name"]);
        curl_setopt($ch, CURLOPT_URL, $this->config['url'].$url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        if($data){
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data, JSON_UNESCAPED_UNICODE));
        }
        if($method == "POST"){
            curl_setopt($ch, CURLOPT_POST, 1);
        }

        $headers = array();
        $headers[] = "Content-Type: application/json;charset=UTF-8";
        $headers[] = "Accept: application/json";
        $headers[] = "Authorization: AccessToken ".$this->config['token'];
        $headers[] = "X-User-Authorization: Basic ".$this->config["key"];
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $result = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        //if($this->config["delivery_log"]){
            $this->sl->tools->log($http_code, $this->config["log_file_name"]);
            $this->sl->tools->log(print_r($result, 1), $this->config["log_file_name"]);
        //}
        if (curl_errno($ch)) {
            $this->modx->log(xPDO::LOG_LEVEL_ERROR,  'POSTRF Error:' . curl_error($ch));
        }
        curl_close ($ch);

        return json_decode($result, 1);
    }

	/**
	 * @param $data
	 * @return mixed
	 */
	public function post_request($data){
		/*
			$data = array(
				"from" => '',
				"to" => '',
				"weight" => '',
				"sumoc" => '',
				"countinpack" => ''
			)
		*/
		$url = 'https://tariff.pochta.ru/v1/calculate/tariff/delivery?';
		/* $params = array(
			'object' => 41020,
			'from' => $from,
			'to' => $to,
			'weight' => $all['weight']*1000,
			'sumoc' => $all['price']*100,
			'countinpack' => $all['places']
		); */
		$p = http_build_query($data);
		if( $curl = curl_init() ) {
			curl_setopt($curl, CURLOPT_URL, $url.$p);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER,true);
			$out = curl_exec($curl);
			curl_close($curl);
		}
		$prf_data = json_decode($out, 1);
		// $this->modx->log(1, print_r($prf_data, 1));
		return $prf_data;
	}
}
