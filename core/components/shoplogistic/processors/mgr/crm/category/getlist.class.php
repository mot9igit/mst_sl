<?php

class slCRMDealCategoryGetListProcessor extends modObjectGetListProcessor
{
	public $objectType = 'slCRMCategory';
	public $classKey = 'slCRMCategory';
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
				'OR:crm_id:LIKE' => "%{$query}%"
			]);
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
			'title' => $this->modx->lexicon('shoplogistic_crm_deal_category_update'),
			//'multiple' => $this->modx->lexicon('shoplogistic_crm_deal_categories_update'),
			'action' => 'updateCategory',
			'button' => true,
			'menu' => true,
		];

		return $array;
	}

}

return 'slCRMDealCategoryGetListProcessor';