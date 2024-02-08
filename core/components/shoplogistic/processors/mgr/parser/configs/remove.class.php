<?php

class slParserConfigRemoveProcessor extends modObjectProcessor
{
    public $objectType = 'slParserConfig';
    public $classKey = 'slParserConfig';
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
            return $this->failure($this->modx->lexicon('shoplogistic_parser_config_err_ns'));
        }

        foreach ($ids as $id) {
            /** @var slParserConfig $object */
            if (!$object = $this->modx->getObject($this->classKey, $id)) {
                return $this->failure($this->modx->lexicon('shoplogistic_parser_config_err_nf'));
            }

            $object->remove();
        }

        return $this->success();
    }

}

return 'slParserConfigRemoveProcessor';