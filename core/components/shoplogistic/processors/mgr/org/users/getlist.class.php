<?php

class slOrgUsersGetListProcessor extends modObjectGetListProcessor
{
    public $objectType = 'slOrgUsers';
    public $classKey = 'slOrgUsers';
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

        $c->select(
            $this->modx->getSelectColumns('slOrgUsers', 'slOrgUsers', '') . ',
            UserProfile.fullname as user, User.username as user_name'
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
            'title' => $this->modx->lexicon('shoplogistic_store_update'),
            'action' => 'updateOrgStores',
            'button' => true,
            'menu' => true,
        ];

        // Remove
        $array['actions'][] = [
            'cls' => '',
            'icon' => 'icon icon-trash-o action-red',
            'title' => $this->modx->lexicon('shoplogistic_delivery_remove'),
            'multiple' => $this->modx->lexicon('shoplogistic_deliveries_remove'),
            'action' => 'removeOrgStores',
            'button' => true,
            'menu' => true,
        ];

        return $array;
    }

}

return 'slOrgUsersGetListProcessor';