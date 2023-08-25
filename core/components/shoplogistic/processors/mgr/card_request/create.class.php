<?php

class slCardRequestCreateProcessor extends modObjectCreateProcessor
{
    public $objectType = 'slCardRequest';
    public $classKey = 'slCardRequest';
    public $languageTopics = ['shoplogistic'];
    //public $permission = 'create';


    /**
     * @return bool
     */
    public function beforeSet()
    {
        $remain_id = trim($this->getProperty('remain_id'));
        if (empty($remain_id)) {
            $this->modx->error->addField('remain_id', $this->modx->lexicon('shoplogistic_card_request_err_name'));
        } elseif ($this->modx->getCount($this->classKey, ['remain_id' => $remain_id])) {
            $this->modx->error->addField('remain_id', $this->modx->lexicon('shoplogistic_card_request_err_ae'));
        }

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

return 'slCardRequestCreateProcessor';