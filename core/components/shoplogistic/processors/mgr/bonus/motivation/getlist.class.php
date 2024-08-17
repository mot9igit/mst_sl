<?php

class slMotivationGetListProcessor extends modObjectGetListProcessor
{
    public $objectType = 'slBonusMotivation';
    public $classKey = 'slBonusMotivation';
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
        if ($query) {
            $c->where([
                'name:LIKE' => "%{$query}%",
                'OR:description:LIKE' => "%{$query}%"
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


        $tmp = [];

        foreach($array['stores'] as $key => $item) {
            if($tmp_store = $this->modx->getObject('slStores', $item)) {
                $tmp[$key]['id'] = $item;
                $tmp[$key]['name'] = $tmp_store->get('name');
            }
        }
        $array['store_ids'] = $tmp;

        $tmplate = [];
        $array['gifts'] = [];
        foreach($array['gift_ids'] as $k => $item) {
            if($tmp_gift = $this->modx->getObject('slBonusMotivationGift', $item)) {
                $tmplate[$k]['id'] = $tmp_gift->get("id");
                $tmplate[$k]['name'] = $tmp_gift->get("name");
            }
        }
        $array['gifts'] = $tmplate;

        // Edit
        $array['actions'][] = [
            'cls' => '',
            'icon' => 'icon icon-edit',
            'title' => $this->modx->lexicon('shoplogistic_store_update'),
            'action' => 'updateMotivation',
            'button' => true,
            'menu' => true,
        ];

        if (!$array['active']) {
            $array['actions'][] = [
                'cls' => '',
                'icon' => 'icon icon-power-off action-green',
                'title' => $this->modx->lexicon('shoplogistic_motivation_enable'),
                'multiple' => $this->modx->lexicon('shoplogistic_motivation_enable'),
                'action' => 'enableMotivation',
                'button' => true,
                'menu' => true,
            ];
        } else {
            $array['actions'][] = [
                'cls' => '',
                'icon' => 'icon icon-power-off action-gray',
                'title' => $this->modx->lexicon('shoplogistic_motivation_disable'),
                'multiple' => $this->modx->lexicon('shoplogistic_motivation_disable'),
                'action' => 'disableMotivation',
                'button' => true,
                'menu' => true,
            ];
        }

        // Remove
        $array['actions'][] = [
            'cls' => '',
            'icon' => 'icon icon-trash-o action-red',
            'title' => $this->modx->lexicon('shoplogistic_motivation_remove'),
            'multiple' => $this->modx->lexicon('shoplogistic_motivation_remove'),
            'action' => 'removeMotivation',
            'button' => true,
            'menu' => true,
        ];

        return $array;
    }

}

return 'slMotivationGetListProcessor';