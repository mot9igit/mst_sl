<?php

class shoplogisticCityCityGetListProcessor extends modObjectGetListProcessor {
    public $classKey = 'slCityCity';
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
                'OR:city:LIKE' => "%{$query}%",
            ));
        }

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

        // Copy
        $array['actions'][] = array(
            'cls' => 'shoplogistic-duplicate',
            'icon' => 'icon icon-copy',
            'title' => $this->modx->lexicon('duplicate'),
            //'multiple' => $this->modx->lexicon('view'),
            'action' => 'duplicateItem',
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

return "shoplogisticCityCityGetListProcessor";