<?php

class slDeliveryCreateProcessor extends modObjectCreateProcessor
{
    public $objectType = 'slDelivery';
    public $classKey = 'slDelivery';
    public $languageTopics = ['shoplogistic'];
    //public $permission = 'create';


    /**
     * @return bool
     */
    public function beforeSet()
    {
        $name = trim($this->getProperty('name'));
        if (empty($name)) {
            $this->modx->error->addField('name', $this->modx->lexicon('shoplogistic_delivery_err_name'));
        } elseif ($this->modx->getCount($this->classKey, ['name' => $name])) {
            $this->modx->error->addField('name', $this->modx->lexicon('shoplogistic_delivery_err_ae'));
        }

        return parent::beforeSet();
    }

}

return 'slDeliveryCreateProcessor';