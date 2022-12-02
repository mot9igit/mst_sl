<?php

class slWarehouseRemainsCreateProcessor extends modObjectCreateProcessor
{
    public $objectType = 'slWarehouseRemains';
    public $classKey = 'slWarehouseRemains';
    public $languageTopics = ['shoplogistic'];
    //public $permission = 'create';


    /**
     * @return bool
     */
    public function beforeSet()
    {
        $product_id = trim($this->getProperty('product_id'));
		$warehouse_id = trim($this->getProperty('warehouse_id'));
        if (empty($product_id)) {
            $this->modx->error->addField('product_id', $this->modx->lexicon('shoplogistic_warehouseremains_err_product_id'));
        } elseif ($this->modx->getCount($this->classKey, ['product_id' => $product_id, 'warehouse_id' => $warehouse_id])) {
            $this->modx->error->addField('product_id', $this->modx->lexicon('shoplogistic_warehouseremains_err_double'));
        }

        return parent::beforeSet();
    }

}

return 'slWarehouseRemainsCreateProcessor';