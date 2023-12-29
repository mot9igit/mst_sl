<?php

class slStoreBalancePayRequestUpdateProcessor extends modObjectUpdateProcessor
{
    public $objectType = 'slStoreBalancePayRequest';
    public $classKey = 'slStoreBalancePayRequest';
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

        $this->object->set('updated_by', $this->modx->user->get('id'));
        $this->object->set('updatedon', time());

        return parent::beforeSave();
    }


    /**
     * @return bool
     */
    public function beforeSet()
    {
        $id = (int)$this->getProperty('id');
        if (empty($id)) {
            return $this->modx->lexicon('shoplogistic_balance_pay_request_err_ns');
        }

        return parent::beforeSet();
    }

}

return 'slStoreBalancePayRequestUpdateProcessor';
