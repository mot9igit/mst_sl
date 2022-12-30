<?php
require_once MODX_CORE_PATH.'components/dartlocation/processors/mgr/city/getlist.class.php';

class shopLogisticLoadCityProcessor extends dartLocationCityGetListProcessor {
	//public $permission = '';
	public function prepareRow(xPDOObject $object)
	{
		$array = parent::prepareRow($object);
		$array['id'] =  $array['id'];
		$array['city'] =  $array['city'];
		return $array;
	}
}

return 'shopLogisticLoadCityProcessor';