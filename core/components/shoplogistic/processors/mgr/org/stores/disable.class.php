<?php

class slOrgStoresDisableProcessor extends modObjectProcessor
{
    public $objectType = 'slOrgStores';
    public $classKey = 'slOrgStores';
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
                return $this->failure($this->modx->lexicon('shoplogistic_delivery_err_nf'));
            }

            $object->set('active', false);
            $object->save();

        }

        return $this->success();
    }

}

return 'slOrgStoresDisableProcessor';
