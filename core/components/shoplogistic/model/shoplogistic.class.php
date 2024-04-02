<?php

class shopLogistic
{
	/** @var modX $modx */
	public $modx;


	/**
	 * @param modX $modx
	 * @param array $config
	 */
	function __construct(modX &$modx, array $config = [])
	{
		$this->modx =& $modx;
		$corePath = $this->modx->getOption('shoplogistic_core_path', $config, $this->modx->getOption('core_path') . 'components/shoplogistic/');
		$assetsUrl = $this->modx->getOption('shoplogistic_assets_url', $config, $this->modx->getOption('assets_url') . 'components/shoplogistic/');
		$assetsPath = $this->modx->getOption('shoplogistic_assets_path', $config, $this->modx->getOption('base_path') . 'assets/components/shoplogistic/');

		$this->config = array_merge([
			'corePath' => $corePath,
			'modelPath' => $corePath . 'model/',
			'processorsPath' => $corePath . 'processors/',
			'version' => '0.0.10',

			'connectorUrl' => $assetsUrl . 'connector.php',
			'actionUrl' => $assetsUrl . 'action.php',
			'assetsUrl' => $assetsUrl,
			'assetsPath' => $assetsPath,
			'cssUrl' => $assetsUrl . 'css/',
			'jsUrl' => $assetsUrl . 'js/',

			'regexp_gen_code' => $this->modx->getOption('shoplogistic_regexp_gen_code'),
			'city_fields' => array_merge(['id'], explode(',', $this->modx->getOption('shoplogistic_city_fields')), ['actions'])
		], $config);


		$this->modx->addPackage('shoplogistic', $this->config['modelPath']);
		$this->modx->lexicon->load('shoplogistic:default');

		if ($this->pdoTools = $this->modx->getService('pdoFetch')) {
			$this->pdoTools->setConfig($this->config);
		}
		$this->dartLocation = $this->modx->getService('dartLocation', 'dartLocation', MODX_CORE_PATH . 'components/dartlocation/model/', array());
		if (!$this->dartLocation) {
			$this->modx->log(xPDO::LOG_LEVEL_ERROR, "shopLogistic: нужен dartLocation!");
		}
	}

	/**
	 * Initializes component into different contexts.
	 *
	 * @param string $ctx The context to load. Defaults to web.
	 * @param array $scriptProperties Properties for initialization.
	 *
	 * @return bool
	 */
	public function initialize($ctx = 'web', $scriptProperties = array())
	{
		if (isset($this->initialized[$ctx])) {
			return $this->initialized[$ctx];
		}
		$this->config = array_merge($this->config, $scriptProperties);
		$this->config['ctx'] = $ctx;
		$this->modx->lexicon->load('shoplogistic:default');

		if ($ctx != 'mgr' && (!defined('MODX_API_MODE') || !MODX_API_MODE) && !$this->config['json_response']) {
			$config = $this->pdoTools->makePlaceholders($this->config); 
			// dadata css
			//$this->modx->regClientCSS("");
			//$this->modx->regClientScript("");

			// Register CSS
			$css = trim($this->modx->getOption('shoplogistic_frontend_css'));
			if (!empty($css) && preg_match('/\.css/i', $css)) {
				if (preg_match('/\.css$/i', $css)) {
					$css .= '?v=' . substr(md5($this->config['version']), 0, 10);
				}
				$this->modx->regClientCSS(str_replace($config['pl'], $config['vl'], $css));
			}

			// Register JS
			$js = trim($this->modx->getOption('shoplogistic_frontend_js'));
			if (!empty($js) && preg_match('/\.js/i', $js)) {
				if (preg_match('/\.js$/i', $js)) {
					$js .= '?v=' . substr(md5($this->config['version']), 0, 10);
				}
				$this->modx->regClientScript(str_replace($config['pl'], $config['vl'], $js));


				$js_setting = array(
					'cssUrl' => $this->config['cssUrl'] . 'web/',
					'jsUrl' => $this->config['jsUrl'] . 'web/',
					'actionUrl' => $this->config['actionUrl'],
					'dadata_api_key' => $this->modx->getOption('shoplogistic_api_key_dadata'),

					'default_delivery' => $this->modx->getOption('shoplogistic_default_delivery'),
					'post_delivery' => $this->modx->getOption('shoplogistic_post_delivery'),
					'punkt_delivery' => $this->modx->getOption('shoplogistic_punkt_delivery'),
					'curier_delivery' => $this->modx->getOption('shoplogistic_curier_delivery'),

					'regexp_gen_code' => $this->modx->getOption('shoplogistic_regexp_gen_code'),

					'ctx' => $ctx
				);

				$data = json_encode($js_setting, true);
				$this->modx->regClientStartupScript(
					'<script>shoplogisticConfig = ' . $data . ';</script>',
					true
				);
			}
		}
		$load = $this->loadServices($ctx);
		$this->initialized[$ctx] = $load;

		// init shoplogistic
		$this->esl = new eShopLogistic($this, $this->modx);
		$this->esl->init();

		return $load;
	}

