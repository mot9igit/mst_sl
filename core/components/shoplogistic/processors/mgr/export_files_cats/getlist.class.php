<?php

class slExportFilesCatsGetListProcessor extends modObjectGetListProcessor
{
    public $objectType = 'slExportFilesCats';
    public $classKey = 'slExportFilesCats';
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
        $c->leftJoin('modResource', 'modResource', '`modResource`.`id` = `slExportFilesCats`.`cat_id`');

        $query = trim($this->getProperty('query'));
        if ($query) {
            $c->where([
                'name:LIKE' => "%{$query}%"
            ]);
        }

        $file_id = trim($this->getProperty('file_id'));
        if ($file_id) {
            $c->where([
                'file_id:=' => $file_id
            ]);
        }


        $c->select(
            $this->modx->getSelectColumns('slExportFilesCats', 'slExportFilesCats', '', array(), true) . ',
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
            'action' => 'updateCats',
            'button' => true,
            'menu' => true,
        ];

        // Remove
        $array['actions'][] = [
            'cls' => '',
            'icon' => 'icon icon-trash-o action-red',
            'title' => $this->modx->lexicon('shoplogistic_menu_remove'),
            'multiple' => $this->modx->lexicon('shoplogistic_menu_remove'),
            'action' => 'removeCats',
            'button' => true,
            'menu' => true,
        ];

        return $array;
    }

}

return 'slExportFilesCatsGetListProcessor';