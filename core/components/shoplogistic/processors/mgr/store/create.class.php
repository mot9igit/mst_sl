<?php

class slStoresCreateProcessor extends modObjectCreateProcessor
{
    public $objectType = 'slStores';
    public $classKey = 'slStores';
    public $languageTopics = ['shoplogistic'];
    //public $permission = 'create';


    /**
     * @return bool
     */
    public function beforeSet()
    {
        $name = trim($this->getProperty('name'));
        if (empty($name)) {
            $this->modx->error->addField('name', $this->modx->lexicon('shoplogistic_store_err_name'));
        } elseif ($this->modx->getCount($this->classKey, ['name' => $name])) {
            $this->modx->error->addField('name', $this->modx->lexicon('shoplogistic_store_err_ae'));
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

return 'slStoresCreateProcessor';