<?php

class slStoresRemainsHistoryGetListProcessor extends modObjectGetListProcessor
{
    public $objectType = 'slStoresRemainsHistory';
    public $classKey = 'slStoresRemainsHistory';
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
        $remain_id = trim($this->getProperty('remain_id'));

        if($remain_id){
            $c->where([
                'remain_id:=' => $remain_id,
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
            'title' => $this->modx->lexicon('shoplogistic_edit'),
            'action' => 'updateStoreRemain',
            'button' => true,
            'menu' => true,
        ];

        // Remove
        $array['actions'][] = [
            'cls' => '',
            'icon' => 'icon icon-trash-o action-red',
            'title' => $this->modx->lexicon('shoplogistic_remove'),
            'action' => 'removeStoreRemain',
            'button' => true,
            'menu' => true,
        ];

        return $array;
    }

}

return 'slStoresRemainsHistoryGetListProcessor';