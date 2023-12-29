<?php

class slStoreBalancePayRequestStatusRemoveProcessor extends modObjectProcessor
{
    public $objectType = 'slStoreBalancePayRequestStatus';
    public $classKey = 'slStoreBalancePayRequestStatus';
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
            return $this->failure($this->modx->lexicon('shoplogistic_balance_pay_request_status_err_ns'));
        }

        foreach ($ids as $id) {
            /** @var shopLogisticItem $object */
            if (!$object = $this->modx->getObject($this->classKey, $id)) {
                return $this->failure($this->modx->lexicon('shoplogistic_balance_pay_request_status_err_nf'));
            }

            $object->remove();
        }

        return $this->success();
    }

}

return 'slStoreBalancePayRequestStatusRemoveProcessor';