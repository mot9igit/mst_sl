<?php

class slRequestDisableProcessor extends modObjectProcessor
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
            return $this->failure($this->modx->lexicon('shoplogistic_store_err_ns'));
        }

        foreach ($ids as $id) {
            /** @var shopLogisticItem $object */
            if (!$object = $this->modx->getObject($this->classKey, $id)) {
                return $this->failure($this->modx->lexicon('shoplogistic_page_err_nf'));
            }

            $statuses = $this->modx->getObject("slActionsStatus", array("name" => "Активна"));

            $object->set('status', $statuses->get('id'));
            $object->save();

        }

        return $this->success();
    }

}

return 'slRequestDisableProcessor';
