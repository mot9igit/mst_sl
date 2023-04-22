<?php
$corePath = $modx->getOption('shoplogistic_core_path', array(), $modx->getOption('core_path') . 'components/shoplogistic/');
$shopLogistic = $modx->getService('shopLogistic', 'shopLogistic', $corePath . 'model/');
/** @var modX $modx */
switch ($modx->event->name) {
	case 'OnMODXInit':
		/*
		$modx->loadClass('msOrder');
		$modx->map['msOrder']['fields']['store_id'] = 0;
		$modx->map['msOrder']['fieldMeta']['store_id'] = [
			'dbtype' => 'int',
			'precision' => 11,
			'phptype' => 'integer',
			'null' => true,
			'default' => 0
		];
		$modx->map['msOrder']['fields']['warehouse_id'] = 0;
		$modx->map['msOrder']['fieldMeta']['warehouse_id'] = [
			'dbtype' => 'int',
			'precision' => 11,
			'phptype' => 'integer',
			'null' => true,
			'default' => 0
		];
		$modx->map['msOrder']['fields']['view_ids'] = '';
		$modx->map['msOrder']['fieldMeta']['view_ids'] = [
			'dbtype' => 'varchar',
			'precision' => 255,
			'phptype' => 'string',
			'null' => true,
			'default' => ''
		];
		$modx->loadClass('msOrderProduct');
		$modx->map['msOrderProduct']['fields']['type'] = '';
		$modx->map['msOrderProduct']['fieldMeta']['type'] = [
			'dbtype' => 'text',
			'phptype' => 'string',
			'null' => true,
			'default' => ''
		];*/
		break;
	case 'OnLoadWebDocument':
		$scriptProperties = array();
		$corePath = $modx->getOption('shoplogistic_core_path', array(), $modx->getOption('core_path') . 'components/shoplogistic/');
		$shopLogistic = $modx->getService('shopLogistic', 'shopLogistic', $corePath . 'model/');
		if (!$shopLogistic) {
			$modx->log(xPDO::LOG_LEVEL_ERROR, 'Could not load shoplogistic class!');
		}else{
			$shopLogistic->initialize($modx->context->key);
		}
		if(is_dir($modx->getOption('core_path').'components/minishop2/model/minishop2/')) {
			$ms2 = $modx->getService('miniShop2');
			if ($ms2 instanceof miniShop2) {
				$context = $modx->context->key ? $modx->context->key : 'web';
				$ms2->initialize($context, ['json_response' => true]);
				$ms2->order->remove('sl_data');
			}
		}
		break;
	case 'OnDocFormRender':
		$corePath = $modx->getOption('shoplogistic_core_path', array(), $modx->getOption('core_path') . 'components/shoplogistic/');
		$shopLogistic = $modx->getService('shopLogistic', 'shopLogistic', $corePath . 'model/');
		if (!$shopLogistic) {
			$modx->log(xPDO::LOG_LEVEL_ERROR, 'Could not load shoplogistic class!');
		}
		$controller->shopLogistic = $shopLogistic;
		$controller->shopLogistic->loadCustomJsCss();
		$modx->regClientStartupHTMLBlock('
            <script type="text/javascript">
                Ext.onReady(function() {
                    shopLogistic.config.richtext = ' . $resource->richtext . ';
                });
            </script>
        ');
		break;
	case 'msOnManagerCustomCssJs':
		if(!empty($scriptProperties['page'])) {
			if($scriptProperties['page'] == 'orders') {
				$corePath = $modx->getOption('shoplogistic_core_path', array(), $modx->getOption('core_path') . 'components/shoplogistic/');
				$controller->shopLogistic = $modx->getService('shopLogistic', 'shopLogistic', $corePath . 'model/');
				$controller->shopLogistic->loadCustomOrderJsCss();
				$modx->controller->addHtml("
        	        <script>
                    Ext.ComponentMgr.onAvailable('minishop2-window-order-update', function(){
                        let orderTab = this.fields.items[2].items
                        let obj = {
                            layout: 'column',
                            defaults: {
                                msgTarget: 'under',
                                border: false
                            },
                            anchor: '100%',
                            items: [
                                { 
                                    columnWidth: 1,
                                    layout: 'form',
                                    items:[
                                        {
                                            title: 'Магазины и склады',
                                            xtype: 'fieldset',
                                            id: 'minishop2-fieldset-tc',
                                            labelAlign: 'top',
                                            autoHeight: true,
                                            border: false,
                                            items: [
                                                {
                                                    xtype: 'shoplogistic-combo-store',
                            						name: 'store_id',
                            						fieldLabel: 'Дилер',
                            						anchor: '100%',
                                                    value: this.record.store_id
                                                },{
                                                    xtype: 'shoplogistic-combo-warehouse',
                            						name: 'warehouse_id',
                            						fieldLabel: 'Дистрибьютор',
                            						anchor: '100%',
                                                    value: this.record.warehouse_id
                                                }
                                            ]
                                        }
                                    ]
                                }
                            ]
                        }
                        orderTab.push(obj)
                    });                
                </script>");
			}
		}
		break;
	case 'msOnGetProductFields':
		$returned_values = & $modx->event->returnedValues;

		$corePath = $modx->getOption('shoplogistic_core_path', array(), $modx->getOption('core_path') . 'components/shoplogistic/');
		$shopLogistic = $modx->getService('shopLogistic', 'shopLogistic', $corePath . 'model/');
		if (!$shopLogistic) {
			$modx->log(xPDO::LOG_LEVEL_ERROR, 'Could not load shoplogistic class!');
		}

		$ctx = $modx->context->key;
		$location = $shopLogistic->getLocationData($ctx);

		$values =  $modx->event->params['data'];
		$product_id = $values['id'];

		// проверяем остаток в магазине
		$criteria = array(
			"product_id:=" => $product_id,
			"AND:available:>=" => 1
		);
		if($modx->getOption("shoplogistic_cart_mode") == 2){
			$criteria['AND:store_id:='] = $location['store']['id'];
		}
		// остатки магазина
		// TODO: предусмотреть работу по 1 режиму
		$find = 0;
		$remains = $modx->getObject("slStoresRemains", $criteria);
		if($remains){
			$returned_values['price'] = $remains->get('price');
			$find = 1;
		}else{
			$criteria = array(
				"product_id:=" => $product_id,
				"AND:available:>=" => 1
			);
			if($modx->getOption("shoplogistic_cart_mode") == 2){
				$criteria['AND:warehouse_id:IN'] = $location['store']['whs'];
				$remains = $modx->getObject("slWarehouseRemains", $criteria);
				if($remains){
					$returned_values['price'] = $remains->get('price');
					$find = 1;
				}
			}
		}
		if(!$find){
			$query = $modx->newQuery("slStoresRemains");
			$query->where(
				array(
					"product_id:=" => $product_id,
					"AND:price:>" => 0
				)
			);
			$query->sortby('price', 'ASC');
			$query->select(array(
				'slStoresRemains.*',
			));
			$query->prepare();
			if($query->stmt->execute()){
				$res = $query->stmt->fetchAll(PDO::FETCH_ASSOC);
				foreach($res as $key => $r){
					$store = $modx->getObject("slStores",  $r['store_id']);
					if($store){
						if($store->get('active')){
							$res[$key]['store'] = $store->toArray();
						}else{
							unset($res[$key]);
						}
					}else{
						unset($res[$key]);
					}
				}
				$res = array_values($res);
				if(count($res)){
					$stores = array();
					$returned_values['price'] = $res[0]['price'];
					$returned_values['price_prefix'] = 1;
					$modx->setPlaceholder("store_".$product_id, $res[0]['store']);
					unset($res[0]);
					if(count($res)){
						foreach($res as $r){
							$stores[] = $r;
						}
					}
					if(count($stores)){
						$modx->setPlaceholder("stores_".$product_id, $stores);
					}
				}else{
					//$returned_values['available'] = 99;
					//$returned_values['price'] = 0;
				}
			}else{
				//$returned_values['available'] = 99;
				//$returned_values['price'] = 0;
			}
		}else{
			$modx->setPlaceholder("store_".$product_id, $location['store']);
		}
		break;
	case 'msOnAddToCart':
		// выставляем стоимость в зависимости от магазина
		$corePath = $modx->getOption('shoplogistic_core_path', array(), $modx->getOption('core_path') . 'components/shoplogistic/');
		$shopLogistic = $modx->getService('shopLogistic', 'shopLogistic', $corePath . 'model/');
		if (!$shopLogistic) {
			$modx->log(xPDO::LOG_LEVEL_ERROR, 'Could not load shoplogistic class!');
		}
		$shopLogistic->loadServices();
		$tmp = $cart->get();
		$modx->log(1, print_r($tmp, 1));
		foreach ($tmp as $k => $value) {
			$product_id = $value['id'];
			if(isset($value['options']['store'])){
				$remains = $shopLogistic->cart->getRemains('slStores', $value['options']['store'], $product_id, $value['count']);
				$modx->log(1, print_r($remains, 1));
				if($remains){
					if($remains['remains']['price']){
						$tmp[$k]['price'] = $remains['remains']['price'];
					}
				}
			}else{
				$position = $shopLogistic->cart->getUserPosition();
				$remains = $shopLogistic->cart->getRemains('slStores', $position['data']['store']['id'], $product_id, $value['count']);
				if(!$remains){
					$remains = $shopLogistic->cart->getRemains('slWarehouse', $position['data']['store']['id'], $product_id, $value['count']);
					if($remains){
						if($remains['remains']['price']){
							$tmp[$k]['price'] = $remains['remains']['price'];
						}
					}
				}else{
					if($remains['remains']['price']){
						$tmp[$k]['price'] = $remains['remains']['price'];
					}
				}
			}
		}
		$cart->set($tmp);
		break;
	case 'msOnCreateOrder':
		$order_data = $order->get();
		$sl_data = [];
		if(!empty($order_data['sl_data'])) {
			$sl_data = json_decode($order_data['sl_data'], 1);
		}
		if(!empty($sl_data)) {
			$order_properties = $msOrder->get('properties');
			$order_properties['sl'] = $sl_data;
			$msOrder->set('properties', $order_properties);
			$msOrder->save();
		}
		if(isset($_SESSION['sl_location']['store']['id'])){
			$msOrder->set('store_id', $_SESSION['sl_location']['store']['id']);
			$msOrder->save();
		}
		break;
	case 'msOnChangeOrderStatus':
		if ($status = $modx->getObject('msOrderStatus', array('id' => $status, 'active' => 1))) {
			if ($miniShop2 = $modx->getService('miniShop2')) {
				$miniShop2->initialize($modx->context->key, array(
					'json_response' => true,
				));
				if (!($miniShop2 instanceof miniShop2)) {
					return;
				}
				if($status->id == 3){
					$pls = $order->toArray();
					$tax = $modx->getOption('shoplogistic_tax_percent') / 100;
					$cost = $pls['cart_cost'] * (1 - $tax);
					$store_id = $pls['store_id'];

					// add log
					$balance = $modx->newObject("slStoreBalance");
					$balance->set("store_id", $store_id);
					$balance->set("type", 1);
					$balance->set("value", $cost);
					$balance->set("createdon", date('Y-m-d H:i:s'));
					$balance->set("description", "Начисление за заказ №".$pls['num']);
					$balance->save();

					//add to store
					$store = $modx->getObject("slStores", $store_id);
					if($store){
						$b = $store->get('balance');
						$store->set('balance', $b + $cost);
						$store->save();
					}
				}
			}
		}
		break;
}