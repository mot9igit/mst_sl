<?php

class slDocsGetProcessor extends modObjectGetProcessor
{
    public $objectType = 'slDocs';
    public $classKey = 'slDocs';
    public $languageTopics = ['shoplogistic:default'];
    //public $permission = 'view';


    /**
     * We doing special check of permission
     * because of our objects is not an instances of modAccessibleObject
     *
     * @return mixed
     */
    public function process()
    {
        if (!$this->checkPermissions()) {
            return $this->failure($this->modx->lexicon('access_denied'));
        }

        return parent::process();
    }

    public function cleanup() {
        $array = $this->object->toArray();

        $tmp = [];
        $stores = explode(",", $array['store_id']);
        foreach($stores as $key => $item) {
            if($tmp_store = $this->modx->getObject('slStores', $item)) {
                $tmp[$key]['id'] = $item;
                $tmp[$key]['name'] = $tmp_store->get('name');
            }
        }
        $array['store_ids'] = $tmp;

        return $this->success('', $array);
    }

}

return 'slDocsGetProcessor';