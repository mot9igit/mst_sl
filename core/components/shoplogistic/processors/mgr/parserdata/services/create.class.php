<?php

class slParserDataServiceCreateProcessor extends modObjectCreateProcessor
{
    public $objectType = 'slParserDataService';
    public $classKey = 'slParserDataService';
    public $languageTopics = ['shoplogistic'];
    //public $permission = 'create';


    /**
     * @return bool
     */
    public function beforeSet()
    {
        $name = trim($this->getProperty('name'));
        if (empty($name)) {
            $this->modx->error->addField('name', $this->modx->lexicon('shoplogistic_parserdata_services_err_name'));
        } elseif ($this->modx->getCount($this->classKey, ['name' => $name])) {
            $this->modx->error->addField('name', $this->modx->lexicon('shoplogistic_parserdata_services_err_ae'));
        }

        return parent::beforeSet();
    }

}

return 'slParserDataServiceCreateProcessor';