<?php

class slBrandAssociationUpdateProcessor extends modObjectUpdateProcessor
{
    public $objectType = 'slBrandAssociation';
    public $classKey = 'slBrandAssociation';
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
        $name = trim($this->getProperty('association'));
        if (empty($id)) {
            return $this->modx->lexicon('shoplogistic_brand_association_err_ns');
        }

        if (empty($name)) {
            $this->modx->error->addField('name', $this->modx->lexicon('shoplogistic_brand_association__err_association'));
        } elseif ($this->modx->getCount($this->classKey, ['name' => $name, 'id:!=' => $id])) {
            $this->modx->error->addField('name', $this->modx->lexicon('shoplogistic_brand_association_err_ae'));
        }

        return parent::beforeSet();
    }
}

return 'slBrandAssociationUpdateProcessor';
