<?php

class slTTypePriceGetListProcessor extends modProcessor {

    public function process() {
        $corePath = $this->modx->getOption('shoplogistic_core_path', array(), $this->modx->getOption('core_path') . 'components/shoplogistic/');
        $shopLogistic = $this->modx->getService('shopLogistic', 'shopLogistic', $corePath . 'model/');
        if (!$shopLogistic) {
            return $this->error('Could not load shoplogistic class!', array());
        }
        $shopLogistic->loadServices('web');
        $scriptProperties = $this->getProperties();
        $output = $shopLogistic->analyticsOpt->getPrices(array("id" => $scriptProperties['store_id']));

        return $this->outputArray($output);
    }
}

return 'slTTypePriceGetListProcessor';