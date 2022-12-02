<?php

class slStoreUsersCreateProcessor extends modObjectCreateProcessor
{
    public $objectType = 'slStoreUsers';
    public $classKey = 'slStoreUsers';
    public $languageTopics = ['shoplogistic'];
    //public $permission = 'create';


    /**
     * @return bool
     */
    public function beforeSet()
    {
        $user = trim($this->getProperty('user_id'));
		$store = trim($this->getProperty('store_id'));
        if (empty($user)) {
            $this->modx->error->addField('user_id', $this->modx->lexicon('shoplogistic_storeusers_err_user_id'));
        } elseif ($this->modx->getCount($this->classKey, ['user_id' => $user, 'store_id' => $store])) {
            $this->modx->error->addField('user_id', $this->modx->lexicon('shoplogistic_storeusers_err_ae'));
        }

        return parent::beforeSet();
    }

}

return 'slStoreUsersCreateProcessor';