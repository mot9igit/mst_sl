<?php

class slActionsGetProcessor extends modObjectGetProcessor
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

    public function beforeOutput() {
        /*
        if($this->object->store){
            $this->object->set('type', 1);
        }
        if($this->object->warehouse){
            $this->object->set('type', 2);
        }
        if($this->object->vendor){
            $this->object->set('type', 3);
        }
        */
    }

    public function cleanup() {
        $array = $this->object->toArray();

        if(!$array['content']){
            $array['content'] = "";
        }

        if($array['cities']){
            $tmp = [];
            // $array['cities'] = json_decode($array['cities'], 1);
            foreach($array['cities'] as $key => $item) {
                if($tmp_city = $this->modx->getObject('dartLocationCity', $item)) {
                    $tmp[] = array(
                        "id" => $item,
                        "city" => $tmp_city->get('city')
                    );
                }
            }
            $array['cities'] = $tmp;
        }

        if($array['regions']) {
            $tmp = [];
            // $array['regions'] = json_decode($array['regions'], 1);
            foreach ($array['regions'] as $key => $item) {
                if ($tmp_region = $this->modx->getObject('dartLocationRegion', $item)) {
                    $tmp[] = array(
                        "id" => $item,
                        "name" => $tmp_region->get('name')
                    );
                }
            }
            // unset($array['regions']);
            $array['regions'] = $tmp;
        }

        return $this->success('', $array);
    }

}

return 'slActionsGetProcessor';