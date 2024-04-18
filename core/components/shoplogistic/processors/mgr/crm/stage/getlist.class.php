<?php

class slCRMStageGetListProcessor extends modObjectGetListProcessor
{
	public $objectType = 'slCRMStage';
	public $classKey = 'slCRMStage';
	public $defaultSortField = 'sort';
	public $defaultSortDirection = 'ASC';
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
				'name:LIKE' => "%{$query}%",
				'OR:crm_id:LIKE' => "%{$query}%",
			]);
		}
        $category_id = trim($this->getProperty('category_id'));
        if ($category_id) {
            $c->where(array("category_id" => $category_id));
        }
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
			'title' => $this->modx->lexicon('shoplogistic_crm_deal_stage_update'),
			//'multiple' => $this->modx->lexicon('shoplogistic_crm_items_update'),
			'action' => 'updateStage',
			'button' => true,
			'menu' => true,
		];

		return $array;
	}

}

return 'slCRMStageGetListProcessor';