	/**
	 * @param string $ctx
	 *
	 * @return bool
	 */
	public function loadServices($ctx = 'web')
	{
		// Default classes
		if (!class_exists('eShopLogistic')) {
			require_once dirname(__FILE__) . '/eshoplogistic.class.php';
		}
		if (!class_exists('postrf')) {
			require_once dirname(__FILE__) . '/delivery/postrf.class.php';
			$this->postrf = new postrf($this, $this->modx);
		}
        if (!class_exists('tools')) {
            require_once dirname(__FILE__) . '/tools.class.php';
            $this->tools = new slTools($this, $this->modx);
        }
        if (!class_exists('cdek')) {
            require_once dirname(__FILE__) . '/delivery/cdek.class.php';
            $this->cdek = new cdek($this, $this->modx);
        }
        if (!class_exists('Yandex')) {
            require_once dirname(__FILE__) . '/delivery/yandex.class.php';
            $this->yandex = new Yandex($this, $this->modx);
        }
		if (!class_exists('slXSLX')) {
			require_once dirname(__FILE__) . '/xslx.class.php';
			$this->xslx = new slXSLX($this, $this->modx);
		}
		if (!class_exists('minishop2_fast_api')) {
			require_once dirname(__FILE__) . '/api.class.php';
			$this->api = new minishop2_fast_api($this->modx, array());
		}
        if (!class_exists('SphinxClient')) {
            require_once dirname(__FILE__) . '/sphinx.class.php';
            $this->sphinx = new SphinxClient();
        }
        if (!class_exists('objectsHandler')) {
            require_once dirname(__FILE__) . '/objects.class.php';
            $this->objects = new objectsHandler($this, $this->modx);
        }
        // Основной обработчик организаций
        if (!class_exists('storeHandler')) {
            require_once dirname(__FILE__) . '/store.class.php';
            $this->store = new storeHandler($this, $this->modx);
        }
        // Основной обработчик программ
        if (!class_exists('programHandler')) {
            require_once dirname(__FILE__) . '/program.class.php';
            $this->program = new programHandler($this, $this->modx);
        }
        // Работа с очередью
        if (!class_exists('queueHandler')) {
            require_once dirname(__FILE__) . '/queue.class.php';
            $this->queue = new queueHandler($this, $this->modx);
        }
        if (!class_exists('reportsHandler')) {
            require_once dirname(__FILE__) . '/reports.class.php';
            $this->reports = new reportsHandler($this, $this->modx);
        }
        if (!class_exists('productHandler')) {
            require_once dirname(__FILE__) . '/product.class.php';
            $this->product = new productHandler($this, $this->modx);
        }
		if (!class_exists('cartDifficultHandler')) {
			require_once dirname(__FILE__) . '/carthandler.class.php';
			$this->cart = new cartDifficultHandler($this, $this->modx);
		}
		if (!class_exists('b24Handler')) {
			require_once dirname(__FILE__) . '/b24.class.php';
			$this->b24 = new b24Handler($this, $this->modx);
		}
        if (!class_exists('slSearch')) {
            require_once dirname(__FILE__) . '/search.class.php';
            $this->search = new slSearch($this, $this->modx);
        }
        if (!class_exists('parser')) {
            require_once dirname(__FILE__) . '/parser.class.php';
            $this->parser = new parser($this, $this->modx);
        }
        if (!class_exists('parserdata')) {
            require_once dirname(__FILE__) . '/parserdata.class.php';
            $this->parserdata = new parserdata($this, $this->modx);
        }
        if (!class_exists('filters')) {
            require_once dirname(__FILE__) . '/filters.class.php';
            $this->filter = new filters($this, $this->modx);
        }
        if (!class_exists('optAnalyticsHandler')) {
            require_once dirname(__FILE__) . '/analytics/opt.class.php';
            $this->analyticsOpt = new optAnalyticsHandler($this, $this->modx);
        }
		// link ms2
		if(is_dir($this->modx->getOption('core_path').'components/minishop2/model/minishop2/')) {
			$this->ms2 = $this->modx->getService('miniShop2');
			if ($this->ms2 instanceof miniShop2) {
				$this->ms2->initialize($ctx);
			}
		}
		if(is_dir($this->modx->getOption('core_path').'components/darttelegram/model/')) {
			$corePath = $this->modx->getOption('darttelegram_core_path', array(), $this->modx->getOption('core_path') . 'components/darttelegram/');
			$this->darttelegram = $this->modx->getService('dartTelegram', 'dartTelegram', $corePath . 'model/', array());
			// $this->darttelegram = $this->modx->getService('dartTelegram');
		}
		return true;
	}

	public function clearCache(){
		// чистим pdoPage из нового класса
		array_map('unlink', glob($this->modx->getOption(xPDO::OPT_CACHE_PATH) . 'default/pdoTools/pdoPage/*.cache.php'));
		// чистим карты ресурсов
		array_map('unlink', glob($this->modx->getOption(xPDO::OPT_CACHE_PATH) . 'default/pdoTools/child_ids/*.cache.php'));
		array_map('unlink', glob($this->modx->getOption(xPDO::OPT_CACHE_PATH) . 'default/pdoTools/parent_ids/*.cache.php'));
		array_map('unlink', glob($this->modx->getOption(xPDO::OPT_CACHE_PATH) . 'default/pdoTools/product_count/*.cache.php'));
	}

	public function generateCache(){
		array_map('unlink', glob($this->modx->getOption(xPDO::OPT_CACHE_PATH) . 'default/pdoTools/pdoPage/*.cache.php'));
		array_map('unlink', glob($this->modx->getOption(xPDO::OPT_CACHE_PATH) . 'resource/web/resources/*.cache.php'));
		$this->loadServices();
		// формируем кеш чилдов
		$fqn = $this->modx->getOption('pdoFetch.class', null, 'pdotools.pdofetch', true);
		$path = $this->modx->getOption('pdofetch_class_path', null, MODX_CORE_PATH . 'components/pdotools/model/', true);
		if ($pdoClass = $this->modx->loadClass($fqn, $path, false, true)) {
			$pdoFetch = new $pdoClass($this->modx, array());
		} else {
			return false;
		}
		// сначала пробегаем по всем категориям и выставляем корректные шаблоны
		$criteria = array(
			"class_key:=" => "msCategory"
		);
		$result = $this->api->getCollection($criteria);
		if($result) {
			foreach ($result as $res) {
				// проверяем есть ли у нас дочки - категории
				$crit = array(
					"class_key:=" => "msCategory",
					"AND:parent:=" => $res['id']
				);
				$sresult = $this->api->getCollection($crit);
				if($sresult !== false){
					if(count($sresult)){
						$this->api->update('modResource', array("template" => 21),$res['id']);
						// проверим товары в категории и удалим мусор
						$c = array(
							"class_key:=" => "msProduct",
							"parent:=" => $res['id']
						);
						$products = $this->api->getCollection($c);
						if($products !== false){
							foreach($products as $product){
								// $this->api->update("modResource", array("deleted" => 1), $product['id']);
							}
						}
					}else{
						// дочек нет
						$this->api->update('modResource', array("template" => 3),$res['id']);
						// выставляем кол-во ресурсов
						$pids = $pdoFetch->getChildIds("modResource", $res['id'], 10, array());
						$this->modx->log(1, $res['id'].': '.count($pids));
						$tmp_var = array(
							"contentid" => $res['id'],
							"tmplvarid" => 51
						);
						$record = $this->api->check("modTemplateVarResource", $tmp_var);
						if(!$record){
							$tmp_var['value'] = count($pids);
							$this->api->create("modTemplateVarResource", $tmp_var);
						}else{
							if($record['value'] != count($pids)){
								$this->api->update("modTemplateVarResource", $tmp_var, $record['id']);
							}
						}
					}
				}
			}
		}
		// зафиналим верхним уровнем
		$pids = $pdoFetch->getChildIds("modResource", 2, 10, array());
	}

	public function getProductCount($parent, $without_cache = 0){
		$cache = $this->modx->getCacheManager();
		$options = array( xPDO::OPT_CACHE_KEY=>'default/pdoTools/product_count' );
		if($ids = $cache->get($parent, $options) && !$without_cache) {
			return $ids;
		}else{
			$pdo = $this->modx->getService('pdoFetch');
			$pids = array_merge(array($parent), $pdo->getChildIds("modResource", $parent, 10, array()));

			$ids = array();
			$q = $this->modx->newQuery('msProduct');
			$q->where(
				array(
					'class_key' => 'msProduct',
					'parent:IN' => $pids,
					'published' => 1,
					'deleted' => 0
				)
			);
			$q->select('COUNT(*) as count');
			if ($q->prepare() && $q->stmt->execute()) {
				$ids = $q->stmt->fetch(PDO::FETCH_ASSOC);
			}
			$cache->set($parent, $ids['count'], 604800, $options);
		}
		if(!count($ids)){
			return 0;
		}else{
			return $ids['count'];
		}
	}

