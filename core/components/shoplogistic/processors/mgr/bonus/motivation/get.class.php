<?php

class slMotivationGetProcessor extends modObjectGetProcessor
{
    public $objectType = 'slBonusMotivation';
    public $classKey = 'slBonusMotivation';
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
        $array['store_ids'] = [];
        foreach($array['stores'] as $key => $item) {
            if($tmp_store = $this->modx->getObject('slStores', $item)) {
                $tmp[$key]['id'] = $item;
                $tmp[$key]['id'] = $tmp_store->get("name");
            }
        }
        $array['store_ids'] = $tmp;

        $tmplate = [];
        $array['gifts'] = [];
        foreach($array['gift_ids'] as $k => $item) {
            if($tmp_gift = $this->modx->getObject('slBonusMotivationGift', $item)) {
                $tmplate[$k]['id'] = $tmp_gift->get("id");
                $tmplate[$k]['name'] = $tmp_gift->get("name");
            }
        }
        $array['gifts'] = $tmplate;

        return $this->success('', $array);
    }

}

return 'slMotivationGetProcessor';