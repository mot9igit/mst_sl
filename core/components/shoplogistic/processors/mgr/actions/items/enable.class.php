<?php

class slActionsEnableProcessor extends modObjectProcessor
{
    public $objectType = 'slActions';
    public $classKey = 'slActions';
    public $languageTopics = ['shoplogistic'];
    public $checkViewPermission = false;
    //public $permission = 'save';

    public function initialize(){
        return true;
    }

    public function checkPermissions() {
        return true;
    }

    /**
     * @return array|string
     */
    public function process()
    {
        if (!$this->checkPermissions()) {
            return $this->failure($this->modx->lexicon('access_denied'));
        }

        $ids = $this->modx->fromJSON($this->getProperty('ids'));
        if (empty($ids)) {
            return $this->failure($this->modx->lexicon('shoplogistic_actions_err_ns'));
        }

        foreach ($ids as $id) {
            /** @var shopLogisticItem $object */
            if (!$object = $this->modx->getObject($this->classKey, $id)) {
                return $this->failure($this->modx->lexicon('shoplogistic_actions_err_nf'));
            }

            $object->set('active', true);
            $object->save();

        }

        return $this->success();
    }

    public function beforeSave()
    {
        if (!$this->checkPermissions()) {
            return $this->modx->lexicon('access_denied');
        }

        $this->object->set('updatedby', $this->modx->user->get('id'));
        $this->object->set('updatedon', time());

        return true;
    }

}

return 'slActionsEnableProcessor';
