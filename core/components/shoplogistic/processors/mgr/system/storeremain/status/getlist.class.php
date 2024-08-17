<?php


class slStoresRemainsStatusGetListProcessor extends modObjectGetListProcessor
{
    public $classKey = 'slStoresRemainsStatus';
    public $defaultSortField = 'id';


    /**
     * @param xPDOQuery $c
     *
     * @return xPDOQuery
     */
    public function prepareQueryBeforeCount(xPDOQuery $c)
    {
        $query = $this->getProperty('query', '');
        if (!empty($query)) {
            $c->where(array(
                'name:LIKE' => "%{$query}%",
                'OR:description:LIKE' => "%{$query}%"
            ));
        }
        $c->where(array(
            'active:=' => 1
        ));
        return $c;
    }


    /**
     * @param xPDOQuery $c
     *
     * @return xPDOQuery
     */
    public function prepareQueryAfterCount(xPDOQuery $c)
    {
        return $c;
    }

    public function prepareRow(xPDOObject $object)
    {
        $array = $object->toArray();
        //$this->modx->log(1, print_r($array, 1));
        if ($this->getProperty('combo')) {
            $array = array(
                'id' => $array['id'],
                'name' => $array['name'],
                'description' => $array['description']
            );
        }

        return $array;
    }
}

return 'slStoresRemainsStatusGetListProcessor';