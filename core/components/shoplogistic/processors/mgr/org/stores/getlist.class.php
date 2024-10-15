<?php

class slOrgStoresGetListProcessor extends modObjectGetListProcessor
{
    public $objectType = 'slOrgStores';
    public $classKey = 'slOrgStores';
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
        $c->leftJoin('slStores', 'slStores', 'slStores.id = slOrgStores.store_id');
        $query = trim($this->getProperty('query'));
        if ($query) {
            $c->where([
                'name:LIKE' => "%{$query}%",
                'OR:description:LIKE' => "%{$query}%"
            ]);
        }

        $org_id = trim($this->getProperty('org_id'));
        if ($org_id) {
            $c->where([
                'org_id:=' => $org_id
            ]);
        }

        $c->select($this->modx->getSelectColumns('slOrgStores', 'slOrgStores', '') . ',slStores.name_short as store_name');

        //$this->modx->log(1, $c);

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
            'action' => 'updateOrgStores',
            'button' => true,
            'menu' => true,
        ];

        // Remove
        $array['actions'][] = [
            'cls' => '',
            'icon' => 'icon icon-trash-o action-red',
            'title' => $this->modx->lexicon('shoplogistic_remove'),
            'multiple' => $this->modx->lexicon('shoplogistic_remove'),
            'action' => 'removeOrgStores',
            'button' => true,
            'menu' => true,
        ];

        return $array;
    }

}

return 'slOrgStoresGetListProcessor';