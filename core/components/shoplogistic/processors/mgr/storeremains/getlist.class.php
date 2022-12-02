<?php

class slStoresRemainsGetListProcessor extends modObjectGetListProcessor
{
    public $objectType = 'slStoresRemains';
    public $classKey = 'slStoresRemains';
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

		$c->leftJoin('msProductData', 'msProductData', '`slStoresRemains`.`product_id` = `msProductData`.`id`');
		$c->leftJoin('modResource', 'modResource', '`slStoresRemains`.`product_id` = `modResource`.`id`');

        if ($query) {
            $c->where([
                'modResource.pagetitle:LIKE' => "%{$query}%",
                'OR:msProductData.article:LIKE' => "%{$query}%",
            ]);
        }

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

		$array['product_article'] = $this->modx->shopLogistic->getProductArticleById($array['product_id']);
		$array['product_name'] = $this->modx->shopLogistic->getProductNameById($array['product_id']);
		$array['store_name'] = $this->modx->shopLogistic->getStoreNameById($array['store_id']);

        // Edit
        $array['actions'][] = [
            'cls' => '',
            'icon' => 'icon icon-edit',
            'title' => $this->modx->lexicon('shoplogistic_storeremain_update'),
            //'multiple' => $this->modx->lexicon('shoplogistic_items_update'),
            'action' => 'updateStoreRemain',
            'button' => true,
            'menu' => true,
        ];

        // Remove
        $array['actions'][] = [
            'cls' => '',
            'icon' => 'icon icon-trash-o action-red',
            'title' => $this->modx->lexicon('shoplogistic_storeremain_remove'),
            'multiple' => $this->modx->lexicon('shoplogistic_storeremains_remove'),
            'action' => 'removeStoreRemain',
            'button' => true,
            'menu' => true,
        ];

        return $array;
    }

}

return 'slStoresRemainsGetListProcessor';