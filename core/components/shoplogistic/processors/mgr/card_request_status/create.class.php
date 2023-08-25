<?php

class slCardRequestStatusCreateProcessor extends modObjectCreateProcessor
{
    public $objectType = 'slCardRequestStatus';
    public $classKey = 'slCardRequestStatus';
    public $languageTopics = ['shoplogistic'];
    //public $permission = 'create';


    /**
     * @return bool
     */
    public function beforeSet()
    {
        $name = trim($this->getProperty('name'));
        if (empty($name)) {
            $this->modx->error->addField('name', $this->modx->lexicon('shoplogistic_card_request_status_err_name'));
        } elseif ($this->modx->getCount($this->classKey, ['name' => $name])) {
            $this->modx->error->addField('name', $this->modx->lexicon('shoplogistic_card_request_status_err_ae'));
        }

        return parent::beforeSet();
    }

}

return 'slCardRequestStatusCreateProcessor';