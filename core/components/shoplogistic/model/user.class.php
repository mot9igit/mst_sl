<?php

/**
 * Класс инструментов
 */

class slUser
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

    /**
     *  Проверка прав доступа
     * @param $user_id
     * @param $org_id
     * @param $action
     * @return bool
     */
    public function checkUserPermission($user_id, $org_id, $action){
        return true;
    }

    /**
     * Регистрация пользователя
     *
     * @param $properties
     * @return mixed
     */
    public function register($properties){
        $count = $this->modx->getCount('modUser', array('username' => $properties['login']));
        if($count > 0){
            $response = $this->sl->tools->error("Пользователь с логином ".$properties['login']." уже существует!");
            return $response;
        }else{
            $query = $this->modx->newQuery("slOrgRequisites");
            $query->leftJoin("slOrg", "slOrg", "slOrg.id == slOrgRequisites.org_id");
            $query->where(array(
                'slOrgRequisites.inn' => $properties['org']['inn'],
                "slOrg.owner_id" => 0
            ));
            $count = $this->modx->getCount('slOrgRequisites', $query);
            if($count > 0){
                return $this->sl->tools->error("Организация с ИНН {$properties['org']['inn']} уже существует!");
            }else{
                // регистрируем пользователя
                $user = $this->modx->newObject('modUser');
                $user->set('username', $properties['login']);
                $user->set('password', $properties['password']);
                $user->save();

                $profile = $this->modx->newObject('modUserProfile');
                $profile->set('fullname', $properties['name']);
                $profile->set('email', $properties['email']);
                $profile->set('phone', $properties['telephone']);
                $user->addOne($profile);
                $profile->save();
                $user->save();
                $userdata = $user->toArray();
                $userdata["profile"] = $profile->toArray();
                if($properties["org"]){
                    // если есть организация
                    $org = $this->modx->newObject('slOrg');
                    $org->set("name", $properties['org']['name']);
                    $org->set("email", $properties['email']);
                    $org->set("phone", $properties['telephone']);
                    $org->set("store", 0);
                    $org->set("contact", $properties['name']);
                    if($org->save()){
                        $userdata["organization"] = $org->toArray();
                        // Создаем реквизиты
                        $org_id = $org->get("id");
                        $org_req = $this->modx->newObject('slOrgRequisites');
                        $org_req->set("org_id", $org_id);
                        $org_req->set("name", $properties['org']['name']);
                        $org_req->set("inn", $properties['org']['inn']);
                        $org_req->set("fact_address", $properties['delivery_addresses'][0]["value"]);
                        $org_req->save();
                        // связь с приватниками
                        $privats = $this->sl->orgHandler->getPrivateClients($properties['org']['inn']);
                        foreach($privats as $privat){
                            // ID организации хранится в privat["org_id"]
                            // slActions client_id, если type 3
                            // slActionsStores -> store_id
                            // slWarehouseStores -> org_id
                        }
                        $userdata["organization"]['requizites'] = $org_req->toArray();
                        // Создаем склады
                        foreach($properties['delivery_addresses'] as $address){
                            if(!isset($address["data"])){
                                if (!class_exists('Dadata')) {
                                    require_once dirname(__FILE__) . '/dadata.class.php';
                                }
                                $token = $this->modx->getOption('shoplogistic_api_key_dadata');
                                $secret = $this->modx->getOption('shoplogistic_secret_key_dadata');
                                $dadata = new Dadata($token, $secret);
                                $dadata->init();
                                $res = $dadata->clean('address', $address["value"]);
                                $address["data"] = $res[0];
                            }
                            $store = $this->modx->newObject("slStores");
                            $store->set("name", $properties['org']['name'].' || '.$address["value"]);
                            $city = $this->sl->tools->getCity($address);
                            $store->set("city", $city);
                            $store->set("address", $address["value"]);
                            $store->set("contact", $properties['name']);
                            $store->set("email", $properties['email']);
                            $store->set("phone", $properties['telephone']);
                            $store->set("integration", 0);
                            $store->set("marketplace", 0);
                            $store->set("opt_marketplace", 0);
                            $store->set("check_remains", 0);
                            $store->set("check_docs", 0);
                            $store->set("active", 1);
                            $store->set("coordinats", $address["data"]["geo_lat"].','.$address["data"]["geo_lon"]);
                            $store->set("lat", $address["data"]["geo_lat"]);
                            $store->set("lng", $address["data"]["geo_lon"]);
                            if($store->save()){
                                $userdata["organization"]['stores'][] = $store->toArray();
                                // связь склада и организации
                                $store_id = $store->get("id");
                                $link = $this->modx->newObject("slOrgStores");
                                $link->set("org_id", $org_id);
                                $link->set("store_id", $store_id);
                                $link->save();

                                // связь юзера и склада
                                $uslink = $this->modx->newObject("slStoreUsers");
                                $uslink->set("store_id", $store_id);
                                $uslink->set("user_id", $userdata['id']);
                                $uslink->save();
                            }
                        }
                        // связь юзера и организации
                        $ulink = $this->modx->newObject("slOrgUsers");
                        $ulink->set("org_id", $org_id);
                        $ulink->set("user_id", $userdata['id']);
                        $ulink->save();
                    }
                }
                // Отправка данных на почту
                $chunk = "@FILE chunks/email_register.tpl";
                $subject = 'Регистрация в сервисе "MachineStore: ЗАКУПКИ"';
                $email = $properties['email'];
                $data = array(
                    "userdata" => array(
                        "username" => $properties['login'],
                        "password" => $properties['password']
                    )
                );
                $this->sl->tools->sendMail($chunk, $data, $email, $subject);
                // отправка данных в Bitrix24
                // 1. Проверяем не создана ли карточка в Бизнес процессе
                $card_id = 0;
                $organization_id = 0;
                $res = $this->sl->b24->checkRequizites($properties['org']['inn']);
                if($res["result"]){
                    $organization_id = $res["result"][0]["ENTITY_ID"];
                    if($organization_id){
                        $criteria = array(
                            "entityTypeId" => 1034,                 // Это наш бизнес процесс
                            "filter" => array(
                                "companyId" => $organization_id
                            )
                        );
                        $res = $this->sl->b24->checkCard($criteria);
                        if($res["total"] > 0){
                            $card_id = $res["result"]["items"][0]["id"];
                        }
                    }
                }
                if($card_id){
                    // 1. Двигаем по стадии
                    $res = $this->sl->b24->updateCard(1034, $card_id, array("stageId" => "DT1034_89:UC_2TBIBK"));
                }else{
                    // 2. Создаем заново объекты и связываем
                    $organization_data = array();
                    $name_data = array();
                    // Клининг параметров
                    if (!class_exists('Dadata')) {
                        require_once dirname(__FILE__) . '/dadata.class.php';
                    }

                    $token = $this->modx->getOption('shoplogistic_api_key_dadata');
                    $secret = $this->modx->getOption('shoplogistic_secret_key_dadata');
                    $dadata = new Dadata($token, $secret);
                    $dadata->init();
                    // клиним имя
                    $result = $dadata->clean("name", $properties['name']);
                    if($result){
                        $name_data = $result[0];
                    }
                    $companyData["TITLE"] = $properties['org']['name'];
                    // Если организация не найдена
                    if(!$organization_id){
                        $result = $dadata->getOrganization($properties['org']['inn']);
                        if($result["suggestions"]){
                            $organization_data = $result["suggestions"][0];
                        }
                        // Собираем данные
                        $companyData = array(
                            "CONTACT" => array(),
                            "COMPANY_TYPE" => "OTHER"
                        );
                        if($organization_data){
                            $companyData["TITLE"] = $organization_data["value"];
                        }else{
                            $companyData["TITLE"] = $properties['org']['name'];
                        }
                        if($name_data){
                            $legalData = array(
                                "NAME" => $name_data["name"],
                                "SECOND_NAME" => $name_data["patronymic"],
                                "LAST_NAME" => $name_data["surname"],
                                "phone" => $properties['telephone'],
                                "email" => $properties["email"],
                                "ASSIGNED_BY_ID" => 55
                            );
                        }else{
                            $legalData = array(
                                "NAME" => $properties['name'],
                                "phone" => $properties['telephone'],
                                "email" => $properties["email"],
                                "ASSIGNED_BY_ID" => 55
                            );
                        }
                        $lpr = $this->sl->b24->addContact($legalData);
                        $companyData["CONTACT"][] = $lpr;
                        $organization_id = $this->sl->b24->addCompany($companyData);
                    }
                    if($organization_data){
                        // Цепляем реквизиты
                        $requiziteData = array(
                            "NAME" => $companyData["TITLE"],
                            "RQ_INN" => $organization_data["data"]["inn"],
                            "RQ_KPP" => $organization_data["data"]["kpp"],
                            "RQ_EMAIL" => $properties["email"],
                            "RQ_PHONE" => $properties['telephone'],
                            'ENTITY_TYPE_ID' => 4,
                            "ENTITY_ID" => $organization_id,
                            "PRESET_ID" => 1,
                            'ACTIVE' => 'Y',
                        );
                        if(strlen(trim($organization_data["data"]["ogrn"])) == 13){
                            $requiziteData["RQ_OGRN"] = $organization_data["data"]["ogrn"];
                            $requiziteData["UF_CRM_1718187136"] = $properties["email"];
                            $requiziteData["UF_CRM_1718187151"] = $properties['telephone'];
                            $requiziteData["PRESET_ID"] = 1;
                        }
                        if(strlen(trim($organization_data["data"]["ogrn"])) == 15){
                            $requiziteData["RQ_OGRNIP"] = $organization_data["data"]["ogrn"];
                            $requiziteData["UF_CRM_1718187196"] = $properties["email"];
                            $requiziteData["UF_CRM_1718187208"] = $properties['telephone'];
                            $requiziteData["PRESET_ID"] = 3;
                        }
                        $req = $this->sl->b24->addRequizite($requiziteData);
                        if($properties['delivery_addresses'][0]["value"]){
                            $addressData = array(
                                'fields'=>array(
                                    'TYPE_ID' => 1,
                                    'ENTITY_TYPE_ID' => 8,
                                    'ENTITY_ID' => $req,
                                    'ADDRESS_1' => $properties['delivery_addresses'][0]["value"]
                                ),
                            );
                            $address_actual = $this->sl->b24->request('crm.address.add', $addressData);
                        }
                    }
                    $cardData = array(
                        "entityTypeId" => 1034,
                        "fields" => array(
                            "title" => $companyData["TITLE"],
                            "categoryId" => 89,
                            "stageId" => "DT1034_89:UC_2TBIBK",
                            "assignedById" => 55,
                            "companyId" => $organization_id,
                            "contactIds" => $companyData["CONTACT"],
                            "ufCrm29_1718046509" => $lpr
                        )
                    );
                    $card = $this->sl->b24->addCard($cardData);
                }
                return $this->sl->tools->success("Пользователь успешно зарегистрирован!", $userdata);
            }
        }
    }

    /**
     * Берем ID юзера
     *
     * @return mixed
     */
    public function getUserId(){
        // analytics, web
        // $_SESSION = [];
        $id = 0;
        if($this->modx->getLoginUserID('analytics')){
            $id = $this->modx->getLoginUserID('analytics');
        }else{
            $id = 0;
        }
        return $id;
    }

    /**
     * @param $action
     * @param $properties
     * @return mixed
     */
    public function handlePages($action, $properties = array()){
        switch ($action) {
            case 'get/count/bonuses':
                $response = $this->getCountBonuses($properties);
                break;
            case 'get/history/bonuses':
                $response = $this->getHistoryBonuses($properties);
                break;

        }
        return $response;
    }

    /**
     * Получаем количество бонусов у пользователя
     * @param $properties
     * @return mixed
     */
    public function getCountBonuses($properties){
        if($properties['user_id']){
            $query = $this->modx->newQuery("slBonusAccount");
            $query->where(array(
                "slBonusAccount.user_id:=" => $properties['user_id'],
            ));
            $query->select(array("slBonusAccount.*"));
            if($query->prepare() && $query->stmt->execute()){
                $bonuses = $query->stmt->fetch(PDO::FETCH_ASSOC);

                return $bonuses;
            }
        }
    }

    /**
     * Получаем историю бонусов
     * @param $properties
     * @return mixed
     */
    public function getHistoryBonuses($properties){
        if($properties['user_id']){

            $query = $this->modx->newQuery("slBonusAccount");
            $query->where(array(
                "slBonusAccount.user_id:=" => $properties['user_id'],
            ));
            $query->select(array("slBonusAccount.*"));
            if($query->prepare() && $query->stmt->execute()){
                $result['bonuses'] = $query->stmt->fetch(PDO::FETCH_ASSOC);
            }

            $q = $this->modx->newQuery("slBonusOperations");
            $q->leftJoin("msOrder", "msOrder", "msOrder.id = slBonusOperations.order_id");

            $q->where(array(
                "slBonusOperations.bonus_id:=" => $result['bonuses']['id'],
            ));

            $q->select(array(
                "slBonusOperations.*",
                "msOrder.id as order_id"
            ));
            $q->sortby("slBonusOperations.date", "DESC");
//            $q->prepare();
//            $this->modx->log(1, $q->toSQL());
            if($q->prepare() && $q->stmt->execute()){
                $history = $q->stmt->fetchAll(PDO::FETCH_ASSOC);

                foreach ($history as $key => $his){

                    $q_order = $this->modx->newQuery("slOrder");
                    $q_order->leftJoin("slStores", "slStores", "slStores.id = slOrder.store_id");

                    $q_order->where(array(
                        "slOrder.order_id:=" => $his['order_id'],
                    ));

                    $q_order->select(array(
                        "slStores.name_short",
                    ));

                    if($q_order->prepare() && $q_order->stmt->execute()) {
                        $store = $q_order->stmt->fetch(PDO::FETCH_ASSOC);

                        if($store['name_short']){
                            $history[$key]['store_name'] = $store['name_short'];
                        } else if($his['store_id']) {
                            $q_store = $this->modx->newQuery("slStores");

                            $q_store->where(array(
                                "slStores.id:=" => $his['store_id'],
                            ));

                            $q_store->select(array(
                                "slStores.name_short",
                            ));

                            if($q_store->prepare() && $q_store->stmt->execute()) {
                                $store = $q_store->stmt->fetch(PDO::FETCH_ASSOC);

                                $history[$key]['store_name'] = $store['name_short'];
                            }
                        }
                    }

                    $q_price = $this->modx->newQuery("msOrderProduct");

                    $q_price->where(array(
                        "msOrderProduct.order_id:=" => $his['order_id'],
                        "msOrderProduct.product_id:=" => $his['product_id'],
                    ));

                    $q_price->select(array(
                        "msOrderProduct.price",
                    ));

                    $q_price->prepare();
                    $this->modx->log(1, $q_price->toSQL());

                    if($q_price->prepare() && $q_price->stmt->execute()) {
                        $order = $q_price->stmt->fetch(PDO::FETCH_ASSOC);

                        $history[$key]['price'] = $order['price'];
                        $this->modx->log(1, $order['price']);
                    }
                }

                foreach ($history as $key => $his){
                    if($his['type'] == 'plus'){
                        foreach ($history as $k => $h){
                            if($h['type'] == 'minus' && $h['order_id'] == $his['order_id']){

                                $history[$key]['minus'] = $h;
                                $history[$key]['price'] = $h['price'];

                            }
                        }
                    } else{
                        $isPlus = false;
                        foreach ($history as $k => $h){
                            if($h['type'] == 'plus' && $h['order_id'] == $his['order_id']){
                                $isPlus = true;
                            }
                        }

                        if(!$isPlus){
                            $waiting = array(
                                'type' => 'waiting',
                                'date' => $his['date'],
                                'store_name' => $his['store_name'],
                                'minus' => $his,
                                'price' => $his['price']
                            );
                            $history[] = $waiting;
                        }
                    }

                }



                $new_history = [];
                $monthes = array(
                    "01" => 'Январь',
                    "02" => 'Февраль',
                    "03" => 'Март',
                    "04" => 'Апрель',
                    "05" => 'Май',
                    "06" => 'Июнь',
                    "07" => 'Июль',
                    "08" => 'Август',
                    "09" => 'Сентябрь',
                    "10" => 'Октябрь',
                    "11" => 'Ноябрь',
                    "12" => 'Декабрь'
                );

                $monthes_two = array(
                    "01" => 'Января',
                    "02" => 'Февраля',
                    "03" => 'Марта',
                    "04" => 'Апреля',
                    "05" => 'Мая',
                    "06" => 'Июня',
                    "07" => 'Июля',
                    "08" => 'Августа',
                    "09" => 'Сентября',
                    "10" => 'Октября',
                    "11" => 'Ноября',
                    "12" => 'Декабря'
                );

                //Собераем все года
                foreach ($history as $k => $his){
                    $timestamp = strtotime($his['date']);
                    if(!$new_history[date('Y', $timestamp)]){
                        $new_history[date('Y', $timestamp)] = array();
                    }
                    if(!$new_history[date('Y', $timestamp)][$monthes[date('m', $timestamp)]]){
                        $new_history[date('Y', $timestamp)][$monthes[date('m', $timestamp)]] = array();
                    }
                    if($his['type'] == 'plus' || $his['type'] == 'waiting'){
                        $history[$k]['date_format'] = date('d', $timestamp) . " " . $monthes_two[date('m', $timestamp)] . " " . date('Y', $timestamp);
                        $new_history[date('Y', $timestamp)][$monthes[date('m', $timestamp)]][] = $history[$k];
                    }


                }

                $result['history'] = $new_history;
            }



            return $result;
        }
    }
}