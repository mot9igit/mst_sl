<?php

class slWarehouseDocsGetListProcessor extends modObjectGetListProcessor
{
	public $objectType = 'slWarehouseDocs';
	public $classKey = 'slWarehouseDocs';
	public $defaultSortField = 'id';
	public $defaultSortDirection = 'DESC';
	//public $permission = 'list';


	/**
	 * We do a special check of permissions
	 * because our objects is not an instances of modAccessibleObject
	 *
	 * @return boolean|string
	 */
	public function beforeQuery()
	{
		if (!$this->checkPermissions()) {
			return $this->modx->lexicon('access_denied');
		}

		return true;
	}


	/**
	 * @param xPDOQuery $c
	 *
	 * @return xPDOQuery
	 */
	public function prepareQueryBeforeCount(xPDOQuery $c)
	{
		$query = trim($this->getProperty('query'));
		if ($query) {
			$c->where([
				'guid:LIKE' => "%{$query}%",
				'OR:description:LIKE' => "%{$query}%",
			]);
		}
		$c->where(array("warehouse_id" => trim($this->getProperty('warehouse_id'))));
		return $c;
	}


	/**
	 * @param xPDOObject $object
	 *
	 * @return array
	 */
	public function prepareRow(xPDOObject $object)
	{
		$array = $object->toArray();
		$array['actions'] = [];

		// Edit
		$array['actions'][] = [
			'cls' => '',
			'icon' => 'icon icon-edit',
			'title' => $this->modx->lexicon('shoplogistic_stores_docs_update'),
			'action' => 'updateWarehouseDoc',
			'button' => true,
			'menu' => true,
		];

		return $array;
	}

}

return 'slWarehouseDocsGetListProcessor';