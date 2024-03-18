<?php

class slActionsProductsGetProcessor extends modObjectGetProcessor
{
    public $objectType = 'slActionsProducts';
    public $classKey = 'slActionsProducts';
    public $languageTopics = ['shoplogistic:default'];
    //public $permission = 'view';


    /**
     * We doing special check of permission
     * because of our objects is not an instances of modAccessibleObject
     *
     * @return mixed
     */
    public function process()
    {
        if (!$this->checkPermissions()) {
            return $this->failure($this->modx->lexicon('access_denied'));
        }

        return parent::process();
    }

    public function beforeOutput() {
        /*
        if($this->object->store){
            $this->object->set('type', 1);
        }
        if($this->object->warehouse){
            $this->object->set('type', 2);
        }
        if($this->object->vendor){
            $this->object->set('type', 3);
        }
        */
    }

}

return 'slActionsProductsGetProcessor';