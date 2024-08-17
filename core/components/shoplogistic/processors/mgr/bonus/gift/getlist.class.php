<?php

class slGiftGetListProcessor extends modObjectGetListProcessor
{
    public $objectType = 'slBonusMotivationGift';
    public $classKey = 'slBonusMotivationGift';
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
        $array['gifts'] = [];

        // Edit
        $array['gifts'][] = [
            'cls' => '',
            'icon' => 'icon icon-edit',
            'title' => $this->modx->lexicon('shoplogistic_store_update'),
            'action' => 'updateGift',
            'button' => true,
            'menu' => true,
        ];

        // Remove
        $array['gifts'][] = [
            'cls' => '',
            'icon' => 'icon icon-trash-o action-red',
            'title' => $this->modx->lexicon('shoplogistic_gift_remove'),
            'multiple' => $this->modx->lexicon('shoplogistic_gift_remove'),
            'action' => 'removeGift',
            'button' => true,
            'menu' => true,
        ];

        return $array;
    }

}

return 'slGiftGetListProcessor';