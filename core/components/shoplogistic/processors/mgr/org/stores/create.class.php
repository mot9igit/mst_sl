<?php

class slOrgStoresCreateProcessor extends modObjectCreateProcessor
{
    public $objectType = 'slOrgStores';
    public $classKey = 'slOrgStores';
    public $languageTopics = ['shoplogistic'];
    //public $permission = 'create';


    /**
     * @return bool
     */
    public function beforeSet()
    {

        return parent::beforeSet();
    }

}

return 'slOrgStoresCreateProcessor';