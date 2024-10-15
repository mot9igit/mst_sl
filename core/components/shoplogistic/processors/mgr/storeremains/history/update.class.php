<?php

class slStoresRemainsHistoryUpdateProcessor extends modObjectUpdateProcessor
{
    public $objectType = 'slStoresRemainsHistory';
    public $classKey = 'slStoresRemainsHistory';
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
            return "Не указан ID!";
        }

		$remain_id = trim($this->getProperty('remain_id'));
		if (empty($remain_id)) {
			$this->modx->error->addField('remain_id', "Не указан REMAIN_ID!");
		}

        return parent::beforeSet();
    }
}

return 'slStoresRemainsHistoryUpdateProcessor';
