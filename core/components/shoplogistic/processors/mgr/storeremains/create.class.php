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
        $product_id = trim($this->getProperty('product_id'));
		$store_id = trim($this->getProperty('store_id'));
        if (empty($product_id)) {
            $this->modx->error->addField('product_id', $this->modx->lexicon('shoplogistic_storeremains_err_product_id'));
        } elseif ($this->modx->getCount($this->classKey, ['product_id' => $product_id, 'store_id' => $store_id])) {
            $this->modx->error->addField('product_id', $this->modx->lexicon('shoplogistic_storeremains_err_double'));
        }

        return parent::beforeSet();
    }

}

return 'slStoreRemainsCreateProcessor';