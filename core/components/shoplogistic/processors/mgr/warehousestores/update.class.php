<?php

class slWarehouseStoresUpdateProcessor extends modObjectUpdateProcessor
{
    public $objectType = 'slWarehouseStores';
    public $classKey = 'slWarehouseStores';
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
        $store = trim($this->getProperty('store_id'));
        if (empty($id)) {
            return $this->modx->lexicon('shoplogistic_warehousestore_err_ns');
        }

        if (empty($store)) {
            $this->modx->error->addField('store_id', $this->modx->lexicon('shoplogistic_warehousestore_err_user_id'));
        } elseif ($this->modx->getCount($this->classKey, ['store_id' => $store, 'id:!=' => $id])) {
            $this->modx->error->addField('store_id', $this->modx->lexicon('shoplogistic_warehousestore_err_ae'));
        }

        return parent::beforeSet();
    }
}

return 'slWarehouseStoresUpdateProcessor';
