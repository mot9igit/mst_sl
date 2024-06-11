<?php

class slStoresRemainsPricesCreateProcessor extends modObjectCreateProcessor
{
    public $objectType = 'slStoresRemainsPrices';
    public $classKey = 'slStoresRemainsPrices';
    public $languageTopics = ['shoplogistic'];
    //public $permission = 'create';


    /**
     * @return bool
     */
    public function beforeSet()
    {
        $name = trim($this->getProperty('key'));
        if (empty($name)) {
            $this->modx->error->addField('key', $this->modx->lexicon('shoplogistic_store_remains_prices_err_key'));
        } elseif ($this->modx->getCount($this->classKey, ['key' => $name])) {
            $this->modx->error->addField('key', $this->modx->lexicon('shoplogistic_store_remains_prices_err_ae'));
        }

        return parent::beforeSet();
    }

}

return 'slStoresRemainsPricesCreateProcessor';