<?php

class slBrandAssociationCreateProcessor extends modObjectCreateProcessor
{
    public $objectType = 'slBrandAssociation';
    public $classKey = 'slBrandAssociation';
    public $languageTopics = ['shoplogistic'];
    //public $permission = 'create';


    /**
     * @return bool
     */
    public function beforeSet()
    {
        $name = trim($this->getProperty('association'));
        if (empty($name)) {
            $this->modx->error->addField('association', $this->modx->lexicon('shoplogistic_brand_association_err_association'));
        } elseif ($this->modx->getCount($this->classKey, ['association' => $name])) {
            $this->modx->error->addField('association', $this->modx->lexicon('shoplogistic_brand_association_err_ae'));
        }

        return parent::beforeSet();
    }

}

return 'slBrandAssociationCreateProcessor';