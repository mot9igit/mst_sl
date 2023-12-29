<?php

class slPricesCreateProcessor extends modObjectCreateProcessor
{
    public $objectType = 'slPrices';
    public $classKey = 'slPrices';
    public $languageTopics = ['shoplogistic'];
    //public $permission = 'create';


    /**
     * @return bool|string
     */
    public function beforeSave()
    {

        $this->object->set('createdby', $this->modx->user->get('id'));
        $this->object->set('createdon', time());

        return parent::beforeSave();
    }

}

return 'slPricesCreateProcessor';