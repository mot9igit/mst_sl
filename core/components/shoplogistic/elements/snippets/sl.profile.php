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

$id = $modx->user->id;
$out = array();

if($id){
	$criteria = array(
		"user_id" => $id
	);
	if($type != 'ss'){
		$whs = $modx->getCollection("slWarehouseUsers", $criteria);
		if($whs){
			$whsids = array();
			foreach($whs as $wh){
				$whsids[] = $wh->get("warehouse_id");
			}
			$cr = array(
				'id:IN' =>  $whsids,
				'active' => 1
			);
			$warehouses = $modx->getCollection("slWarehouse", $cr);
			if($warehouses){
				foreach($warehouses as $warehouse){
					$out['warehouses'][] = $warehouse->toArray();
				}
			}
		}
	}
	if($type != 'wh'){
		$ss = $modx->getCollection("slStoreUsers", $criteria);
		if($ss){
			$ssids = array();
			foreach($ss as $s){
				$ssids[] = $s->get("store_id");
			}
			$cr = array(
				'id:IN' =>  $ssids,
				'active' => 1
			);
			$stores = $modx->getCollection("slStores", $cr);
			if($stores){
				foreach($stores as $store){
					$out['stores'][] = $store->toArray();
				}
			}
		}
	}
}

$output = $pdoFetch->getChunk($tpl, $out);
return $output;