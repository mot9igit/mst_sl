<?php

class slStoresRemainsGroupsRemoveProcessor extends modObjectProcessor
{
    public $objectType = 'slStoresRemainsGroups';
    public $classKey = 'slStoresRemainsGroups';
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
            /** @var slStoresRemainsGroups $object */
            if (!$object = $this->modx->getObject($this->classKey, $id)) {
                return $this->failure("Объекты не найдены");
            }

            $object->remove();
        }

        return $this->success();
    }

}

return 'slStoresRemainsGroupsRemoveProcessor';