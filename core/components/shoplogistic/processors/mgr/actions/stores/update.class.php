<?php

class slActionsStoresUpdateProcessor extends modObjectUpdateProcessor
{
    public $objectType = 'slActionsStores';
    public $classKey = 'slActionsStores';
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

        $this->object->set('updatedby', $this->modx->user->get('id'));
        $this->object->set('updatedon', time());

        return true;
    }


    /**
     * @return bool
     */
    public function beforeSet()
    {
        $id = (int)$this->getProperty('id');
        $store_id = (int)trim($this->getProperty('store_id'));
        $action_id = (int)trim($this->getProperty('action_id'));
        if (empty($id)) {
            return $this->modx->lexicon('shoplogistic_actions_err_ns');
        }

        if (empty($store_id)) {
            $this->modx->error->addField('store_id', $this->modx->lexicon('shoplogistic_actions_store_id_err_name'));
        } elseif ($this->modx->getCount($this->classKey, ['store_id' => $store_id, 'action_id' => $action_id])) {
            $this->modx->error->addField('store_id', $this->modx->lexicon('shoplogistic_actions_store_err_ae'));
        }

        return parent::beforeSet();
    }
}

return 'slActionsStoresUpdateProcessor';
