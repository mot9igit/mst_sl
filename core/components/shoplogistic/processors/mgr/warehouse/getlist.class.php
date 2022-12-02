<?php

class slWarehouseGetListProcessor extends modObjectGetListProcessor
{
    public $objectType = 'slWarehouse';
    public $classKey = 'slWarehouse';
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
                'name:LIKE' => "%{$query}%",
                'OR:description:LIKE' => "%{$query}%",
				'OR:apikey:LIKE' => "%{$query}%",
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

		$array['city'] = $this->modx->shopLogistic->getCityNameById($array['city']);

        // Edit
        $array['actions'][] = [
            'cls' => '',
            'icon' => 'icon icon-edit',
            'title' => $this->modx->lexicon('shoplogistic_warehouse_update'),
            //'multiple' => $this->modx->lexicon('shoplogistic_warehouses_update'),
            'action' => 'updateWarehouse',
            'button' => true,
            'menu' => true,
        ];

        if (!$array['active']) {
            $array['actions'][] = [
                'cls' => '',
                'icon' => 'icon icon-power-off action-green',
                'title' => $this->modx->lexicon('shoplogistic_warehouse_enable'),
                'multiple' => $this->modx->lexicon('shoplogistic_warehouses_enable'),
                'action' => 'enableWarehouse',
                'button' => true,
                'menu' => true,
            ];
        } else {
            $array['actions'][] = [
                'cls' => '',
                'icon' => 'icon icon-power-off action-gray',
                'title' => $this->modx->lexicon('shoplogistic_warehouse_disable'),
                'multiple' => $this->modx->lexicon('shoplogistic_warehouses_disable'),
                'action' => 'disableWarehouse',
                'button' => true,
                'menu' => true,
            ];
        }

        // Remove
        $array['actions'][] = [
            'cls' => '',
            'icon' => 'icon icon-trash-o action-red',
            'title' => $this->modx->lexicon('shoplogistic_warehouse_remove'),
            'multiple' => $this->modx->lexicon('shoplogistic_warehouses_remove'),
            'action' => 'removeWarehouse',
            'button' => true,
            'menu' => true,
        ];

        return $array;
    }

}

return 'slWarehouseGetListProcessor';