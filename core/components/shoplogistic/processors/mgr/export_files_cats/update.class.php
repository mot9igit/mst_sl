<?php

class slExportFilesCatsUpdateProcessor extends modObjectUpdateProcessor
{
    public $objectType = 'slExportFilesCats';
    public $classKey = 'slExportFilesCats';
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
        if (empty($id)) {
            return $this->modx->lexicon('shoplogistic_export_file_cats_err_ns');
        }

        if ($this->modx->getCount($this->classKey, ['name' => $name, 'id:!=' => $id])) {
            $this->modx->error->addField('name', $this->modx->lexicon('shoplogistic_export_file_cats_err_ae'));
        }

        return parent::beforeSet();
    }
}

return 'slExportFilesCatsUpdateProcessor';
