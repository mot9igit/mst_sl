<?php

class slMotivationRemoveProcessor extends modObjectProcessor
{
    public $objectType = 'slBonusMotivation';
    public $classKey = 'slBonusMotivation';
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
            return $this->failure($this->modx->lexicon('shoplogistic_motivation_err_ns'));
        }

        foreach ($ids as $id) {
            /** @var slDelivery $object */
            if (!$object = $this->modx->getObject($this->classKey, $id)) {
                return $this->failure($this->modx->lexicon('shoplogistic_motivation_err_nf'));
            }

            $object->remove();
        }

        return $this->success();
    }

}

return 'slMotivationRemoveProcessor';