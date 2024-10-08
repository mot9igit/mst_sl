<?php

class slStoresDisableProcessor extends modObjectProcessor
{
    public $objectType = 'slStores';
    public $classKey = 'slStores';
    public $languageTopics = ['shoplogistic'];
    public $checkViewPermission = false;
    //public $permission = 'save';

    public function initialize(){
        return true;
    }

    public function checkPermissions() {
        return true;
    }

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
            return $this->failure($this->modx->lexicon('shoplogistic_store_err_ns'));
        }

        foreach ($ids as $id) {
            /** @var shopLogisticItem $object */
            if (!$object = $this->modx->getObject($this->classKey, $id)) {
                return $this->failure($this->modx->lexicon('shoplogistic_store_err_nf'));
            }

            $object->set('active', false);
            $object->save();

            $corePath = $this->modx->getOption('shoplogistic_core_path', array(), $this->modx->getOption('core_path') . 'components/shoplogistic/');
            $shopLogistic = $this->modx->getService('shopLogistic', 'shopLogistic', $corePath . 'model/');
            if ($shopLogistic) {
                $shopLogistic->loadServices("web");
                // Ставим на товар статус НЕ в наличии
                if($object->get('marketplace')){
                    $shopLogistic->product->changeAvailableStatus($id, 99);
                }
            }

        }

        return $this->success();
    }

}

return 'slStoresDisableProcessor';
