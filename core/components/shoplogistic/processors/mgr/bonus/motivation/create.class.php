<?php

class slMotivationCreateProcessor extends modObjectCreateProcessor
{
    public $objectType = 'slBonusMotivation';
    public $classKey = 'slBonusMotivation';
    public $languageTopics = ['shoplogistic'];
    //public $permission = 'create';


    /**
     * @return bool
     */
    public function beforeSet()
    {
        $name = trim($this->getProperty('name'));
        if($this->object->get('global')){
            $this->object->set('store_id', 0);
        }
        if (empty($name)) {
            $this->modx->error->addField('name', $this->modx->lexicon('shoplogistic_motivation_err_name'));
        } elseif ($this->modx->getCount($this->classKey, ['name' => $name])) {
            $this->modx->error->addField('name', $this->modx->lexicon('shoplogistic_motivation_err_ae'));
        }

        return parent::beforeSet();
    }

}

return 'slMotivationCreateProcessor';