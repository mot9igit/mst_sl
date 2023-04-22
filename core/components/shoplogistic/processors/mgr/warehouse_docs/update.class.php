<?php

class slWarehouseDocsUpdateProcessor extends modObjectUpdateProcessor
{
    public $objectType = 'slWarehouseDocs';
    public $classKey = 'slWarehouseDocs';
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
        return parent::beforeSet();
    }
}

return 'slWarehouseDocsUpdateProcessor';
