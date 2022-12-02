<?php

class slWarehouseRemainsGetListProcessor extends modObjectGetListProcessor
{
    public $objectType = 'slWarehouseRemains';
    public $classKey = 'slWarehouseRemains';
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
		$warehouse_id = trim($this->getProperty('warehouse_id'));

		$c->leftJoin('msProductData', 'msProductData', '`slWarehouseRemains`.`product_id` = `msProductData`.`id`');
		$c->leftJoin('modResource', 'modResource', '`slWarehouseRemains`.`product_id` = `modResource`.`id`');

        if ($query) {
            $c->where([
                'modResource.pagetitle:LIKE' => "%{$query}%",
                'OR:msProductData.article:LIKE' => "%{$query}%",
            ]);
        }

		if($warehouse_id){
			$c->where([
				'warehouse_id:=' => $warehouse_id,
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

		$array['product_article'] = $this->modx->shopLogistic->getProductArticleById($array['product_id']);
		$array['product_name'] = $this->modx->shopLogistic->getProductNameById($array['product_id']);
		$array['warehouse_name'] = $this->modx->shopLogistic->getWarehouseNameById($array['warehouse_id']);

        // Edit
        $array['actions'][] = [
            'cls' => '',
            'icon' => 'icon icon-edit',
            'title' => $this->modx->lexicon('shoplogistic_warehouseremain_update'),
            //'multiple' => $this->modx->lexicon('shoplogistic_items_update'),
            'action' => 'updateWarehouseRemain',
            'button' => true,
            'menu' => true,
        ];

        // Remove
        $array['actions'][] = [
            'cls' => '',
            'icon' => 'icon icon-trash-o action-red',
            'title' => $this->modx->lexicon('shoplogistic_warehouseremain_remove'),
            'multiple' => $this->modx->lexicon('shoplogistic_warehouseremains_remove'),
            'action' => 'removeWarehouseRemain',
            'button' => true,
            'menu' => true,
        ];

        return $array;
    }

}

return 'slWarehouseRemainsGetListProcessor';