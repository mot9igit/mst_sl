<?php

class slParserTasksGetListProcessor extends modObjectGetListProcessor
{
    public $objectType = 'slParserTasks';
    public $classKey = 'slParserTasks';
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

        $c->leftJoin('slParserTasksStatus', 'Status');
        $c->leftJoin('slParserConfig', 'Config');
        $query = trim($this->getProperty('query'));
        if ($query) {
            $c->where([
                'name:LIKE' => "%{$query}%",
                'OR:description:LIKE' => "%{$query}%",
            ]);
        }
        $c->select(
            $this->modx->getSelectColumns('slParserTasks', 'slParserTasks', '', array(), true) . ',
            Status.name as status_name, Status.color as color, Config.name as config'
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
            'action' => 'updateTask',
            'button' => true,
            'menu' => true,
        ];

        // Remove
        $array['actions'][] = [
            'cls' => '',
            'icon' => 'icon icon-trash-o action-red',
            'title' => $this->modx->lexicon('shoplogistic_menu_remove'),
            'multiple' => $this->modx->lexicon('shoplogistic_menu_remove'),
            'action' => 'removeTask',
            'button' => true,
            'menu' => true,
        ];
        if($object->file){
            $array['actions'][] = [
                'cls' => '',
                'icon' => 'icon icon-download',
                'title' => $this->modx->lexicon('shoplogistic_menu_download'),
                // 'multiple' => $this->modx->lexicon('shoplogistic_menu_download'),
                'action' => 'downloadTaskFile',
                'button' => true,
                'menu' => true,
            ];
        }

        return $array;
    }

}

return 'slParserTasksGetListProcessor';