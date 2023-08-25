<?php

class slReportsTypeFieldsUpdateProcessor extends modObjectUpdateProcessor
{
    public $objectType = 'slReportsTypeFields';
    public $classKey = 'slReportsTypeFields';
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
        $type = (int)$this->getProperty('type');

        if (empty($name)) {
            $this->modx->error->addField('name', $this->modx->lexicon('shoplogistic_reporttypefield_err_name'));
        } elseif ($this->modx->getCount($this->classKey, ['name' => $name, 'id:!=' => $id, 'type' => $type])) {
            $this->modx->error->addField('name', $this->modx->lexicon('shoplogistic_reporttypefield_err_ae'));
        }

        return parent::beforeSet();
    }

}

return 'slReportsTypeFieldsUpdateProcessor';
