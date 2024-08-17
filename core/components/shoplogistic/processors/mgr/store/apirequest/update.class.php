<?php

class slAPIRequestHistoryUpdateProcessor extends modObjectUpdateProcessor
{
    public $objectType = 'slAPIRequestHistory';
    public $classKey = 'slAPIRequestHistory';
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
            return $this->modx->lexicon('shoplogistic_apirequest_err_ns');
        }
        if ($this->modx->getCount($this->classKey, ['id:=' => $id])) {
            return $this->modx->lexicon('shoplogistic_apirequest_err_nf');
        }

        return parent::beforeSet();
    }
}

return 'slAPIRequestHistoryUpdateProcessor';
