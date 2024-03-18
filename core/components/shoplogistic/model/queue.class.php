<?php

/**
 *  Обработчик действий с программами
 *
 */

class queueHandler
{
    public $modx;
    public $sl;
    public $config;

    public function __construct(shopLogistic &$sl, modX &$modx)
    {
        $this->sl =& $sl;
        $this->modx =& $modx;
        $this->modx->lexicon->load('shoplogistic:default');
        $this->log = true;
    }

    public function getQueue(){
        // берем все элементы очереди, которые нужно выполнить
        $query = $this->modx->newQuery("slQueue");
        $query->where(array("processed:=" => 0, "AND:processing:=" => 0));
        $query->select(array("slQueue.*"));
        $query->sortby('createdon','ASC');
        if ($query->prepare() && $query->stmt->execute()) {
            $actions = $query->stmt->fetchAll(PDO::FETCH_ASSOC);
            return $actions;
        }
        return false;
    }

    public function addTask($action, $properties){
        if($action){
            $queue = $this->modx->newObject("slQueue");
            $queue->set("action", $action);
            $queue->set("createdon", time());
            $queue->set("properties", $properties);
            if($queue->save()){
                return true;
            }else{
                return false;
            }
        }
    }

    public function clearQueue(){
        // очищаем задачи, которые висят более 30 мин
        $diff = time() - 1800;
        $query = $this->modx->newQuery("slQueue");
        $query->where(array("processing:=" => 1, "AND:startedon:<" => date('Y-m-d H:i:s', $diff)));
        $query->select(array("slQueue.*"));
        $query->sortby('createdon','ASC');
        if ($query->prepare() && $query->stmt->execute()) {
            $actions = $query->stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach($actions as $action){
                $queue = $this->modx->getObject("slQueue", $action["id"]);
                $queue->set("startedon", "");
                $queue->set("processing", 0);
                $queue->save();
                if($this->log){
                    $this->sl->darttelegram->sendMessage("ERROR", "Подвисла задача из очереди {$action['action']} ({$action['id']})");
                }
            }
        }
    }

    public function checkProccessing($id){
        $action = $this->modx->getObject("slQueue", $id);
        if($action){
            return $action->get("processing");
        }
        return false;
    }

    public function setProccessing($id){
        $action = $this->modx->getObject("slQueue", $id);
        if($action){
            $action->set("processing", 1);
            $action->set("startedon", time());
            $action->save();
            return true;
        }
        return false;
    }

    public function setProccessed($id, $response = array()): bool
    {
        $action = $this->modx->getObject("slQueue", $id);
        $this->toLog($id, $response);
        if($action){
            // $action->set("response", $response);
            // $action->set("finishedon", time());
            $action->set("processing", 0);
            $action->set("processed", 1);
            $action->save();
            $this->toLog($id, "Сохранил!");
            return true;
        }else{
            $this->toLog($id, "Не найдено!");
        }
        return false;
    }

