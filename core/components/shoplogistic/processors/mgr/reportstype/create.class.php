<?php

class slReportsTypeCreateProcessor extends modObjectCreateProcessor
{
    public $objectType = 'slReportsType';
    public $classKey = 'slReportsType';
    public $languageTopics = ['shoplogistic'];
    //public $permission = 'create';


    /**
     * @return bool
     */
    public function beforeSet()
    {
        $name = trim($this->getProperty('name'));
        if (empty($name)) {
            $this->modx->error->addField('name', $this->modx->lexicon('shoplogistic_reporttype_err_name'));
        } elseif ($this->modx->getCount($this->classKey, ['name' => $name])) {
            $this->modx->error->addField('name', $this->modx->lexicon('shoplogistic_reporttype_err_ae'));
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

return 'slReportsTypeCreateProcessor';