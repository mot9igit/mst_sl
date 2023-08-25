<?php


class slCategoryGetListProcessor extends modObjectGetListProcessor
{
	public $classKey = 'modResource';
	public $defaultSortField = 'id';


	/**
	 * @param xPDOQuery $c
	 *
	 * @return xPDOQuery
	 */
	public function prepareQueryBeforeCount(xPDOQuery $c)
	{

		$id = $this->getProperty('id');
		if (!empty($id) and $this->getProperty('combo')) {
			$c->sortby("FIELD (modResource.id, {$id})", "DESC");
		}
		$c->where(array(
			'modResource.class_key:=' => "msCategory"
		));
		$query = $this->getProperty('query', '');
		if (!empty($query)) {
			$c->where(array(
				'modResource.pagetitle:LIKE' => "%{$query}%",
				'OR:modResource.longtitle:LIKE' => "%{$query}%"
			));
		}

		return $c;
	}


	/**
	 * @param xPDOQuery $c
	 *
	 * @return xPDOQuery
	 */
	public function prepareQueryAfterCount(xPDOQuery $c)
	{
		$c->select($this->modx->getSelectColumns('modResource', 'modResource'));

		return $c;
	}

	public function prepareRow(xPDOObject $object)
	{
		$array = $object->toArray();
		//$this->modx->log(1, print_r($array, 1));
		if ($this->getProperty('combo')) {
			$array = array(
				'id' => $array['id'],
				'pagetitle' => $array['pagetitle']
			);
		}

		return $array;
	}
}

return 'slCategoryGetListProcessor';