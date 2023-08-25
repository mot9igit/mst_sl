<?php

class slReportsTypeFieldsGetListProcessor extends modObjectGetListProcessor
{
    public $objectType = 'slReportsTypeFields';
    public $classKey = 'slReportsTypeFields';
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
        $c->leftJoin('slReportsType', 'Type');
        $query = trim($this->getProperty('query'));
        $report_id = trim($this->getProperty('report_id'));
        if ($query) {
            $c->where([
                'name:LIKE' => "%{$query}%",
                'OR:description:LIKE' => "%{$query}%",
            ]);
        }
        if($report_id){
            $c->where([
                'type:=' => $report_id,
            ]);
        }
        $c->select(
            $this->modx->getSelectColumns('slReportsTypeFields', 'slReportsTypeFields', '', array(), true) . ',
            Type.name as type_name'
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
            'action' => 'updateReportTypeField',
            'button' => true,
            'menu' => true,
        ];

        // Remove
        $array['actions'][] = [
            'cls' => '',
            'icon' => 'icon icon-trash-o action-red',
            'title' => $this->modx->lexicon('shoplogistic_menu_remove'),
            'multiple' => $this->modx->lexicon('shoplogistic_menu_remove'),
            'action' => 'removeReportTypeField',
            'button' => true,
            'menu' => true,
        ];

        return $array;
    }

}

return 'slReportsTypeFieldsGetListProcessor';