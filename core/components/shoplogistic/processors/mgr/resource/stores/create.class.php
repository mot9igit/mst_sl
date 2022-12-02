<?php
class slStoresRemainsCreateProcessor extends modObjectCreateProcessor {
	public $classKey = 'slStoresRemains';

	/**
	 * var modX
	 */
	public function beforeSet() {
		$store_id = trim($this->getProperty('store_id'));
		$product_id = trim($this->getProperty('product_id'));

		if ($this->modx->getCount($this->classKey, ['store_id' => $store_id, 'product_id' => $product_id])) {
			$this->modx->error->addField('store_id', $this->modx->lexicon('shoplogistic_resource_store_id_ae'));
		}

		return parent::beforeSet();
	}
}

return "slStoresRemainsCreateProcessor";