	/**
	 * Handle frontend requests with actions
	 *
	 * @param $action
	 * @param array $data
	 *
	 * @return array|bool|string
	 */
	public function handleRequest($action, $data = array())
	{
		$ctx = !empty($data['ctx'])
			? (string)$data['ctx']
			: 'web';
		if ($ctx != 'web') {
			$this->modx->switchContext($ctx);
		}
		$isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest';
		$this->initialize($ctx, array('json_response' => $isAjax));
		switch ($action) {
			case 'get/suggestion':
				if ($data['value']) {
					$token = $this->modx->getOption('shoplogistic_api_key_dadata');
					$secret = $this->modx->getOption('shoplogistic_secret_key_dadata');
					$this->dadata = new Dadata($token, $secret);
					$this->dadata->init();
					$response = $this->dadata->clean("address", $data['value']);
				}
				break;
            case "get/filterdata":
                if($data["category"]){
                    if($data["hash"]){
                        $config = $_SESSION["dart_filters"][$data["hash"]];
                    }
                    $response = $this->filter->getData($data["category"], $config);
                }

                break;
			case 'delivery/get_price':
				$s = $data['service'];
				$this->loadServices($ctx);
				$delivery = $this->cart->getDeliveryPrice($data);
				// $offset = $this->getDeliveryDateOffset("cart");
				$services['main_key'] = $s;
				$response = $delivery;
				break;
			case 'delivery/add_order':
				$response = $this->esl->add_toorder($data);
				break;
			case 'apikey/generate':
				$response = $this->apikeyGenerate($data);
				break;
			case 'p_filter/set':
				$response = $this->p_filter($data);
				break;
			case 'remain/add':
				$response = $this->remainUpdate($data);
				break;
			case 'sw/update':
				$response = $this->sw_update($data);
				break;
			case 'sw/alert_change':
				$response = $this->sw_alert_change($data);
				break;
			case 'calendar/get':
				$response = $this->getCalendar($data);
				break;
			case 'city/accept':
				$response = $this->setShopCity($data);
				break;
			case 'get/cities':
				$response = $this->getCities($data);
				break;
			case 'get/stores':
				$response = $this->getStores($data);
				break;
			case 'city/status':
				$response = $this->getCityStatus($data);
				break;
			case 'city/check':
				$response = $this->checkCity($data);
				break;
			case 'city/more':
				$response = $this->cityMore($data);
				break;
			case 'store/check':
				$response = $this->checkStore($data);
				break;
			case 'status/change':
				$response = $this->changeStatus($data);
				break;
			case 'shipment/add':
				$response = $this->addShipment($data);
				break;
			case 'get/delivery':
				$response = $this->getDelivery($data);
				break;
			case 'map/build_stores':
				$response = $this->buildStoresMap($data);
				break;
            case 'search/get_preresults':
                $response = $this->getPreresult($data);
                break;
            case 'cart/update':
                $response = $this->cart->update();
                break;
		}
		return $response;
	}

    public function num_word($value, $words, $show = true)
    {
        $num = $value % 100;
        if ($num > 19) {
            $num = $num % 10;
        }

        $out = ($show) ?  $value . ' ' : '';
        switch ($num) {
            case 1:  $out .= $words[0]; break;
            case 2:
            case 3:
            case 4:  $out .= $words[1]; break;
            default: $out .= $words[2]; break;
        }

        return $out;
    }

    public function getPreresult($data, $html = 1){
        $this->loadServices();
        $output = array();
        $output['categories'] = $this->getResults($data['search'], 'msCategory', 5, 0);
        $output['products'] = $this->getResults($data['search']);
        if(count($output['categories']) < 2){
            foreach($output['products'] as $product){
                $obj = $this->modx->getObject("modResource", $product);
                if($obj){
                    $output['categories'][] = $obj->get("parent");
                }
            }
        }
        $output['categories'] = array_unique($output['categories']);
        $output['requests'] = $this->getRequests($data['search']);
        $output['search'] = $data['search'];
        $pdo = $this->modx->getService('pdoFetch');
        $chunk = "@FILE chunks/search_pre_results.tpl";
        if($html){
            $this->search->addRequest($data['search']);
            $out = $pdo->getChunk($chunk, $output);
            return $this->success("", array("data" => $out));
        }else{
            return $output;
        }
    }

    public function getRequests($search){
        $query = $this->modx->newQuery('slSearchHistory');
        $query->select(array('slSearchHistory.*'));
        $query->where(array(
            "request:LIKE" => '%'.$search.'%'
        ));
        $query->sortby('slSearchHistory.num', 'DESC');
        $query->limit(5);
        if ($query->prepare() && $query->stmt->execute()) {
            $result = $query->stmt->fetchAll(PDO::FETCH_ASSOC);
            return $result;
        }
        return false;
    }

    public function getResults($string, $type = 'msProduct', $limit = 12, $offset = 0){
        if (!class_exists('SphinxClient')) {
            require_once dirname(__FILE__) . '/sphinx.class.php';
        }
        $sphinx = new SphinxClient();
        $sphinx->SetServer('localhost', 9312);
        $sphinx->SetMatchMode(SPH_MATCH_ALL);
        $sphinx->SetSortMode(SPH_SORT_EXTENDED, "@relevance DESC, source_id ASC");
        $sphinx->SetFieldWeights(array('vendor_article' => 20, 'pagetitle' => 10));
        $sphinx->SetFilterString('class_key', $type);
        if($type == 'class_key'){
            $sphinx->SetFilter('available', array(1));
        }
        $sphinx->SetLimits($offset, $limit, 1000);
        $result = $sphinx->Query($string, 'dev_mst_tools');
        if ($result && isset($result['matches'])){
            $res = array();
            foreach($result['matches'] as $key => $val){
                $res[] = $key;
            }
            return $res;
        }else{
            return array();
        }
    }

    public function getBigResults($string, $type = 'msProduct', $limit = 12, $offset = 0){
        if (!class_exists('SphinxClient')) {
            require_once dirname(__FILE__) . '/sphinx.class.php';
        }
        $sphinx = new SphinxClient();
        $sphinx->SetServer('localhost', 9312);
        $sphinx->SetMatchMode(SPH_MATCH_ALL);
        $sphinx->SetSortMode(SPH_SORT_EXTENDED, "@relevance DESC, source_id ASC");
        $sphinx->SetFieldWeights(array('vendor_article' => 20, 'pagetitle' => 10));
        $sphinx->SetFilterString('class_key', $type);
        if($type == 'class_key'){
            $sphinx->SetFilter('available', array(1));
        }
        $sphinx->SetLimits($offset, $limit, 1000);
        $result = $sphinx->Query($string, 'dev_mst_tools');
        if ($result && isset($result['matches'])){
            return $result;
        }else{
            return false;
        }
    }

    /**
     * Генерим sitemap и возвращаем XML
     *
     * @return void
     */
    public function generateSitemap(){
        $query = $this->modx->newQuery("modResource");
        $query->where(array(
           "contentType" => "text/html"
        ));
        $total = $this->modx->getCount("modResource", $query);

        $limit = 1000;

        $output['index'] = ceil($total / $limit) - 1;
        if(isset($_GET["sitemap"])){
            $query = $this->modx->newQuery("modResource");
            $query->where(array(
                "contentType" => "text/html"
            ));
            $offset = $limit * $_GET["sitemap"];
            $query->limit($limit, $offset);
            $query->select(array("modResource.*"));
            if($query->prepare() && $query->stmt->execute()){
                $output["items"] = $query->stmt->fetchAll(PDO::FETCH_ASSOC);
                $pdo = $this->modx->getService('pdoFetch');
                $chunk = "@FILE chunks/sitemap_small.tpl";
                $out = $pdo->getChunk($chunk, $output);
            }
        }else{
            $pdo = $this->modx->getService('pdoFetch');
            $chunk = "@FILE chunks/sitemap_big.tpl";
            $out = $pdo->getChunk($chunk, $output);
        }

        return $out;
    }

