<?php
class notificationHandler
{
    public $modx;
    public $sl;
    public $config;

    public function __construct(shopLogistic &$sl, modX &$modx)
    {
        $this->sl =& $sl;
        $this->modx =& $modx;
        $this->modx->lexicon->load('shoplogistic:default');
        // link ms2
        if (is_dir($this->modx->getOption('core_path') . 'components/minishop2/model/minishop2/')) {
            $ctx = 'web';
            $this->ms2 = $this->modx->getService('miniShop2');
            if ($this->ms2 instanceof miniShop2) {
                $this->ms2->initialize($ctx);
                return true;
            }
        }
    }

    /**
     * @param $action
     * @param $properties
     * @return mixed
     */
    public function handlePages($action, $properties = array()){
        switch ($action) {
            case 'set':
                $response = $this->setNotification($properties);
                break;
            case 'get':
                $response = $this->getNotification($properties);
                break;
            case 'read':
                $response = $this->readNotification($properties);
                break;
            case 'delete':
                $response = $this->deleteNotification($properties);
                break;
            case 'get/regions/stores':
                $response = $this->getCityAndStores($properties);
                break;
        }
        return $response;
    }

    /**
     * @param $properties
     * $properties['data'] = array(
     *      "org_id" => 1,
     *      "namespace" => 1,
     *      "link_id" => "43",
     *      "store_id" => "1"
     * );
     *
     *
     * org_id - id организации, которой прислать уведомление
     * link_id - Ссылка (id) на какой-то ресурс
     * namespace - тип уведомления, у нас это:
     *      1) Изменение статуса заказа в маркетплейсе
     *      2) Поступил новый оптовый заказ
     *      3) Ваша компания отключена
     *      4) Ваша компания подключена
     *      5) Появился новый поставщик
     *      6) TODO
     *      7) Вас добавили в поставщики
     *      8) Вас удалили из поставщиков
     *      9) Ваш склад отключен
     *      10) Ваш склад подключен
     *
     * @return mixed
     */
    public function setNotification($properties){
        if($properties['data']){

            $notification = $this->modx->newObject('slNotification');
            if($notification) {
                $notification->set("org_id", $properties['data']['org_id']);
                $notification->set("link_id", $properties['data']['link_id']);
                $notification->set("namespace", $properties['data']['namespace']);
                if($properties['data']['store_id']){
                    $notification->set("store_id", $properties['data']['store_id']);
                }

                $notification->set("date", date('Y-m-d H:i:s'));

                $notification->save();
            }
        }
    }

    /**
     * @param $properties
     * Получить все уведомления пользователя
     * @return mixed
     */
    public function getNotification($properties){
        if($properties['id']){
            $pdo = $this->modx->getParser()->pdoTools;
            $result = array();
            $urlMain = $this->modx->getOption("site_url");

            $query = $this->modx->newQuery("slNotification");
            $query->where(array(
                "slNotification.org_id:=" => $properties['id'],
            ));

            if($properties['data_start']){
                $query->where(array(
                    "slNotification.date:>=" => date('Y-m-d H:i:s', strtotime($properties['data_start'])),
                    "slNotification.read:=" => '0',
                ));
            }

            $query->select(array("slNotification.*"));

            $result['total'] = $this->modx->getCount("slNotification", $query);

            // Устанавливаем лимит 1/10 от общего количества записей
            // со сдвигом 1/20 (offset)
            if($properties['page'] && $properties['perpage']){
                $limit = $properties['perpage'];
                $offset = ($properties['page'] - 1) * $properties['perpage'];
                $query->limit($limit, $offset);
            }

            // И сортируем по дате в обратном порядке
            $query->sortby('date', "DESC");

//            $query->prepare();
//            $this->modx->log(1, $query->toSQL());

            if($query->prepare() && $query->stmt->execute()) {
                $items = $query->stmt->fetchAll(PDO::FETCH_ASSOC);

                foreach ($items as $key => $item){

                    $elem = $item;


                    if($item['namespace'] == '2'){
                        $q = $this->modx->newQuery("slOrg");
                        $q->where(array(
                            "slOrg.id:=" => $item['store_id'],
                        ));
                        $q->select(array("slOrg.*"));
                        if($q->prepare() && $q->stmt->execute()) {
                            $org = $q->stmt->fetch(PDO::FETCH_ASSOC);
                            if($org['image']){
                                $org['image'] = $this->sl->tools->prepareImage($org['image'])['image'];
                            } else {
                                $org['image'] = $urlMain . '/assets/files/img/nopic.png';
                            }
                            $elem['org'] = $org;
                        }

                        $quer = $this->modx->newQuery("slOrderOpt");
                        $quer->where(array(
                            "slOrderOpt.id:=" => $item['link_id'],
                        ));
                        $quer->select(array("slOrderOpt.*"));
                        if($quer->prepare() && $quer->stmt->execute()) {
                            $order = $quer->stmt->fetch(PDO::FETCH_ASSOC);
                            $order['cost'] = number_format($order['cost'], 0, ',', ' ');
                            $elem['order'] = $order;
                        }
                    } else if($item['namespace'] == '7' || $item['namespace'] == '8'){
                        $q = $this->modx->newQuery("slOrg");
                        $q->where(array(
                            "slOrg.id:=" => $item['store_id'],
                        ));
                        $q->select(array("slOrg.*"));
                        if($q->prepare() && $q->stmt->execute()) {
                            $org = $q->stmt->fetch(PDO::FETCH_ASSOC);
                            if($org['image']){
                                $org['image'] = $this->sl->tools->prepareImage($org['image'])['image'];
                            } else {
                                $org['image'] = $urlMain . '/assets/files/img/nopic.png';
                            }
                            $elem['org'] = $org;
                        }
                    } else if($item['namespace'] == '9' || $item['namespace'] == '10'){
                        $q = $this->modx->newQuery("slStores");
                        $q->where(array(
                            "slStores.id:=" => $item['store_id'],
                        ));
                        $q->select(array("slStores.*"));
                        if($q->prepare() && $q->stmt->execute()) {
                            $store = $q->stmt->fetch(PDO::FETCH_ASSOC);
                            $elem['store'] = $store;
                        }
                    }

                    $items[$key]['chunk'] = $pdo->getChunk('sl.notification', array("item" => $elem));
                }

                $result['items'] = $items;
            }


            $query->where(array(
                "slNotification.read:=" => '0',
            ));

            $result['no_read'] = $this->modx->getCount("slActions", $query);

            return $result;
        }
    }

