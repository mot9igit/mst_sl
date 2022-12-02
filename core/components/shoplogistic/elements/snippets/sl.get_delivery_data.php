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

$tpl = $modx->getOption('tpl', $scriptProperties, '@FILE chunks/sl_delivery_data.tpl');
$id = $modx->getOption('id', $scriptProperties, $modx->resource->id);

$shopLogistic->loadServices();

$delivery_data = $shopLogistic->cart->getDeliveryInfoDays($id);

$output = $pdoFetch->getChunk($tpl, $delivery_data);
return $output;