    /**
     * Берем объект
     *
     * @param $id
     * @param $type
     * @return array
     */

	public function getObject($id, $type = 'slStores'){
        // $this->modx->log(1, $type.' '.$id );
		$output = array();
		if($id){
			$sql = "SELECT * FROM {$this->modx->getTableName($type)} WHERE `id` = {$id} LIMIT 1";
			$q = $this->modx->prepare($sql);
			$q->execute();
			$str = $q->fetch(PDO::FETCH_ASSOC);
			if($str) {
				$output = $str;
			}
            // $this->modx->log(1, print_r($output, 1));
		}
		return $output;
	}

    /**
     * Берем текущую позицию
     *
     * @param $ctx
     * @return array
     */
    public function getLocationData($ctx = false){
        if($ctx){
            $fias_id = '';
            if(!empty($_SESSION['dartlocation'][$ctx]['settlement_fias_id'])){
                $fias_id = $_SESSION['dartlocation'][$ctx]['settlement_fias_id'];
            }else{
                $fias_id = $_SESSION['dartlocation'][$ctx]['city_fias_id'];
            }

            $query = $this->modx->newQuery("dartLocationCity");
            $query->where(array("dartLocationCity.fias_id:=" => $fias_id));
            $query->select(array("dartLocationCity.*"));
            $query->prepare();

            if($query->prepare() && $query->stmt->execute()){
                $result = $query->stmt->fetch(PDO::FETCH_ASSOC);
                $dartlocation = $_SESSION['dartlocation'][$ctx];
                $dartlocation['city_id'] = $result['id'];
                //$this->modx->log(1, print_r($dartlocation, 1));
            }


            return $dartlocation;
        }else{
            return $_SESSION['dartlocation'];
        }
	}

    /**
     * Строим карту магазинов
     *
     * @param $data
     * @return array|string|void
     */
	public function buildStoresMap($data){
		$output = array();
		$location = $this->getLocationData($data['ctx']);

		if($location){
			$coords_data = array(
				$location['geo_lat'],
				$location['geo_lon']
			);
			$output['center'] = $coords_data;
			if($data['filter']){
				$stores = $this->dartLocation->get_nearby('slStores', $coords_data, 9999, $data['filter']);
			}else{
				$stores = $this->dartLocation->get_nearby('slStores', $coords_data, 9999);
			}

			$output['html_stores'] = '';
			foreach($stores as $key => $store) {
                // TODO: ID убрать в переменную
                $store['redirect'] = $this->modx->makeURL(1);
                $store['data'] = json_encode($store);
                $stores[$key]['data'] = json_encode($store);
                $stores[$key]['text'] = $this->pdoTools->getChunk("@FILE chunks/sl_store_baloon.tpl", $store);
                if ($data['type'] == 'modal'){
                    $output['html_stores'] .= $this->pdoTools->getChunk("@FILE chunks/sl_modal_store_list.tpl", $store);
                }else {
                    if ($data['type'] == 'list'){
                        $output['html_stores'] .= $this->pdoTools->getChunk("@FILE chunks/sl_store_list_all.tpl", $store);
                    }else{
                        $output['html_stores'] .= $this->pdoTools->getChunk("@FILE chunks/sl_store_list.tpl", $store);
                    }
                }
			}
			if($stores){
				$output['stores'] = $stores;
			}else{
				$output['stores'] = array();
			}
			return $this->success('', $output);
		}else{
			$this->failure('Не определено местоположение пользователя.', $data);
		}
	}

    /**
     * Берем по AJAX доставку
     *
     * @param $data
     * @return array|string
     */
	public function getDelivery($data){
		$output = '';
		if($data['id']){
			$props = array(
				"id" => $data["id"],
				"type" => $data['type'],
				"from_id" => $data['from_id'],
				"tpl" => "@FILE chunks/tpl_delivery_card.tpl"
			);
			$output = $this->modx->runSnippet("sl.get_delivery_data", $props);
		}
		return $this->success("", array("selector_id" => "#delivery_".$data["id"]."_".$data['from_id'], "selector_modal_id" => "#modal_delivery_".$data["id"]."_".$data['from_id'], "html_delivery" => $output));
	}

	public function getCityStatus(){
		$citycheck = 0;
		if(isset($_SESSION['sl_location']['pls']['citycheck'])){
			if($_SESSION['sl_location']['pls']['citycheck']){
				$citycheck = 1;
			}
		}
		if(empty($_SESSION['sl_location']) || $citycheck){
			$location = $this->getLoocationByIP();
			$store = $this->get_nearby('slStores', array($location['location']['data']['geo_lat'], $location['location']['data']['geo_lon']));
			$border = $this->modx->getOption("shoplogistic_km");
			if($store[0]['distance'] <= $border){
				// check this city
			}
			//$this->modx->log(1, print_r($location, 1));
			//$this->modx->log(1, print_r($store, 1));
			$_SESSION['sl_location'] = $location;
			$_SESSION['sl_location']['store'] = $store[0];
			$_SESSION['sl_location']['pls'] = array(
				'citycheck' => 1,
				'city' => $location['location']['value'],
				'store' => $store[0]['name']
			);
		}
		return $this->success("", $_SESSION['sl_location']);
	}

	public function addShipment($data){
		if(count($data['d'])){
			if($data['id']){
				$shipment = $this->modx->getObject("slWarehouseShipment", $data['id']);
			}else{
				$shipment = $this->modx->newObject("slWarehouseShipment");
			}
			if($shipment){
				$shipment->set("warehouse_id", $data['warehouse_id']);
				$shipment->set("date", date("Y-m-d H:i:s", $data['date']));
				$shipment->set("createdon", strftime('%Y-%m-%d %H:%M:%S'));
				$shipment->set("store_ids", implode(",", $data['d']));
				$shipment->set("description", $data['description']);
				$shipment->save();
				$response = array(
					"success" => true,
					"data" => array(
						"showSuccessModal" => true,
						'ms2_response' => "Запись в отгрузках успешно создана."
					)
				);
			}else{
				$response = array(
					"success" => false
				);
			}
		}else{
			$response = array(
				"success" => false
			);
		}
		return $response;
	}

	public function changeStatus($data){
		if($data['order_id']){
			$this->loadServices();
			$response = array(
				"success" => true,
				"data" => array(
					"showSuccessModal" => true
				)
			);
			$response['data']['ms2_response'] = $this->ms2->changeOrderStatus($data['order_id'], $data['status']);
			if($response['data']['ms2_response'] == 1){
				$response['data']['ms2_response'] = "Статус заказа успешно изменен.";
			}
			return $response;
		}
	}

	public function getBalance(){
		$user = $this->modx->getUser();
		$balance = 0;
		if($user->get('id')){
			// собираем баланс по всем магазинам
			$stores = $this->modx->getCollection("slStoreUsers", array("user_id" => $user->get('id')));
			$strs = array();
			foreach($stores as $store){
				//$strs[] = $store->get("store_id");
			}
			if($_GET['col_id']){
				$strs[] = $_GET['col_id'];
			}
			if(count($strs)){
				$bls = $this->modx->getCollection("slStores", array("id:IN" => $strs));
				foreach($bls as $bl){
					$balance += (float) $bl->get("balance");
				}
			}
		}
		return $balance;
	}

