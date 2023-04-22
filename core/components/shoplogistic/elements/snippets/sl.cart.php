<?php
$ctx = $modx->context->key;
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

$shopLogistic->loadServices($ctx);
$cart = array();
$cart['deliveries'] = $shopLogistic->cart->checkCart();

$stores = array_unique($cart['deliveries']['stores']);
if(count($stores) > 1){
	$modx->setPlaceholder("hide_pickup", 1);
}
unset($cart['deliveries']['stores']);

$output = $pdoFetch->getChunk($cartTpl, $cart);

return $output;