<?php

class slParserDataTasksStatusCreateProcessor extends modObjectCreateProcessor
{
    public $objectType = 'slParserDataTasksStatus';
    public $classKey = 'slParserDataTasksStatus';
    public $languageTopics = ['shoplogistic'];
    //public $permission = 'create';


    /**
     * @return bool
     */
    public function beforeSet()
    {
        $name = trim($this->getProperty('name'));
        if (empty($name)) {
            $this->modx->error->addField('name', $this->modx->lexicon('shoplogistic_parserdata_statuses_err_name'));
        } elseif ($this->modx->getCount($this->classKey, ['name' => $name])) {
            $this->modx->error->addField('name', $this->modx->lexicon('shoplogistic_parserdata_statuses_err_ae'));
        }

        return parent::beforeSet();
    }

}

return 'slParserDataTasksStatusCreateProcessor';