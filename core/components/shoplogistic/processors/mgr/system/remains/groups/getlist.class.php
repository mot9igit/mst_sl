<?php


class slGroupsGetListProcessor extends modObjectGetListProcessor
{
    public $classKey = 'slStoresRemainsGroups';
    public $defaultSortField = 'id';


    /**
     * @param xPDOQuery $c
     *
     * @return xPDOQuery
     */
    public function prepareQueryBeforeCount(xPDOQuery $c)
    {
        $id = $this->getProperty('id');
        if (!empty($id) and $this->getProperty('combo')) {
            $c->sortby("FIELD (slStoresRemainsGroups.id, {$id})", "DESC");
        }
        $query = $this->getProperty('query', '');
        if (!empty($query)) {
            $c->where(array(
                'slStoresRemainsGroups.name:LIKE' => "%{$query}%"
            ));
        }

        $store_id = $this->getProperty('store_id', '');
        if (!empty($store_id)) {
            $c->where(array(
                'slStoresRemainsGroups.store_id:=' => $store_id
            ));
        }

        return $c;
    }


    /**
     * @param xPDOQuery $c
     *
     * @return xPDOQuery
     */
    public function prepareQueryAfterCount(xPDOQuery $c)
    {
        $c->select($this->modx->getSelectColumns('slStoresRemainsGroups', 'slStoresRemainsGroups'));

        return $c;
    }

    public function prepareRow(xPDOObject $object)
    {
        $array = $object->toArray();
        //$this->modx->log(1, print_r($array, 1));
        if ($this->getProperty('combo')) {
            $array = array(
                'id' => $array['id'],
                'name' => $array['name']
            );
        }

        return $array;
    }
}

return 'slGroupsGetListProcessor';