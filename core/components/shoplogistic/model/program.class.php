<?php

/**
 *  Обработчик действий с программами
 *
 */

class programHandler
{
    public $modx;
    public $sl;
    public $config;

    public function __construct(shopLogistic &$sl, modX &$modx)
    {
        $this->sl =& $sl;
        $this->modx =& $modx;
        $this->modx->lexicon->load('shoplogistic:default');
    }

    public function set($properties){
        // TODO: проверить уровень доступа
        if($properties['action'] == 'set'){
            $store_id = $properties['id'];
            $properties['available_dates'][0] = date('Y-m-d H:i:s', strtotime($properties['available_dates'][0]));
            $properties['available_dates'][1] = date('Y-m-d H:i:s', strtotime($properties['available_dates'][1]));
            $this->modx->log(1, print_r($properties['available_dates'], 1));
            $start = new DateTime($properties['available_dates'][0]);
            $start->setTime(00,00);
            $end = new DateTime($properties['available_dates'][1]);
            $end->setTime(23,59);

            if($properties['bonus_id']){
                $bonus = $this->modx->getObject('slBonuses', $properties['bonus_id']);
                $bonus->set("updatedon", time());
            }else{
                $bonus = $this->modx->newObject('slBonuses');
                $bonus->set("createdon", time());
            }
            $bonus->set("store_id", $store_id);
            $bonus->set("name", $properties['name']);
            $bonus->set("stores", $properties['stores']);
            $bonus->set("warehouses", $properties['warehouses']);
            $bonus->set("auto_accept", $properties['auto']);
            $bonus->set("brand_id", $properties['brand']);
            $bonus->set("date_from", $start->format('Y-m-d H:i:s'));
            $bonus->set("date_to", $end->format('Y-m-d H:i:s'));
            if($properties['trigger_programs']){
                $programs = array();
                foreach($properties['trigger_programs'] as $program){
                    $programs[] =  $program['id'];
                }
                $bonus->set("conditions_programs", implode(',', $programs));
            }else{
                $bonus->set("conditions_programs", '');
            }
            if($properties['store_ids']){
                $ids = array();
                foreach($properties['store_ids'] as $store){
                    $ids[] = $store['id'];
                }
                $bonus->set("store_ids", implode(',', $ids));
            }
            if($properties['reward']){
                $bonus->set("reward", $properties['reward']);
            }
            if($properties['conditions']){
                $bonus->set("conditions", $properties['conditions']);
            }
            if(isset($properties['region'])) {
                $cities = array();
                $regions = array();
                foreach ($properties['region'] as $key => $val) {
                    if ($val['checked']) {
                        $k_r = explode("_", $key);
                        if ($k_r[0] == 'region') {
                            $regions[] = $k_r[1];
                        }
                        if ($k_r[0] == 'city') {
                            $cities[] = $k_r[1];
                        }
                    }
                }
                if(count($cities)){
                    $bonus->set("cities", implode(',', $cities));
                }else{
                    $bonus->set("cities", '');
                }
                if(count($regions)){
                    $bonus->set("regions", implode(',', $regions));
                }else{
                    $bonus->set("regions", '');
                }
                $props = $bonus->get("properties");
                $props["region"] = $properties['region'];
                $bonus->set("properties", $props);
            }
            $bonus->set("active", 1);
            $bonus->save();
            if($properties['files']){
                if($file = $bonus->get("banner")){
                    $full_path = $this->modx->getOption("base_path").$file;
                    if(is_file($full_path)) {
                        unlink($full_path);
                    }
                }
                $source = $this->modx->getOption("base_path").$properties['files'][0]["original"];
                // грузим новый
                if($properties['files'][0]['path']){
                    $target_path = $this->modx->getOption("base_path")."assets/files/organizations/{$store_id}/{$properties['files'][0]['path']}/";
                    $target_file = $target_path.$this->pcgbasename($source);
                    $url = "assets/files/organizations/{$store_id}/{$properties['files'][0]['path']}/".$this->pcgbasename($source);
                }else{
                    $target_path = $this->modx->getOption("base_path")."assets/files/organizations/{$store_id}/";
                    $target_file = $target_path.$this->pcgbasename($source);
                    $url = "assets/files/organizations/{$store_id}/".$this->pcgbasename($source);
                }
                if(!file_exists($target_path)){
                    mkdir($target_path, 0777, true);
                }
                if (copy($source, $target_file)) {
                    if(is_file($source)) {
                        unlink($source);
                    }
                    $bonus->set("banner", $url);
                    $bonus->save();
                }
            }
            return $bonus->toArray();
        }
        return false;
    }

    public function delete($properties){

    }

    public function changeStatus($properties){

    }

    function pcgbasename($param, $suffix=null) {
        if ( $suffix ) {
            $tmpstr = ltrim(substr($param, strrpos($param, DIRECTORY_SEPARATOR) ), DIRECTORY_SEPARATOR);
            if ( (strpos($param, $suffix)+strlen($suffix) )  ==  strlen($param) ) {
                return str_ireplace( $suffix, '', $tmpstr);
            } else {
                return ltrim(substr($param, strrpos($param, DIRECTORY_SEPARATOR) ), DIRECTORY_SEPARATOR);
            }
        } else {
            return ltrim(substr($param, strrpos($param, DIRECTORY_SEPARATOR) ), DIRECTORY_SEPARATOR);
        }
    }
}