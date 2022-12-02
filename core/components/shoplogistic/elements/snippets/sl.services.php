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

$shopLogistic->esl = new eShopLogistic($shopLogistic, $modx);

$init = $shopLogistic->esl->init();

$services = array();
foreach($init['data']['services'] as $key => $val){
	$tmp = array(
		"name" => $val["name"],
		"from" => $val["city_code"],
		"logo" => $val["logo"]
	);
	$services[$key] = $tmp;
}

$output = $pdoFetch->getChunk($tpl, array('services' => $services));
return $output;