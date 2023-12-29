<?php

class slStoreBalancePayRequestCreateProcessor extends modObjectCreateProcessor
{
    public $objectType = 'slStoreBalancePayRequest';
    public $classKey = 'slStoreBalancePayRequest';
    public $languageTopics = ['shoplogistic'];
    //public $permission = 'create';


    /**
     * @return bool
     */
    public function beforeSet()
    {

        return parent::beforeSet();
    }

    /**
     * @return bool|string
     */
    public function beforeSave()
    {
        $this->object->set('created_by', $this->modx->user->get('id'));
        $this->object->set('createdon', time());

        return parent::beforeSave();
    }

}

return 'slStoreBalancePayRequestCreateProcessor';