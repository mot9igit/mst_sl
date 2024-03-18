<?php

class slActionsStoresGetListProcessor extends modObjectGetListProcessor
{
    public $objectType = 'slActionsStores';
    public $classKey = 'slActionsStores';
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
        $c->leftJoin('slStores', 'slStores');
        $c->leftJoin('slActions', 'slActions');

        $query = trim($this->getProperty('query'));
        if ($query) {
            $c->where([
                'slStores.name:LIKE' => "%{$query}%",
                'OR:slStores.address:LIKE' => "%{$query}%",
                'OR:description:LIKE' => "%{$query}%"
            ]);
        }

        $action_id = (int)trim($this->getProperty('action_id'));
        if($action_id){
            $c->where([
                'action_id:=' => $action_id,
            ]);
        }

        $c->select(
            $this->modx->getSelectColumns('slActionsStores', 'slActionsStores', '') . ',
            slStores.name as store_name,
            slActions.name as action_name'
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
            'title' => $this->modx->lexicon('shoplogistic_action_store_update'),
            //'multiple' => $this->modx->lexicon('shoplogistic_actions_update'),
            'action' => 'updateActionStore',
            'button' => true,
            'menu' => true,
        ];

        if (!$array['active']) {
            $array['actions'][] = [
                'cls' => '',
                'icon' => 'icon icon-power-off action-green',
                'title' => $this->modx->lexicon('shoplogistic_action_store_enable'),
                'multiple' => $this->modx->lexicon('shoplogistic_action_stores_enable'),
                'action' => 'enableActionStore',
                'button' => true,
                'menu' => true,
            ];
        } else {
            $array['actions'][] = [
                'cls' => '',
                'icon' => 'icon icon-power-off action-gray',
                'title' => $this->modx->lexicon('shoplogistic_action_store_disable'),
                'multiple' => $this->modx->lexicon('shoplogistic_action_stores_disable'),
                'action' => 'disableActionStore',
                'button' => true,
                'menu' => true,
            ];
        }

        // Remove
        $array['actions'][] = [
            'cls' => '',
            'icon' => 'icon icon-trash-o action-red',
            'title' => $this->modx->lexicon('shoplogistic_action_store_remove'),
            'multiple' => $this->modx->lexicon('shoplogistic_action_stores_remove'),
            'action' => 'removeActionStore',
            'button' => true,
            'menu' => true,
        ];

        return $array;
    }

}

return 'slActionsStoresGetListProcessor';