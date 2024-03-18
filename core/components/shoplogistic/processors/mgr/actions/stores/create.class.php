<?php

class slActionsStoresCreateProcessor extends modObjectCreateProcessor
{
    public $objectType = 'slActionsStores';
    public $classKey = 'slActionsStores';
    public $languageTopics = ['shoplogistic'];
    //public $permission = 'create';


    /**
     * @return bool
     */
    public function beforeSet()
    {
        $store_id = trim($this->getProperty('store_id'));
        $action_id = trim($this->getProperty('action_id'));
        if (empty($store_id)) {
            $this->modx->error->addField('store_id', $this->modx->lexicon('shoplogistic_actions_store_id_err_name'));
        } elseif ($this->modx->getCount($this->classKey, ['store_id' => $store_id, 'action_id' => $action_id])) {
            $this->modx->error->addField('store_id', $this->modx->lexicon('shoplogistic_actions_store_err_ae'));
        }

        return parent::beforeSet();
    }

    /**
     * @return bool|string
     */
    public function beforeSave()
    {
        $this->object->set('createdby', $this->modx->user->get('id'));
        $this->object->set('createdon', time());

        return parent::beforeSave();
    }

}

return 'slActionsStoresCreateProcessor';