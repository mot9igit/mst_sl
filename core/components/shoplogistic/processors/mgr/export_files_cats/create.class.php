<?php

class slExportFilesCatsCreateProcessor extends modObjectCreateProcessor
{
    public $objectType = 'slExportFilesCats';
    public $classKey = 'slExportFilesCats';
    public $languageTopics = ['shoplogistic'];
    //public $permission = 'create';


    /**
     * @return bool
     */
    public function beforeSet()
    {
        $name = trim($this->getProperty('name'));
        if (empty($name)) {
            $this->modx->error->addField('name', $this->modx->lexicon('shoplogistic_export_file_cats_err_name'));
        } elseif ($this->modx->getCount($this->classKey, ['name' => $name])) {
            $this->modx->error->addField('name', $this->modx->lexicon('shoplogistic_export_file_cats_err_ae'));
        }

        return parent::beforeSet();
    }

}

return 'slExportFilesCatsCreateProcessor';