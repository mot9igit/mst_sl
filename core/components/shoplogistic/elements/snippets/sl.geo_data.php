<?php
$tpl = $modx->getOption('tpl', $scriptProperties, 'sl.geodata');
$modal_tpl = $modx->getOption('modal_tpl', $scriptProperties, 'sl.geomodal');

$corePath = $modx->getOption('shoplogistic_core_path', array(), $modx->getOption('core_path') . 'components/shoplogistic/');
$shopLogistic = $modx->getService('shopLogistic', 'shopLogistic', $corePath . 'model/');
if (!$shopLogistic) {
	return 'Could not load shoplogistic class!';
}

if (!$modx->loadClass('pdofetch', MODX_CORE_PATH . 'components/pdotools/model/pdotools/', false, true)) {
	return false;
}
$pdoFetch = new pdoFetch($modx, $scriptProperties);
$data = array();

if(isset($_SESSION['sl_location'])){
	$data = $_SESSION['sl_location']['pls'];
}

$output = $pdoFetch->getChunk($tpl, $data);
$modal = $pdoFetch->getChunk($modal_tpl, $data);
$modx->regClientHTMLBlock($modal);

echo $output;