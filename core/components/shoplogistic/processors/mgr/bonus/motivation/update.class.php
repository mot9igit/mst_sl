<?php

class slMotivationUpdateProcessor extends modObjectUpdateProcessor
{
    public $objectType = 'slBonusMotivation';
    public $classKey = 'slBonusMotivation';
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
            return $this->modx->lexicon('shoplogistic_motivation_err_ns');
        }

        if (empty($name)) {
            $this->modx->error->addField('name', $this->modx->lexicon('shoplogistic_motivation_err_name'));
        } elseif ($this->modx->getCount($this->classKey, ['name' => $name, 'id:!=' => $id])) {
            $this->modx->error->addField('name', $this->modx->lexicon('shoplogistic_motivation_err_ae'));
        }

        $store_ids = $this->getProperty('store_ids');
        if(!empty($store_ids)){
            $this->setProperty("stores", $store_ids);
        }

        $gifts = $this->getProperty('gifts');
        if(!empty($gifts)){
            $this->setProperty("gift_ids", $gifts);
        }



        return parent::beforeSet();
    }
}

return 'slMotivationUpdateProcessor';
