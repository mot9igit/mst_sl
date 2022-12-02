<?php

class slStoreBalanceCreateProcessor extends modObjectCreateProcessor
{
    public $objectType = 'slStoreBalance';
    public $classKey = 'slStoreBalance';
    public $languageTopics = ['shoplogistic'];
    //public $permission = 'create';


    /**
     * @return bool
     */
    public function beforeSet()
    {
		$type = trim($this->getProperty('type'));
		$store_id = trim($this->getProperty('store_id'));
		$value = trim($this->getProperty('value'));

		$store = $this->modx->getObject("slStores", $store_id);
		if($store){
			$b = $store->get('balance');
			if((int)$type == 1){
				$bal = (float) $b + (float) $value;
			}
			if((int)$type == 2){
				$bal = (float) $b - (float) $value;
			}
			$store->set('balance', $bal);
			$store->save();
		}
        return parent::beforeSet();
    }

	public function beforeSave() {

		$scriptProperties = $this->getProperties();
		if(empty($scriptProperties['createdon'])){
			$this->object->set('createdon', strftime('%Y-%m-%d %H:%M:%S'));
		}

		return parent::beforeSave();
	}
}


return 'slStoreBalanceCreateProcessor';