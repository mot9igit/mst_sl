<?php

class slExportFilesCreateProcessor extends modObjectCreateProcessor
{
    public $objectType = 'slExportFiles';
    public $classKey = 'slExportFiles';
    public $languageTopics = ['shoplogistic'];
    //public $permission = 'create';


    /**
     * @return bool
     */
    public function beforeSet()
    {
        $name = trim($this->getProperty('name'));
        if (empty($name)) {
            $this->modx->error->addField('name', $this->modx->lexicon('shoplogistic_export_file_err_name'));
        } elseif ($this->modx->getCount($this->classKey, ['name' => $name])) {
            $this->modx->error->addField('name', $this->modx->lexicon('shoplogistic_export_file_err_ae'));
        }

        return parent::beforeSet();
    }

    /**
     * @return bool|string
     */
    public function beforeSave()
    {
        $this->object->set('created_by', $this->modx->user->get('id'));
        $this->object->set('createdon', time());

        return parent::beforeSave();
    }

}

return 'slExportFilesCreateProcessor';