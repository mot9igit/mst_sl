<?php

class slStoreBalanceUpdateProcessor extends modObjectUpdateProcessor
{
    public $objectType = 'slStoreBalance';
    public $classKey = 'slStoreBalance';
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

        // TODO: check operation and make return to default value

        return parent::beforeSet();
    }
}

return 'slStoreBalanceUpdateProcessor';
