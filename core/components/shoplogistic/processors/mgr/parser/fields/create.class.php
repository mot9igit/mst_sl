<?php

class slParserConfigFieldsCreateProcessor extends modObjectCreateProcessor
{
    public $objectType = 'slParserConfigFields';
    public $classKey = 'slParserConfigFields';
    public $languageTopics = ['shoplogistic'];
    //public $permission = 'save';


    /**
     * @return bool
     */
    public function beforeSet()
    {
        $name = (int)$this->getProperty('name');
        $config_id = (int)$this->getProperty('config_id');
        $field_object = (int)$this->getProperty('field_object');

        if ($this->modx->getCount($this->classKey, ['name' => $name, "config_id:=" => $config_id, "field_object:=" => $field_object])) {
            $this->modx->error->addField('name', $this->modx->lexicon('shoplogistic_parser_config_fields_err_ae'));
        }

        return parent::beforeSet();
    }

}

return 'slParserConfigFieldsCreateProcessor';