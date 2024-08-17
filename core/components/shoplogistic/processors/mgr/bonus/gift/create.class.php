<?php

class slGitfCreateProcessor extends modObjectCreateProcessor
{
    public $objectType = 'slBonusMotivationGift';
    public $classKey = 'slBonusMotivationGift';
    public $languageTopics = ['shoplogistic'];
    //public $permission = 'create';


    /**
     * @return bool
     */
    public function beforeSet()
    {
        $name = trim($this->getProperty('description'));
        if (empty($name)) {
            $this->modx->error->addField('name', $this->modx->lexicon('shoplogistic_gift_err_name'));
        } elseif ($this->modx->getCount($this->classKey, ['name' => $name])) {
            $this->modx->error->addField('name', $this->modx->lexicon('shoplogistic_gift_err_ae'));
        }

        return parent::beforeSet();
    }

}

return 'slGitfCreateProcessor';