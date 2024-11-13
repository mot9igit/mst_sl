<?php

class slStoresRemainsGroupsUpdateProcessor extends modObjectUpdateProcessor
{
    public $objectType = 'slStoresRemainsGroups';
    public $classKey = 'slStoresRemainsGroups';
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

		$store_id = trim($this->getProperty('store_id'));
		if (empty($store_id)) {
			$this->modx->error->addField('store_id', "Не указан STORE_ID!");
		}

        return parent::beforeSet();
    }
}

return 'slStoresRemainsGroupsUpdateProcessor';
