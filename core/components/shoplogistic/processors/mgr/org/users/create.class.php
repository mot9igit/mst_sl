<?php

class slOrgUsersCreateProcessor extends modObjectCreateProcessor
{
    public $objectType = 'slOrgUsers';
    public $classKey = 'slOrgUsers';
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

return 'slOrgUsersCreateProcessor';