<?php

class slExportFilesGetListProcessor extends modObjectGetListProcessor
{
    public $objectType = 'slExportFiles';
    public $classKey = 'slExportFiles';
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
        $c->leftJoin('slExportFileStatus', 'Status');
        $c->leftJoin('msVendor', 'Vendor');
        $query = trim($this->getProperty('query'));
        if ($query) {
            $c->where([
                'name:LIKE' => "%{$query}%",
                'OR:description:LIKE' => "%{$query}%",
            ]);
        }
        $c->select(
            $this->modx->getSelectColumns('slExportFiles', 'slExportFiles', '', array(), true) . ',
            Status.name as status_name, Status.color as color, Vendor.name as vendor_name'
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

        $stores = explode(",", $array['store_id']);
        foreach($stores as $key => $item) {
            if($tmp_store = $this->modx->getObject('slStores', $item)) {
                $tmp[$key]['id'] = $item;
                $tmp[$key]['name'] = $tmp_store->get('name');
            }
        }
        $array['store_ids'] = $tmp;

        // Edit
        $array['actions'][] = [
            'cls' => '',
            'icon' => 'icon icon-edit',
            'title' => $this->modx->lexicon('shoplogistic_menu_update'),
            //'multiple' => $this->modx->lexicon('shoplogistic_items_update'),
            'action' => 'updateFile',
            'button' => true,
            'menu' => true,
        ];

        // Remove
        $array['actions'][] = [
            'cls' => '',
            'icon' => 'icon icon-trash-o action-red',
            'title' => $this->modx->lexicon('shoplogistic_menu_remove'),
            'multiple' => $this->modx->lexicon('shoplogistic_menu_remove'),
            'action' => 'removeFile',
            'button' => true,
            'menu' => true,
        ];

        return $array;
    }

}

return 'slExportFilesGetListProcessor';