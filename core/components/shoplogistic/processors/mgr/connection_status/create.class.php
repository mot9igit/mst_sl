<?php

class slBonusesConnectionStatusCreateProcessor extends modObjectCreateProcessor
{
    public $objectType = 'slBonusesConnectionStatus';
    public $classKey = 'slBonusesConnectionStatus';
    public $languageTopics = ['shoplogistic'];
    //public $permission = 'create';


    /**
     * @return bool
     */
    public function beforeSet()
    {
        $name = trim($this->getProperty('name'));
        if (empty($name)) {
            $this->modx->error->addField('name', $this->modx->lexicon('shoplogistic_connection_status_err_name'));
        } elseif ($this->modx->getCount($this->classKey, ['name' => $name])) {
            $this->modx->error->addField('name', $this->modx->lexicon('shoplogistic_connection_status_err_ae'));
        }

        return parent::beforeSet();
    }

}

return 'slBonusesConnectionStatusCreateProcessor';