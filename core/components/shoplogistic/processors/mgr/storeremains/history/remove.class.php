<?php

class slStoresRemainsHistoryRemoveProcessor extends modObjectProcessor
{
    public $objectType = 'slStoresRemainsHistory';
    public $classKey = 'slStoresRemainsHistory';
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
            return $this->failure("Не указаны ID объектов");
        }

        foreach ($ids as $id) {
            /** @var slStoresRemainsHistory $object */
            if (!$object = $this->modx->getObject($this->classKey, $id)) {
                return $this->failure("Объекты не найдены");
            }

            $object->remove();
        }

        return $this->success();
    }

}

return 'slStoresRemainsHistoryRemoveProcessor';