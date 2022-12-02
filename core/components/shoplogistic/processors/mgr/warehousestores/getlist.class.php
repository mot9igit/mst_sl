<?php

class slWarehouseStoresGetListProcessor extends modObjectGetListProcessor
{
    public $objectType = 'slWarehouseStores';
    public $classKey = 'slWarehouseStores';
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
		$c->leftJoin('slStores', 'Store');
		$c->leftJoin('slWarehouse', 'Warehouse');
    	$warehouse_id = trim($this->getProperty('warehouse_id'));
        $query = trim($this->getProperty('query'));
        if ($query) {
            $c->where([
                'description:LIKE' => "%{$query}%",
				'OR:Store.name:LIKE' => "%{$query}%",
				'OR:Warehouse.name:LIKE' => "%{$query}%",
            ]);
        }

        if($warehouse_id){
			$c->where([
				'warehouse_id:=' => $warehouse_id,
			]);
		}

		$c->select(
			$this->modx->getSelectColumns('slWarehouseStores', 'slWarehouseStores', '') . ',
            Store.name as store,
            Warehouse.name as warehouse'
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

        // Edit
        $array['actions'][] = [
            'cls' => '',
            'icon' => 'icon icon-edit',
            'title' => $this->modx->lexicon('shoplogistic_warehousestore_update'),
            //'multiple' => $this->modx->lexicon('shoplogistic_items_update'),
            'action' => 'updateWarehouseStores',
            'button' => true,
            'menu' => true,
        ];

        // Remove
        $array['actions'][] = [
            'cls' => '',
            'icon' => 'icon icon-trash-o action-red',
            'title' => $this->modx->lexicon('shoplogistic_warehousestore_remove'),
            'multiple' => $this->modx->lexicon('shoplogistic_warehousestores_remove'),
            'action' => 'removeWarehouseStores',
            'button' => true,
            'menu' => true,
        ];

        return $array;
    }

}

return 'slWarehouseStoresGetListProcessor';