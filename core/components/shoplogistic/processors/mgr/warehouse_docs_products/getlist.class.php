<?php

class slWarehouseDocsProductsGetListProcessor extends modObjectGetListProcessor
{
	public $objectType = 'slWarehouseDocsProducts';
	public $classKey = 'slWarehouseDocsProducts';
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
		$c->leftJoin('slWarehouseRemains', 'Remain');
		$query = trim($this->getProperty('query'));
		if ($query) {
			$c->where([
				'article:LIKE' => "%{$query}%",
				'OR:description:LIKE' => "%{$query}%",
			]);
		}
		$c->where(array("doc_id" => trim($this->getProperty('doc_id'))));
		$c->select(
			$this->modx->getSelectColumns('slWarehouseDocsProducts', 'slWarehouseDocsProducts', '', ['article'], true) . ',
            Remain.article as product_article, Remain.guid as product_guid, Remain.name as product_name'
		);
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

		return $array;
	}

}

return 'slWarehouseDocsProductsGetListProcessor';