<?php

class slPricesUpdateProcessor extends modObjectUpdateProcessor
{
    public $objectType = 'slPrices';
    public $classKey = 'slPrices';
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

        $this->object->set('updatedby', $this->modx->user->get('id'));
        $this->object->set('updatedon', time());

        return parent::beforeSave();
    }

}

return 'slPricesUpdateProcessor';
