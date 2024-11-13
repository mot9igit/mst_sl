<?php

class slStoresRemainsUpdateProcessor extends modObjectUpdateProcessor
{
    public $objectType = 'slStoresRemains';
    public $classKey = 'slStoresRemains';
    public $languageTopics = ['shoplogistic'];
    //public $permission = 'save';


    /**
     * We doing special check of permission
     * because of our objects is not an instances of modAccessibleObject
     *
     * @return bool|string
     */
    public function beforeSave()
    {
        if (!$this->checkPermissions()) {
            return $this->modx->lexicon('access_denied');
        }

        return true;
    }


    /**
     * @return bool
     */
    public function beforeSet()
    {
        $id = (int)$this->getProperty('id');
        if (empty($id)) {
            return $this->modx->lexicon('shoplogistic_store_err_ns');
        }

        // если Бренд сменили ставим блокировку
        $brand_id = (int)$this->getProperty('brand_id');
        $obj = $this->modx->getObject($this->classKey, ['id' => $id]);
        if($obj){
            $old_brand_id = $obj->get('brand_id');
            if($old_brand_id != $brand_id){
                $this->setProperty('brand_manual', 1);
            }
        }


		$guid = trim($this->getProperty('guid'));
		$store_id = trim($this->getProperty('store_id'));
        $groups = $this->getProperty('groups');
        $this->modx->log(1, print_r($groups, 1));
        $this->setProperty('groups', implode(",", $groups));
		$obj = $this->modx->getObject($this->classKey, ['guid' => $guid, 'store_id' => $store_id]);
		if (empty($guid)) {
			$this->modx->error->addField('guid', $this->modx->lexicon('shoplogistic_storeremains_err_product_id'));
		} elseif ($obj->id != $id) {
			$this->modx->error->addField('guid', $this->modx->lexicon('shoplogistic_storeremains_err_double'));
		}

        return parent::beforeSet();
    }
}

return 'slStoresRemainsUpdateProcessor';
