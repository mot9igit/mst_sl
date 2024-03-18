<?php

class slActionsGetListProcessor extends modObjectGetListProcessor
{
    public $objectType = 'slActions';
    public $classKey = 'slActions';
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
        $c->leftJoin('slStores', 'slStores', "slStores.id = slActions.store_id");
        $query = trim($this->getProperty('query'));
        if ($query) {
            $c->where([
                'name:LIKE' => "%{$query}%",
                'OR:description:LIKE' => "%{$query}%",
				'OR:content:LIKE' => "%{$query}%",
            ]);
        }

        $c->select(
            $this->modx->getSelectColumns('slActions', 'slActions', '') . ',
            slStores.name as store_name'
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

        if($array["regions"]){
            $array["regions_name"] = array();
            foreach($array["regions"] as $region){
                if($r = $this->modx->getObject("dartLocationRegion", $region)){
                    $array["regions_name"][] = $r->get("name");
                }
            }
            $array["regions_name"] = implode(", ", $array["regions_name"]);
        }

        if($array["cities"]){
            $array["cities_name"] = array();
            foreach($array["cities"] as $city){
                if($c = $this->modx->getObject("dartLocationCity", $city)){
                    $array["cities_name"][] = $c->get("city");
                }
            }
            $array["cities_name"] = implode(", ", $array["cities_name"]);
        }

        // Edit
        $array['actions'][] = [
            'cls' => '',
            'icon' => 'icon icon-edit',
            'title' => $this->modx->lexicon('shoplogistic_action_update'),
            //'multiple' => $this->modx->lexicon('shoplogistic_actions_update'),
            'action' => 'updateAction',
            'button' => true,
            'menu' => true,
        ];

        if (!$array['active']) {
            $array['actions'][] = [
                'cls' => '',
                'icon' => 'icon icon-power-off action-green',
                'title' => $this->modx->lexicon('shoplogistic_action_enable'),
                'multiple' => $this->modx->lexicon('shoplogistic_actions_enable'),
                'action' => 'enableAction',
                'button' => true,
                'menu' => true,
            ];
        } else {
            $array['actions'][] = [
                'cls' => '',
                'icon' => 'icon icon-power-off action-gray',
                'title' => $this->modx->lexicon('shoplogistic_action_disable'),
                'multiple' => $this->modx->lexicon('shoplogistic_actions_disable'),
                'action' => 'disableAction',
                'button' => true,
                'menu' => true,
            ];
        }

        // Remove
        $array['actions'][] = [
            'cls' => '',
            'icon' => 'icon icon-trash-o action-red',
            'title' => $this->modx->lexicon('shoplogistic_action_remove'),
            'multiple' => $this->modx->lexicon('shoplogistic_actions_remove'),
            'action' => 'removeAction',
            'button' => true,
            'menu' => true,
        ];

        return $array;
    }

}

return 'slActionsGetListProcessor';