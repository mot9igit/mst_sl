<?php
class slWarehouseRemainsCreateProcessor extends modObjectCreateProcessor {
	public $classKey = 'slWarehouseRemains';

	/**
	 * var modX
	 */
	public function beforeSet() {
		$warehouse_id = trim($this->getProperty('warehouse_id'));
		$product_id = trim($this->getProperty('product_id'));

		if ($this->modx->getCount($this->classKey, ['warehouse_id' => $warehouse_id, 'product_id' => $product_id])) {
			$this->modx->error->addField('store_id', $this->modx->lexicon('shoplogistic_resource_warehouse_id_ae'));
		}

		return parent::beforeSet();
	}
}

return "slWarehouseRemainsCreateProcessor";