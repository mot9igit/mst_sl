<?php

class slParserDataTasksCreateProcessor extends modObjectCreateProcessor
{
    public $objectType = 'slParserDataTasks';
    public $classKey = 'slParserDataTasks';
    public $languageTopics = ['shoplogistic'];
    //public $permission = 'create';


    /**
     * @return bool
     */
    public function beforeSet()
    {
        $name = trim($this->getProperty('name'));
        if (empty($name)) {
            $this->modx->error->addField('name', $this->modx->lexicon('shoplogistic_parserdata_tasks_err_name'));
        } elseif ($this->modx->getCount($this->classKey, ['name' => $name])) {
            $this->modx->error->addField('name', $this->modx->lexicon('shoplogistic_parserdata_tasks_err_ae'));
        }

        return parent::beforeSet();
    }

}

return 'slParserDataTasksCreateProcessor';