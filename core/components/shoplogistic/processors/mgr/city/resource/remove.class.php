<?php

class slCityResourceRemoveProcessor extends modObjectProcessor
{
    public $objectType = 'slCityResource';
    public $classKey = 'slCityResource';
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
        if (!$ids) {
            return $this->failure($this->modx->lexicon('error'));
        }

        foreach ($ids as $id) {
            if (!$object = $this->modx->getObject($this->classKey, $id)) {
                return $this->failure($this->modx->lexicon('error'));
            }

            $object->remove();
        }

        return $this->success();
    }

}

return 'slCityResourceRemoveProcessor';