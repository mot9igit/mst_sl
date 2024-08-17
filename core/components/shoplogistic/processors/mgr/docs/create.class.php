<?php

class slDocsCreateProcessor extends modObjectCreateProcessor
{
    public $objectType = 'slDocs';
    public $classKey = 'slDocs';
    public $languageTopics = ['shoplogistic'];
    //public $permission = 'create';


    /**
     * @return bool
     */
    public function beforeSet()
    {
        $name = trim($this->getProperty('name'));
        if (empty($name)) {
            $this->modx->error->addField('name', $this->modx->lexicon('shoplogistic_doc_err_name'));
        } elseif ($this->modx->getCount($this->classKey, ['name' => $name])) {
            $this->modx->error->addField('name', $this->modx->lexicon('shoplogistic_doc_err_ae'));
        }

        $store_ids = $this->getProperty('store_ids');
        if(!empty($store_ids)){
            $this->setProperty("stores", $store_ids);
        }

        return parent::beforeSet();
    }

    /**
     * @return bool|string
     */
    public function beforeSave()
    {

        if($this->object->get('global')){
            $this->object->set('store_id', 0);
        }
        $this->object->set('created_by', $this->modx->user->get('id'));
        $this->object->set('createdon', time());

        return parent::beforeSave();
    }

}

return 'slDocsCreateProcessor';