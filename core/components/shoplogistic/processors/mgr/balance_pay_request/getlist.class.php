<?php

class slStoreBalancePayRequestGetListProcessor extends modObjectGetListProcessor
{
    public $objectType = 'slStoreBalancePayRequest';
    public $classKey = 'slStoreBalancePayRequest';
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
        $c->leftJoin('slStoreBalancePayRequestStatus', 'Status');
        $c->leftJoin('slStores', 'Store', "Store.id = slStoreBalancePayRequest.store_id");
        $query = trim($this->getProperty('query'));
        if ($query) {
            $c->where([
                'name:LIKE' => "%{$query}%",
                'OR:description:LIKE' => "%{$query}%",
            ]);
        }
        $c->select(
            $this->modx->getSelectColumns('slStoreBalancePayRequest', 'slStoreBalancePayRequest', '', array(), true) . ',
            Status.name as status_name, Status.color as color, Store.name as store_name'
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
            'title' => $this->modx->lexicon('shoplogistic_menu_update'),
            //'multiple' => $this->modx->lexicon('shoplogistic_items_update'),
            'action' => 'updateRequest',
            'button' => true,
            'menu' => true,
        ];

        // Remove
        $array['actions'][] = [
            'cls' => '',
            'icon' => 'icon icon-trash-o action-red',
            'title' => $this->modx->lexicon('shoplogistic_menu_remove'),
            'multiple' => $this->modx->lexicon('shoplogistic_menu_remove'),
            'action' => 'removeRequest',
            'button' => true,
            'menu' => true,
        ];

        return $array;
    }

}

return 'slStoreBalancePayRequestGetListProcessor';