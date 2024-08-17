<?php

class slStoresComboGetListProcessor extends modObjectGetListProcessor
{
    public $objectType = 'slStores';
    public $classKey = 'slStores';
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
                'OR:description:LIKE' => "%{$query}%",
                'OR:apikey:LIKE' => "%{$query}%",
            ]);
        }

        $c->select($this->modx->getSelectColumns('slStores', 'slStores', '', array("id", "name")));

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

        $out = array(
            "id" => $array["id"],
            "name" => $array["name"],
        );

        return $out;
    }
}

return 'slStoresComboGetListProcessor';