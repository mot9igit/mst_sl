<?php

class slWarehouseStoresCreateProcessor extends modObjectCreateProcessor
{
    public $objectType = 'slWarehouseStores';
    public $classKey = 'slWarehouseStores';
    public $languageTopics = ['shoplogistic'];
    //public $permission = 'create';


    /**
     * @return bool
     */
    public function beforeSet()
    {
        $store = trim($this->getProperty('store_id'));
		$warehouse = trim($this->getProperty('warehouse_id'));
        if (empty($store)) {
            $this->modx->error->addField('store_id', $this->modx->lexicon('shoplogistic_warehousestores_err_store_id'));
        } elseif ($this->modx->getCount($this->classKey, ['store_id' => $store, 'warehouse_id' => $warehouse])) {
            $this->modx->error->addField('store_id', $this->modx->lexicon('shoplogistic_warehousestores_err_ae'));
        }

        return parent::beforeSet();
    }

}

return 'slWarehouseStoresCreateProcessor';