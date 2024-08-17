<?php

class slOrgRequisitesBankCreateProcessor extends modObjectCreateProcessor
{
    public $objectType = 'slOrgBankRequisites';
    public $classKey = 'slOrgBankRequisites';
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

return 'slOrgRequisitesBankCreateProcessor';