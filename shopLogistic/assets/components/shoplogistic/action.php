<?php
define('MODX_API_MODE', true);
if (file_exists(dirname(dirname(dirname(dirname(__FILE__)))) . '/index.php')) {
	/** @noinspection PhpIncludeInspection */
	require_once dirname(dirname(dirname(dirname(__FILE__)))) . '/index.php';
} else {
	require_once dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))) . '/index.php';
}

$modx->getService('error', 'error.modError');
$modx->setLogLevel(modX::LOG_LEVEL_ERROR);
$modx->setLogTarget('FILE');

$scriptProperties = array();
$corePath = $modx->getOption('shoplogistic_core_path', array(), $modx->getOption('core_path') . 'components/shoplogistic/');
$shopLogistic = $modx->getService('shopLogistic', 'shopLogistic', $corePath . 'model/');
if (!$shopLogistic) {
	return 'Could not load shoplogistic class!';
}

if (empty($_SERVER['HTTP_X_REQUESTED_WITH']) || $_SERVER['HTTP_X_REQUESTED_WITH'] != 'XMLHttpRequest') {
	$modx->sendRedirect($modx->makeUrl($modx->getOption('site_start'), '', '', 'full'));
}else{
	$out = $shopLogistic->handleRequest($_REQUEST['sl_action'], @$_REQUEST);
	if(is_array ($out)){
		echo $response = json_encode($out);
	}else{
		echo $response = $out;
	}
}
@session_write_close();