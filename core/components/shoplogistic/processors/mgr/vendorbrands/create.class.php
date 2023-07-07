<?php

class slStoresBrandsCreateProcessor extends modObjectCreateProcessor
{
    public $objectType = 'slStoresBrands';
    public $classKey = 'slStoresBrands';
    public $languageTopics = ['shoplogistic'];
    //public $permission = 'create';


    /**
     * @return bool
     */
    public function beforeSet()
    {
        $store = trim($this->getProperty('store_id'));
		$vendor = trim($this->getProperty('vendor_id'));
        if (empty($store)) {
            $this->modx->error->addField('store_id', $this->modx->lexicon('shoplogistic_vendorbrands_err_store_id'));
        } elseif ($this->modx->getCount($this->classKey, ['store_id' => $store, 'vendor_id' => $vendor])) {
            $this->modx->error->addField('store_id', $this->modx->lexicon('shoplogistic_vendorbrands_err_ae'));
        }

        return parent::beforeSet();
    }

}

return 'slStoresBrandsCreateProcessor';