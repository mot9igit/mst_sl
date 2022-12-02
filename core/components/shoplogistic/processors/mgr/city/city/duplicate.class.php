<?php
class shoplogisticCityCityDuplicateProcessor extends modObjectCreateProcessor {
    public $classKey = 'slCityCity';
    
    /**
     * var modX
     */
    public function beforeSet() {
		$key = trim($this->getProperty('key'));

		if(trim($this->getProperty('default'))){
			$sql = "UPDATE {$this->modx->getTableName($this->classKey)} SET `default` = 0 WHERE 1";
			$query = $this->modx->query($sql);
		}
        
        if ($this->modx->getCount($this->classKey, array('key' => $key))) {
            $this->modx->error->addField('key', $this->modx->lexicon('shoplogistic_err_name_ae'));
        } else {
            // Set coordinats
            if ($this->getProperty('address_full')) {
                $this->setProperty('address_coordinats', $this->modx->shopLogistic->getCoordinats($this->getProperty('address_full')));
            } elseif ($this->getProperty('address')) {
				$this->setProperty('address_coordinats', $this->modx->shopLogistic->getCoordinats($this->getProperty('address')));
			}
        }
        
        return parent::beforeSet();
    }
    
    /**
     * var modX
     */
    public function afterSave() {
        $this->modx->shopLogistic->duplicateFields($this->getProperty('id'), $this->object->get('id'));
        
        return parent::afterSave();
    }
}

return "shoplogisticCityCityDuplicateProcessor";