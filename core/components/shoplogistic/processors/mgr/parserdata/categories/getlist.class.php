<?php

class slParserDataCatsGetListProcessor extends modObjectGetListProcessor
{
    public $objectType = 'slParserDataCats';
    public $classKey = 'slParserDataCats';
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
        $c->leftJoin('modResource', 'modResource', '`modResource`.`id` = `slParserDataCats`.`cat_id`');

        $query = trim($this->getProperty('query'));
        if ($query) {
            $c->where([
                'name:LIKE' => "%{$query}%"
            ]);
        }

        $service_id = trim($this->getProperty('service_id'));
        if ($service_id) {
            $c->where([
                'service_id:=' => $service_id
            ]);
        }

        $c->select(
            $this->modx->getSelectColumns('slParserDataCats', 'slParserDataCats', '', array(), true) . ',
            modResource.pagetitle as cat'
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
            'action' => 'updateCat',
            'button' => true,
            'menu' => true,
        ];

        // Remove
        $array['actions'][] = [
            'cls' => '',
            'icon' => 'icon icon-trash-o action-red',
            'title' => $this->modx->lexicon('shoplogistic_menu_remove'),
            'multiple' => $this->modx->lexicon('shoplogistic_menu_remove'),
            'action' => 'removeCat',
            'button' => true,
            'menu' => true,
        ];

        return $array;
    }

}

return 'slParserDataCatsGetListProcessor';