<?php


class slStageGetListProcessor extends modObjectGetListProcessor
{
	public $classKey = 'slCRMStage';
	public $defaultSortField = 'id';


	/**
	 * @param xPDOQuery $c
	 *
	 * @return xPDOQuery
	 */
	public function prepareQueryBeforeCount(xPDOQuery $c)
	{
		$c->leftJoin('slCRMCategory', 'slCRMCategory', '`slCRMStage`.`category_id` = `slCRMCategory`.`id`');

		$id = $this->getProperty('id');
		if (!empty($id) and $this->getProperty('combo')) {
			$c->sortby("FIELD (modResource.id, {$id})", "DESC");
		}
		$query = $this->getProperty('query', '');
		if (!empty($query)) {
			$c->where(array(
				'slCRMStage.name:LIKE' => "%{$query}%",
				'OR:slCRMStage.crm_id:LIKE' => "%{$query}%"
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
		$c->select($this->modx->getSelectColumns('slCRMStage', 'slCRMStage'));
		$c->select('slCRMCategory.name AS category');

		return $c;
	}

	public function prepareRow(xPDOObject $object)
	{
		$array = $object->toArray();
		//$this->modx->log(1, print_r($array, 1));
		if ($this->getProperty('combo')) {
			$array = array(
				'id' => $array['id'],
				'name' => $array['name'],
				'category' => $array['category']
			);
		}

		return $array;
	}
}

return 'slStageGetListProcessor';