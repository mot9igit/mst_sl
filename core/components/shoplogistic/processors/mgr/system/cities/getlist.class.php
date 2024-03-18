<?php


class slCityGetListProcessor extends modObjectGetListProcessor
{
    public $classKey = 'dartLocationCity';
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
            $c->sortby("FIELD (dartLocationCity.id, {$id})", "DESC");
        }
        $query = $this->getProperty('query', '');
        if (!empty($query)) {
            $c->where(array(
                'dartLocationCity.city:LIKE' => "%{$query}%"
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
        $c->select($this->modx->getSelectColumns('dartLocationCity', 'dartLocationCity'));

        return $c;
    }

    public function prepareRow(xPDOObject $object)
    {
        $array = $object->toArray();
        //$this->modx->log(1, print_r($array, 1));
        if ($this->getProperty('combo')) {
            $array = array(
                'id' => $array['id'],
                'city' => $array['city']
            );
        }

        return $array;
    }
}

return 'slCityGetListProcessor';