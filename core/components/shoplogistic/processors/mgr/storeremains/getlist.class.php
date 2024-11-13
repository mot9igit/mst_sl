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
        $status = trim($this->getProperty('status'));
        $published = trim($this->getProperty('published'));
        $copo = trim($this->getProperty('copo'));

		$c->leftJoin('msProductData', 'msProductData', '`slStoresRemains`.`product_id` = `msProductData`.`id`');
		$c->leftJoin('modResource', 'modResource', '`slStoresRemains`.`product_id` = `modResource`.`id`');
        $c->leftJoin('slStoresRemainsStatus', 'slStoresRemainsStatus', '`slStoresRemainsStatus`.`id` = `slStoresRemains`.`status`');

        if ($query) {
            $c->where([
                'modResource.pagetitle:LIKE' => "%{$query}%",
                'OR:msProductData.article:LIKE' => "%{$query}%",
				'OR:name:LIKE' => "%{$query}%",
				'OR:article:LIKE' => "%{$query}%",
            ]);
        }

		if($store_id){
			$c->where([
				'store_id:=' => $store_id,
			]);
		}

        if($status){
            $c->where([
                'status:=' => $status,
            ]);
        }

        if(($published == 0 || $published == 1) && $published != ''){
            $c->where([
                'published:=' => $published,
            ]);
        }

        if(($copo == 0 || $copo == 1) && $copo != ''){
            if($copo == 0){
                $c->where([
                    'product_id:=' => 0,
                ]);
            }else{
                $c->where([
                    'product_id:>' => 0,
                ]);
            }
        }

        $c->select(
            $this->modx->getSelectColumns('slStoresRemains', 'slStoresRemains', '', array(), true) . ',
            slStoresRemainsStatus.name as status_name, slStoresRemainsStatus.color as color'
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

		$array['product_article'] = $this->modx->shopLogistic->getProductArticleById($array['product_id']);
		$array['product_name'] = $this->modx->shopLogistic->getProductNameById($array['product_id']);
		$array['store_name'] = $this->modx->shopLogistic->getStoreNameById($array['store_id']);

        if($array["groups"]){
            $array["groups"] = explode(",", $array["groups"]);
            $array["groups_name"] = array();
            foreach($array["groups"] as $group){
                if($c = $this->modx->getObject("slStoresRemainsGroups", $group)){
                    $array["groups_name"][] = array(
                        "id" => $c->get("id"),
                        "name" => $c->get("name")
                    );
                }
            }
            $array["groups"] = $array["groups_name"];
        }

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