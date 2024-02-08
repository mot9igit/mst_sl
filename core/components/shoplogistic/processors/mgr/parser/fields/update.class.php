<?php

class slParserConfigFieldsUpdateProcessor extends modObjectUpdateProcessor
{
    public $objectType = 'slParserConfigFields';
    public $classKey = 'slParserConfigFields';
    public $languageTopics = ['shoplogistic'];
    //public $permission = 'save';


    /**
     * We doing special check of permission
     * because of our objects is not an instances of modAccessibleObject
     *
     * @return bool|string
     */
    public function beforeSave()
    {
        if (!$this->checkPermissions()) {
            return $this->modx->lexicon('access_denied');
        }

        return true;
    }


    /**
     * @return bool
     */
    public function beforeSet()
    {
        $id = (int)$this->getProperty('id');
        $name = (int)$this->getProperty('name');
        $config_id = (int)$this->getProperty('config_id');
        $field_object = (int)$this->getProperty('field_object');
        if (empty($id)) {
            return $this->modx->lexicon('shoplogistic_parser_config_fields_err_ns');
        }

        if ($this->modx->getCount($this->classKey, ['name' => $name, 'id:!=' => $id, "config_id:=" => $config_id, "field_object:=" => $field_object])) {
            $this->modx->error->addField('name', $this->modx->lexicon('shoplogistic_parser_config_fields_err_ae'));
        }

        return parent::beforeSet();
    }
}

return 'slParserConfigFieldsUpdateProcessor';
