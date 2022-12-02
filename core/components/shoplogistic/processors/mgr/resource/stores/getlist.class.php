<?php

class slStoresRemainsGetListProcessor extends modObjectGetListProcessor {
	public $classKey = 'slStoresRemains';
	public $defaultSortField = 'id';
	public $defaultSortDirection = 'ASC';

	/**
	 * @param xPDOQuery $c
	 *
	 * @return array
	 */
	public function prepareQueryBeforeCount(xPDOQuery $c) {
		$query = trim($this->getProperty('query'));
		if ($query) {
			$c->where(array(
				'description:LIKE' => "%{$query}%"
			));
		}

		$c->where(array(
			'product_id' => $this->getProperty('product_id'),
		));

		return $c;
	}

	/**
	 * @param xPDOObject $object
	 *
	 * @return array
	 */
	public function prepareRow(xPDOObject $object) {
		$array = $object->toArray();

		$array['store'] = $this->modx->shopLogistic->getStoreNameById($array['store_id']);


		$array['description'] = mb_substr(strip_tags($array['value']), 0, 60);

		$array['actions'] = [];

		// Edit
		$array['actions'][] = [
			'cls' => 'shoplogistic-update',
			'icon' => 'icon icon-edit',
			'title' => $this->modx->lexicon('update'),
			//'multiple' => $this->modx->lexicon('view'),
			'action' => 'updateItem',
			'button' => true,
			'menu' => true,
		];

		// Remove
		$array['actions'][] = [
			'cls' => 'shoplogistic-remove',
			'icon' => 'icon icon-trash-o action-red',
			'title' => $this->modx->lexicon('remove'),
			'multiple' => $this->modx->lexicon('remove'),
			'action' => 'removeItem',
			'button' => true,
			'menu' => true,
		];

		return $array;
	}
}

return "slStoresRemainsGetListProcessor";