<?php

class slWarehouseUsersCreateProcessor extends modObjectCreateProcessor
{
    public $objectType = 'slWarehouseUsers';
    public $classKey = 'slWarehouseUsers';
    public $languageTopics = ['shoplogistic'];
    //public $permission = 'create';


    /**
     * @return bool
     */
    public function beforeSet()
    {
        $user = trim($this->getProperty('user_id'));
		$store = trim($this->getProperty('warehouse_id'));
        if (empty($user)) {
            $this->modx->error->addField('user_id', $this->modx->lexicon('shoplogistic_warehouseusers_err_user_id'));
        } elseif ($this->modx->getCount($this->classKey, ['user_id' => $user, 'warehouse_id' => $store])) {
            $this->modx->error->addField('user_id', $this->modx->lexicon('shoplogistic_warehouseusers_err_ae'));
        }

        return parent::beforeSet();
    }

}

return 'slWarehouseUsersCreateProcessor';