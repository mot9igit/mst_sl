<?php

class slCityResourceGetListProcessor extends modObjectGetListProcessor {
    public $classKey = 'slCityResource';
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
                'value:LIKE' => "%{$query}%"
            ));
        }
        
        $c->where(array(
            'resource' => $this->getProperty('resource'),
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
        
        $array['city'] = $this->modx->shopLogistic->getCityNameById($array['city']);
        
        
        $array['value'] = mb_substr(strip_tags($array['value']), 0, 60);
        
        $array['actions'] = [];

        // Edit
        $array['actions'][] = [
            'cls' => 'shoplogistic-update',
            'icon' => 'icon icon-edit',
            'title' => $this->modx->lexicon('update'),
            //'multiple' => $this->modx->lexicon('view'),
            'action' => 'updateItem',
            'button' => true,
            'menu' => true,
        ];

        // Remove
        $array['actions'][] = [
            'cls' => 'shoplogistic-remove',
            'icon' => 'icon icon-trash-o action-red',
            'title' => $this->modx->lexicon('remove'),
            'multiple' => $this->modx->lexicon('remove'),
            'action' => 'removeItem',
            'button' => true,
            'menu' => true,
        ];

        return $array;
    }
}

return "slCityResourceGetListProcessor";