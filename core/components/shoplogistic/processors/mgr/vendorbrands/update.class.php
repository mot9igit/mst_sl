<?php

class slStoresBrandsUpdateProcessor extends modObjectUpdateProcessor
{
    public $objectType = 'slStoresBrands';
    public $classKey = 'slStoresBrands';
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
        $vendor_id = trim($this->getProperty('vendor_id'));
        if (empty($id)) {
            return $this->modx->lexicon('shoplogistic_vendorbrands_err_ns');
        }

        if (empty($vendor_id)) {
            $this->modx->error->addField('vendor_id', $this->modx->lexicon('shoplogistic_vendorbrands_err_vendor_id'));
        } elseif ($this->modx->getCount($this->classKey, ['vendor_id' => $vendor_id, 'id:!=' => $id])) {
            $this->modx->error->addField('vendor_id', $this->modx->lexicon('shoplogistic_vendorbrands_err_ae'));
        }

        return parent::beforeSet();
    }
}

return 'slStoresBrandsUpdateProcessor';
