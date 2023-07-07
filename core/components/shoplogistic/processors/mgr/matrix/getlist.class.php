<?php

class slStoresMatrixGetListProcessor extends modObjectGetListProcessor
{
    public $objectType = 'slStoresMatrix';
    public $classKey = 'slStoresMatrix';
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
    	$store_id = trim($this->getProperty('store_id'));
        $query = trim($this->getProperty('query'));
        if ($query) {
            $c->where([
                'description:LIKE' => "%{$query}%",
				'OR:Store.name:LIKE' => "%{$query}%"
            ]);
        }

        if($store_id){
			$c->where([
				'store_id:=' => $store_id,
			]);
		}

		$c->select(
			$this->modx->getSelectColumns('slStoresMatrix', 'slStoresMatrix', '') . ',
            Store.name as store'
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
            'title' => $this->modx->lexicon('shoplogistic_matrix_update'),
            //'multiple' => $this->modx->lexicon('shoplogistic_items_update'),
            'action' => 'updateMatrix',
            'button' => true,
            'menu' => true,
        ];

        // Remove
        $array['actions'][] = [
            'cls' => '',
            'icon' => 'icon icon-trash-o action-red',
            'title' => $this->modx->lexicon('shoplogistic_matrix_remove'),
            'multiple' => $this->modx->lexicon('shoplogistic_matrix_remove'),
            'action' => 'removeMatrix',
            'button' => true,
            'menu' => true,
        ];

        return $array;
    }

}

return 'slStoresMatrixGetListProcessor';