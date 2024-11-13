<?php

class slStoresRemainsGroupsGetListProcessor extends modObjectGetListProcessor
{
    public $objectType = 'slStoresRemainsGroups';
    public $classKey = 'slStoresRemainsGroups';
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
        $store_id = trim($this->getProperty('store_id'));
        $query = trim($this->getProperty('query'));
        if($store_id){
            $c->where([
                'store_id:=' => $store_id,
            ]);
        }
        if ($query) {
            $c->where([
                'slStoresRemainsGroups.name:LIKE' => "%{$query}%",
                'OR:slStoresRemainsGroups.description:LIKE' => "%{$query}%",
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

        // Edit
        $array['actions'][] = [
            'cls' => '',
            'icon' => 'icon icon-edit',
            'title' => $this->modx->lexicon('shoplogistic_update'),
            'action' => 'updateStoreGroup',
            'button' => true,
            'menu' => true,
        ];

        // Remove
        $array['actions'][] = [
            'cls' => '',
            'icon' => 'icon icon-trash-o action-red',
            'title' => $this->modx->lexicon('shoplogistic_remove'),
            'multiple' => $this->modx->lexicon('shoplogistic_remove'),
            'action' => 'removeStoreGroup',
            'button' => true,
            'menu' => true,
        ];

        return $array;
    }

}

return 'slStoresRemainsGroupsGetListProcessor';