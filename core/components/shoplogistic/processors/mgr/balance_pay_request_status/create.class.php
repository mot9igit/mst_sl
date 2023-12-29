<?php

class slStoreBalancePayRequestStatusCreateProcessor extends modObjectCreateProcessor
{
    public $objectType = 'slStoreBalancePayRequestStatus';
    public $classKey = 'slStoreBalancePayRequestStatus';
    public $languageTopics = ['shoplogistic'];
    //public $permission = 'create';


    /**
     * @return bool
     */
    public function beforeSet()
    {
        $name = trim($this->getProperty('name'));
        if (empty($name)) {
            $this->modx->error->addField('name', $this->modx->lexicon('shoplogistic_balance_pay_request_status_err_name'));
        } elseif ($this->modx->getCount($this->classKey, ['name' => $name])) {
            $this->modx->error->addField('name', $this->modx->lexicon('shoplogistic_balance_pay_request_status_err_ae'));
        }

        return parent::beforeSet();
    }

}

return 'slStoreBalancePayRequestStatusCreateProcessor';