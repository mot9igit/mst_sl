<?php

class slStoresMatrixCreateProcessor extends modObjectCreateProcessor
{
    public $objectType = 'slStoresMatrix';
    public $classKey = 'slStoresMatrix';
    public $languageTopics = ['shoplogistic'];
    //public $permission = 'create';


    /**
     * @return bool
     */
    public function beforeSet()
    {
        $store = trim($this->getProperty('store_id'));
        if (empty($store)) {
            $this->modx->error->addField('store_id', $this->modx->lexicon('shoplogistic_matrix_err_store_id'));
        }

        return parent::beforeSet();
    }

}

return 'slStoresMatrixCreateProcessor';