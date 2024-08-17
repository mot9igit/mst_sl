<?php

class slRequestb2bUpdateProcessor extends modObjectUpdateProcessor
{
    public $objectType = 'slActions';
    public $classKey = 'slActions';
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
        $name = trim($this->getProperty('name'));
        if (empty($id)) {
            return $this->modx->lexicon('shoplogistic_page_err_ns');
        }

//        if (empty($name)) {
//            $this->modx->error->addField('name', $this->modx->lexicon('shoplogistic_page_err_name'));
//        } elseif ($this->modx->getCount($this->classKey, ['name' => $name, 'id:!=' => $id])) {
//            $this->modx->error->addField('name', $this->modx->lexicon('shoplogistic_page_err_ae'));
//        }

        $page_places = $this->getProperty('page_places');
        if(!empty($page_places)){
            $this->setProperty("page_places", implode(', ', $page_places));
        }

        return parent::beforeSet();
    }
}

return 'slRequestb2bUpdateProcessor';
