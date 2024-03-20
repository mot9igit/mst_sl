<?php

class slParserDataCatsUpdateProcessor extends modObjectUpdateProcessor
{
    public $objectType = 'slParserDataCats';
    public $classKey = 'slParserDataCats';
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
        $service_id = (int)$this->getProperty('service_id');
        $export_parents = (int)$this->getProperty('export_parents');
        if (empty($id)) {
            return $this->modx->lexicon('shoplogistic_parserdata_cats_err_ns');
        }

        if ($this->modx->getCount($this->classKey, ['name' => $name, 'id:!=' => $id, 'service_id:=' => $service_id, 'export_parents:=' => $export_parents])) {
            $this->modx->error->addField('name', $this->modx->lexicon('shoplogistic_parserdata_cats_err_ae'));
        }

        return parent::beforeSet();
    }
}

return 'slParserDataCatsUpdateProcessor';
