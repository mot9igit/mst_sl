<?php

class slReportsTypeFieldsCreateProcessor extends modObjectCreateProcessor
{
    public $objectType = 'slReportsTypeFields';
    public $classKey = 'slReportsTypeFields';
    public $languageTopics = ['shoplogistic'];
    //public $permission = 'create';


    /**
     * @return bool
     */
    public function beforeSet()
    {
        $name = trim($this->getProperty('name'));
        $type = trim($this->getProperty('type'));
        if (empty($name)) {
            $this->modx->error->addField('name', $this->modx->lexicon('shoplogistic_reporttypefield_err_name'));
        } elseif ($this->modx->getCount($this->classKey, ['name' => $name, 'type' => $type])) {
            $this->modx->error->addField('name', $this->modx->lexicon('shoplogistic_reporttypefield_err_ae'));
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

return 'slReportsTypeFieldsCreateProcessor';