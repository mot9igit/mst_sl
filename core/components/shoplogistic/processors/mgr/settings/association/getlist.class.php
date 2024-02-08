<?php

class slBrandAssociationGetListProcessor extends modObjectGetListProcessor
{
    public $objectType = 'slBrandAssociation';
    public $classKey = 'slBrandAssociation';
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
        $c->leftJoin('msVendor', 'msVendor', '`msVendor`.`id` = `slBrandAssociation`.`brand_id`');

        $query = trim($this->getProperty('query'));
        if ($query) {
            $c->where([
                'association:LIKE' => "%{$query}%"
            ]);
        }

        $c->select(
            $this->modx->getSelectColumns('slBrandAssociation', 'slBrandAssociation') . ',
            msVendor.name as brand'
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
            'action' => 'updateAssociation',
            'button' => true,
            'menu' => true,
        ];

        // Remove
        $array['actions'][] = [
            'cls' => '',
            'icon' => 'icon icon-trash-o action-red',
            'title' => $this->modx->lexicon('shoplogistic_menu_remove'),
            'multiple' => $this->modx->lexicon('shoplogistic_menu_remove'),
            'action' => 'removeAssociation',
            'button' => true,
            'menu' => true,
        ];

        return $array;
    }

}

return 'slBrandAssociationGetListProcessor';