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

$user = $modx->getUser();
if($user->get('id')){
	// собираем баланс по всем магазинам
	$stores = $modx->getCollection("slStoreUsers", array("user_id" => $user->get('id')));
	$strs = array();
	/*foreach($stores as $store){
		$strs[] = $store->get("store_id");
	}*/
	if($_GET['col_id']){
		$strs[] = $_GET['col_id'];
	}
	if(count($strs)){
		$bls = $modx->getCollection("slStoreBalance", array("store_id:IN" => $strs));
		foreach($bls as $bl){
			$operation_data = $bl->toArray();
			if($operation_data['type'] == 1){
				$operation_data['type_name'] = "Начисление";
			}
			if($operation_data['type'] == 2){
				$operation_data['type_name'] = "Списание";
			}
			$operation_data['value'] = $miniShop2->formatPrice($operation_data['value']);
			$output["operations"][] = $operation_data;
		}
	}
}

$out = $pdoFetch->getChunk($tpl, $output);
return $out;