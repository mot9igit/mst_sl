<?php

class slParserConfigFieldsGetListProcessor extends modObjectGetListProcessor
{
    public $objectType = 'slParserConfigFields';
    public $classKey = 'slParserConfigFields';
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
                'name:LIKE' => "%{$query}%"
            ]);
        }

        $config_id = trim($this->getProperty('config_id'));
        if ($config_id) {
            $c->where([
                'config_id:=' => $config_id
            ]);
        }

        $field_object = trim($this->getProperty('field_object'));
        if ($field_object) {
            $c->where([
                'field_object:=' => $field_object
            ]);
        }

        // $c->prepare();
        // $this->modx->log(1, $c->toSQL());

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
            'action' => 'updateField',
            'button' => true,
            'menu' => true,
        ];

        // Remove
        $array['actions'][] = [
            'cls' => '',
            'icon' => 'icon icon-trash-o action-red',
            'title' => $this->modx->lexicon('shoplogistic_menu_remove'),
            'multiple' => $this->modx->lexicon('shoplogistic_menu_remove'),
            'action' => 'removeField',
            'button' => true,
            'menu' => true,
        ];

        return $array;
    }

}

return 'slParserConfigFieldsGetListProcessor';