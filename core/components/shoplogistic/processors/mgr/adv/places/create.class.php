<?php

class slPlaceCreateProcessor extends modObjectCreateProcessor
{
    public $objectType = 'slPlaceBanners';
    public $classKey = 'slPlaceBanners';
    public $languageTopics = ['shoplogistic'];
    //public $permission = 'create';


    /**
     * @return bool
     */
    public function beforeSet()
    {
        $name = trim($this->getProperty('name'));
        if (empty($name)) {
            $this->modx->error->addField('name', $this->modx->lexicon('shoplogistic_place_err_name'));
        } elseif ($this->modx->getCount($this->classKey, ['name' => $name])) {
            $this->modx->error->addField('name', $this->modx->lexicon('shoplogistic_place_err_ae'));
        }

        $this->setProperty('type', 2);

        return parent::beforeSet();
    }

}

return 'slPlaceCreateProcessor';