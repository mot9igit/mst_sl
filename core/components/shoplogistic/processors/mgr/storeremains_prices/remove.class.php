<?php

class slStoresRemainsPricesRemoveProcessor extends modObjectProcessor
{
    public $objectType = 'slStoresRemainsPrices';
    public $classKey = 'slStoresRemainsPrices';
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
            return $this->failure($this->modx->lexicon('shoplogistic_store_remains_prices_err_ns'));
        }

        foreach ($ids as $id) {
            /** @var slStoresRemainsPrices $object */
            if (!$object = $this->modx->getObject($this->classKey, $id)) {
                return $this->failure($this->modx->lexicon('shoplogistic_store_remains_prices_err_nf'));
            }

            $object->remove();
        }

        return $this->success();
    }

}

return 'slStoresRemainsPricesRemoveProcessor';