	public function setShopCity($data){
		$_SESSION['sl_location']['pls']['citycheck'] = 0;
		return array(
			"success" => true,
			"data" => array(
				"cityclose" => 1
			)
		);
	}

	public function getCalendar($data){
		$output['data'] = array(
			"calendar" => 1
		);
		$output['data']['html'] = $this->modx->runSnippet("sl.calendar", array(
			"tpl" => "@FILE chunks/sl_calendar.tpl",
			"dateSource" => 'date'
		));
		return $output;
	}

	public function sw_alert_change($data){
		$obj = 'slStoreDataRequest';
		$criteria = array(
			"col_id" => $data["id"],
			"user_id" => $this->modx->user->id,
			"type" => $data["type"],
			"active" => 1
		);
		$alert = $this->modx->getObject($obj, $criteria);
		if($alert){
			$out = $alert->toArray();
			return $this->error('<div class="sl-alert sl-alert-error">У вас уже есть активный запрос на редактирование данных. Свяжитесь с тех. поддержкой.</div>', $out);
		}else{
			$alert = $this->modx->newObject($obj);
			$alert->set("col_id", $data["id"]);
			$alert->set("user_id", $this->modx->user->id);
			$alert->set("createdon", date('Y-m-d H:i:s'));
			$alert->set("type", $data["type"]);
			$alert->set("active", 1);
			$alert->set("description", $data["description"]);
			$alert->set("properties", json_encode($data, JSON_UNESCAPED_UNICODE));
			$alert->save();
			$out = $alert->toArray();
			$out['action'] = "sw/alert_change";
			return $this->success('<div class="sl-alert sl-alert-success">Запрос на изменение данных успешно отправлен.</div>', $out);
		}
	}

	public function remainUpdate($data){
		if($data['type'] == 'slStores'){
			$obj = 'slStoresRemains';
			$col = "store_id";
		}else{
			$obj = 'slWarehouseRemains';
			$col = "warehouse_id";
		}
		$criteria = array(
			"product_id" => $data["product_id"],
			$col => $data["col_id"]
		);
		$remain = $this->modx->getObject($obj, $criteria);
		if(!$remain){
			$remain = $this->modx->newObject($obj);
			$remain->set("product_id", $data["product_id"]);
			$remain->set($col, $data["col_id"]);
		}
		$remain->set('price', $data['price']);
		$remain->set('remains', $data['remains']);
		$remain->set('description', $data['description']);
		$remain->save();
		return $this->success('<div class="sl-alert sl-alert-success">Остаток успешно сохранен</div>', $remain->toArray());
	}

	public function sw_update($data){
		$showed_data = $data;
		$type = $data['type'];
		$id = $data['id'];
		$out = array();
		$out['type'] = $type;
		if($type == 'slStores'){
			$opt_type = 'shoplogistic_open_fields_store';
		}
		if($type == 'slWarehouse'){
			$opt_type = 'shoplogistic_open_fields_warehouse';
		}
		// security fix
		$allow_fields = explode(',', $this->modx->getOption($opt_type));
		foreach($data as $k => $d){
			if (!in_array($k, $allow_fields)){
				unset($data[$k]);
			}
		}
		$obj = $this->modx->getObject($type, $id);
		if($obj){
			$obj->fromArray($data);
			$obj->save();
			$out = $obj->toArray();
			$out['type'] = $type;
			// confidential fix
			foreach($out as $key => $val){
				if(!array_key_exists($key, $showed_data)){
					unset($out[$key]);
				}
			}
			return $this->success('<div class="sl-alert sl-alert-success">Данные успешно обновлены.</div>', $out);
		}
		return $this->error('<div class="sl-alert sl-alert-error">Ошибка редактирования объекта</div>', $out);
	}

	public function p_filter($data){
		$b_criteria = array(
			'element' => 'sl.profile-products',
			'parents' => 0,
			'limit' => 10,
			'showZeroPrice' => 1,
			'type' => $data['type'],
			'col_id' => $data['col_id'],
			"tpl" => "@FILE chunks/profile_products.tpl",
			'tplPage' => '@INLINE <li class="page-item"><a class="page-link" href="{$href}" data-number="{$pageNo}">{$pageNo}</a></li>',
			'tplPageWrapper' => '@INLINE  <nav><ul class="pagination justify-content-center">{$prev}{$pages}{$next}</ul></nav>',
			'tplPageActive' => '@INLINE <li class="page-item active"><a class="page-link" href="{$href}" data-number="{$pageNo}">{$pageNo}</a></li>',
			'tplPagePrev' => '@INLINE <li class="page-item"><a class="page-link" href="{$href}" aria-label="Previous" data-number="{$pageNo}"><span aria-hidden="true">&laquo;</span><span class="sr-only">Previous</span></a></li>',
			'tplPageNext' => '@INLINE <li class="page-item"><a class="page-link" href="{$href}" aria-label="Next" data-number="{$pageNo}"><span aria-hidden="true">&raquo;</span><span class="sr-only">Next</span></a></li>',
			'tplPagePrevEmpty' => '@INLINE ',
			'tplPageNextEmpty' => '@INLINE '
		);
		if($data['type'] == 'slStores'){
			$obj = 'slStoresRemains';
			$col = "store_id";
		}else{
			$obj = 'slWarehouseRemains';
			$col = "warehouse_id";
		}
		if($data['only_remains']){
			$ids = array();
			$criteria = array(
				$col => $data['col_id']
			);
			$cols = $this->modx->getCollection($obj, $criteria);
			foreach($cols as $col){
				$ids[] = $col->get('product_id');
			}
			//$b_criteria['parents'] = 0;
			//$b_criteria['resources'] = implode(',', $ids);
			$b_criteria['where'][] = "msProduct.id IN (".implode(',', $ids).')';
		}
		if($data['name']){
			if(count($b_criteria['where'])){
				$b_criteria['where'][] = array(
					"pagetitle:LIKE" => '%'.$data['name'].'%',
					"OR:Data.article:LIKE" => '%'.$data['name'].'%',
				);
			}else{
				$b_criteria['where'] = array(
					"pagetitle:LIKE" => '%'.$data['name'].'%',
					"OR:Data.article:LIKE" => '%'.$data['name'].'%',
				);
			}
		}
		//$this->modx->log(1, print_r($b_criteria, 1));
		$out = array();
		//$this->modx->log(1, $data['spage'].'_'.$data['type'].'_'.$data['col_id']);
		$_SESSION['sl_filters'][$data['spage'].'_'.$data['type'].'_'.$data['col_id']] = $b_criteria['where'];
		$out['data'] = $this->modx->runSnippet("pdoPage", $b_criteria);
		$out['pagination'] = $this->modx->getPlaceholder('page.nav');
		$out['total'] = $this->modx->getPlaceholder('page.total');
		$out['topdo'] = 1;
		return $out;
	}

	public function apikeyGenerate($data){
		if($data['type'] && $data['id'] && $data['apikey']){
			$object = $this->modx->getObject($data['type'], $data['id']);
			if($object){
				$object->set('apikey', $data['apikey']);
				$object->save();
				if($data['type'] == 'slStores'){
					$data['type'] = 's';
				}else{
					$data['type'] = 'w';
				}
				return $this->success('', $data);
			}else{
				return $this->error('Объект не найден.');
			}
		}else{
			return $this->error('Некорректные данные.');
		}
	}

