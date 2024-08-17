<?php

class slRequestGetProcessor extends modObjectGetProcessor
{
    public $objectType = 'slActions';
    public $classKey = 'slActions';
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

        $page_places = explode( ', ', $array['page_places']);
        $array['page_places'] = [];
        foreach($page_places as $key => $item) {
            if($tmp_store = $this->modx->getObject('slPlaceBanners', $item)) {
                $tmp[$key]['id'] = $item;
                $tmp[$key]['id'] = $tmp_store->get("name");
            }
        }
        $array['page_places'] = $tmp;

        $tmp_store = $this->modx->getObject('slStores', $array['store_id']);
        $array['store'] = $tmp_store->get("name");

        return $this->success('', $array);
    }

}

return 'slRequestGetProcessor';