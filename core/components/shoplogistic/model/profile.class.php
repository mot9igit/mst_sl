<?php

/**
 * Класс работы с профилем пользователя
 */


class profileHandler
{
    public $modx;
    public $sl;
    public $config;

    public function __construct(shopLogistic &$sl, modX &$modx)
    {
        $this->sl =& $sl;
        $this->modx =& $modx;

        $this->config = array(
            "user_id" => $this->modx->user->id
        );

        $this->modx->lexicon->load('shoplogistic:default');
    }

    /**
     * Берем локацию для адреса
     *
     * @param $data
     * @return void
     */
    public function getLocation($data){
        // если нет адреса, то берем текущее местоположение
        if($data["location_data"]){
            $output["map_location"] = json_decode($data["location_data"], 1);
            $output["map_location"]['name'] = $data["text_address"];
        }else{
            $output["map_location"] = $this->sl->getLocationData("web");
            $output["map_location"]['name'] = "Вы здесь";
        }
        return $output;
    }

    /**
     * Установка адреса
     *
     * @param $data
     * @return mixed
     */
    public function setAddress ($data) {
        $mode = "create";
        $data['user_id'] = $this->modx->user->id;
        if($data['id']){
            $address = $this->modx->getObject("slUserAddress", $data['id']);
            if(!$address){
                return $this->sl->tools->error("Объект адреса не найден");
            }else{
                $mode = "update";
                $address->set("updatedon", time());
            }
        }else{
            $address = $this->modx->newObject("slUserAddress");
            $address->set("createdon", time());
        }
        unset($data['id']);
        foreach($data as $k => $v){
            $address->set($k, $v);
        }
        $address->save();
        if($mode == "create"){
            return $this->sl->tools->success("Адрес создан!");
        }else{
            return $this->sl->tools->success("Адрес отредактирован!");
        }
    }

    /**
     * Удаление адреса
     *
     * @param $data
     * @return mixed
     */
    public function removeAddress($data){
        if($data['id']){
            $address = $this->modx->getObject("slUserAddress", $data['id']);
            if(!$address){
                return $this->sl->tools->error("Объект адреса не найден");
            }else{
                $address->remove();
                return $this->sl->tools->success("Адрес удален!");
            }
        }
        return $this->sl->tools->error("Не передан ID адреса");
    }

    /**
     * Обновление списка адресов
     *
     * @return bool[]
     */
    public function updateAddresses(){
        $out = array();
        $pdo = $this->modx->getParser()->pdoTools;
        $out['update_data'] = $pdo->runSnippet("@FILE snippets/get_profile_addresses.php", array(
            "tpl" => "@FILE chunks/profile_address.tpl"
        ));
        $out['update_data_modal'] = $pdo->runSnippet("@FILE snippets/get_profile_addresses.php", array(
            "tpl" => "@FILE chunks/profile_address_modal.tpl"
        ));
        return $out;
    }
}