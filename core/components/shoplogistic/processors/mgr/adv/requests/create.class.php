<?php

class slRequestCreateProcessor extends modObjectCreateProcessor
{
    public $objectType = 'slActions';
    public $classKey = 'slActions';
    public $languageTopics = ['shoplogistic'];
    //public $permission = 'create';


    /**
     * @return bool
     */
    public function beforeSet()
    {
//        $name = trim($this->getProperty('name'));
//        if (empty($name)) {
//            $this->modx->error->addField('name', $this->modx->lexicon('shoplogistic_page_err_name'));
//        } elseif ($this->modx->getCount($this->classKey, ['name' => $name])) {
//            $this->modx->error->addField('name', $this->modx->lexicon('shoplogistic_page_err_ae'));
//        }

        $page_places = $this->getProperty('page_places');
        if(!empty($page_places)){
            $this->setProperty("page_places", implode(', ', $page_places));
        }

        return parent::beforeSet();
    }

}

return 'slRequestCreateProcessor';