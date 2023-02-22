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
		$remains = $modx->getObject("slStoresRemains", $criteria);
		if($remains){
			$returned_values['price'] = $remains->get('price');
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
				}
			}
		}
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