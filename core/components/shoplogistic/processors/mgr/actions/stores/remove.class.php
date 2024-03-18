<?php

class slActionsStoresRemoveProcessor extends modObjectProcessor
{
    public $objectType = 'slActionsStores';
    public $classKey = 'slActionsStores';
    public $languageTopics = ['shoplogistic'];
    //public $permission = 'remove';


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
            return $this->failure($this->modx->lexicon('shoplogistic_actions_stores_err_ns'));
        }

        foreach ($ids as $id) {
            /** @var shopLogisticItem $object */
            if (!$object = $this->modx->getObject($this->classKey, $id)) {
                return $this->failure($this->modx->lexicon('shoplogistic_actions_stores_err_nf'));
            }

            $object->remove();
        }

        return $this->success();
    }

}

return 'slActionsStoresRemoveProcessor';