	/**
	 * Load custom js & css
	 */
	public function loadCustomOrderJsCss (){
		$this->modx->controller->addCss($this->config['cssUrl'] . 'mgr/shoplogistic.css?v='.$this->config['version']);
		$this->modx->controller->addJavascript($this->config['jsUrl'] . 'mgr/shoplogistic.js?v='.$this->config['version']);
		$this->modx->controller->addLastJavascript($this->config['jsUrl'] . 'mgr/misc/utils.js?v='.$this->config['version']);
		$this->modx->controller->addLastJavascript($this->config['jsUrl'] . 'mgr/misc/combo.js?v='.$this->config['version']);
		$this->modx->controller->addLastJavascript($this->config['jsUrl'] . 'mgr/widgets/order.info.js');
		$this->modx->controller->addLastJavascript($this->config['jsUrl'] . 'mgr/widgets/order.tab.js');

		$this->modx->controller->addHtml('<script>
            Ext.onReady(function() {
                shopLogistic.config = ' . json_encode($this->config) . ';
                shopLogistic.config.connector_url = "' . $this->config['connectorUrl'] . '";
            });
        </script>');

		$this->modx->controller->addLexiconTopic('shoplogistic:default');
	}

	/**
	 * Load custom js & css
	 */
	public function loadCustomJsCss (){
		//$this->modx->log(1, $this->config['version']);

		$this->modx->controller->addCss($this->config['cssUrl'] . 'mgr/shoplogistic.css?v='.$this->config['version']);
		$this->modx->controller->addJavascript($this->config['jsUrl'] . 'mgr/shoplogistic.js?v='.$this->config['version']);
		$this->modx->controller->addLastJavascript($this->config['jsUrl'] . 'mgr/misc/utils.js?v='.$this->config['version']);
		$this->modx->controller->addLastJavascript($this->config['jsUrl'] . 'mgr/misc/combo.js?v='.$this->config['version']);
        $this->modx->controller->addLastJavascript($this->config['jsUrl'] . 'mgr/misc/default.grid.js?v='.$this->config['version']);
        $this->modx->controller->addLastJavascript($this->config['jsUrl'] . 'mgr/misc/default.window.js?v='.$this->config['version']);
		$this->modx->controller->addLastJavascript($this->config['jsUrl'] . 'mgr/widgets/resource.tab.js?v='.$this->config['version']);
		$this->modx->controller->addLastJavascript($this->config['jsUrl'] . 'mgr/widgets/resource.panel.js?v='.$this->config['version']);
		$this->modx->controller->addLastJavascript($this->config['jsUrl'] . 'mgr/widgets/resource.grid.js?v='.$this->config['version']);
		$this->modx->controller->addLastJavascript($this->config['jsUrl'] . 'mgr/widgets/resource.windows.js?v='.$this->config['version']);
        $this->modx->controller->addLastJavascript($this->config['jsUrl'] . 'mgr/prices/grid.js?v='.$this->config['version']);
        $this->modx->controller->addLastJavascript($this->config['jsUrl'] . 'mgr/prices/windows.js?v='.$this->config['version']);
		$this->modx->controller->addLastJavascript($this->config['jsUrl'] . 'mgr/widgets/order.info.js');
		$this->modx->controller->addLastJavascript($this->config['jsUrl'] . 'mgr/widgets/order.tab.js');

		$this->modx->controller->addHtml('<script>
            Ext.onReady(function() {
                shopLogistic.config = ' . json_encode($this->config) . ';
                shopLogistic.config.connector_url = "' . $this->config['connectorUrl'] . '";
            });
        </script>');

		$this->modx->controller->addLexiconTopic('shoplogistic:default');
	}

	/**
	 * Get nearby objects by coordinats
	 * @param type $object
	 * @return array, boolean
	 */
	public function get_nearby($type, $coordinats, $limit = 1, $filter = ''){
		if(is_array($coordinats)){
			$lat = trim($coordinats[0]);
			$lng = trim($coordinats[1]);
		}else{
			$coord = explode(',', $coordinats);
			$lat = trim($coord[0]);
			$lng = trim($coord[1]);
		}
		if($filter){
			$where = 'AND (`name` LIKE "%'.$filter.'%" OR `address` LIKE "%'.$filter.'%")';
		}else{
			$where = '';
		}
		// this select have a small inaccuracy
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
			FROM {$this->modx->getTableName($type)} WHERE `active` = 1 {$where} ORDER BY distance LIMIT {$limit} ";
		$statement = $this->modx->prepare($sql);
		// $this->modx->log(1, $sql);
		if ( $statement->execute()) {
			$result = $statement->fetchAll(PDO::FETCH_ASSOC);
			return $result;
		}else{
			return false;
		}
	}

	/**
	 * Get product article by id
	 * @param type $store_id
	 * @return type
	 */
	public function getProductArticleById($id) {
		$response = $this->modx->getObject('msProductData', ['id' => $id]);

		return $response->article;
	}

	/**
	 * Get product name by id
	 * @param type $id
	 * @return type
	 */
	public function getProductNameById($id) {
		$response = $this->modx->getObject('modResource', ['id' => $id]);

		return $response->pagetitle;
	}

	/**
	 * Get store name by store id
	 * @param type $store_id
	 * @return type
	 */
	public function getStoreNameById($store_id) {
		$response = $this->modx->getObject('slStores', ['id' => $store_id]);

		return $response->name;
	}

	/**
	 * Get warehouse name by warehouse id
	 * @param type $warehouse_id
	 * @return type
	 */
	public function getWarehouseNameById($warehouse_id) {
		$response = $this->modx->getObject('slWarehouse', ['id' => $warehouse_id]);

		return $response->name;
	}

	public function getLoocationByIP(){
		$token = $this->modx->getOption("shoplogistic_api_key_dadata");
		$ch = curl_init('https://suggestions.dadata.ru/suggestions/api/4_1/rs/iplocate/address?ip=' . $_SERVER['REMOTE_ADDR']);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: Token '.$token));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_HEADER, false);
		$res = curl_exec($ch);
		curl_close($ch);

		$res = json_decode($res, true);
		return $res;
	}

	/**
	 * Run processor
	 * @param type $name
	 * @param type $params
	 * @return type
	 */
	public function runProcessor($name = '', $params = array()) {
		return $this->modx->runProcessor($name, $params, array('processors_path' => $this->config['processorsPath']))->response;
	}

	/**
	 * Base fields
	 * @return type
	 */
	public function fields() {
		return array(
			1 => 'key',
			2 => 'city',
			3 => 'city_r',
			4 => 'phone',
			5 => 'email',
			6 => 'address',
			7 => 'address_full',
			8 => 'address_coordinats',
		);
	}

	/**
	 * Get coordinats
	 * @param type $address
	 * @return type
	 */
	public function getCoordinats ($address) {
		$yandex = file_get_contents("https://geocode-maps.yandex.ru/1.x/?format=json&geocode=".$address);
		$json = json_decode($yandex, true);
		$output = str_replace(' ',',', $json["response"]["GeoObjectCollection"]["featureMember"][0]["GeoObject"]["Point"]["pos"]);
		$array = explode(",", $output);

		return  $array[1].','.$array[0];
	}

	/**
	 * Get more fields
	 * @param type $domain_id
	 * @return type
	 */
	public function getFields($domain_id) {
		/* @var pdoFetch $pdoFetch */
		if (!$pdo = $this->modx->getService('pdoFetch')) {
			return 'Could not load pdoFetch class!';
		}

		$response = $pdo->getCollection('slCityFields', array('city' => $domain_id));

		$output = [];

		if (count($response)) {
			foreach ($response as $item) {
				$output[$item['key']] = $item['value'];
			}
		}

		return $output;
	}

	/**
	 * Duplicate fields
	 * @param type $old_item
	 * @param type $new_item
	 */
	public function duplicateFields($old_item, $new_item) {
		$fields = $this->modx->getCollection('slCityFields', array('city' => $old_item));

		if (count((array)$fields)) {
			foreach ($fields as $item) {
				$fields = $this->modx->newObject('slCityFields');

				$fields->set('city', $new_item);
				$fields->set('name', $item->name);
				$fields->set('key', $item->key);
				$fields->set('value', $item->value);

				$fields->save();
			}
		}
	}

	public function checkCity($data){
		$store = $this->get_nearby('slStores', array($data['data']['data']['geo_lat'], $data['data']['data']['geo_lon']));
		$_SESSION['sl_location'] = $data['data'];
		$_SESSION['sl_location']['store'] = $store[0];
		$_SESSION['sl_location']['pls'] = array(
			'citycheck' => 0,
			'city' => $data['data']['data']['city_with_type']? : $data['data']['data']['settlement_with_type'],
			'store' => $store[0]['name']
		);
		return array(
			"success" => true,
			"data" => array(
				"reload" => true
			)
		);
	}

	public function checkStore($data){
		if($data['data']['id']){
			$store = $this->modx->getObject('slStores', $data['data']['id']);
		}else{
			$store = $this->modx->getObject('slStores', $data['data']['data']['id']);
		}
		if($store){
			$_SESSION['sl_location']['store'] = $store->toArray();
			$_SESSION['sl_location']['pls']['store'] = $_SESSION['sl_location']['store']['name'];
		}
		return array(
			"success" => true,
			"data" => array(
				"reload" => true
			)
		);
	}

	public function cityMore($data){
		$pos = array((float) $data['latitude'], (float) $data['longitude']);
		$dt = json_decode($this->getGeoData($pos), 1);
		$data = $dt['suggestions'][0];
		$this->modx->log(1, print_r($data, 1));
		$store = $this->get_nearby('slStores', array($data['data']['geo_lat'], $data['data']['geo_lon']));
		$_SESSION['sl_location']['location'] = $data;
		$_SESSION['sl_location']['store'] = $store[0];
		$_SESSION['sl_location']['pls'] = array(
			'citycheck' => 0,
			'city' => $data['data']['city_with_type']? : $data['data']['settlement_with_type'],
			'store' => $store[0]['name']
		);
		$this->modx->log(1, print_r($_SESSION['sl_location'], 1));
		return array(
			"success" => true,
			"data" => array(
				"reload" => true
			)
		);
	}

	public function getGeoData($coords){
		$url = 'https://suggestions.dadata.ru/suggestions/api/4_1/rs/geolocate/address';

		$dt = array(
			"lat" => $coords[0],
			"lon" => $coords[1],
			"count" => 1
		);
		$token = $this->modx->getOption("shoplogistic_api_key_dadata");
		$ch = curl_init();

		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($dt, JSON_UNESCAPED_UNICODE));

		$headers = array();
		$headers[] = 'Content-Type: application/json';
		$headers[] = 'Accept: application/json';
		$headers[] = 'Authorization: Token ' . $token;
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

		$result = curl_exec($ch);
		if (curl_errno($ch)) {
			echo 'Error:' . curl_error($ch);
		}
		curl_close($ch);

		$res = json_decode($result, true);

		return $result;
	}

	/**
	 * Get city suggestion by dadata
	 * @param array $data
	 * @return mixed
	 */
	public function getCities($data) {
		//$this->modx->log(1, print_r($data, 1));
		$dt = array(
			'from_bound' => array(
				"value" => "city"
			),
			'to_bound' => array(
				"value" => "city"
			),
			"restrict_value" => 1
		);
		if($data['query']) {
			$dt['query'] = $data['query'];

			$token = $this->modx->getOption("shoplogistic_api_key_dadata");
			$ch = curl_init();

			curl_setopt($ch, CURLOPT_URL, 'https://suggestions.dadata.ru/suggestions/api/4_1/rs/suggest/address');
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($dt, JSON_UNESCAPED_UNICODE));

			$headers = array();
			$headers[] = 'Content-Type: application/json';
			$headers[] = 'Accept: application/json';
			$headers[] = 'Authorization: Token ' . $token;
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

			$result = curl_exec($ch);
			if (curl_errno($ch)) {
				echo 'Error:' . curl_error($ch);
			}
			curl_close($ch);

			$res = json_decode($result, true);

			return $result;
		}
	}

