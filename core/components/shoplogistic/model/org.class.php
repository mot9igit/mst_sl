<?php

/**
 * Класс огранизации
 */

class slOrganization
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
     * @param $action
     * @param $properties
     * @return mixed
     */
    public function handlePages($action, $properties = array()){
        switch ($action) {
            case 'get/orgs':
                $response = $this->getOrgs($properties);
                break;
            case 'get/stores':
                $response = $this->getStoresOrg($properties);
                break;
            case 'get/org/store':
                $response = $this->getOrgStore($properties);
                break;
            case 'get/org/profile':
                $response = $this->getOrgProfile($properties);
                break;
            case 'set/org/profile':
                $response = $this->setOrgProfile($properties);
                break;
            case 'set/request/profile':
                $response = $this->requestChangeRequisite($properties);
                break;
        }
        return $response;
    }

    /**
     * Информация о складе
     *
     * @param $store_id
     * @return array|false
     */
    public function getOrgStore($properties){
        $store_id = $properties["store_id"];
        $user_id = $this->sl->userHandler->getUserId();
        if($this->sl->userHandler->checkUserPermission($user_id, $store_id, 'org_store_view')){
            $organization = $this->modx->getObject('slStores', $store_id);
            if($organization) {
                $out = $organization->toArray();
                // чекаем роль для меню
                if($out["store"]){
                    $out["type"] = 1;
                }
                if($out["warehouse"]){
                    $out["type"] = 2;
                }
                if($out["vendor"]){
                    $out["type"] = 3;
                }
                if($out['image']){
                    $out["images"] = $this->sl->tools->prepareImage($out['image']);
                    $out["image"] = $out["images"]["image"];
                }
                // время работы на неделе
                $query = $this->modx->newQuery("slStoresWeekWork");
                $query->where(array(
                    "store_id" => $store_id,
                    "week_day:>" => 0
                ));
                $query->select(array("slStoresWeekWork.*"));
                $query->sortby("slStoresWeekWork.week_day", "ASC");
                $query->prepare();
                $this->modx->log(1,  $query->toSQL());
                if($query->prepare() && $query->stmt->execute()){
                    $data = $query->stmt->fetchAll(PDO::FETCH_ASSOC);
                    foreach($data as $key => $item){
                        $timestamp_from = strtotime($item["date_from"]);
                        $timestamp_to = strtotime($item["date_to"]);
                        $data[$key]["timestamp_from"] = $timestamp_from;
                        $data[$key]["timestamp_to"] = $timestamp_to;
                        $date = new DateTime();
                        $date->setTimestamp($timestamp_from);
                        if($properties['timezone']){
                            $date->setTimezone(new DateTimeZone($properties['timezone']));
                        }
                        $data[$key]["time_from"] = $date->format('H:i');
                        $date->setTimestamp($timestamp_to);
                        if($properties['timezone']){
                            $date->setTimezone(new DateTimeZone($properties['timezone']));
                        }
                        $data[$key]["time_to"] = $date->format('H:i');
                    }
                    $out["worktime"] = $data;
                }
                // время работы на конкретные дни
                $out["workdays"] = array(
                    array(
                        "dot" => "red",
                        "type" => "weekend",
                        "dates" => array()
                    ),
                    array(
                        "dot" => "blue",
                        "type" => "shortdays",
                        "dates" => array()
                    )
                );
                $query = $this->modx->newQuery("slStoresWeekWork");
                $query->where(array(
                    "store_id" => $store_id,
                    "week_day:=" => 0
                ));
                $query->select(array("slStoresWeekWork.*"));
                $query->sortby("slStoresWeekWork.week_day", "ASC");
                if($query->prepare() && $query->stmt->execute()){
                    $data = $query->stmt->fetchAll(PDO::FETCH_ASSOC);
                    foreach($data as $key => $item){
                        $timestamp_from = strtotime($item["date_from"]);
                        $timestamp_to = strtotime($item["date_to"]);
                        $timestamp = strtotime($item["date"]);
                        $data[$key]["timestamp_from"] = $timestamp_from;
                        $data[$key]["timestamp_to"] = $timestamp_to;
                        $date = new DateTime();
                        $date->setTimestamp($timestamp_from);
                        if($properties['timezone']){
                            $date->setTimezone(new DateTimeZone($properties['timezone']));
                        }
                        $data[$key]["time_from"] = $date->format('H:i');
                        $date->setTimestamp($timestamp_to);
                        if($properties['timezone']){
                            $date->setTimezone(new DateTimeZone($properties['timezone']));
                        }
                        $data[$key]["time_to"] = $date->format('H:i');
                        $date = new DateTime();
                        $date->setTimestamp($timestamp);
                        if($properties['timezone']){
                            $date->setTimezone(new DateTimeZone($properties['timezone']));
                        }
                        $data[$key]["date"] = $date->format('Y-m-d');
                        if($item['weekend']){
                            $out["workdays"][] = array(
                                "dot" => "red",
                                "dates" => array($data[$key]["date"]),
                                "popover" => array(
                                    "label" => "Выходной"
                                )
                            );
                        }else{
                            $out["workdays"][] = array(
                                "dot" => "blue",
                                "dates" => array($data[$key]["date"]),
                                "popover" => array(
                                    "label" => $data[$key]["time_from"].' - '.$data[$key]["time_to"]
                                )
                            );
                        }
                    }
                    $out["workdays_source"] = $data;
                }
                // берем товары организации
                $query = $this->modx->newQuery("slStoresRemains");
                $query->where(array("slStoresRemains.store_id" => $store_id));
                $query->select(array("slStoresRemains.*"));
                $out["products"]["count"] = $this->modx->getCount("slStoresRemains", $query);
                $query->where(array("slStoresRemains.product_id:>" => 0));
                $out["products"]["copo_count"] = $this->modx->getCount("slStoresRemains", $query);
                if($out["products"]["count"]){
                    $out["products"]["no_copo_percent"] = (($out["products"]["count"] - $out["products"]["copo_count"]) * 100) / $out["products"]["count"];
                    $out["products"]["no_copo_percent"] = round($out["products"]["no_copo_percent"], 2);
                    $out["products"]["copo_percent"] = 100 - $out["products"]["no_copo_percent"];
                }
                $summ = 0;
                $query = $this->modx->newQuery("slStoresRemains");
                $query->where(array("slStoresRemains.store_id" => $store_id));
                $query->select(array("SUM(slStoresRemains.price * slStoresRemains.remains) as price, SUM(slStoresRemains.remains) as count"));
                if($query->prepare() && $query->stmt->execute()){
                    $data = $query->stmt->fetch(PDO::FETCH_ASSOC);
                    $summ = $data["price"];
                    $out["products"]["summ"] = number_format($data["price"], 2, ',', ' ');
                    $out["products"]["count_all"] = number_format($data["count"], 0, '', ' ');
                }

                $query = $this->modx->newQuery("slStoresRemains");
                $query->where(array("slStoresRemains.store_id" => $store_id));
                $query->select(array("SUM(slStoresRemains.price * slStoresRemains.remains) as price, SUM(slStoresRemains.remains) as count"));
                $query->where(array("slStoresRemains.product_id:>" => 0));
                if($query->prepare() && $query->stmt->execute()){
                    $data = $query->stmt->fetch(PDO::FETCH_ASSOC);
                    if($summ > 0 && $data["price"]){
                        $out["products"]["summ_copo"] = number_format($data["price"], 2, ',', ' ');
                        $out["products"]["count_copo"] = number_format($data["count"], 0, '', ' ');
                        $perc = ($data["price"] / $summ) * 100;
                        $out["products"]["copo_money_percent"] = round($perc, 2);
                        $out["products"]["no_copo_money_percent"] = 100 - $out["products"]["copo_money_percent"];
                    }else{
                        $out["products"]["summ_copo"] = 0;
                        $out["products"]["count_copo"] = 0;
                        $out["products"]["copo_money_percent"] = 0;
                        $out["products"]["no_copo_money_percent"] = 0;
                    }
                }
                $out["settings"] = $this->sl->store->getStoreSettings($store_id);
            }

            return $out;
        }else{
            return false;
        }
    }

    /**
     * Берем информацию по организации
     *
     * @param $id
     * @return false|array
     */
    public function getOrganization($id){
        $user_id = $this->sl->userHandler->getUserId();
        if($this->sl->userHandler->checkUserPermission($user_id, $id, 'org_view')){
            $organization = $this->modx->getObject('slOrg', $id);
            if($organization) {
                $out = $organization->toArray();
                // чекаем роль для меню
                if($out["store"]){
                    $out["type"] = 1;
                }
                if($out["warehouse"]){
                    $out["type"] = 2;
                }
                if($out["vendor"]){
                    $out["type"] = 3;
                }
                // заказы за 7 дней
                $newDate = new DateTime('7 days ago');
                $date = $newDate->format('Y-m-d H:i:s');
                $query = $this->modx->newQuery("slOrder");
                $query->select(array("SUM(slOrder.cart_cost) as summ, COUNT(*) as count"));
                $query->where(array(
                    "org_id:=" => $id
                    ),
                );
                $query->where(array(
                    "createdon:>=" => $date
                ));
                if($query->prepare() && $query->stmt->execute()){
                    $data = $query->stmt->fetch(PDO::FETCH_ASSOC);
                    $out["orders"]["summ"] = number_format($data["summ"], 2, ',', ' ');
                    $out["orders"]["count"] = number_format($data["count"], 0, '', ' ');
                }
                if($out['image']){
                    $out["images"] = $this->sl->tools->prepareImage($out['image']);
                    $out["image"] = $out["images"]["image"];
                }
                // берем товары организации
                $ids = array();
                $stores = $this->getStoresOrg(array("id" => $id));
                foreach($stores["items"] as $store){
                    $ids[] = $store["id"];
                }
                $query = $this->modx->newQuery("slStoresRemains");
                $query->where(array("slStoresRemains.store_id:IN" => $ids));
                $query->select(array("slStoresRemains.*"));
                $query->prepare();
                $this->modx->log(1, $query->toSQL());
                $out["products"]["count"] = $this->modx->getCount("slStoresRemains", $query);
                $query->where(array("slStoresRemains.product_id:>" => 0));
                $out["products"]["copo_count"] = $this->modx->getCount("slStoresRemains", $query);
                if($out["products"]["count"]){
                    $out["products"]["no_copo_percent"] = (($out["products"]["count"] - $out["products"]["copo_count"]) * 100) / $out["products"]["count"];
                    $out["products"]["no_copo_percent"] = round($out["products"]["no_copo_percent"], 2);
                    $out["products"]["copo_percent"] = 100 - $out["products"]["no_copo_percent"];
                }
                $summ = 0;

                $query = $this->modx->newQuery("slStoresRemains");
                $query->where(array("slStoresRemains.store_id:IN" => $ids));
                $query->select(array("SUM(slStoresRemains.price * slStoresRemains.remains) as price, SUM(slStoresRemains.remains) as count"));
                if($query->prepare() && $query->stmt->execute()){
                    $data = $query->stmt->fetch(PDO::FETCH_ASSOC);
                    $summ = $data["price"];
                    $out["products"]["summ"] = number_format($data["price"], 2, ',', ' ');
                    $out["products"]["count_all"] = number_format($data["count"], 0, '', ' ');
                }

                $query = $this->modx->newQuery("slStoresRemains");
                $query->where(array("slStoresRemains.store_id:IN" => $ids));
                $query->select(array("SUM(slStoresRemains.price * slStoresRemains.remains) as price, SUM(slStoresRemains.remains) as count"));
                $query->where(array("slStoresRemains.product_id:>" => 0));
                if($query->prepare() && $query->stmt->execute()){
                    $data = $query->stmt->fetch(PDO::FETCH_ASSOC);
                    if($summ > 0 && $data["price"]){
                        $out["products"]["summ_copo"] = number_format($data["price"], 2, ',', ' ');
                        $out["products"]["count_copo"] = number_format($data["count"], 0, '', ' ');
                        $perc = ($data["price"] / $summ) * 100;
                        $out["products"]["copo_money_percent"] = round($perc, 2);
                        $out["products"]["no_copo_money_percent"] = 100 - $out["products"]["copo_money_percent"];
                    }else{
                        $out["products"]["summ_copo"] = 0;
                        $out["products"]["count_copo"] = 0;
                        $out["products"]["copo_money_percent"] = 0;
                        $out["products"]["no_copo_money_percent"] = 0;
                    }
                }
                // топ товаров по прогнозу упущенной выручки
                $today = date_create();
                $month_ago = date_create("-1 MONTH");
                date_time_set($month_ago, 00, 00);

                $date_from = date_format($month_ago, 'Y-m-d H:i:s');
                $date_to = date_format($today, 'Y-m-d H:i:s');

                $query = $this->modx->newQuery("slStoresRemains");
                $query->leftJoin("msProductData", "msProductData", "msProductData.id = slStoresRemains.product_id");
                $query->leftJoin("modResource", "modResource", "modResource.id = slStoresRemains.product_id");
                $query->where(array(
                    "slStoresRemains.store_id:IN" => $ids,
                    "slStoresRemains.purchase_speed:>" => 0,
                    "slStoresRemains.remains:=" => 0
                ));
                $query->select(array("slStoresRemains.*,msProductData.image,msProductData.vendor_article,modResource.pagetitle"));
                $query->limit(5);
                $query->sortby('no_money', 'DESC');
                if($query->prepare() && $query->stmt->execute()){
                    $out["no_money"]['top'] = $query->stmt->fetchAll(PDO::FETCH_ASSOC);
                    foreach($out["no_money"]['top'] as $key => $item){
                        $out["no_money"]['top'][$key]["no_money"] = number_format($item["no_money"], 2, ',', ' ');
                    }
                }
                // топ товаров по прогнозу остатков
                $query = $this->modx->newQuery("slStoresRemains");
                $query->leftJoin("msProductData", "msProductData", "msProductData.id = slStoresRemains.product_id");
                $query->leftJoin("modResource", "modResource", "modResource.id = slStoresRemains.product_id");
                $query->where(array(
                    "slStoresRemains.store_id:=" => $ids,
                    "slStoresRemains.purchase_speed:>" => 0,
                    "slStoresRemains.remains:>" => 0
                ));
                $query->select(array("slStoresRemains.*,msProductData.image,msProductData.vendor_article,modResource.pagetitle,FLOOR((slStoresRemains.remains - slStoresRemains.purchase_speed)) as forecast,FLOOR((slStoresRemains.remains - (7 * slStoresRemains.purchase_speed))) as forecast_7, CONCAT(FLOOR((slStoresRemains.remains - slStoresRemains.purchase_speed)), ' / ', FLOOR((slStoresRemains.remains - (7 * slStoresRemains.purchase_speed)))) as forecast_all"));
                $query->limit(5);
                $query->sortby('forecast', 'ASC');
                // $query->prepare();
                // $this->modx->log(1, $query->toSQL());
                if($query->prepare() && $query->stmt->execute()){
                    $out["forecast"]['top'] = $query->stmt->fetchAll(PDO::FETCH_ASSOC);
                    foreach($out["forecast"]['top'] as $key => $item){
                        // $out["forecast"]['top'][$key]["no_money"] = number_format($item["no_money"], 2, ',', ' ');
                    }
                }
            }
            return $out;
        }else{
            return false;
        }
    }

    /**
     * Берем подключенные к магазину склады
     *
     * @param $id
     * @return array
     */
    public function getDilers($id, $properties){
        $results = array();
        $stores = $this->getStoresOrg(array("id" => $id));
        foreach($stores["items"] as $store){
            $ids[] = $store["id"];
        }
        $query = $this->modx->newQuery("slWarehouseStores");
        $query->leftJoin("slOrg", "slOrg", "slOrg.id = slWarehouseStores.org_id");
        $query->leftJoin("slStores", "slStores", "slStores.id = slWarehouseStores.warehouse_id");
        $query->select(array("slOrg.*,slStores.id as warehouse_id,slStores.name_short as warehouse,slWarehouseStores.base_sale as base_sale,slWarehouseStores.id as obj_id"));
        $query->where(array(
            "slWarehouseStores.warehouse_id:IN" => $ids,
            "AND:slOrg.active:=" => 1
        ));
        $results['total'] = $this->modx->getCount('slWarehouseStores', $query);
        if($properties['page'] && $properties['perpage']){
            $limit = $properties['perpage'];
            $offset = ($properties['page'] - 1) * $properties['perpage'];
            $query->limit($limit, $offset);
        }
        if($properties['sort']){
            $keys = array_keys($properties['sort']);
            $query->sortby($keys[0], $properties['sort'][$keys[0]]['dir']);
        }
        if($query->prepare() && $query->stmt->execute()) {
            $results['items'] = $query->stmt->fetchAll(PDO::FETCH_ASSOC);
            return $results;
        }
    }

    /**
     * Берем подключенные к магазину склады
     *
     * @param $id
     * @return array
     */
    public function getWarehouses($id, $visible = 0){
        $warehouses = array();
        $query = $this->modx->newQuery("slWarehouseStores");
        $query->leftJoin("slStores", "slStores", "slStores.id = slWarehouseStores.warehouse_id");
        $query->select(array("slStores.*"));
        $query->where(array(
            "slWarehouseStores.org_id:=" => $id,
            "AND:slStores.active:=" => 1
        ));
        if($visible){
            $query->where(array(
                "slWarehouseStores.visible:=" => 1
            ));
        }
        if($query->prepare() && $query->stmt->execute()) {
            $warehouses = $query->stmt->fetchAll(PDO::FETCH_ASSOC);
            return $warehouses;
        }
    }

    /**
     * Получаем все организации пользователя
     * @return array|null
     */
    public function getOrgs(){
        $user_id = $this->sl->userHandler->getUserId();
        if($user_id){
            $query = $this->modx->newQuery("slOrgUsers");
            $query->leftJoin("slOrg", "slOrg", "slOrg.id = slOrgUsers.org_id");
            $query->where(array(
                "slOrgUsers.user_id:=" => $user_id,
            ));
            $query->select(array(
                "slOrg.*"
            ));
            if($query->prepare() && $query->stmt->execute()) {
                $urlMain = $this->modx->getOption("site_url");
                $orgs = $query->stmt->fetchAll(PDO::FETCH_ASSOC);
                foreach ($orgs as $key => $org){
                    // заказы за 7 дней
                    $newDate = new DateTime('7 days ago');
                    $date = $newDate->format('Y-m-d H:i:s');
                    $query = $this->modx->newQuery("slOrder");
                    $query->select(array("SUM(slOrder.cart_cost) as summ, COUNT(*) as count"));
                    $query->where(array(
                        "org_id:=" => $org['id']
                    ),
                    );
                    $query->where(array(
                        "createdon:>=" => $date
                    ));
                    if($query->prepare() && $query->stmt->execute()){
                        $data = $query->stmt->fetch(PDO::FETCH_ASSOC);
                        $orgs[$key]["orders"]["summ"] = number_format($data["summ"], 2, ',', ' ');
                        $orgs[$key]["orders"]["count"] = number_format($data["count"], 0, '', ' ');
                    }
                    if($org["image"]){
                        $out["images"] = $this->sl->tools->prepareImage($org['image']);
                        $orgs[$key]['image'] = $out["images"]["image"];
                    }
                }
                return $orgs;
            }
        }
    }

    /**
     *
     * Получаем все склады пользователя
     * @return
     */
    public function getStoresOrg($properties){
        if($properties['id']){
            $query = $this->modx->newQuery("slOrgStores");
            $query->leftJoin("slStores", "slStores", "slStores.id = slOrgStores.store_id");
            $query->where(array(
                "slOrgStores.org_id:=" => $properties['id'],
            ));
            $query->select(array(
                "slStores.*",
            ));
            if($query->prepare() && $query->stmt->execute()) {
                $result['items'] = $query->stmt->fetchAll(PDO::FETCH_ASSOC);
                foreach ($result['items'] as $key => $store){
                    if($store["image"]) {
                        $out["images"] = $this->sl->tools->prepareImage($store['image']);
                        $result['items'][$key]['image'] = $out["images"]["image"];
                    }
                }
                $result['total'] = count($result['items']);
                return $result;
            }
        }
    }

    /**
     *
     * Получаем настройки огранизации
     * @return
     */
    public function getOrgProfile($properties){
        if($properties['id']){
            $urlMain = $this->modx->getOption("site_url");
            $query = $this->modx->newQuery("slOrg");
            $query->where(array(
                "`slOrg`.id:=" => $properties['id'],
            ));
            $query->select(array(
                "`slOrg`.*"
            ));
            if($query->prepare() && $query->stmt->execute()){
                $org = $query->stmt->fetch(PDO::FETCH_ASSOC);

                if($org['image']){
                    $org['image'] = $urlMain . "assets/content/" .  $org['image'];
                }

                $q = $this->modx->newQuery("slOrgRequisites");
                $q->where(array(
                    "`slOrgRequisites`.`org_id`:=" => $org['id'],
                ));
                $q->select(array(
                    "`slOrgRequisites`.*"
                ));

                if($q->prepare() && $q->stmt->execute()){
                    $orgRequisites = $q->stmt->fetchAll(PDO::FETCH_ASSOC);

                    $org['requisites'] = $orgRequisites;

                    foreach ($org['requisites'] as $key => $requisite){
                        $queryBank = $this->modx->newQuery("slOrgBankRequisites");
                        $queryBank->where(array(
                            "`slOrgBankRequisites`.`org_requisite_id`:=" => $requisite['id'],
                        ));
                        $queryBank->select(array(
                            "`slOrgBankRequisites`.*"
                        ));
                        if($queryBank->prepare() && $queryBank->stmt->execute()) {
                            $bankRequisites = $queryBank->stmt->fetchAll(PDO::FETCH_ASSOC);
                            $org['requisites'][$key]['banks'] = $bankRequisites;
                        }
                    }
                }

                return $org;
            }
        }
    }

    /**
     *
     * Настройки огранизации
     * @return
     */
    public function setOrgProfile($properties){
        if($properties['id'] && $properties['data']){
            $org = $this->modx->getObject('slOrg', $properties['id']);

            if($properties['data']['contact']){
                $org->set('contact', $properties['data']['contact']);
            }

            if($properties['data']['email']){
                $org->set('email', $properties['data']['email']);
            }

            if($properties['data']['phone']){
                $org->set('phone', $properties['data']['phone']);
            }

            if($properties['data']['upload_image']){
                if ($properties['data']['image']) {
                    $avatar = $this->modx->getOption('base_path') . "assets/content/avatars/" . $properties['data']['image']['name'];

                    if (rename($this->modx->getOption('base_path') . $properties['data']['image']['original'], $avatar)) {
                        $org->set("image", "avatars/" . $properties['data']['image']['name']);
                    }
                }
            }

            $org->save();
            return array(
                "status" => true,
                "message" => "Данные успешно сохранены!"
            );
        }
    }

    /**
     *
     * Отправка запроса на изменение/добавление реквизитов или банковских реквизитов
     * @return
     */
    public function requestChangeRequisite ($properties) {
        if($properties['id'] && $properties['data']){
            $pdo = $this->modx->getService('pdoFetch');
            $chunk = "@FILE chunks/send_email_request_org.tpl";
            //$this->modx->log(1, "{$chunk}");
            if($pdo) {
                $data = $properties['data'];
                $data['date'] = date('d.m.Y H:i');
                $message = $pdo->getChunk($chunk, $data);
                $emailsender = $this->modx->getOption("emailsender");

                $this->modx->getService('mail', 'mail.modPHPMailer');
                $this->modx->mail->set(modMail::MAIL_BODY, $message);
                $this->modx->mail->set(modMail::MAIL_FROM, $emailsender);
                $this->modx->mail->set(modMail::MAIL_FROM_NAME,'MST Аналитика');
                $this->modx->mail->set(modMail::MAIL_SUBJECT,'Новый запрос редактирования/добавляния реквизитов огранизации');
                $this->modx->mail->address('to','info@dart.agency');
                $this->modx->mail->address('to','artpetropavlovskij@gmail.com');
                $this->modx->mail->address('to','info@mst.tools');
                $this->modx->mail->address('reply-to', $emailsender);
                $this->modx->mail->setHTML(true);
                if (!$this->modx->mail->send()) {
                    $this->modx->log(1, 'An error occurred while trying to send the email: '.$this->modx->mail->mailer->ErrorInfo);
                }
                $this->modx->mail->reset();


                if($properties['data']['id']){
                    $org = $this->modx->getObject('slOrgRequisites', $properties['data']['id']);
                    if($org){
                        $org->set("send_request", true);
                        $org->save();
                    }
                }
            }
            return array(
                "status" => true,
                "message" => "Запрос успешно отправлен!"
            );
        }
    }
}