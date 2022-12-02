<?php

class slWarehouseUsersUpdateProcessor extends modObjectUpdateProcessor
{
    public $objectType = 'slWarehouseUsers';
    public $classKey = 'slWarehouseUsers';
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
        $user = trim($this->getProperty('user_id'));
        if (empty($id)) {
            return $this->modx->lexicon('shoplogistic_warehouseusers_err_ns');
        }

        if (empty($user)) {
            $this->modx->error->addField('user_id', $this->modx->lexicon('shoplogistic_warehouseusers_err_user_id'));
        } elseif ($this->modx->getCount($this->classKey, ['user_id' => $user, 'id:!=' => $id])) {
            $this->modx->error->addField('user_id', $this->modx->lexicon('shoplogistic_warehouseusers_err_ae'));
        }

        return parent::beforeSet();
    }
}

return 'slWarehouseUsersUpdateProcessor';
