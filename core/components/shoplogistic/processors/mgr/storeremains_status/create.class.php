<?php

class slStoresRemainsStatusCreateProcessor extends modObjectCreateProcessor
{
    public $objectType = 'slStoresRemainsStatus';
    public $classKey = 'slStoresRemainsStatus';
    public $languageTopics = ['shoplogistic'];
    //public $permission = 'create';


    /**
     * @return bool
     */
    public function beforeSet()
    {
        $name = trim($this->getProperty('name'));
        if (empty($name)) {
            $this->modx->error->addField('name', $this->modx->lexicon('shoplogistic_store_remains_status_err_name'));
        } elseif ($this->modx->getCount($this->classKey, ['name' => $name])) {
            $this->modx->error->addField('name', $this->modx->lexicon('shoplogistic_store_remains_status_err_ae'));
        }

        return parent::beforeSet();
    }

}

return 'slStoresRemainsStatusCreateProcessor';