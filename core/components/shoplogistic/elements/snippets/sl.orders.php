<?php
$corePath = $modx->getOption('shoplogistic_core_path', array(), $modx->getOption('core_path') . 'components/shoplogistic/');
$shopLogistic = $modx->getService('shopLogistic', 'shopLogistic', $corePath . 'model/');
if (!$shopLogistic) {
	return 'Could not load shoplogistic class!';
}

$miniShop2 = $modx->getService('miniShop2');
$miniShop2->initialize($modx->context->key);

if (!$modx->loadClass('pdofetch', MODX_CORE_PATH . 'components/pdotools/model/pdotools/', false, true)) {
	return 'Could not load pdoFetch class!';
}
$pdoFetch = new pdoFetch($modx, $scriptProperties);
// снипет выводит заказы текущего пользователя
$strs = array();

if($_GET['type'] == 'slWarehouse'){
	/*
	$criteria = array(
		"warehouse_id" => $_GET['col_id']
	);
	$stores = $modx->getCollection("slWarehouseStores", $criteria);
	foreach($stores as $store){
		$strs[] = $store->get("store_id");
	}
	*/
	if($_GET['col_id']){
		$strs[] = $_GET['col_id'];
	}
	$where = array(
		'warehouse_id:IN' => $strs
	);
}else{
	if($_GET['col_id']){
		$strs[] = $_GET['col_id'];
	}
	$where = array(
		'store_id:IN' => $strs
	);
}
if($_GET['show_proccessed'] == 1){
	$where["AND:status:IN"] = array(3,4);
}else{
	$where["AND:status:NOT IN"] = array(3,4);
}
$q = $modx->newQuery('msOrder');
$q->where($where);
$q->sortby('id','DESC');
$total = $modx->getCount("msOrder", $q);
$limit = $modx->getOption('limit', $scriptProperties, 10);
$offset = $modx->getOption('offset', $scriptProperties, 0);
$q->limit($limit, $offset);
$totalVar = $modx->getOption('totalVar', $scriptProperties, 'page.total');
$results = $modx->getCollection('msOrder', $q);
$data = array();

$output = '';
// выводим или чанками или предупреждением что заказов пока нет

