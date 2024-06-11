<?php

class slPageCreateProcessor extends modObjectCreateProcessor
{
    public $objectType = 'slPagesBanners';
    public $classKey = 'slPagesBanners';
    public $languageTopics = ['shoplogistic'];
    //public $permission = 'create';


    /**
     * @return bool
     */
    public function beforeSet()
    {
        $name = trim($this->getProperty('name'));
        if (empty($name)) {
            $this->modx->error->addField('name', $this->modx->lexicon('shoplogistic_page_err_name'));
        } elseif ($this->modx->getCount($this->classKey, ['name' => $name])) {
            $this->modx->error->addField('name', $this->modx->lexicon('shoplogistic_page_err_ae'));
        }

        return parent::beforeSet();
    }

}

return 'slPageCreateProcessor';