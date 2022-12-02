<?php

class slStoresRemainsUpdateProcessor extends modObjectUpdateProcessor
{
    public $objectType = 'slStoresRemains';
    public $classKey = 'slStoresRemains';
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
        if (empty($id)) {
            return $this->modx->lexicon('shoplogistic_store_err_ns');
        }



		$product_id = trim($this->getProperty('product_id'));
		$store_id = trim($this->getProperty('store_id'));
		$obj = $this->modx->getObject($this->classKey, ['product_id' => $product_id, 'store_id' => $store_id]);
		if (empty($product_id)) {
			$this->modx->error->addField('product_id', $this->modx->lexicon('shoplogistic_storeremains_err_product_id'));
		} elseif ($obj->id != $id) {
			$this->modx->error->addField('product_id', $this->modx->lexicon('shoplogistic_storeremains_err_double'));
		}

        return parent::beforeSet();
    }
}

return 'slStoresRemainsUpdateProcessor';
