<?php

class shopLogisticItemCreateProcessor extends modObjectCreateProcessor
{
    public $objectType = 'shopLogisticItem';
    public $classKey = 'shopLogisticItem';
    public $languageTopics = ['shoplogistic'];
    //public $permission = 'create';


    /**
     * @return bool
     */
    public function beforeSet()
    {
        $name = trim($this->getProperty('name'));
        if (empty($name)) {
            $this->modx->error->addField('name', $this->modx->lexicon('shoplogistic_item_err_name'));
        } elseif ($this->modx->getCount($this->classKey, ['name' => $name])) {
            $this->modx->error->addField('name', $this->modx->lexicon('shoplogistic_item_err_ae'));
        }

        return parent::beforeSet();
    }

}

return 'shopLogisticItemCreateProcessor';