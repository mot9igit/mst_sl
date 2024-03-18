<?php


class slMS2OptionsGetListProcessor extends modObjectGetListProcessor
{
    public $classKey = 'msOption';
    public $defaultSortField = 'id';


    /**
     * @param xPDOQuery $c
     *
     * @return xPDOQuery
     */
    public function prepareQueryBeforeCount(xPDOQuery $c)
    {
        $category = $this->getProperty('category');
        if($category){
            $opts = array();
            $query = $this->modx->newQuery("msCategoryOption");
            $query->select(array("msCategoryOption.option_id as id"));
            $query->where(array(
                'msCategoryOption.category_id:=' => $category
            ));
            if($query->prepare() && $query->stmt->execute()){
                $options = $query->stmt->fetchAll(PDO::FETCH_ASSOC);
                foreach($options as $opt){
                    $opts[] = $opt["id"];
                }
                if($opts) {
                    $options = array_unique($opts);
                    $c->where(array(
                        'msOption.id:IN' => $options
                    ));
                }
            }
        }
        $query = $this->getProperty('query', '');
        if (!empty($query)) {
            $c->where(array(
                'msOption.caption:LIKE' => "%{$query}%",
                'OR:msOption.description:LIKE' => "%{$query}%",
                'OR:msOption.key:LIKE' => "%{$query}%"
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
        return $c;
    }

    public function prepareRow(xPDOObject $object)
    {
        $array = $object->toArray();
        // $this->modx->log(1, print_r($array, 1));
        if ($this->getProperty('combo')) {
            $array = array(
                'id' => $array['id'],
                'caption' => $array['caption'],
                'description' => $array['description']
            );
        }

        return $array;
    }

    public function afterIteration(array $list)
    {
        $list = parent::afterIteration($list);
        $non = array(
            "caption" => "Не задано",
            "id" => 0
        );
        array_unshift($list , $non);
        return $list;
    }
}

return 'slMS2OptionsGetListProcessor';