foreach ($results as $result) {
	$pdoFetch->addTime('pdoTools loaded.');

	// Select ordered products
	$where = array(
		'msOrderProduct.order_id' => $result->get("id"),
	);

	// Include products properties
	$leftJoin = array(
		'msProduct' => array(
			'class' => 'msProduct',
			'on' => 'msProduct.id = msOrderProduct.product_id',
		),
		'Data' => array(
			'class' => 'msProductData',
			'on' => 'msProduct.id = Data.id',
		),
		'Vendor' => array(
			'class' => 'msVendor',
			'on' => 'Data.vendor = Vendor.id',
		),
	);

	// Select columns
	$select = array(
		'msProduct' => !empty($includeContent)
			? $modx->getSelectColumns('msProduct', 'msProduct')
			: $modx->getSelectColumns('msProduct', 'msProduct', '', array('content'), true),
		'Data' => $modx->getSelectColumns(
				'msProductData',
				'Data',
				'',
				array('id'),
				true
			) . ',`Data`.`price` as `original_price`',
		'Vendor' => $modx->getSelectColumns('msVendor', 'Vendor', 'vendor.', array('id'), true),
		'OrderProduct' => $modx->getSelectColumns('msOrderProduct', 'msOrderProduct', '', array('id'), true) . ', `msOrderProduct`.`id` as `order_product_id`',
	);

	// Include products thumbnails
	if (!empty($includeThumbs)) {
		$thumbs = array_map('trim', explode(',', $includeThumbs));
		if (!empty($thumbs[0])) {
			foreach ($thumbs as $thumb) {
				$leftJoin[$thumb] = array(
					'class' => 'msProductFile',
					'on' => "`{$thumb}`.product_id = msProduct.id AND `{$thumb}`.parent != 0 AND `{$thumb}`.path LIKE '%/{$thumb}/%'",
				);
				$select[$thumb] = "`{$thumb}`.url as '{$thumb}'";
			}
			$pdoFetch->addTime('Included list of thumbnails: <b>' . implode(', ', $thumbs) . '</b>.');
		}
	}

	// Add user parameters
	foreach (array('where', 'leftJoin', 'select') as $v) {
		if (!empty($scriptProperties[$v])) {
			$tmp = $scriptProperties[$v];
			if (!is_array($tmp)) {
				$tmp = json_decode($tmp, true);
			}
			if (is_array($tmp)) {
				$$v = array_merge($$v, $tmp);
			}
		}
		unset($scriptProperties[$v]);
	}
	$pdoFetch->addTime('Conditions prepared');

	// Tables for joining
	$default = array(
		'class' => 'msOrderProduct',
		'where' => $where,
		'leftJoin' => $leftJoin,
		'select' => $select,
		'joinTVsTo' => 'msProduct',
		'sortby' => 'msOrderProduct.id',
		'sortdir' => 'asc',
		'groupby' => 'msOrderProduct.id',
		'fastMode' => false,
		'return' => 'data',
		'decodeJSON' => true,
		'nestedChunkPrefix' => 'sl_',
	);
	// Merge all properties and run!

	$pdoFetch->setConfig(array_merge($default, $scriptProperties), true);
	$rows = $pdoFetch->run();



	$products = array();
	$cart_count = 0;
	foreach ($rows as $product) {
		$product['old_price'] = $miniShop2->formatPrice(
			$product['original_price'] > $product['price']
				? $product['original_price']
				: $product['old_price']
		);
		$product['price'] = $miniShop2->formatPrice($product['price']);
		$product['cost'] = $miniShop2->formatPrice($product['cost']);
		$product['weight'] = $miniShop2->formatWeight($product['weight']);

		$product['id'] = (int)$product['id'];
		if (empty($product['name'])) {
			$product['name'] = $product['pagetitle'];
		} else {
			$product['pagetitle'] = $product['name'];
		}

		// Additional properties of product
		if (!empty($product['options']) && is_array($product['options'])) {
			foreach ($product['options'] as $option => $value) {
				$product['option.' . $option] = $value;
			}
		}

		// Add option values
		$options = $modx->call('msProductData', 'loadOptions', array($modx, $product['id']));
		$products[] = array_merge($product, $options);

		// Count total
		$cart_count += $product['count'];
	}



	$pls = array_merge($scriptProperties, array(
		'order' => $result->toArray(),
		'products' => $products,
		'user' => ($tmp = $result->getOne('User'))
			? array_merge($tmp->getOne('Profile')->toArray(), $tmp->toArray())
			: array(),
		'address' => ($tmp = $result->getOne('Address'))
			? $tmp->toArray()
			: array(),
		'delivery' => ($tmp = $result->getOne('Delivery'))
			? $tmp->toArray()
			: array(),
		'payment' => ($payment = $result->getOne('Payment'))
			? $payment->toArray()
			: array(),
		'status' => ($status = $result->getOne('Status'))
			? $status->toArray()
			: array(),
		'total' => array(
			'cost' => $miniShop2->formatPrice($result->get('cost')),
			'cart_cost' => $miniShop2->formatPrice($result->get('cart_cost')),
			'delivery_cost' => $miniShop2->formatPrice($result->get('delivery_cost')),
			'weight' => $miniShop2->formatWeight($result->get('weight')),
			'cart_weight' => $miniShop2->formatWeight($result->get('weight')),
			'cart_count' => $cart_count,
		),
	));

	//$modx->log(1, print_r($pls, 1));

	$output .= $pdoFetch->getChunk($tpl, $pls);
	$output_modal .= $pdoFetch->getChunk($modal_tpl, $pls);
}
$modx->regClientHTMLBlock($output_modal);
$modx->setPlaceholder($totalVar, $total);
return $output;