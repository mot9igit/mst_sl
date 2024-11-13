<?php

class slStoresRemainsGroupsCreateProcessor extends modObjectCreateProcessor
{
    public $objectType = 'slStoresRemainsGroups';
    public $classKey = 'slStoresRemainsGroups';
    public $languageTopics = ['shoplogistic'];
    //public $permission = 'create';


    /**
     * @return bool
     */
    public function beforeSet()
    {
        $guid = trim($this->getProperty('guid'));
		$store_id = trim($this->getProperty('store_id'));
        if (empty($store_id)) {
            $this->modx->error->addField('store_id', "Не заполнен STORE_ID!");
        }
        return parent::beforeSet();
    }

}

return 'slStoresRemainsGroupsCreateProcessor';