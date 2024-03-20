<?php

class slParserDataCatsOptionsGetListProcessor extends modObjectGetListProcessor
{
    public $objectType = 'slParserDataCatsOptions';
    public $classKey = 'slParserDataCatsOptions';
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

        $cat_id = trim($this->getProperty('cat_id'));
        if ($cat_id) {
            $c->where([
                'cat_id:=' => $cat_id
            ]);
        }

        $c->leftJoin('msOption', 'msOption', '`msOption`.`id` = `slParserDataCatsOptions`.`option_id`');

        $c->select(
            $this->modx->getSelectColumns('slParserDataCatsOptions', 'slParserDataCatsOptions', '', array(), true) . ',
            msOption.caption as opt'
        );
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
            'action' => 'updateOptions',
            'button' => true,
            'menu' => true,
        ];

        // Remove
        $array['actions'][] = [
            'cls' => '',
            'icon' => 'icon icon-trash-o action-red',
            'title' => $this->modx->lexicon('shoplogistic_menu_remove'),
            'multiple' => $this->modx->lexicon('shoplogistic_menu_remove'),
            'action' => 'removeOptions',
            'button' => true,
            'menu' => true,
        ];

        return $array;
    }

}

return 'slParserDataCatsOptionsGetListProcessor';