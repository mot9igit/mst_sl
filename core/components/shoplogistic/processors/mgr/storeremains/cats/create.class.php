<?php

class slStoresRemainsCategoriesCreateProcessor extends modObjectCreateProcessor
{
    public $objectType = 'slStoresRemainsCategories';
    public $classKey = 'slStoresRemainsCategories';
    public $languageTopics = ['shoplogistic'];
    //public $permission = 'create';


    /**
     * @return bool
     */
    public function beforeSet()
    {
        $guid = trim($this->getProperty('guid'));
		$remain_id = trim($this->getProperty('store_id'));
        if (empty($remain_id)) {
            $this->modx->error->addField('store_id', "Не заполнен STORE_ID!");
        }
        return parent::beforeSet();
    }

}

return 'slStoresRemainsCategoriesCreateProcessor';