	/**
	 * Вычисляем ближайший магазин откуда будет доставка
	 * @param int $product_id
	 * @return mixed
	 */
	public function getObjectDelivery($product_id, $lat = 0, $lng = 0){
		/* выходной массив
			'product_id' - ID товара
			'type' => 0 - не найдено ничего, следовательно ошибка системы -> alert в телеграм
				   => 1 - есть в наличии в ближайшем магазине магазине ($_SESSION['sl_location']['store']['id'])
				   => 2 - нет в наличии в магазине, есть в наличии у дистрибьютора магазина
				   => 3 - есть в наличии в каком-то ближайшем магазине
			       => 4 - отправляем из магазина по умолчанию
			'store_id' - ID магазина,
			'warehouse_id' - ID дистрибьютора
		*/
		$output = array(
			'product_id' => $product_id,
			'type' => 0,
			'store_id' => 0,
			'warehouse_id' => 0
		);
		// сначала проверяем есть ли в наличии в выбранном магазине
		$remains = $this->modx->getObject("slStoresRemains", array("product_id" => $product_id, "store_id" => $_SESSION['sl_location']['store']['id']));
		if($remains){
			// если в наличии
			$output['type'] = 1;
			$output['store_id'] = $_SESSION['sl_location']['store']['id'];
		}else{
			// проверяем дистра, если он привязан к магазину
			$warehouse = $this->modx->getObject("slWarehouseStores", array("store_id" => $_SESSION['sl_location']['store']['id']));
			if($warehouse){
				// товар есть у дистра
				$warehouse_id = $warehouse->get('warehouse_id');
				$remains = $this->modx->getObject("slWarehouseRemains", array("product_id" => $product_id, "warehouse_id" => $warehouse_id));
				if($remains){
					$output['type'] = 2;
					$output['warehouse_id'] = $_SESSION['sl_location']['store']['id'];
				}else{
					// товара нет у дистра ищем ближайший АКТИВНЫЙ магазин у которого есть
					if($lat == 0){
						$lat = $_SESSION['sl_location']['data']['geo_lat'];
					}
					if($lng == 0){
						$lng = $_SESSION['sl_location']['data']['geo_lon'];
					}
					$sql = "SELECT remains.*, stores.*, coordinats, (6371 * acos(cos(radians({$lat})) * cos(radians(stores.lat)) * cos(radians(stores.lng) - radians({$lng})) + sin(radians({$lat})) * sin(radians(stores.lat))) ) AS distance 
						FROM {$this->modx->getTableName('slStoresRemains')} as remains
						LEFT JOIN {$this->modx->getTableName('slStores')} as stores ON remains.store_id = stores.id 
						WHERE stores.active = 1 AND remains.product_id = {$product_id} 
						ORDER BY distance LIMIT 1";
					$statement = $this->modx->prepare($sql);
					if($statement->execute()) {
						$result = $statement->fetchAll(PDO::FETCH_ASSOC);
						if(count($result)){
							$output['type'] = 3;
							$output['store_id'] = $result[0]['store_id'];
						}else{
							$output['type'] = 4;
							$output['store_id'] = $this->modx->getOption("shoplogistic_default_store");
						}
					}
				}
			}
		}
		return $output;
	}

