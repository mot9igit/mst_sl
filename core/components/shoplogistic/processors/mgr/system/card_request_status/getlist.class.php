<?php


class slCardRequestStatusGetListProcessor extends modObjectGetListProcessor
{
    public $classKey = 'slCardRequestStatus';
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

return 'slCardRequestStatusGetListProcessor';