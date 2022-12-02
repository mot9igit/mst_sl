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

$output = array();

$output['balance'] = $miniShop2->formatPrice($shopLogistic->getBalance());

$out = $pdoFetch->getChunk($tpl, $output);
return $out;