<?php

class slSettingsCreateProcessor extends modObjectCreateProcessor
{
    public $objectType = 'slSettings';
    public $classKey = 'slSettings';
    public $languageTopics = ['shoplogistic'];
    //public $permission = 'create';


    /**
     * @return bool
     */
    public function beforeSet()
    {
        $name = trim($this->getProperty('key'));
        if (empty($name)) {
            $this->modx->error->addField('key', $this->modx->lexicon('shoplogistic_setting_err_name'));
        } elseif ($this->modx->getCount($this->classKey, ['key' => $name])) {
            $this->modx->error->addField('key', $this->modx->lexicon('shoplogistic_setting_err_ae'));
        }

        return parent::beforeSet();
    }

}

return 'slSettingsCreateProcessor';