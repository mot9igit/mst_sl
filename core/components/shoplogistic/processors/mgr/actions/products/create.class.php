<?php

class slActionsProductsCreateProcessor extends modObjectCreateProcessor
{
    public $objectType = 'slActionsProducts';
    public $classKey = 'slActionsProducts';
    public $languageTopics = ['shoplogistic'];
    //public $permission = 'create';


    /**
     * @return bool
     */
    public function beforeSet()
    {
        $product_id = trim($this->getProperty('product_id'));
        $action_id = trim($this->getProperty('action_id'));
        if (empty($product_id)) {
            $this->modx->error->addField('product_id', $this->modx->lexicon('shoplogistic_actions_product_id_err_name'));
        } elseif ($this->modx->getCount($this->classKey, ['product_id' => $product_id, 'action_id' => $action_id])) {
            $this->modx->error->addField('product_id', $this->modx->lexicon('shoplogistic_actions_product_err_ae'));
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

return 'slActionsProductsCreateProcessor';