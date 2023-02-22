<?php

class slWarehouseRemainsUpdateProcessor extends modObjectUpdateProcessor
{
    public $objectType = 'slWarehouseRemains';
    public $classKey = 'slWarehouseRemains';
    public $languageTopics = ['shoplogistic'];
    //public $permission = 'save';


    /**
     * We doing special check of permission
     * because of our objects is not an instances of modAccessibleObject
     *
     * @return bool|string
     */
    public function beforeSave()
    {
        if (!$this->checkPermissions()) {
            return $this->modx->lexicon('access_denied');
        }

        return true;
    }


    /**
     * @return bool
     */
    public function beforeSet()
    {
        $id = (int)$this->getProperty('id');
        if (empty($id)) {
            return $this->modx->lexicon('shoplogistic_store_err_ns');
        }



		$guid = trim($this->getProperty('guid'));
		$warehouse_id = trim($this->getProperty('warehouse_id'));
		$obj = $this->modx->getObject($this->classKey, ['guid' => $guid, 'warehouse_id' => $warehouse_id]);
		if (empty($guid)) {
			$this->modx->error->addField('guid', $this->modx->lexicon('shoplogistic_warehouseremains_err_product_id'));
		} elseif ($obj->id != $id) {
			$this->modx->error->addField('guid', $this->modx->lexicon('shoplogistic_warehouseremains_err_double'));
		}

        return parent::beforeSet();
    }
}

return 'slWarehouseRemainsUpdateProcessor';
