<?php

class slStoreBalanceGetListProcessor extends modObjectGetListProcessor
{
    public $objectType = 'slStoreBalance';
    public $classKey = 'slStoreBalance';
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
		$store_id = trim($this->getProperty('store_id'));

		if($store_id){
			$c->where([
				'store_id:=' => $store_id,
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

        if($array['type'] == 1){
			$array['type_name'] = "Начисление";
		}
		if($array['type'] == 2){
			$array['type_name'] = "Списание";
		}
		if($array['type'] == 3){
			$array['type_name'] = "Информационное";
		}

        // Edit
        $array['actions'][] = [
            'cls' => '',
            'icon' => 'icon icon-edit',
            'title' => $this->modx->lexicon('shoplogistic_storebalance_update'),
            //'multiple' => $this->modx->lexicon('shoplogistic_items_update'),
            'action' => 'updateStoreBalance',
            'button' => true,
            'menu' => true,
        ];

        // Remove
        $array['actions'][] = [
            'cls' => '',
            'icon' => 'icon icon-trash-o action-red',
            'title' => $this->modx->lexicon('shoplogistic_storebalance_remove'),
            'multiple' => $this->modx->lexicon('shoplogistic_storebalance_remove'),
            'action' => 'removeStoreBalance',
            'button' => true,
            'menu' => true,
        ];

        return $array;
    }

}

return 'slStoreBalanceGetListProcessor';