<?php

class slStoresRemainsHistoryCreateProcessor extends modObjectCreateProcessor
{
    public $objectType = 'slStoresRemainsHistory';
    public $classKey = 'slStoresRemainsHistory';
    public $languageTopics = ['shoplogistic'];
    //public $permission = 'create';


    /**
     * @return bool
     */
    public function beforeSet()
    {
        $guid = trim($this->getProperty('guid'));
		$remain_id = trim($this->getProperty('remain_id'));
        if (empty($remain_id)) {
            $this->modx->error->addField('remain_id', "Не заполнен REMAIN_ID!");
        }
        return parent::beforeSet();
    }

}

return 'slStoresRemainsHistoryCreateProcessor';