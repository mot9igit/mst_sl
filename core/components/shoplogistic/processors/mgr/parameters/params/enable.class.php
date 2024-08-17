<?php

class slSettingsEnableProcessor extends modObjectProcessor
{
    public $objectType = 'slSettings';
    public $classKey = 'slSettings';
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
            return $this->failure($this->modx->lexicon('shoplogistic_setting_err_ns'));
        }

        foreach ($ids as $id) {
            /** @var slSettingsGroup $object */
            if (!$object = $this->modx->getObject($this->classKey, $id)) {
                return $this->failure($this->modx->lexicon('shoplogistic_setting_err_nf'));
            }

            $object->set('active', true);
            $object->save();
        }

        return $this->success();
    }

}

return 'slSettingsEnableProcessor';
