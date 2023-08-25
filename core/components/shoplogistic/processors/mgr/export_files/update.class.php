<?php

class slExportFilesUpdateProcessor extends modObjectUpdateProcessor
{
    public $objectType = 'slExportFiles';
    public $classKey = 'slExportFiles';
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

        $this->object->set('updated_by', $this->modx->user->get('id'));
        $this->object->set('updatedon', time());

        return parent::beforeSave();
    }


    /**
     * @return bool
     */
    public function beforeSet()
    {
        $id = (int)$this->getProperty('id');
        $name = trim($this->getProperty('name'));
        $store_ids = $this->getProperty('store_ids');
        if (empty($id)) {
            return $this->modx->lexicon('shoplogistic_export_file_err_ns');
        }

        if (empty($name)) {
            $this->modx->error->addField('name', $this->modx->lexicon('shoplogistic_export_file_err_name'));
        } elseif ($this->modx->getCount($this->classKey, ['name' => $name, 'id:!=' => $id])) {
            $this->modx->error->addField('name', $this->modx->lexicon('shoplogistic_export_file_err_ae'));
        }

        if(!empty($store_ids)){
            $this->setProperty("store_id", implode(",", $store_ids));
        }

        return parent::beforeSet();
    }

}

return 'slExportFilesUpdateProcessor';
