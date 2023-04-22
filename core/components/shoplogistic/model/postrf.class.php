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


		$this->config = array_merge([
			'corePath' => $corePath,
			'modelPath' => $corePath . 'model/',
			'processorsPath' => $corePath . 'processors/',

			'connectorUrl' => $assetsUrl . 'connector.php',
			'assetsUrl' => $assetsUrl,
			'assetsPath' => $assetsPath,
			'cssUrl' => $assetsUrl . 'css/',
			'jsUrl' => $assetsUrl . 'js/'
		], $config);
	}

	/**
	 * Подготовка данных для запроса
	 *
	 * @param $products
	 * @return mixed
	 */
	public function prepareProducts($products){
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
	 * @param $to
	 * @param $from
	 * @param array $products
	 * @return array
	 */

	public function getPrice($to, $from, $products = array()){
		// кеш
		$cache_id = md5($from.' '.$to.' '.json_encode($products));
		$cache = $this->modx->getCacheManager();
		$cache_options = array( xPDO::OPT_CACHE_KEY => 'default/delivery/postrf/' );
		if($out = $cache->get($cache_id, $cache_options)) {
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
			foreach ($products as $product) {
				if($product['weight'] > 10){
					if($product['weight'] < 20){
						// Посылка (до отделения)
						// EMS PT (курьер)
						$tariffs = array(
							'terminal' => 27020,
							'door' => 24020
						);
					}else{
						$tariffs = array(
							'terminal' => 41020,
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
				$this->modx->log(1, print_r($tariffs, 1));
				foreach($tariffs as $key => $tarif) {
					$params = array(
						'object' => $tarif,
						'from' => $from,
						'to' => $to,
						'weight' => $product['weight'] * 1000,
						'sumoc' => $product['price'] * 100,
						'countinpack' => count($product['places'])
					);
					$prf_data = $this->post_request($params);
					// $out['product'] = $product;
					$this->modx->log(1, print_r($params, 1));
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
				$out['door']['price'] = round(((($all['door']['price']  / 100) * 1.13) + 15), 2);
			}
			if(!empty($all['terminal']['price'])){
				$out['terminal']['price'] = round(((($all['terminal']['price']  / 100) * 1.13) + 15), 2);
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
		$this->modx->log(1, print_r($prf_data, 1));
		return $prf_data;
	}
}
