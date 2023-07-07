<?php

class slStoresBrandsRemoveProcessor extends modObjectProcessor
{
    public $objectType = 'slStoresBrands';
    public $classKey = 'slStoresBrands';
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
            return $this->failure($this->modx->lexicon('shoplogistic_vendorbrands_err_remove'));
        }

        foreach ($ids as $id) {
            /** @var shopLogisticItem $object */
            if (!$object = $this->modx->getObject($this->classKey, $id)) {
                return $this->failure($this->modx->lexicon('shoplogistic_vendorbrands_err_remove'));
            }

            $object->remove();
        }

        return $this->success();
    }

}

return 'slStoresBrandsRemoveProcessor';