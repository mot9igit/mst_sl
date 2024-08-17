<?php

class slSettingsGroupCreateProcessor extends modObjectCreateProcessor
{
    public $objectType = 'slSettingsGroup';
    public $classKey = 'slSettingsGroup';
    public $languageTopics = ['shoplogistic'];
    //public $permission = 'create';


    /**
     * @return bool
     */
    public function beforeSet()
    {
        $name = trim($this->getProperty('name'));
        if (empty($name)) {
            $this->modx->error->addField('name', $this->modx->lexicon('shoplogistic_settings_group_err_name'));
        } elseif ($this->modx->getCount($this->classKey, ['name' => $name])) {
            $this->modx->error->addField('name', $this->modx->lexicon('shoplogistic_settings_group_err_ae'));
        }

        return parent::beforeSet();
    }

}

return 'slSettingsGroupCreateProcessor';