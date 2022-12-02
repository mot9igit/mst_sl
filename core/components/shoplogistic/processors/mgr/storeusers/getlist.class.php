<?php

class slStoreUsersGetListProcessor extends modObjectGetListProcessor
{
    public $objectType = 'slStoreUsers';
    public $classKey = 'slStoreUsers';
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
		$c->leftJoin('slStores', 'Store');
    	$store_id = trim($this->getProperty('store_id'));
        $query = trim($this->getProperty('query'));
        if ($query) {
            $c->where([
                'description:LIKE' => "%{$query}%",
            ]);
        }

        if($store_id){
			$c->where([
				'store_id:=' => $store_id,
			]);
		}

		$c->select(
			$this->modx->getSelectColumns('slStoreUsers', 'slStoreUsers', '') . ',
            UserProfile.fullname as user, User.username as user_name,
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
            'title' => $this->modx->lexicon('shoplogistic_storeuser_update'),
            //'multiple' => $this->modx->lexicon('shoplogistic_items_update'),
            'action' => 'updateStoreusers',
            'button' => true,
            'menu' => true,
        ];

        // Remove
        $array['actions'][] = [
            'cls' => '',
            'icon' => 'icon icon-trash-o action-red',
            'title' => $this->modx->lexicon('shoplogistic_storeuser_remove'),
            'multiple' => $this->modx->lexicon('shoplogistic_storeusers_remove'),
            'action' => 'removeStoreusers',
            'button' => true,
            'menu' => true,
        ];

        return $array;
    }

}

return 'slStoreUsersGetListProcessor';