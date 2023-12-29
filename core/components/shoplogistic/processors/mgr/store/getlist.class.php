<?php

class slStoresGetListProcessor extends modObjectGetListProcessor
{
    public $objectType = 'slStores';
    public $classKey = 'slStores';
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

        $type = trim($this->getProperty('type'));
        if ($type) {
            if($type == 1){
                $field = 'store';
                $value = 1;
            }
            if($type == 2){
                $field = 'warehouse';
                $value = 1;
            }
            if($type == 3){
                $field = 'vendor';
                $value = 1;
            }
            $c->where([
                $field.':=' => $value
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

        if($array['store']){
            $array['type'] = 1;
        }
        if($array['warehouse']){
            $array['type'] = 2;
        }
        if($array['vendor']){
            $array['type'] = 3;
        }

        // Edit
        $array['actions'][] = [
            'cls' => '',
            'icon' => 'icon icon-edit',
            'title' => $this->modx->lexicon('shoplogistic_store_update'),
            //'multiple' => $this->modx->lexicon('shoplogistic_items_update'),
            'action' => 'updateStore',
            'button' => true,
            'menu' => true,
        ];

        if (!$array['active']) {
            $array['actions'][] = [
                'cls' => '',
                'icon' => 'icon icon-power-off action-green',
                'title' => $this->modx->lexicon('shoplogistic_store_enable'),
                'multiple' => $this->modx->lexicon('shoplogistic_stores_enable'),
                'action' => 'enableStore',
                'button' => true,
                'menu' => true,
            ];
        } else {
            $array['actions'][] = [
                'cls' => '',
                'icon' => 'icon icon-power-off action-gray',
                'title' => $this->modx->lexicon('shoplogistic_store_disable'),
                'multiple' => $this->modx->lexicon('shoplogistic_stores_disable'),
                'action' => 'disableStore',
                'button' => true,
                'menu' => true,
            ];
        }

        // Remove
        $array['actions'][] = [
            'cls' => '',
            'icon' => 'icon icon-trash-o action-red',
            'title' => $this->modx->lexicon('shoplogistic_store_remove'),
            'multiple' => $this->modx->lexicon('shoplogistic_stores_remove'),
            'action' => 'removeStore',
            'button' => true,
            'menu' => true,
        ];

        return $array;
    }

}

return 'slStoresGetListProcessor';