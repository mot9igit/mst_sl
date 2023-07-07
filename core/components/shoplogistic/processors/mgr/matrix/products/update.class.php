<?php

class slStoresMatrixProductsUpdateProcessor extends modObjectUpdateProcessor
{
    public $objectType = 'slStoresMatrixProducts';
    public $classKey = 'slStoresMatrixProducts';
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
        // $vendor_id = trim($this->getProperty('vendor_id'));
        if (empty($id)) {
            return $this->modx->lexicon('shoplogistic_matrix_err_ns');
        }

        return parent::beforeSet();
    }
}

return 'slStoresMatrixProductsUpdateProcessor';
