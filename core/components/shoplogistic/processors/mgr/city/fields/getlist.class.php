<?php

class slCityFieldsGetListProcessor extends modObjectGetListProcessor {
    public $classKey = 'slCityFields';
    public $defaultSortField = 'id'; 
    public $defaultSortDirection = 'ASC';
    
    /**
     * @param xPDOQuery $c
     *
     * @return array
     */
    public function prepareQueryBeforeCount(xPDOQuery $c) {
        $query = trim($this->getProperty('query'));
        if ($query) {
            $c->where(array(
                'key:LIKE' => "%{$query}%",
                'OR:value:LIKE' => "%{$query}%",
            ));
        }
        
        $c->where(array(
            'city' => $this->getProperty('city'),
        ));
        
        return $c;
    }
    
     /**
     * @param xPDOObject $object
     *
     * @return array
     */
    public function prepareRow(xPDOObject $object) {
        $array = $object->toArray();
        
        $array['actions'] = array();

        // Edit
        $array['actions'][] = array(
            'cls' => 'shoplogistic-update',
            'icon' => 'icon icon-edit',
            'title' => $this->modx->lexicon('update'),
            //'multiple' => $this->modx->lexicon('view'),
            'action' => 'updateItem',
            'button' => true,
            'menu' => true,
        );

        // Remove
        $array['actions'][] = array(
            'cls' => 'shoplogistic-remove',
            'icon' => 'icon icon-trash-o action-red',
            'title' => $this->modx->lexicon('remove'),
            'multiple' => $this->modx->lexicon('remove'),
            'action' => 'removeItem',
            'button' => true,
            'menu' => true,
        );

        return $array;
    }
}

return "slCityFieldsGetListProcessor";