<?php

class slSettingsGroupDisableProcessor extends modObjectProcessor
{
    public $objectType = 'slSettingsGroup';
    public $classKey = 'slSettingsGroup';
    public $languageTopics = ['shoplogistic'];
    //public $permission = 'save';


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
            return $this->failure($this->modx->lexicon('shoplogistic_settings_group_err_ns'));
        }

        foreach ($ids as $id) {
            /** @var shopLogisticItem $object */
            if (!$object = $this->modx->getObject($this->classKey, $id)) {
                return $this->failure($this->modx->lexicon('shoplogistic_settings_group_err_nf'));
            }

            $object->set('active', false);
            $object->save();
        }

        return $this->success();
    }

}

return 'slSettingsGroupDisableProcessor';