	/**
	 * Get city suggestion by dadata
	 * @param array $data
	 * @return mixed
	 */
	public function getStores($data) {
		//$this->modx->log(1, print_r($data, 1));
		$result = array();
		if($data['query']) {
			$query = $this->modx->newQuery("slStores");
			if($data['warehouse_id']){
				$out = array();
				$criteria = array(
					"warehouse_id" => $data['warehouse_id']
				);
				$stores = $this->modx->getCollection("slWarehouseStores", $criteria);
				foreach($stores as $store){
					$s = $store->getOne("Store");
					if($s){
						$out['stores'][] = $s->get('id');
					}
				}
				$query->where(
					array(
						"name:LIKE" => "%".$data['query']."%",
						"AND:id:IN" => $out['stores']
					)
				);
			}else{
				$query->where(
					array(
						"name:LIKE" => "%".$data['query']."%",
					)
				);
			}
			$query->limit(5,0);
			$stores = $this->modx->getCollection('slStores', $query);
			foreach($stores as $store){
				$tmp = array();
				$tmp["value"] = $store->get('name');
				$tmp["data"] = $store->toArray();
				$result['suggestions'][] = $tmp;
			}

			return $result;
		}
	}

	public function getCityNameById($id){
		$object = $this->modx->getObject("dartLocationCity", $id);
		if($object){
			return $object->get('city');
		}
	}

	/**
	 * @return bool
	 */
	public function isAjaxRequest()
	{
		if (
			isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
			strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest'
		) {
			return true;
		}
		return false;
	}

	/**
	 * @return false
	 */
	public function isAjaxRequestInAssets()
	{
		if (!$this->isAjaxRequest()) return false;
		$assetsUrl = $this->modx->getOption('assets_url', null, MODX_ASSETS_URL);
		$assetsUrl = preg_quote($assetsUrl, '/');
		return (bool)preg_match("/^{$assetsUrl}/", $_SERVER['REQUEST_URI']);

	}

	/**
	 * Function for formatting dates
	 *
	 * @param string $date Source date
	 *
	 * @return string $date Formatted date
	 */
	public function formatDate($date = '')
	{
		$df = $this->modx->getOption('ms2_date_format', null, '%d.%m.%Y %H:%M');

		return (!empty($date) && $date !== '0000-00-00 00:00:00')
			? strftime($df, strtotime($date))
			: '&nbsp;';
	}


	/**
	 * Function for price format
	 *
	 * @param $price
	 *
	 * @return int|mixed|string
	 */
	public function formatPrice($price = 0)
	{
		if (!$pf = json_decode($this->modx->getOption('ms2_price_format', null, '[2, ".", " "]'), true)) {
			$pf = array(2, '.', ' ');
		}
		$price = number_format($price, $pf[0], $pf[1], $pf[2]);

		if ($this->modx->getOption('ms2_price_format_no_zeros', null, true)) {
			$tmp = explode($pf[1], $price);
			$tmp[1] = rtrim(rtrim(@$tmp[1], '0'), '.');
			$price = !empty($tmp[1])
				? $tmp[0] . $pf[1] . $tmp[1]
				: $tmp[0];
		}

		return $price;
	}


	/**
	 * Function for weight format
	 *
	 * @param $weight
	 *
	 * @return int|mixed|string
	 */
	public function formatWeight($weight = 0)
	{
		if (!$wf = json_decode($this->modx->getOption('ms2_weight_format', null, '[3, ".", " "]'), true)) {
			$wf = array(3, '.', ' ');
		}
		$weight = number_format($weight, $wf[0], $wf[1], $wf[2]);

		if ($this->modx->getOption('ms2_weight_format_no_zeros', null, true)) {
			$tmp = explode($wf[1], $weight);
			$tmp[1] = rtrim(rtrim(@$tmp[1], '0'), '.');
			$weight = !empty($tmp[1])
				? $tmp[0] . $wf[1] . $tmp[1]
				: $tmp[0];
		}

		return $weight;
	}

	public function remove_dir($dir)
	{
		if ($objs = glob($dir . '/*')) {
			foreach($objs as $obj) {
				is_dir($obj) ? $this->remove_dir($obj) : unlink($obj);
			}
		}
		rmdir($dir);
	}
	/**
	 * Shorthand for original modX::invokeEvent() method with some useful additions.
	 *
	 * @param $eventName
	 * @param array $params
	 * @param $glue
	 *
	 * @return array
	 */
	public function invokeEvent($eventName, array $params = array(), $glue = '<br/>')
	{
		if (isset($this->modx->event->returnedValues)) {
			$this->modx->event->returnedValues = null;
		}

		$response = $this->modx->invokeEvent($eventName, $params);
		if (is_array($response) && count($response) > 1) {
			foreach ($response as $k => $v) {
				if (empty($v)) {
					unset($response[$k]);
				}
			}
		}

		$message = is_array($response) ? implode($glue, $response) : trim((string)$response);
		if (isset($this->modx->event->returnedValues) && is_array($this->modx->event->returnedValues)) {
			$params = array_merge($params, $this->modx->event->returnedValues);
		}

		return array(
			'success' => empty($message),
			'message' => $message,
			'data' => $params,
		);
	}

    // telegram alert
    public function sendAlert($message = '', $data = array()){
        return true;
    }

	/**
	 * This method returns an error of the order
	 *
	 * @param string $message A lexicon key for error message
	 * @param array $data .Additional data, for example cart status
	 * @param array $placeholders Array with placeholders for lexicon entry
	 *
	 * @return array|string $response
	 */
	public function error($message = '', $data = array(), $placeholders = array())
	{
		$response = array(
			'success' => false,
			//'message' => $this->modx->lexicon($message, $placeholders),
			'message' => $message,
			'data' => $data,
		);

		return $this->config['json_response']
			? json_encode($response)
			: $response;
	}


	/**
	 * This method returns an success of the order
	 *
	 * @param string $message A lexicon key for success message
	 * @param array $data .Additional data, for example cart status
	 * @param array $placeholders Array with placeholders for lexicon entry
	 *
	 * @return array|string $response
	 */
	public function success($message = '', $data = array(), $placeholders = array())
	{
		$response = array(
			'success' => true,
			'message' => $this->modx->lexicon($message, $placeholders),
			'data' => $data,
		);

		return $this->config['json_response']
			? json_encode($response)
			: $response;
	}
}