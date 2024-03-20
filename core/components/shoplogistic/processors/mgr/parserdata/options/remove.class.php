<?php

class slParserDataCatsOptionsRemoveProcessor extends modObjectProcessor
{
    public $objectType = 'slParserDataCatsOptions';
    public $classKey = 'slParserDataCatsOptions';
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
            return $this->failure($this->modx->lexicon('shoplogistic_parserdata_options_err_ns'));
        }

        foreach ($ids as $id) {
            /** @var slParserConfig $object */
            if (!$object = $this->modx->getObject($this->classKey, $id)) {
                return $this->failure($this->modx->lexicon('shoplogistic_parserdata_options_err_nf'));
            }

            $object->remove();
        }

        return $this->success();
    }

}

return 'slParserDataCatsOptionsRemoveProcessor';