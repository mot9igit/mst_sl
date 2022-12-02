<?php

class slStoreRegistryUpdateProcessor extends modObjectUpdateProcessor
{
    public $objectType = 'slStoreRegistry';
    public $classKey = 'slStoreRegistry';
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
        $name = trim($this->getProperty('num'));
        if (empty($id)) {
            return $this->modx->lexicon('shoplogistic_store_registry_err_ns');
        }

        if (empty($name)) {
            $this->modx->error->addField('name', $this->modx->lexicon('shoplogistic_store_registry_err_num'));
        } elseif ($this->modx->getCount($this->classKey, ['num' => $name, 'id:!=' => $id])) {
            $this->modx->error->addField('name', $this->modx->lexicon('shoplogistic_store_registry_err_ae'));
        }

        return parent::beforeSet();
    }
}

return 'slStoreRegistryUpdateProcessor';
