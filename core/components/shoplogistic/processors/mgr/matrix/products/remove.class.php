<?php

class slStoresMatrixProductsRemoveProcessor extends modObjectProcessor
{
    public $objectType = 'slStoresMatrixProducts';
    public $classKey = 'slStoresMatrixProducts';
    public $languageTopics = ['shoplogistic'];
    //public $permission = 'remove';


    /**
     * @return array|string
     */
    public function process()
    {
        if (!$this->checkPermissions()) {
            return $this->failure($this->modx->lexicon('access_denied'));
        }

        $ids = $this->modx->fromJSON($this->getProperty('ids'));
        if (empty($ids)) {
            return $this->failure($this->modx->lexicon('shoplogistic_matrix_err_remove'));
        }

        foreach ($ids as $id) {
            /** @var shopLogisticItem $object */
            if (!$object = $this->modx->getObject($this->classKey, $id)) {
                return $this->failure($this->modx->lexicon('shoplogistic_matrix_err_remove'));
            }

            $object->remove();
        }

        return $this->success();
    }

}

return 'slStoresMatrixProductsRemoveProcessor';