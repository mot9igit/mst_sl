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

$cities = $modx->getCollection('slCityCity');

foreach($cities as $city){
	$tmp = $city->toArray();
	$pos = array((float) $tmp['lat'], (float) $tmp['lng']);
	if(!$tmp['properties']) {
		$data = json_decode($shopLogistic->getGeoData($pos), 1);
		if (count($data['suggestions'])) {
			$tmp['data'] = str_replace("{", "{ ", json_encode($data['suggestions'][0]));
		}
		$city->set('properties', $tmp['data']);
		$city->save();
	}else{
		$tmp['data'] = str_replace("{", "{ ", json_encode($tmp['properties']));
	}
	$out['cities'][] =  $tmp;
}

if($tpl){
	$output = $pdoFetch->parseChunk($tpl, $out);
	return $output;
}else{
	echo "<pre>";
	print_r($out);
	echo "</pre>";
}