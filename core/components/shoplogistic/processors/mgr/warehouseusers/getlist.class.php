<?php

class slWarehouseUsersGetListProcessor extends modObjectGetListProcessor
{
    public $objectType = 'slWarehouseUsers';
    public $classKey = 'slWarehouseUsers';
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
		$c->leftJoin('modUser', 'User');
		$c->leftJoin('modUserProfile', 'UserProfile');
		$c->leftJoin('slWarehouse', 'Warehouse');
    	$warehouse_id = trim($this->getProperty('warehouse_id'));
        $query = trim($this->getProperty('query'));
        if ($query) {
            $c->where([
                'description:LIKE' => "%{$query}%",
            ]);
        }

        if($warehouse_id){
			$c->where([
				'warehouse_id:=' => $warehouse_id,
			]);
		}

		$c->select(
			$this->modx->getSelectColumns('slWarehouseUsers', 'slWarehouseUsers', '') . ',
            UserProfile.fullname as user, User.username as user_name,
            Warehouse.name as warehouse'
		);
        //$c->prepare();
        //$this->modx->log(1, $c->toSQL());

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
            'title' => $this->modx->lexicon('shoplogistic_warehouseuser_update'),
            //'multiple' => $this->modx->lexicon('shoplogistic_items_update'),
            'action' => 'updateWarehouseUsers',
            'button' => true,
            'menu' => true,
        ];

        // Remove
        $array['actions'][] = [
            'cls' => '',
            'icon' => 'icon icon-trash-o action-red',
            'title' => $this->modx->lexicon('shoplogistic_warehouseuser_remove'),
            'multiple' => $this->modx->lexicon('shoplogistic_warehouseusers_remove'),
            'action' => 'removeWarehouseUsers',
            'button' => true,
            'menu' => true,
        ];

        return $array;
    }

}

return 'slWarehouseUsersGetListProcessor';