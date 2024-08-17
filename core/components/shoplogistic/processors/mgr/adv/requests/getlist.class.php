<?php

class slRequestGetListProcessor extends modObjectGetListProcessor
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
        $c->leftJoin('slActionsStatus', 'slActionsStatus', 'slActionsStatus.id = slActions.status' );
        //$c->leftJoin('slBonusMotivation', 'Config');


        $query = trim($this->getProperty('query'));
        if ($query) {
            $c->where([
                'name:LIKE' => "%{$query}%",
                'OR:description:LIKE' => "%{$query}%"
            ]);
        }

        $c->select(
            $this->modx->getSelectColumns('slActions', 'slActions', '', array(), true) . ',
            slActionsStatus.name as status_name, slActionsStatus.color as color'
        );

        $c->where([
            'page_create' => 1,
            'type' => 2
        ]);

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

        //$array['image'] = "assets/content/" . $array['image'];

        $tmp_store = $this->modx->getObject('slStores', $array['store_id']);
        $array['store'] = $tmp_store->get("name");

        $page_places = explode( ', ', $array['page_places']);
        $array['page_places'] = [];
        foreach($page_places as $key => $item) {
            if($tmp_store = $this->modx->getObject('slPlaceBanners', $item)) {
                $tmp[$key]['id'] = $item;
                $tmp[$key]['name'] = $tmp_store->get("name");
            }
        }
        $array['page_places'] = $tmp;


        // Edit
        $array['actions'][] = [
            'cls' => '',
            'icon' => 'icon icon-edit',
            'title' => $this->modx->lexicon('shoplogistic_store_update'),
            'action' => 'updateRequests',
            'button' => true,
            'menu' => true,
        ];

        if (!$array['active']) {
            $array['actions'][] = [
                'cls' => '',
                'icon' => 'icon icon-power-off action-green',
                'title' => $this->modx->lexicon('shoplogistic_page_enable'),
                'multiple' => $this->modx->lexicon('shoplogistic_page_enable'),
                'action' => 'enableRequests',
                'button' => true,
                'menu' => true,
            ];
        } else {
            $array['actions'][] = [
                'cls' => '',
                'icon' => 'icon icon-power-off action-gray',
                'title' => $this->modx->lexicon('shoplogistic_page_disable'),
                'multiple' => $this->modx->lexicon('shoplogistic_page_disable'),
                'action' => 'disableRequests',
                'button' => true,
                'menu' => true,
            ];
        }

        // Remove
        $array['actions'][] = [
            'cls' => '',
            'icon' => 'icon icon-trash-o action-red',
            'title' => $this->modx->lexicon('shoplogistic_page_remove'),
            'multiple' => $this->modx->lexicon('shoplogistic_page_remove'),
            'action' => 'removeRequests',
            'button' => true,
            'menu' => true,
        ];

        if ($array['status_name'] == "Модерация") {
            $array['actions'][] = [
                'cls' => '',
                'icon' => 'icon icon-thumbs-up action-green',
                'title' => $this->modx->lexicon('shoplogistic_request_status_approve'),
                'multiple' => $this->modx->lexicon('shoplogistic_request_status_approve'),
                'action' => 'statusApproveRequests',
                'button' => true,
                'menu' => true,
            ];

            $array['actions'][] = [
                'cls' => '',
                'icon' => 'icon icon-thumbs-down action-red',
                'title' => $this->modx->lexicon('shoplogistic_request_status_deny'),
                'multiple' => $this->modx->lexicon('shoplogistic_request_status_deny'),
                'action' => 'statusDenyRequests',
                'button' => true,
                'menu' => true,
            ];
        }

        return $array;
    }

}

return 'slRequestGetListProcessor';