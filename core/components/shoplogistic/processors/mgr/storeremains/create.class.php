<?php

class slStoreRemainsCreateProcessor extends modObjectCreateProcessor
{
    public $objectType = 'slStoresRemains';
    public $classKey = 'slStoresRemains';
    public $languageTopics = ['shoplogistic'];
    //public $permission = 'create';


    /**
     * @return bool
     */
    public function beforeSet()
    {
        $guid = trim($this->getProperty('guid'));
		$store_id = trim($this->getProperty('store_id'));
        if (empty($product_id)) {
            // $this->modx->error->addField('product_id', $this->modx->lexicon('shoplogistic_storeremains_err_product_id'));
        } elseif ($this->modx->getCount($this->classKey, ['guid' => $guid, 'store_id' => $store_id])) {
            $this->modx->error->addField($guid, $this->modx->lexicon('shoplogistic_storeremains_err_double'));
        }

        return parent::beforeSet();
    }

}

return 'slStoreRemainsCreateProcessor';