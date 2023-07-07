<?php

class slStoresMatrixProductsCreateProcessor extends modObjectCreateProcessor
{
    public $objectType = 'slStoresMatrixProducts';
    public $classKey = 'slStoresMatrixProducts';
    public $languageTopics = ['shoplogistic'];
    //public $permission = 'create';


    /**
     * @return bool
     */
    public function beforeSet()
    {
        $store = trim($this->getProperty('matrix_id'));
        if (empty($store)) {
            $this->modx->error->addField('matrix_id', $this->modx->lexicon('shoplogistic_matrixproducts_err_matrix_id'));
        }
        $store = trim($this->getProperty('product_id'));
        if (empty($store)) {
            $this->modx->error->addField('product_id', $this->modx->lexicon('shoplogistic_matrixproducts_err_product_id'));
        }
        return parent::beforeSet();
    }

}

return 'slStoresMatrixProductsCreateProcessor';