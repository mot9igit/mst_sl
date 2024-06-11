<?php

class slStoresRemainsPricesUpdateProcessor extends modObjectUpdateProcessor
{
    public $objectType = 'slStoresRemainsPrices';
    public $classKey = 'slStoresRemainsPrices';
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
        $name = trim($this->getProperty('key'));
        if (empty($id)) {
            return $this->modx->lexicon('shoplogistic_store_remains_prices_err_ns');
        }

        if (empty($name)) {
            $this->modx->error->addField('key', $this->modx->lexicon('shoplogistic_store_remains_prices_err_key'));
        } elseif ($this->modx->getCount($this->classKey, ['key' => $name, 'id:!=' => $id])) {
            $this->modx->error->addField('key', $this->modx->lexicon('shoplogistic_store_remains_prices_err_ae'));
        }

        return parent::beforeSet();
    }
}

return 'slStoresRemainsPricesUpdateProcessor';
