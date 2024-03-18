<?php

class slActionsProductsUpdateProcessor extends modObjectUpdateProcessor
{
    public $objectType = 'slActionsProducts';
    public $classKey = 'slActionsProducts';
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
        if (empty($id)) {
            return $this->modx->lexicon('shoplogistic_actions_err_ns');
        }

        $product_id = trim($this->getProperty('product_id'));
        $action_id = trim($this->getProperty('action_id'));
        if (empty($product_id)) {
            $this->modx->error->addField('product_id', $this->modx->lexicon('shoplogistic_actions_product_id_err_name'));
        } elseif ($this->modx->getCount($this->classKey, ['product_id' => $product_id, 'action_id' => $action_id, "id:!=" => $id])) {
            $this->modx->error->addField('product_id', $this->modx->lexicon('shoplogistic_actions_product_err_ae'));
        }

        return parent::beforeSet();
    }
}

return 'slActionsProductsUpdateProcessor';
