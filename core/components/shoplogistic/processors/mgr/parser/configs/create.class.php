<?php

class slParserConfigCreateProcessor extends modObjectCreateProcessor
{
    public $objectType = 'slParserConfig';
    public $classKey = 'slParserConfig';
    public $languageTopics = ['shoplogistic'];
    //public $permission = 'create';


    /**
     * @return bool
     */
    public function beforeSet()
    {
        $name = trim($this->getProperty('name'));
        if (empty($name)) {
            $this->modx->error->addField('name', $this->modx->lexicon('shoplogistic_parser_config_err_name'));
        } elseif ($this->modx->getCount($this->classKey, ['name' => $name])) {
            $this->modx->error->addField('name', $this->modx->lexicon('shoplogistic_parser_config_err_ae'));
        }

        return parent::beforeSet();
    }

}

return 'slParserConfigCreateProcessor';