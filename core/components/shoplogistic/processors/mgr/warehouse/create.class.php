<?php

class slWarehouseCreateProcessor extends modObjectCreateProcessor
{
    public $objectType = 'slWarehouse';
    public $classKey = 'slWarehouse';
    public $languageTopics = ['shoplogistic'];
    //public $permission = 'create';


    /**
     * @return bool
     */
    public function beforeSet()
    {
        $name = trim($this->getProperty('name'));
        if (empty($name)) {
            $this->modx->error->addField('name', $this->modx->lexicon('shoplogistic_warehouse_err_name'));
        } elseif ($this->modx->getCount($this->classKey, ['name' => $name])) {
            $this->modx->error->addField('name', $this->modx->lexicon('shoplogistic_warehouse_err_ae'));
        }

		$lng = trim($this->getProperty('lng'));
		if(empty($lng)){
			$this->setProperty('lng', 0);
		}

		$lat = trim($this->getProperty('lat'));
		if(empty($lat)){
			$this->setProperty('lat', 0);
		}

        return parent::beforeSet();
    }

}

return 'slWarehouseCreateProcessor';