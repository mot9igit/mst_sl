<?php

/*
 *
 * Обработчик отчетов по крону
 *
 */

define('MODX_API_MODE', true);
if (file_exists(dirname(dirname(dirname(dirname(dirname(__FILE__))))) . '/index.php')) {
    /** @noinspection PhpIncludeInspection */
    require_once dirname(dirname(dirname(dirname(dirname(__FILE__))))) . '/index.php';
} else {
    require_once dirname(dirname(dirname(dirname(dirname(dirname(dirname(__FILE__))))))) . '/index.php';
}

$modx->getService('error', 'error.modError');
$modx->setLogLevel(modX::LOG_LEVEL_ERROR);
$modx->setLogTarget('FILE');

$corePath = $modx->getOption('shoplogistic_core_path', array(), $modx->getOption('core_path') . 'components/shoplogistic/');
$shopLogistic = $modx->getService('shopLogistic', 'shopLogistic', $corePath . 'model/');
$modx->lexicon->load('shoplogistic:default');

// handle request
$corePath = $modx->getOption('shoplogistic_core_path', null, $modx->getOption('core_path') . 'components/shoplogistic/');
$path = $modx->getOption('processorsPath', $shopLogistic->config, $corePath . 'processors/');

$shopLogistic->loadServices();

$shopLogistic->reports->handleReports();