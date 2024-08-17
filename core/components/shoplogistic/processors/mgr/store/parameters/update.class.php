<?php

class slStoreSettingsUpdateProcessor extends modProcessor {

    public function process() {
        $scriptProperties = $this->getProperties();
        $output = $scriptProperties;

        $criteria = array(
            "setting_id" => $scriptProperties["id"],
            "store_id" => $scriptProperties["store_id"]
        );
        $value = $this->modx->getObject("slStoresSettings", $criteria);
        if(!$value){
            $value = $this->modx->newObject("slStoresSettings");
            $value->set("setting_id", $scriptProperties["id"]);
            $value->set("store_id", $scriptProperties["store_id"]);
        }
        $value->set("value", $scriptProperties["value"]);
        $value->save();

        return $this->success($output);
    }
}

return 'slStoreSettingsUpdateProcessor';
