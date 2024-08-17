<?php

class slStoreSettingsGetListProcessor extends modObjectGetListProcessor
{
    public $objectType = 'slSettings';
    public $classKey = 'slSettings';
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
                'OR:key:LIKE' => "%{$query}%",
                'OR:label:LIKE' => "%{$query}%"
            ]);
        }

        $c->where(["active:=" => 1]);

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

        $store_id = trim($this->getProperty('store_id'));
        $array['store_id'] = $store_id;
        $criteria = array("setting_id" => $array["id"], "store_id" => $store_id);
        $value = $this->modx->getObject("slStoresSettings", $criteria);
        if($value){
            $v = $value->toArray();
        }else{
            $v = false;
        }
        $array["value"] = ($v["value"] !== false) ? $v["value"] : $array['default'];

        if($array["type"] == 3){
            if($array["value"] == 1){
                $array["value"] = true;
            }else{
                $array["value"] = false;
            }
        }

        // Edit
        $array['actions'][] = [
            'cls' => '',
            'icon' => 'icon icon-edit',
            'title' => $this->modx->lexicon('shoplogistic_update'),
            //'multiple' => $this->modx->lexicon('shoplogistic_items_update'),
            'action' => 'updateSetting',
            'button' => true,
            'menu' => true,
        ];

        return $array;
    }

}

return 'slStoreSettingsGetListProcessor';