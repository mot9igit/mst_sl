<?php

class slWarehouseShipmentStatusRemoveProcessor extends modObjectProcessor
{
    public $objectType = 'slWarehouseShipmentStatus';
    public $classKey = 'slWarehouseShipmentStatus';
    public $languageTopics = ['shoplogistic'];
    //public $permission = 'remove';


    /**
     * @return array|string
     */
    public function process()
    {
        if (!$this->checkPermissions()) {
            return $this->failure($this->modx->lexicon('access_denied'));
        }

        $ids = $this->modx->fromJSON($this->getProperty('ids'));
        if (empty($ids)) {
            return $this->failure($this->modx->lexicon('shoplogistic_shipment_status_err_ns'));
        }

        foreach ($ids as $id) {
            /** @var shopLogisticItem $object */
            if (!$object = $this->modx->getObject($this->classKey, $id)) {
                return $this->failure($this->modx->lexicon('shoplogistic_shipment_status_err_nf'));
            }

            $object->remove();
        }

        return $this->success();
    }

}

return 'slWarehouseShipmentStatusRemoveProcessor';