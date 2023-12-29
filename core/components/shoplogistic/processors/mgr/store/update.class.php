<?php

class slStoresUpdateProcessor extends modObjectUpdateProcessor
{
    public $objectType = 'slStores';
    public $classKey = 'slStores';
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
        $name = trim($this->getProperty('name'));
        if (empty($id)) {
            return $this->modx->lexicon('shoplogistic_store_err_ns');
        }

        if (empty($name)) {
            $this->modx->error->addField('name', $this->modx->lexicon('shoplogistic_store_err_name'));
        } elseif ($this->modx->getCount($this->classKey, ['name' => $name, 'id:!=' => $id])) {
            $this->modx->error->addField('name', $this->modx->lexicon('shoplogistic_store_err_ae'));
        }

        $active = trim($this->getProperty('active'));

        $corePath = $this->modx->getOption('shoplogistic_core_path', array(), $this->modx->getOption('core_path') . 'components/shoplogistic/');
        $shopLogistic = $this->modx->getService('shopLogistic', 'shopLogistic', $corePath . 'model/');
        if ($shopLogistic) {
            $shopLogistic->loadServices("web");
            if($active){
                // Ставим на товар статус В наличии
                $shopLogistic->product->changeAvailableStatus($id, 1);
            }else{
                // Ставим на товар статус НЕ в наличии
                $shopLogistic->product->changeAvailableStatus($id, 99);
            }
        }

        return parent::beforeSet();
    }
}

return 'slStoresUpdateProcessor';
