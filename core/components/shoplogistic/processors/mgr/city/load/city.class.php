<?php
require_once MODX_CORE_PATH.'components/shoplogistic/processors/mgr/city/city/getlist.class.php';

class slCityLoadCityProcessor extends shoplogisticCityCityGetListProcessor {
    public $permission = '';

    public function prepareRow(xPDOObject $object)
    {
        $array = parent::prepareRow($object);
        $array['id'] =  $array['id'];
        $array['city'] =  $array['city'];
        
        return $array;
    }

}

return 'slCityLoadCityProcessor';