    /**
     * Обработка заданий
     *
     * @return void
     */
    public function handleQueue(){
        // $this->clearQueue();
        $queues = $this->getQueue();
        if($queues){
            foreach($queues as $queue){
                if(!$this->checkProccessing($queue['id'])){
                    $this->setProccessing($queue['id']);
                    switch ($queue['action']) {
                        case 'store/products/copo/update':
                            $properties = json_decode($queue['properties'], 1);
                            $q = $this->modx->newQuery("slStoresRemains");
                            $q->leftJoin("slStores", "slStores", "slStores.id = slStoresRemains.store_id");
                            $q->select("`slStoresRemains`.`id`");
                            if($properties['store']){
                                $q->where(array("slStoresRemains.store_id:=" => $properties['store']));
                            }else{
                                $q->where(array("slStores.active:=" => 1));
                            }
                            $all_data = $this->modx->getCount("slStoresRemains", $q);
                            $response['all'] = $all_data;
                            // перепривязываем
                            $limit = 1000;
                            for($i = 0; $i <= $all_data; $i += $limit){
                                $query = $this->modx->newQuery("slStoresRemains");
                                $query->leftJoin("slStores", "slStores", "slStores.id = slStoresRemains.store_id");
                                $query->select(array("slStoresRemains.id"));
                                if($properties['store']){
                                    $query->where(array("slStoresRemains.store_id:=" => $properties['store']));
                                }else{
                                    $query->where(array("slStores.active:=" => 1));
                                }
                                $query->limit($limit, $i);
                                if($query->prepare() && $query->stmt->execute()){
                                    $remains = $query->stmt->fetchAll(PDO::FETCH_ASSOC);
                                    foreach($remains as $remain){
                                        $res = $this->sl->product->linkProduct($remain['id'], 'slStores');
                                    }
                                }
                            }
                            if($properties['store']){
                                $res = $this->sl->product->generateCopoReport($properties['store'], 1);
                            }else{
                                $q = $this->modx->newQuery("slStores");
                                $q->select("`slStores`.`id`");
                                $q->where(array("slStores.active:=" => 1));
                                $all_data = $this->modx->getCount("slStores", $q);
                                $response['all'] = $all_data;
                                // генерируем отчеты
                                $limit = 1000;
                                for($i = 0; $i <= $all_data; $i += $limit){
                                    $query = $this->modx->newQuery("slStores");
                                    $query->select(array("slStores.id"));
                                    $q->where(array("slStores.active:=" => 1));
                                    $query->limit($limit, $i);
                                    if($query->prepare() && $query->stmt->execute()){
                                        $stores = $query->stmt->fetchAll(PDO::FETCH_ASSOC);
                                        foreach($stores as $store){
                                            $res = $this->sl->product->generateCopoReport($store['id'], 1);
                                        }
                                    }
                                }
                            }

                            $this->setProccessed($queue['id'], $response);
                            break;
                        // импорт файла из API
                        case 'api/file/handler':
                            $properties = json_decode($queue['properties'], 1);
                            $files = scandir($properties['file_path']);
                            $content = trim(file_get_contents($properties['file_path'].'/'.$files[2]), "\xEF\xBB\xBF");
                            $data = json_decode($content, true);
                            $response = array();
                            if($data['key']) {
                                $store = $this->sl->product->getStore($data['key'], "date_remains_update");
                                if ($store['id']) {
                                    // обнуляем остатки
                                    $table = $this->modx->getTableName("slStoresRemains");
                                    if ($table) {
                                        $sql = "UPDATE {$table} SET `price` = 0, `remains` = 0, `reserved` = 0, `available` = 0 WHERE `store_id` = {$store['id']};";
                                        $stmt = $this->modx->prepare($sql);
                                        if (!$stmt) {
                                            $this->modx->log(1, print_r($stmt->errorInfo, true) . ' SQL: ' . $sql);
                                        }
                                        if (!$stmt->execute($data)) {
                                            $this->modx->log(1, print_r($stmt->errorInfo, true) . ' SQL: ' . $sql);
                                        }
                                    }
                                    $this->toLog($queue['id'], "Всего остатков: " . count($data['product_archive']));
                                    if ($data['product_archive']) {
                                        $response['remains_checkpoint'] = $this->sl->product->importRemainsCheckpoints($data);
                                    }
                                    $this->toLog($queue['id'], $response);
                                    $this->toLog($queue['id'], "Всего документов: " . count($data['docs']));
                                    if ($data['docs']) {
                                        $response['docs'] = $this->sl->product->importDocs($data);
                                    }
                                    $this->toLog($queue['id'], $response);
                                    $this->setProccessed($queue['id'], $response);
                                    // $this->toLog($queue['id'], $response);
                                }
                            }
                            break;
                        // Проверка статуса заявки в Яндекс.Доставке (Экспресс)
                        // TODO: продумать обработку других статусов
                        case 'tk/yandex/check':
                            $properties = json_decode($queue['properties'], 1);
                            $response = $this->sl->yandex->getRequest($properties['claim_id']);
                            if($response[0]){
                                $success_statuses = array(
                                    "delivered",
                                    "delivered_finish"
                                );
                                if(in_array($response[0]['status'], $success_statuses)){
                                    $this->sl->cart->setDeliveryStage($properties['claim_id']);
                                }
                            }
                            $this->setProccessed($queue['id'], $response);
                            break;
                    }
                }
            }
        }
    }

    public function tolog($id, $data) {
        $this->modx->log(xPDO::LOG_LEVEL_ERROR, print_r($data, 1), array(
            'target' => 'FILE',
            'options' => array(
                'filename' => 'queue_'.$id.'.log'
            )
        ));
    }
}