    /**
     * @param $properties
     * Прочитать все уведомления или несколько уведомлений (Массив)
     * @return mixed
     */

    public function readNotification($properties){
        if($properties['id']){
            if($properties['ids'] == 'all'){
                $sql = "UPDATE {$this->modx->getTableName('slNotification')} SET `read` = 1 WHERE `org_id` = {$properties['id']}";
                $query = $this->modx->query($sql);

//                $this->modx->log(1, "KENOST slNotification");
//                $this->modx->log(1, "{$sql}");

                return $this->getNotification($properties);
            } else {
                $ids = "";
                foreach ($properties['ids'] as $id){
                    if($ids == ""){
                        $ids = $id;
                    } else {
                        $ids = $ids . ', ' . $id;
                    }
                }
                $sql = "UPDATE {$this->modx->getTableName('slNotification')} SET `read` = 1 WHERE `id` IN ({$ids})";
                $query = $this->modx->query($sql);

                return $this->getNotification($properties);
            }
        }
    }

    /**
     * @param $properties
     * Удалить уведомления пользователя
     * @return mixed
     */

    public function deleteNotification($properties){
        if($properties['id']){
            if($properties['notification_id']){
                $notification = $this->modx->getObject("slNotification", array('id' => $properties['notification_id'], 'org_id' => $properties['id']));
                if($notification){
                    $notification->remove();

                    $result = array(
                        "status" => true
                    );

                    return $this->getNotification($properties);
                }
            } else {
                $sql = "DELETE FROM {$this->modx->getTableName('slNotification')} WHERE `org_id` = {$properties['id']}";
                $query = $this->modx->query($sql);

                return $this->getNotification($properties);
            }
        }
    }


    public function getCityAndStores($properties)
    {
        $q = $this->modx->newQuery("dartLocationRegion");
        $q->select(array(
            "dartLocationRegion.id",
            "dartLocationRegion.name",
        ));
        if ($q->prepare() && $q->stmt->execute()) {
            $regions = $q->stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($regions as $kr => $region){
                $regions[$kr]['id'] = "r_".$region['id'];
            }
        }

        $properties['perpage'] = 999;
        $getDillers = $this->sl->orgHandler->getDilers($properties['id'], $properties);
        $orgs = array();
        foreach ($getDillers['items'] as $k => $diller){
            $orgs[] = array(
                "id" => "o_" . $diller['id'],
                "name" => $diller['warehouse']
            );
        }

        $quer = $this->modx->newQuery("dartLocationCity");
        $quer->select(array(
            "dartLocationCity.id",
            "dartLocationCity.city as name",
        ));
        if ($quer->prepare() && $quer->stmt->execute()) {
            $city = $quer->stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($city as $kc => $cit){
                $city[$kc]['id'] = "c_".$cit['id'];
            }

            return array_merge($regions, $city, $orgs);
        }
    }

    public function getEmailManagers($id_to, $id_from, $type){
        //id_to - id организации, которой нужно отправить уведомления
        //id_from - id организации, которая вызвала это уведомление
        //type - тип уведомления

        if (!$id_to || !$id_from || !$type) {
            return null;
        }

        // Получаем локации
        $getLocations = $this->sl->orgHandler->getRegionsAndCity($id_from);

        $query = $this->modx->newQuery("slNotificationManagers");

        // Условие на org_id
        $query->where(array(
            "slNotificationManagers.org_id" => $id_to,
        ));

        // Условие на тип
        $query->where("FIND_IN_SET('$type', slNotificationManagers.type)");

        // Основное условие: global = 1 или совпадение по region или city или org содержит $id_from
        $additionalConditions = [
            "slNotificationManagers.global = 1",
            "FIND_IN_SET('$id_from', slNotificationManagers.org)"
        ];

        // Условия для region
        if (!empty($getLocations['region'])) {
            $regionConditions = [];
            foreach ($getLocations['region'] as $region) {
                $regionConditions[] = "FIND_IN_SET('$region', slNotificationManagers.region)";
            }
            $additionalConditions[] = '(' . implode(' OR ', $regionConditions) . ')';
        }

        // Условия для city
        if (!empty($getLocations['city'])) {
            $cityConditions = [];
            foreach ($getLocations['city'] as $city) {
                $cityConditions[] = "FIND_IN_SET('$city', slNotificationManagers.city)";
            }
            $additionalConditions[] = '(' . implode(' OR ', $cityConditions) . ')';
        }

        // Объединяем все условия для OR
        $query->where('(' . implode(' OR ', $additionalConditions) . ')');

        $query->select(array("slNotificationManagers.*"));

        $query->prepare();
        $this->modx->log(1, $query->toSQL());

        if($query->prepare() && $query->stmt->execute()) {
            $managers = $query->stmt->fetchAll(PDO::FETCH_ASSOC);

            $emails = array();
            foreach ($managers as $key => $manager){
                $emails[] = $manager['email'];
            }
            return $emails;
        }
    }
}