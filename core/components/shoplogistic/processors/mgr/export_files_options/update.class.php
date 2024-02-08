<?php

class slExportFilesCatsOptionsUpdateProcessor extends modObjectUpdateProcessor
{
    public $objectType = 'slExportFilesCatsOptions';
    public $classKey = 'slExportFilesCatsOptions';
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
        $cat_id = (int)$this->getProperty('cat_id');
        if (empty($id)) {
            return $this->modx->lexicon('shoplogistic_export_file_cat_options_err_ns');
        }

        if ($this->modx->getCount($this->classKey, ['name' => $name, 'id:!=' => $id, "cat_id:=" => $cat_id])) {
            $this->modx->error->addField('name', $this->modx->lexicon('shoplogistic_export_file_cat_options_err_ae'));
        }

        return parent::beforeSet();
    }
}

return 'slExportFilesCatsOptionsUpdateProcessor';
