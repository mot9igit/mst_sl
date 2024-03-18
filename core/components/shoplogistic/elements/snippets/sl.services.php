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
$shopLogistic->loadServices($modx->context->key);

$init = $shopLogistic->cart->getDeliveries();

$services = array();
foreach($init['data'] as $key => $val){
    $tmp = array(
        "name" => $val["name"],
        "logo" => $val["logo"]
    );
    $services[$key] = $tmp;
}

$modx->log(1, print_r($services, 1));

$output = $pdoFetch->getChunk($tpl, array('services' => $services));
return $output;