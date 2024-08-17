<?php

class slStoreBalanceRemoveProcessor extends modObjectProcessor
{
    public $objectType = 'slStoreBalance';
    public $classKey = 'slStoreBalance';
    public $languageTopics = ['shoplogistic'];
    //public $permission = 'remove';


    /**
     * @return array|string
     */
    public function process()
    {
        if (!$this->checkPermissions()) {
            return $this->failure($this->modx->lexicon('access_denied'));
        }

		$ids = $this->modx->fromJSON($this->getProperty('ids'));
		if (empty($ids)) {
			return $this->failure($this->modx->lexicon('shoplogistic_storeuser_err_ns'));
		}

		foreach ($ids as $id) {
			/** @var shopLogisticItem $object */
			if (!$object = $this->modx->getObject($this->classKey, $id)) {
				return $this->failure($this->modx->lexicon('shoplogistic_storeuser_err_nf'));
			}

			$type = trim($object->get('type'));
			$store_id = trim($object->get('store_id'));
			$value = trim($object->get('value'));

			$store = $this->modx->getObject("slStores", $store_id);
			if($store){
				if($type == 1){
					$b = $store->get('balance');
					$store->set('balance', $b - $value);
					$store->save();
				}
				if($type == 2){
					$b = $store->get('balance');
					$store->set('balance', $b + $value);
					$store->save();
				}
			}

			$object->remove();
		}

        return $this->success();
    }

}

return 'slStoreBalanceRemoveProcessor';