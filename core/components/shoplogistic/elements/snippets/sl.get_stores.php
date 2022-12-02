<?php
$corePath = $modx->getOption('shoplogistic_core_path', array(), $modx->getOption('core_path') . 'components/shoplogistic/');
$shopLogistic = $modx->getService('shopLogistic', 'shopLogistic', $corePath . 'model/');
if (!$shopLogistic) {
	return 'Could not load shoplogistic class!';
}

if (!$modx->loadClass('pdofetch', MODX_CORE_PATH . 'components/pdotools/model/pdotools/', false, true)) {
	return false;
}
$pdoFetch = new pdoFetch($modx, $scriptProperties);
$out = array();

$coords_data = array(
	$_SESSION['sl_location']['location']['data']['geo_lat']? :$_SESSION['sl_location']['data']['geo_lat'],
	$_SESSION['sl_location']['location']['data']['geo_lon']? :$_SESSION['sl_location']['data']['geo_lon']
);

//$modx->log(1, print_r($_SESSION['sl_location'], 1));
$stores = $shopLogistic->get_nearby('slStores', $coords_data, 9999);

//$modx->log(1,print_r($stores, 1));

if (is_array($stores) || is_object($stores)){
	foreach($stores as $store){
		$out['stores'][] =  $store;
	}
}

$output = $pdoFetch->getChunk($tpl, $out);
return $output;