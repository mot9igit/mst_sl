<?php

/**
 * Обработка данных от Я.Доставки
 *
 *
 */


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

$modx->log(xPDO::LOG_LEVEL_ERROR, "INCOMING UPDATE: ".print_r($_GET, 1), array(
    'target' => 'FILE',
    'options' => array(
        'filename' => 'yandex_incoming.log'
    )
));

$scriptProperties = array();
$corePath = $modx->getOption('shoplogistic_core_path', array(), $modx->getOption('core_path') . 'components/shoplogistic/');
$shopLogistic = $modx->getService('shopLogistic', 'shopLogistic', $corePath . 'model/');
if (!$shopLogistic) {
    return 'Could not load shoplogistic class!';
}

$shopLogistic->loadServices('web');

if($_GET["updates_ts"] && $_GET["claim_id"]){
    $this->shopLogistic->queue->addTask("tk/yandex/check", $_GET);
}

@session_write_close();