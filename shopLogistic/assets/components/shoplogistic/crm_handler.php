<?php
// token e7k2p9s9w16unj5j35sqvhbj0nauaxxe
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

$modx->log(xPDO::LOG_LEVEL_ERROR, "INCOMING UPDATE", array(
    'target' => 'FILE',
    'options' => array(
        'filename' => 'b24_debug_icoming.log'
    )
));

$scriptProperties = array();
$corePath = $modx->getOption('shoplogistic_core_path', array(), $modx->getOption('core_path') . 'components/shoplogistic/');
$shopLogistic = $modx->getService('shopLogistic', 'shopLogistic', $corePath . 'model/');
if (!$shopLogistic) {
    return 'Could not load shoplogistic class!';
}

$shopLogistic->loadServices('web');

$token = "e7k2p9s9w16unj5j35sqvhbj0nauaxxe";

if($_REQUEST['auth']['application_token'] == $token){
    $modx->log(xPDO::LOG_LEVEL_ERROR, "INCOMING UPDATE", array(
        'target' => 'FILE',
        'options' => array(
            'filename' => 'b24_debug_icoming.log'
        )
    ));
    if($_REQUEST['data']['FIELDS']['ID']){
        // если пришел ID то нужно получить сделку
        $shopLogistic->b24->initialize();
        $response = $shopLogistic->b24->getDeal($_REQUEST['data']['FIELDS']['ID']);
        // ищем связь и проверяем статус
        if($response['result']['ID'] == $_REQUEST['data']['FIELDS']['ID']) {
            $order = $modx->getObject("slOrder", array("crm_id" => $response['result']['ID']));
            if ($order) {
                $status = $order->get('status');
                $stage = $shopLogistic->cart->getStageId($response['result']['STAGE_ID']);
                if($stage){
                    if ($status != $stage['id']) {
                        $shopLogistic->cart->changeOrderStage($order->get('id'), $stage['id'], false);
                    }
                }
            }
        }
    }else{
        $modx->log(xPDO::LOG_LEVEL_ERROR, "Нет ID сделки", array(
            'target' => 'FILE',
            'options' => array(
                'filename' => 'b24_debug_icoming.log'
            )
        ));
    }
}else{
    $modx->log(xPDO::LOG_LEVEL_ERROR, "INCORRECT TOKEN", array(
        'target' => 'FILE',
        'options' => array(
            'filename' => 'b24_debug_icoming.log'
        )
    ));
}

@session_write_close();