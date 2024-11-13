<?php

class productHandler
{
    public $modx;
    public $sl;
    public $config;

    public function __construct(shopLogistic &$sl, modX &$modx)
    {
        $this->sl =& $sl;
        $this->modx =& $modx;

        $this->config = array(
            "product_price_percent" => 20
        );

        $this->modx->lexicon->load('shoplogistic:default');
    }

    /**
     * Берем товары и информацию по магазину
     *
     * @return void
     */
    public function getProducts($properties){
        // TODO: чекнуть уровень доступа
        $prefix = $this->modx->getOption('table_prefix');
        if(!$properties['store_id']){
            //Берём все магазины
            $ids = array();
            $stores = $this->sl->orgHandler->getStoresOrg(array("id" => $properties['id']), 0);
            foreach($stores["items"] as $store){
                $ids[] = $store["id"];
            }
            $criteria = array(
                "slStoresRemains.store_id:IN" => $ids
            );
        }else{
            //Берём только 1 магазин
            $criteria = array(
                "slStoresRemains.store_id:=" => $properties['store_id']
            );
        }
        // Если передан остаток
        if($properties['product_id']){
            $product = array();
            $dates = array();
            $sales = array();
            $criteria['id'] = $properties['product_id'];
            $q = $this->modx->newQuery("slStoresRemains");
            $q->leftJoin('msProductData', 'msProduct', 'msProduct.id = slStoresRemains.product_id');
            $q->leftJoin('modResource', 'modResource', 'modResource.id = msProduct.id');
            $q->leftJoin('slStores', 'slStores', 'slStores.id = slStoresRemains.store_id');
            $q->select(array(
                'slStoresRemains.*',
                'msProduct.image',
                'msProduct.article as product_article',
                'msProduct.vendor_article',
                'modResource.pagetitle',
                'slStores.name_short as store_name'
            ));
            $q->where($criteria);


            if($q->prepare() && $q->stmt->execute()){
                $result = $q->stmt->fetch(PDO::FETCH_ASSOC);
                $product = $result;
                // get Remains History
                $q = $this->modx->newQuery("slStoresRemainsHistory");
                $q->select(array(
                    'slStoresRemainsHistory.*'
                ));
                $q->where(array(
                    "remain_id" => $product['id']
                ));
                if($q->prepare() && $q->stmt->execute()){
                    $results = $q->stmt->fetchAll(PDO::FETCH_ASSOC);
                    /* -------------- HIGHLIGHT --------------- */
                    $dates[] = array(
                        'highlight' => array(
                            'color' => 'red',
                            'fillMode' => 'outline',
                        ),
                        'dates' => array()
                    );
                    foreach($results as $index => $result){
                        if($result['count'] > 0){
                            $dates[0]['dates'][] = date("Y-m-d", strtotime($result['date']));
                        }
                    }
                    /* ------------ / HIGHLIGHT --------------- */
                }
                /* ------------ SALES --------------------- */
                $sql = "SELECT SUM(count) AS sales FROM `{$prefix}sl_stores_docs_products` WHERE `type` = 1 AND `remain_id` = {$product['id']} GROUP BY `remain_id`";
                $statement = $this->modx->query($sql);
                $sales = $statement->fetch(PDO::FETCH_ASSOC);
                /* ------------ / SALES ------------------- */
            }
            if(count($product)){
                $product['sales'] = $sales['sales']? : 0;
                $product['dates'] = $dates;
                return array(
                    "success" => true,
                    "message" => "Товар магазина выгружен успешно",
                    "data" => $product
                );
            }else{
                return array(
                    "success" => false,
                    "message" => "Товар магазина не найден",
                    "data" => $result
                );
            }
        }else{
            $result = array();

            $q = $this->modx->newQuery("slStoresRemains");
            $q->leftJoin('msProductData', 'msProduct', 'msProduct.id = slStoresRemains.product_id');
            $q->leftJoin('modResource', 'modResource', 'modResource.id = msProduct.id');
            $q->leftJoin('slStores', 'slStores', 'slStores.id = slStoresRemains.store_id');
            $q->where($criteria);
            if($properties['filter']){
                $words = explode(" ", $properties['filter']);
                foreach($words as $word){
                    $criteria = array();
                    $criteria['name:LIKE'] = '%'.trim($word).'%';
                    $criteria['OR:article:LIKE'] = '%'.trim($word).'%';
                    $q->where($criteria);
                }
            }
            if($properties['filtersdata']){
                $criteria = array();
                if($properties['filtersdata']['product_id']){
                    if($properties['filtersdata']['product_id'] == 1){
                        $criteria['slStoresRemains.product_id:>'] = 0;
                    }
                    if($properties['filtersdata']['product_id'] == 0){
                        $criteria['slStoresRemains.product_id'] = 0;
                    }
                }
                if($properties['filtersdata']['status']){
                    $criteria['slStoresRemains.status'] = $properties['filtersdata']['status'];
                }
                if($properties['filtersdata']['instock']){
                    $criteria['slStoresRemains.remains:>'] = 0;
                }
                if(count($criteria)){
                    $q->where($criteria);
                }
                if(isset($properties['filtersdata']['catalog'])){
                    $catalogs = array();
                    foreach($properties['filtersdata']['catalog'] as $key => $val){
                        if($val['checked']){
                            $catalogs[] = $key;
                        }
                    }
                    if(count($catalogs)){
                        $q->where(array(
                            "modResource.parent:IN" => $catalogs
                        ));
                    }
                }
                if(isset($properties['filtersdata']['vendor'])){
                    $q->where(array(
                        "msProduct.vendor:=" => $properties['filtersdata']['vendor']
                    ));
                }
                if(isset($properties['filtersdata']['minuses'][0])){
                    $q->where(array(
                        "(FLOOR((`slStoresRemains`.`remains` - slStoresRemains.purchase_speed)) < 0 OR FLOOR((slStoresRemains.remains - (7 * slStoresRemains.purchase_speed))) < 0)"
                    ));
                }
            }

            $today = date_create();
            $month_ago = date_create("-1 MONTH");
            date_time_set($month_ago, 00, 00);

            $date_from = date_format($month_ago, 'Y-m-d H:i:s');
            $date_to = date_format($today, 'Y-m-d H:i:s');

            $select_array = array(
                'slStoresRemains.*',
                'msProduct.image',
                'slStores.name_short as store_name',
                'COALESCE(msProduct.price_rrc, 0) as price_rrc',
                'COALESCE((slStoresRemains.price * slStoresRemains.remains), 0) as summ',
                'IF(price_rrc > 0, (slStoresRemains.price - price_rrc), 0) as price_rrc_delta',
                "COALESCE((SELECT SUM(count) AS sales FROM `{$prefix}sl_stores_docs_products` LEFT JOIN `{$prefix}sl_stores_docs` ON `{$prefix}sl_stores_docs_products`.doc_id = `{$prefix}sl_stores_docs`.id WHERE `type` = 1 AND `remain_id` = `slStoresRemains`.`id` AND `{$prefix}sl_stores_docs`.date >= '{$date_from}' AND `{$prefix}sl_stores_docs`.date <= '{$date_to}' GROUP BY `remain_id`), 0) AS `sales_30`",
                "COALESCE((SELECT SUM(count) AS sales FROM `{$prefix}sl_stores_docs_products` WHERE `type` = 1 AND `remain_id` = `slStoresRemains`.`id` GROUP BY `remain_id`), 0) AS `sales`,
							   FLOOR((slStoresRemains.remains - slStoresRemains.purchase_speed)) as forecast,FLOOR((slStoresRemains.remains - (7 * slStoresRemains.purchase_speed))) as forecast_7, CONCAT(FLOOR((slStoresRemains.remains - slStoresRemains.purchase_speed)), ' / ', FLOOR((slStoresRemains.remains - (7 * slStoresRemains.purchase_speed)))) as forecast_all"
            );

            // товары без движения
            if(isset($properties['filtersdata']['no_motion'])){
                $days_ago = $properties['filtersdata']['no_motion'];
                $day = date_create("-".$days_ago." day");
                date_time_set($day,00,00);
                $df = date_format($day, 'Y-m-d H:i:s');
                $select_array[] = "COALESCE((SELECT COUNT(*) AS diff FROM `{$prefix}sl_stores_remains_history` WHERE `remain_id` = `slStoresRemains`.`id` AND `remains` != `slStoresRemains`.`remains` AND `date` >= '{$df}'), 0) AS `diff`";
                $q->where(array(
                    "COALESCE((SELECT COUNT(*) AS diff FROM `{$prefix}sl_stores_remains_history` WHERE `remain_id` = `slStoresRemains`.`id` AND `remains` != `slStoresRemains`.`remains` AND `date` >= '{$df}'), 0) = 0",
                    "(SELECT remains FROM `{$prefix}sl_stores_remains_history` WHERE `remain_id` = `slStoresRemains`.`id` AND `date` < '{$df}' ORDER BY date DESC LIMIT 1) = `slStoresRemains`.`remains`",
                    "COALESCE((SELECT SUM(count) AS sales FROM `{$prefix}sl_stores_docs_products` LEFT JOIN `{$prefix}sl_stores_docs` ON `{$prefix}sl_stores_docs_products`.doc_id = `{$prefix}sl_stores_docs`.id WHERE `type` = 1 AND `remain_id` = `slStoresRemains`.`id` AND `{$prefix}sl_stores_docs`.date >= '{$df}' AND `{$prefix}sl_stores_docs`.date <= '{$date_to}' GROUP BY `remain_id`), 0) = 0",
                ));
            }

            $q->select($select_array);

            // Подсчитываем общее число записей
            $result['total'] = $this->modx->getCount('slStoresRemains', $q);

            // Устанавливаем лимит 1/10 от общего количества записей
            // со сдвигом 1/20 (offset)
            if($properties['page'] && $properties['perpage']){
                $limit = $properties['perpage'];
                $offset = ($properties['page'] - 1) * $properties['perpage'];
                $q->limit($limit, $offset);
            }

            // И сортируем по ID в обратном порядке
            if($properties['sort']){
                $keys = array_keys($properties['sort']);
                // нужно проверить какому объекту принадлежит поле
                $q->sortby($keys[0], $properties['sort'][$keys[0]]['dir']);
            }
            $q->prepare();
            $this->modx->log(1, $q->toSQL());
            $this->modx->log(1, "_MOT9I_");
            if($q->prepare() && $q->stmt->execute()){
                $result['products'] = $q->stmt->fetchAll(PDO::FETCH_ASSOC);
                foreach($result['products'] as $key => $product) {
                    if ($product["image"]){
                        $outimg = $this->sl->tools->prepareImage($product["image"], array(), 0);
                        $result['products'][$key]["image"] = $outimg['image'];
                    }
                    $result['products'][$key]["summ"] = $this->sl->tools->numberFormat($product["summ"]);
                    $result['products'][$key]["price"] = $this->sl->tools->numberFormat($product["price"]);
                    $result['products'][$key]["no_money"] = $this->sl->tools->numberFormat($product["no_money"]);
                    $remains_data = array(
                        'labels' => array(),
                        'datasets' => array(
                            array(
                                "data" => array(),
                                "label" => 'Остатки',
                                "fill" => false,
                                "borderColor" => "#64748b",
                                "tension" => 0.4
                            )
                        )
                    );
                    // берем первый остаток вне диапазона
                    $query = $this->modx->newQuery("slStoresRemainsHistory");
                    $query->where(array(
                        "remain_id:=" => $product['id'],
                        "date:<=" => $date_from
                    ));
                    $query->sortby("date", "DESC");
                    $query->select(array("slStoresRemainsHistory.*"));
                    if($query->prepare() && $query->stmt->execute()){
                        $remains_prev_history = $query->stmt->fetch(PDO::FETCH_ASSOC);
                        if($remains_prev_history) {
                            $date = strtotime($remains_prev_history['date']);
                            $remains_data['labels'][] = date('d/m/Y', $date);
                            $remains_data['datasets'][0]["data"][] = $remains_prev_history['remains'];
                        }
                    }
                    // берем остатки за 30 дней
                    $query = $this->modx->newQuery("slStoresRemainsHistory");
                    $query->where(array(
                        "remain_id:=" => $product['id'],
                        "date:>=" => $date_from,
                        "date:<=" => $date_to,
                    ));
                    $query->sortby("date", "ASC");
                    $query->select(array("slStoresRemainsHistory.*"));
                    if($query->prepare() && $query->stmt->execute()){
                        $remains_history = $query->stmt->fetchAll(PDO::FETCH_ASSOC);
                        $today = date('d/m/Y');
                        $find = 0;
                        foreach($remains_history as $k => $v){
                            $date = strtotime($v['date']);
                            $date_remain = date('d/m/Y', $date);
                            $remains_data['labels'][] = date('d/m/Y', $date);
                            if($date_remain != $today){
                                $remains_data['datasets'][0]["data"][] = $v["remains"];
                            }else{
                                $remains_data['datasets'][0]["data"][] = $result['products'][$key]['remains'];
                                $find = 1;
                            }
                        }
                        if(!$find){
                            $remains_data['labels'][] = $today;
                            $remains_data['datasets'][0]["data"][] = $result['products'][$key]['remains'];
                        }
                        $result['products'][$key]["remains_history"] = $remains_data;
                    }
                }
                // берем сводную информацию

                //Сопоставление по статусам
                $countAllStatus = 0;
                for ($i = 1; $i <= 5; $i++) {
                    $newQuery = $this->modx->newQuery("slStoresRemains");
                    if(!$properties['store_id']){
                        $criteria = array(
                            "store_id:IN" => $ids,
                            "status:=" => $i
                        );
                    }else{
                        $criteria = array(
                            "store_id:=" => $properties['store_id'],
                            "status:=" => $i
                        );
                    }
                    $newQuery->where($criteria);
                    $newQuery->select(array("SUM(slStoresRemains.remains * slStoresRemains.price) as sum"));
                    if($newQuery->prepare() && $newQuery->stmt->execute()){
                        $sum = $newQuery->stmt->fetchAll(PDO::FETCH_ASSOC);
                        foreach ($sum as $k => $s){
                            $result['status'][$i]['sum'] = $result['status'][$i]['sum'] + $s['sum'];
                        }
                    }
                    // FIX
                    $newQuery = $this->modx->newQuery("slStoresRemains");
                    $newQuery->where($criteria);
                    $newQuery->select(array("COUNT(*) as count"));
                    if($newQuery->prepare() && $newQuery->stmt->execute()){
                        $count = $newQuery->stmt->fetch(PDO::FETCH_ASSOC);
                        $result['status'][$i]['count'] = $count['count'];
                    }
                    $countAllStatus = $countAllStatus + $result['status'][$i]['count'];
                }
                // общее кол-во
                $result['status']['total'] = $countAllStatus;

                $query = $this->modx->newQuery("slStoresRemains");
                $query->leftJoin('msProductData', 'msProduct', 'msProduct.id = slStoresRemains.product_id');
                $query->leftJoin('modResource', 'modResource', 'modResource.id = msProduct.id');
                $query->where($criteria);
                if($properties['filter']){
                    $words = explode(" ", $properties['filter']);
                    foreach($words as $word){
                        $criteria = array();
                        $criteria['name:LIKE'] = '%'.trim($word).'%';
                        $criteria['OR:article:LIKE'] = '%'.trim($word).'%';
                        $query->where($criteria);
                    }
                }
                if($properties['filtersdata']){
                    $criteria = array();
                    if($properties['filtersdata']['product_id']){
                        if($properties['filtersdata']['product_id'] == 1){
                            $criteria['slStoresRemains.product_id:>'] = 0;
                        }
                        if($properties['filtersdata']['product_id'] == 0){
                            $criteria['slStoresRemains.product_id'] = 0;
                        }
                    }
                    if(count($criteria)){
                        $query->where($criteria);
                    }
                    if(isset($properties['filtersdata']['catalog'])){
                        $catalogs = array();
                        foreach($properties['filtersdata']['catalog'] as $key => $val){
                            if($val['checked']){
                                $catalogs[] = $key;
                            }
                        }
                        if(count($catalogs)){
                            $query->where(array(
                                "modResource.parent:IN" => $catalogs
                            ));
                        }
                    }
                    if(isset($properties['filtersdata']['vendor'])){
                        $query->where(array(
                            "msProduct.vendor:=" => $properties['filtersdata']['vendor']
                        ));
                    }
                    if(isset($properties['filtersdata']['minuses'][0])){
                        $query->where(array(
                            "(FLOOR((`slStoresRemains`.`remains` - slStoresRemains.purchase_speed)) < 0 OR FLOOR((slStoresRemains.remains - (7 * slStoresRemains.purchase_speed))) < 0)"
                        ));
                    }
                }
                $query->select(array("SUM(slStoresRemains.remains) as remains, SUM(slStoresRemains.no_money) as no_money, AVG(slStoresRemains.purchase_speed) as sales_speed"));
                if($query->prepare() && $query->stmt->execute()){
                    $result['avg_info'] = $query->stmt->fetch(PDO::FETCH_ASSOC);
                    $result['avg_info']['remains'] = $this->sl->tools->numberFormat($result['avg_info']['remains'], 0);
                    $result['avg_info']['no_money'] = $this->sl->tools->numberFormat($result['avg_info']['no_money'], 2);
                    $result['avg_info']['sales_speed'] = $this->sl->tools->numberFormat($result['avg_info']['sales_speed'], 6);
                }
            }
            if(!count($result["products"])){
                $result = array(
                    'total' => 0,
                    'products' => 0,
                    'avg_info' => array(
                        'remains' => 0,
                        'no_money' => 0,
                        'sales_speed' => 0
                    )
                );
            }
            return array(
                "success" => true,
                "message" => "Товары магазина выгружены успешно",
                "data" => $result
            );
        }
    }

    /**
     * Берем данные по стоимости товара
     *
     * @param $store_id
     * @param $product_id
     * @return array|void
     */
    public function getRemainAndPriceForStore($store_id, $product_id){
        $remain = $this->getStoreRemain($store_id, $product_id);
        if(!$remain){
            $remain = $this->getMinStoreRemain($product_id);
            if($remain){
                $remain['remains'] = 0;
            }else{
                $remain = array(
                    "price" => 99999999,
                    "remains" => 0
                );
            }
        }
        return $remain;
    }

    /**
     * Проверяем цену товара по двум параметрам
     *
     * @param $remain_id
     * @return bool
     */
    public function checkRemainPrice($remain_id){
        $obj = $this->modx->getObject("slStoresRemains", $remain_id);
        if($obj){
            $remain = $obj->toArray();
            if($remain){
                // 1. Убираем 1 руб
                if($remain["price"] == 1){
                    $message = array(
                        "action_id" => 3,
                        "date" => time(),
                        "message" => "Цена товара не может быть равна 1 руб.",
                        "actions" => "Скорректируйте стоимость товара"
                    );
                    $obj->set("status", 6);
                    $obj->set("published", 0);
                    $data = $obj->get("properties");
                    if ($data) {
                        $properties = json_decode($data, 1);
                    } else {
                        $properties = array(
                            "actions" => array()
                        );
                    }
                    if ($properties["actions"]) {
                        array_unshift($properties["actions"], $message);
                    } else {
                        $properties["actions"][] = $message;
                    }
                    $obj->set("properties", json_encode($properties, JSON_UNESCAPED_UNICODE));
                    $obj->save();
                }else{
                    if($remain["product_id"] && !$remain["force_price"]) {
                        // Проверка по нескольким метрикам
                        // 1. Средняя стоимость конкурентов (ВСЕХ), доп добавить флаг архива DEPRECATED
                        /*
                        $q = $this->modx->newQuery("slStoresRemains");
                        $q->leftJoin("slStores", "slStores", "slStores.id = slStoresRemains.store_id");
                        $q->where(array(
                            "slStoresRemains.id:!=" => $remain_id,
                            "slStoresRemains.product_id:=" => $remain["product_id"]
                        ));
                        $q->select(array("AVG(slStoresRemains.price) as avg_price"));
                        if($q->prepare() && $q->stmt->execute()) {
                            $avg = $q->stmt->fetch(PDO::FETCH_ASSOC);
                            if ($avg) {
                                if ($avg["avg_price"] > 10) {
                                    // TODO: убрать в системную настройку
                                    $percent = 50;
                                    $min_k = 1 - ($percent * 0.01);
                                    $max_k = 1 + ($percent * 0.01);
                                    $from = $avg["avg_price"] * $min_k;
                                    $to = $avg["avg_price"] * $max_k;
                                    if ($remain["price"] < $from || $remain["price"] > $to) {
                                        $message = array(
                                            "action_id" => 1,
                                            "date" => time(),
                                            "message" => "Цена товара отличается от цены других магазинов на 50%.",
                                            "actions" => "Поставьте флаг принулительного выставления цены, если Вы в ней уверены или скорректируйте стоимость товара"
                                        );
                                        $obj->set("status", 6);
                                        $obj->set("published", 0);
                                        $data = $obj->get("properties");
                                        if ($data) {
                                            $properties = json_decode($data, 1);
                                        } else {
                                            $properties = array(
                                                "actions" => array()
                                            );
                                        }
                                        if ($properties["actions"]) {
                                            array_unshift($properties["actions"], $message);
                                        }else{
                                            $properties["actions"][] = $message;
                                        }
                                        $obj->set("properties", json_encode($properties, JSON_UNESCAPED_UNICODE));
                                        $obj->save();
                                        return false;
                                    } else {
                                        if (!$remain["published"]) {
                                            $message = array(
                                                "action_id" => 2,
                                                "date" => time(),
                                                "message" => "Товар опубликован.",
                                                "actions" => ""
                                            );
                                            $obj->set("status", 3);
                                            $obj->set("published", 1);
                                            $data = $obj->get("properties");
                                            if ($data) {
                                                $properties = json_decode($data, 1);
                                            } else {
                                                $properties = array(
                                                    "actions" => array()
                                                );
                                            }
                                            if ($properties["actions"]) {
                                                array_unshift($properties["actions"], $message);
                                            }else{
                                                $properties["actions"][] = $message;
                                            }
                                            $obj->set("properties", json_encode($properties, JSON_UNESCAPED_UNICODE));
                                            $obj->save();
                                        }
                                    }
                                }
                            }
                        }
                        */
                        // 2. Минимальная стоимость по категории (ОБНОВЛЯТЬ???)
                        /*
                        $query = $this->modx->newQuery("modResource");
                        $query->leftJoin("modTemplateVarResource", "modTemplateVarResource", "modTemplateVarResource.contentid = modResource.parent AND modTemplateVarResource.tmplvarid = 42");
                        $query->where(array(
                            "modResource.id" => $remain["product_id"]
                        ));
                        $query->select(array("modResource.*,modTemplateVarResource.value as min_price"));
                        if ($query->prepare() && $query->stmt->execute()) {
                            $resource = $query->stmt->fetch(PDO::FETCH_ASSOC);
                            if ($resource) {
                                if ($resource["min_price"]) {
                                    if ($remain["price"] < $resource["min_price"]) {
                                        $message = array(
                                            "action_id" => 3,
                                            "date" => time(),
                                            "message" => "Цена товара отличается от минимальной цены по категории.",
                                            "actions" => "Поставьте флаг принудительного выставления цены, если Вы в ней уверены или скорректируйте стоимость товара"
                                        );
                                        $obj->set("status", 6);
                                        $obj->set("published", 0);
                                        $data = $obj->get("properties");
                                        if ($data) {
                                            $properties = json_decode($data, 1);
                                        } else {
                                            $properties = array(
                                                "actions" => array()
                                            );
                                        }
                                        if (isset($properties["actions"])) {
                                            array_unshift($properties["actions"], $message);
                                        } else {
                                            $properties["actions"][] = $message;
                                        }
                                        $obj->set("properties", json_encode($properties, JSON_UNESCAPED_UNICODE));
                                        $obj->save();
                                        return false;
                                    } else {
                                        if (!$remain["published"]) {
                                            $message = array(
                                                "action_id" => 4,
                                                "date" => time(),
                                                "message" => "Товар опубликован.",
                                                "actions" => ""
                                            );
                                            $obj->set("status", 3);
                                            $obj->set("published", 1);
                                            $data = $obj->get("properties");
                                            if ($data) {
                                                $properties = json_decode($data, 1);
                                            } else {
                                                $properties = array(
                                                    "actions" => array()
                                                );
                                            }
                                            if (isset($properties["actions"])) {
                                                array_unshift($properties["actions"], $message);
                                            } else {
                                                $properties["actions"][] = $message;
                                            }
                                            $obj->set("properties", json_encode($properties, JSON_UNESCAPED_UNICODE));
                                            $obj->save();
                                        }
                                    }
                                }
                            }
                        }*/
                    }
                }
                return true;
            }
        }
    }

    /**
     * Берем остаток по ID товара и магазину
     *
     * @param $store_id
     * @param $product_id
     * @return array
     */
    public function getMinStoreRemain($product_id){
        $min_price = 10;
        // TODO: $this->getSales($remain['id'], $remain["store_id"])
        $q = $this->modx->newQuery("modResource");
        $q->leftJoin("modTemplateVarResource", "modTemplateVarResource", "modTemplateVarResource.contentid = modResource.parent AND modTemplateVarResource.tmplvarid = 42");
        $q->where(array("modResource.id:=" => $product_id));
        $q->select(array("modTemplateVarResource.value as min_price"));
        if($q->prepare() && $q->stmt->execute()){
            $p = $q->stmt->fetch(PDO::FETCH_ASSOC);
            if($p['min_price']){
                $min_price = $p['min_price'];
            }
        }
        $query = $this->modx->newQuery("slStoresRemains");
        $query->leftJoin("slStores", "slStores", "slStores.id = slStoresRemains.store_id");
        $m_criteria = $this->sl->store->getMarketplaceAvailableCriteria("slStores.");
        $criteria = array(
            "slStores.active:=" => 1,
            "slStoresRemains.available:>" => 0,
            "slStoresRemains.published:=" => 1,
            "slStoresRemains.price:>" => $min_price,
            "slStoresRemains.product_id:=" => $product_id,
        );
        $query->where(array_merge($m_criteria, $criteria));
        $query->select(array("slStoresRemains.*"));
        $query->sortby("price", "ASC");
        $query->limit(1);
        if($query->prepare() && $query->stmt->execute()){
            $response = $query->stmt->fetch(PDO::FETCH_ASSOC);
            if($response){
                // чекаем акции
                $action = $this->sl->cart->getSales($response['id'], $response["store_id"]);
                if($action){
                    if($action["new_price"]){
                        $response["price"] = $action["new_price"];
                    }
                    if($action["old_price"]){
                        $response["old_price"] = $action["old_price"];
                    }
                }
            }
            return $response;
        }
        return array();
    }

    /**
     * Берем остаток по ID товара и магазину
     *
     * @param $store_id
     * @param $product_id
     * @return array
     */
    public function getStoreRemain($store_id, $product_id){
        $query = $this->modx->newQuery("slStoresRemains");
        $query->leftJoin("slStores", "slStores", "slStores.id = slStoresRemains.store_id");
        $m_criteria = $this->sl->store->getMarketplaceAvailableCriteria("slStores.");
        $criteria = array(
            "slStores.id:=" => $store_id,
            "slStoresRemains.available:>" => 0,
            "slStoresRemains.published:=" => 1,
            "slStoresRemains.price:>" => 0,
            "slStoresRemains.product_id:=" => $product_id,
        );
        $query->where(array_merge($m_criteria, $criteria));
        $query->select(array("slStoresRemains.*"));
        if($query->prepare() && $query->stmt->execute()){
            $response = $query->stmt->fetch(PDO::FETCH_ASSOC);
            if($response){
                $action = $this->sl->cart->getSales($response['id'], $response["store_id"]);
                if($action){
                    if($action["new_price"]){
                        $response["price"] = $action["new_price"];
                    }
                    if($action["old_price"]){
                        $response["old_price"] = $action["old_price"];
                    }
                }
            }
            return $response;
        }
        return array();
    }

    /**
     *
     * Берем параметры товара, габариты в см, вес в кг, объем в куб.м.
     *
     * @param $product_id
     * @param $count
     * @return array|false
     */
    public function getProductParams($product_id = 0, $count = 1){
        $tmp = array();
        $output = array();
        if($product_id){
            $query = $this->modx->newQuery("modResource");
            $query->leftJoin("msProductData", "Data");
            $query->where(array(
                "`modResource`.`id`:=" => $product_id
            ));
            $query->select(array(
                "`modResource`.*",
                "`Data`.*"
            ));
            $query->limit(1);
            if ($query->prepare() && $query->stmt->execute()) {
                $product = $query->stmt->fetch(PDO::FETCH_ASSOC);
                if(is_countable($product)){
                    if(count($product)){
                        $params = array();
                        $tmp["id"] = $product['id'];
                        $tmp["article"] = $product['article'];
                        $tmp["name"] = $product['pagetitle'];
                        $tmp['weight'] = (float)$product['weight']?:(float)$product['weight_netto'];
                        $tmp['weight_netto'] = (float)$product['weight_netto'];
                        $tmp['volume'] = (float)$product['volume'];
                        $tmp['price'] = (float)$product['price'];
                        $tmp['count'] = $count;
                        $params['dimensions'][0] = (int)$product['length'];
                        $params['dimensions'][1] = (int)$product['width'];
                        $params['dimensions'][2] = (int)$product['height'];
                        $tmp['length'] = $params['dimensions'][0];
                        $tmp['width'] = $params['dimensions'][1];
                        $tmp['height'] = $params['dimensions'][2];
                        $tmp['dimensions'] = implode('*', $params['dimensions']);
                        $tmp['product'] = $product;
                        $output[] = $tmp;
                        return $output;
                    }
                }
            }
        }
        return false;
    }

    /**
     * Установка версии модуля обмена
     *
     * @param $store_id
     * @param $version
     * @return void
     */
    public function setVersion($store_id, $version){
        $store = $this->modx0>getObject("slStores", $store_id);
        if($store){
            $store->set("version", $version);
            $store->save();
        }
    }

    /**
     * Установка версии модуля обмена
     *
     * @param $store_id
     * @param $version
     * @return void
     */
    public function splitVersion($version){
        $v = explode(",", $version);
        return $v;
    }

    /**
     * Импорт остатка
     *
     * @param $data
     * @return array|false
     */
    public function importRemain($data){
        if($data['key']) {
            $store = $this->getStore($data['key'], "date_remains_update");
            if ($store['id']) {
                if($data['version']){
                    $this->setVersion($store['id'], $data['version']);
                }
                $response = array();
                $response['success_info'] = array();
                $response['failed_info'] = array();
                if($data['isFull']){
                    // обнуляем остатки
                    $table = $this->modx->getTableName("slStoresRemains");
                    if($table){
                        // TODO: проверить полную выгрузку
                        /*
                        $sql = "UPDATE {$table} SET `price` = 0, `remains` = 0, `reserved` = 0, `available` = 0 WHERE `store_id` = {$store['id']};";
                        $stmt = $this->modx->prepare($sql);
                        if(!$stmt){
                            $this->modx->log(1, print_r($stmt->errorInfo, true) . ' SQL: ' . $sql);
                        }
                        if (!$stmt->execute($data)) {
                            $this->modx->log(1, print_r($stmt->errorInfo, true) . ' SQL: ' . $sql);
                        }
                        */
                    }
                    // $this->sl->api->update("slStoresRemains", );
                }
				// массив цен
				$prices = array();
                if ($data['catalog_list']) {
                    $response['catalog_list'] = $this->importCatalogs($data);
                }
				if($data['promo_prices_list']){
					foreach($data['promo_prices_list'] as $price){
						$prices[$price['price_id']] = array(
							"name" => $price['price_Name']
						);
					}
				}
                foreach ($data['products'] as $key => $product) {
                    $error = false;
                    $message = '';
                    if (!isset($product['article'])) {
                        if ($message) {
                            $message = $message . ' || WARN! Не указан артикул товара';
                        } else {
                            $message = 'WARN! Не указан артикул товара';
                        }
                    }
                    if (!isset($product['count_current'])) {
                        if ($message) {
                            $message = $message . ' || Не указан текущий остаток товара';
                        } else {
                            $message = 'Не указан текущий остаток товара';
                        }
                        // $error = true;
                    }
                    if (!isset($product['count_free'])) {
                        if ($message) {
                            $message = $message . ' || Не указан доступный для продажи остаток товара используем count_current';
                        } else {
                            $message = 'Не указан доступный для продажи остаток товара используем count_current';
                        }
                        $product['count_free'] = $product['count_current'];
                        // $error = true;
                    }
                    if (!isset($product['price'])) {
                        if ($message) {
                            $message = $message . ' || Не указана цена товара';
                        } else {
                            $message = 'Не указана цена товара';
                        }
                        // $error = true;
                    }
                    if (!isset($product['catalog'])) {
                        if ($message) {
                            $message = $message . ' || WARN! Не указана категория товара';
                        } else {
                            $message = 'WARN! Не указана категория товара';
                        }
                    }

                    if(isset($product['catalog_id'])){
                        $product["catalog_guid"] = $product['catalog_id'];
                        $product['category_id'] = $this->getProductCategory($store['id'], $product['catalog_id']);
                    }else{
                        $product['category_id'] = 0;
                    }
                    // чекаем GUID
                    if(isset($product['GUID'])){
                        $guid = $product['GUID'];
                    }else{
                        $guid = $key;
                    }
                    if(isset($data['base_GUID'])){
                        $product['base_GUID'] = $data['base_GUID'];
                    }else{
                        $product['base_GUID'] = '';
                    }
                    if ($error) {
                        $response['failed_info'][] = array(
                            'guid' => $guid,
                            'message' => $message
                        );
                    } else {
                        $resp = $this->importRemainSingle($store['id'], $product);
                        if($resp){
							// если есть цены
							if($product['promo_prices']){
								foreach($product['promo_prices'] as $pr){
									if(!$product_price = $this->modx->getObject("slStoresRemainsPrices", array("remain_id" => $resp, "key" => $pr["price_id"]))){
										$product_price = $this->modx->newObject("slStoresRemainsPrices");
									}									
									$product_price->set("remain_id", $resp);
									$product_price->set("name", $prices[$pr["price_id"]]['name']);
									$product_price->set("key", $pr["price_id"]);
									$product_price->set("price", $pr["value"]);
									$product_price->set("active", 1);
									$product_price->save();
								}
							}
                            $response['success_info'][] = $resp;
                        }else{
                            $response['failed_info'][] = $resp;
                        }
                    }
                }
                $response['success'] = count($response['success_info']);
                $response['failed'] = count($response['failed_info']);
                return $response;
            }
        }
        return false;
    }

    /**
     * Импорт остатка из 1С
     *
     * @param $store_id
     * @param $data
     * @return int
     */
    public function importRemainSingle($store_id, $data){
        $message = "";
        // проверяем товар на дублирование по GUID
        $criteria = array(
            'guid' => $data["GUID"],
            'store_id' => $store_id
        );
        $o = $this->modx->getObject('slStoresRemains', $criteria);
        if (!$o) {
            $o = $this->modx->newObject('slStoresRemains');
        }
        $o->set("guid", $data["GUID"]);
        if($data['base_GUID']){
            $o->set("base_guid", $data['base_GUID']);
        }
        if($data['catalog_id']){
            $o->set("catalog_id", $data['catalog_id']);
        }
        if(isset($data['catalog_guid'])){
            $o->set("catalog_guid", $data['catalog_guid']);
        }
        if($data['barcode']){
            $o->set("barcode", implode(",", $data['barcode']));
        }
        if($data['tags']){
            $o->set("tags", implode(",", str_replace(",", "", $data['tags'])));
        }
        $o->set("article", $data['article']);
        if((int) $data['count_current'] < 0){
            $o->set("remains", 0);
        }else{
            $o->set("remains", $data['count_current']);
        }
        $o->set("catalog", $data['catalog']);
        $reserved = $o->get('reserved');
        if ((int) $reserved > 0) {
            $available = $data['count_free'] - $reserved;
        } else {
            $available = $data['count_free'];
        }
        if ($available < 0) {
            $o->set("available", 0);
        } else {
            $o->set("available", $available);
        }
        if (isset($data['published'])) {
            if((bool) $data['published'] === false){
                $o->set("published", 0);
            }else{
                $o->set("published", 1);
            }
        }
        // Внесено изменение из-за КЛС обновление цен в 0 значение
        if($data['price'] > 1) {
            $o->set("price", $data['price']);
        }
        $o->set('store_id', $store_id);
        // set statuses

        if ($data['name']) {
            $o->set("name", $data['name']);
        }
        if ($o->save()) {
            $remain_id = $o->get('id');
            // линкуем товар
            // if (!$o->get('product_id') && $o->get('autolink')) {
            if (!$o->get('product_id')) {
                $prod = $this->linkProduct($remain_id);
                if($prod["product_id"]){
                    $product_id = $prod["product_id"];
                }else{
                    $product_id = 0;
                }
            }else{
                $product_id = $o->get('product_id');
            }
            // Update remain checkpoint
            $today = new DateTime();
            $dd = $today->getTimestamp();
            $today->setTime(0,0,0);
            $date_from = $today->getTimestamp();
            $today->setTime(23,59,59);
            $date_to = $today->getTimestamp();
            $criteria = array(
                "remain_id" => $remain_id,
                "date:>=" => date('Y-m-d H:i:s', $date_from),
                "date:<=" => date('Y-m-d H:i:s', $date_to),
            );
            $setter = $this->modx->getObject("slStoresRemainsHistory", $criteria);
            $dt = $o->toArray();
            if(!$setter){
                $setter = $this->modx->newObject("slStoresRemainsHistory");
                $setter->set("remain_id", $remain_id);
                $setter->set("createdon", time());
                $setter->set("date", $dd);
            }else{
                $setter->set("updatedon", time());
            }
            $setter->set("price", $dt['price']);
            $setter->set("available", $dt['available']);
            $setter->set("remains", $dt['remains']);
            $setter->set("count", $dt['remains']);
            $setter->set("reserved", $dt['reserved']);
            $setter->save();
            if($product_id){
                $this->checkRemainPrice($remain_id);
                $stores = $this->getProductOffers($product_id);
                if(count($stores)){
                    $status = 1;
                }else{
                    $status = 99;
                }
                $this->setProductStatus($product_id, $status);
            }
            return $o->get("id");
        }
        return 0;
    }

    /**
     * Берем ID категории товара
     *
     * @param $store_id
     * @param $guid
     * @return int
     */
    public function getProductCategory($store_id, $guid){
        // TODO: добавить base_GUID
        $criteria = array(
            "store_id:=" => $store_id,
            "guid:=" => $guid,
            // "base_guid:=" => $save_data["base_guid"]
        );
        $category = $this->modx->getObject("slStoresRemainsCategories", $criteria);
        if($category){
            return $category->get("id");
        }else{
            return 0;
        }
    }

    /**
     * Импорт дерева каталогов
     *
     * @param $data
     * @return void
     */
    public function importCatalogs($data){
        if($data['key']) {
            $store = $this->getStore($data['key'], "date_remains_update");
            if ($store['id']) {
                if($data['version']){
                    $this->setVersion($store['id'], $data['version']);
                }
                if ($data["catalog_list"]) {
                    $updated_ids = array();
                    foreach ($data["catalog_list"] as $k => $item) {
                        $save_data = array();
                        $save_data["guid"] = $item["group_id"];
                        $save_data["name"] = $item["group_name"];
                        $save_data["parent_guid"] = $item["parent_id"];
                        $save_data["store_id"] = $store['id'];
                        $save_data["active"] = 1;
                        if($data["base_GUID"]){
                            $save_data["base_guid"] = $data["base_GUID"];
                        }else{
                            $save_data["base_guid"] = "";
                        }
                        $criteria = array(
                            "store_id:=" => $save_data["store_id"],
                            "guid:=" => $save_data["guid"],
                            "base_guid:=" => $save_data["base_guid"]
                        );
                        if(!$cat = $this->modx->getObject("slStoresRemainsCategories", $criteria)){
                            $cat = $this->modx->newObject("slStoresRemainsCategories");
                            $cat->set("createdon", time());
                        }else{
                            $cat->set("updatedon", time());
                        }
                        foreach($save_data as $key => $v){
                            $cat->set($key, $v);
                        }
                        $cat->save();
                        $updated_ids[] = $cat->get("id");
                    }
                    $criteria = array(
                        "store_id:=" => $store['id'],
                        "base_guid:=" => $data["base_GUID"],
                        "id:NOT IN" => $updated_ids,
                    );
                    //$disabled = $this->modx->getCollection("slStoresRemainsCategories", $criteria);
                    //foreach($disabled as $d){
                        // $d->set("active", 0);
                        // $d->save();
                    //}
                    return $updated_ids;
                }
            }
        }
    }

    /**
     * Чекаем товарные предложения
     *
     * @param $product_id
     * @return array
     */
    public function getProductOffers($product_id){
        $result = array();
        $query = $this->modx->newQuery("slStoresRemains");
        $query->leftJoin("slStores", "slStores", "slStores.id = slStoresRemains.store_id");
        $query->where(array(
            "slStoresRemains.remains:>" => 0,
            "slStoresRemains.published:=" => 1,
            "slStoresRemains.price:>" => 0,
            "slStores.active:=" => 1,
            "slStoresRemains.product_id:=" => $product_id
        ));
        $query->select(array("slStoresRemains.*"));
        if($query->prepare() && $query->stmt->execute()){
            $result = $query->stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        return $result;
    }

    /**
     * Берем магазин и записываем последние пинги API
     *
     * @param $key
     * @param $type
     * @return void
     */
    public function getStore($key, $type = "date_api_ping") {
        $store = $this->modx->getObject("slStores", array('apikey' => $key));
        if($store){
            // set request dates
            $store->set($type, time());
            $store->set("date_api_ping", time());
            $store->save();
            $resp = $store->toArray();
            return $resp;
        }
    }

    /**
     *
     * Автоматическая прилинковка товара на основании бренда, артикула и цены
     *
     * @param $remain_id
     * @param $type
     * @return void
     */

    public function linkProduct($remain_id, $type = 'slStores'){
        $update_data = array();
        $vendor = array();
        $remain = $this->sl->getObject($remain_id, "slStoresRemains");
        if($remain){
            if($remain['brand_manual']){
                if($remain['brand_id']){
                    $vendor['id'] = $remain['brand_id'];
                }
            }else{
                $vendor = $this->searchVendor($remain['name']);
                if(!$vendor){
                    $vendor = $this->searchVendor($remain['catalog']);
                }
            }

            if($vendor){
                if(!$remain['article']){
                    $update_data = array(
                        "brand_id" => $vendor['id'],
                        "status" => 2
                    );
                }else{
                    if(!$remain['price']){
                        $update_data = array(
                            "brand_id" => $vendor['id'],
                            "status" => 5
                        );
                    }else{
                        // ищем карточку товара
                        $query = $this->modx->newQuery("modResource");
                        $query->leftJoin("msProductData", "Data");
                        $query->where(array(
                            "`Data`.`vendor_article`:=" => trim($remain['article']),
                            "`Data`.`vendor`:=" => $vendor['id']
                        ));
                        $query->select(array(
                            "`modResource`.*",
                            "`Data`.*"
                        ));
                        $query->limit(1);
                        if ($query->prepare() && $query->stmt->execute()) {
                            // нашли товар
                            $product = $query->stmt->fetch(PDO::FETCH_ASSOC);
                            if ($product) {
                                $update_data = array(
                                    "status" => 3,
                                    "brand_id" => $vendor['id'],
                                    "product_id" => $product['id']
                                );
                            }else{
                                $update_data = array(
                                    "status" => 4,
                                    "brand_id" => $vendor['id'],
                                    "product_id" => 0
                                );
                            }
                        }else{
                            $update_data = array(
                                "status" => 4,
                                "brand_id" => $vendor['id'],
                                "product_id" => 0
                            );
                        }
                    }
                }
            }else{
                if(!$remain['price']){
                    $update_data = array(
                        "brand_id" => 0,
                        "status" => 5
                    );
                }else{
                    // если не найден бренд выставляем статус
                    $update_data = array(
                        "status" => 1,
                        "brand_id" => 0,
                        "product_id" => 0
                    );
                }
            }
            if(count($update_data)){
                $this->sl->api->update("slStoresRemains", $update_data, $remain['id']);
            }
            return $update_data;
        }
        return 0;
    }

    /**
     *
     * Поиск производителя из набора слов
     *
     * @param $name
     * @return mixed
     */

    public function searchVendor($name){
        $output = array();
        $search_name = trim(mb_strtolower(str_replace('.', '', $name)));
        $name = trim(preg_replace('/\s+/', ' ', preg_replace('/[^ a-zа-яё\d]/ui', ' ', $search_name)));
        $words = explode(" ", $name);
        $crit_name = array();
        $crit_assoc = array();
        foreach($words as $key => $word){
            $w = preg_replace('/[^ a-zа-яё\d]/ui', '', trim($word));
            $words[$key] = $w;
            $crit_name[]["name:LIKE"] = "%{$w}%";
            $crit_assoc[]["association:LIKE"] = "%{$w}%";
        }
        // сначала пробегаем по ассоциациям
        $query = $this->modx->newQuery("slBrandAssociation");
        $query->select(array("slBrandAssociation.*, LENGTH(association) as lenght_name"));
        $query->where($crit_assoc, xPDOQuery::SQL_OR);
        $query->sortby('LENGTH(association)', 'ASC');
        $query->prepare();
        if ($query->prepare() && $query->stmt->execute()) {
            $associations = $query->stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach($associations as $association){
                $pos = strpos($search_name, mb_strtolower($association["association"]));
                if($pos !== false){
                    $output = $this->sl->objects->getObject("msVendor", $association['brand_id']);
                }
            }
        }
        if(!$output) {
            // берем бренды
            $query = $this->modx->newQuery("msVendor");
            $query->select(array("msVendor.*, LENGTH(name) as lenght_name"));
            $query->where($crit_name, xPDOQuery::SQL_OR);
            $query->sortby('LENGTH(name)', 'ASC');
            $query->prepare();
            if ($query->prepare() && $query->stmt->execute()) {
                $vendors = $query->stmt->fetchAll(PDO::FETCH_ASSOC);
                foreach ($vendors as $vendor) {
                    $pos = strpos($search_name, mb_strtolower($vendor["name"]));
                    if ($pos !== false) {
                        $output = $vendor;
                    }
                }
            }
        }
        return $output;
    }

    public function getCategories () {
        $query = $this->modx->newQuery("modResource");
        $query->select(array(
            "`modResource`.*"
        ));
        $query->where(array(
            "`modResource`.`class_key`:=" => 'msCategory',
            "`modResource`.`deleted`:=" => 0,
            "`modResource`.`published`:=" => 1
        ));
        if ($query->prepare() && $query->stmt->execute()) {
            $categories = $query->stmt->fetchAll(PDO::FETCH_ASSOC);
            return $categories;
        }
    }

    public function buildCategoriesTree (array $categories, $parentId = 0, $idKey = 'id') {
        $branch = array();
        foreach ($categories as $element) {
            if ($element['parent'] == $parentId) {
                $children = $this->buildCategoriesTree($categories, $element[$idKey]);
                if ($children) {
                    $element['children'] = $children;
                }
                // $elem_id = $element['id'];
                unset($element['id']);
                unset($element['parent']);
                $branch[] = $element;
                unset($element);
            }
        }
        return $branch;
    }

    public function getVendors ($search = '') {
        $query = $this->modx->newQuery("msVendor");
        $query->select(array(
            "`msVendor`.*"
        ));
        if($search){
            $query->where(array(
                "`msVendor`.`name`:LIKE" => "%{$search}%"
            ));
        }
        if ($query->prepare() && $query->stmt->execute()) {
            $vendors = $query->stmt->fetchAll(PDO::FETCH_ASSOC);
            return $vendors;
        }
    }

    public function getMatrix ($id, $search = '') {
        $query = $this->modx->newQuery("slStoresMatrix");
        $query->select(array(
            "`slStoresMatrix`.*"
        ));
        if($search){
            $query->where(array(
                "`slStoresMatrix`.`name`:LIKE" => "%{$search}%"
            ));
        }
        if($id){
            $query->where(array(
                "`slStoresMatrix`.`store_id`:=" => $id
            ));
        }
        $query->prepare();
        $this->modx->log(1, $query->toSQL());
        if ($query->prepare() && $query->stmt->execute()) {
            $matrix = $query->stmt->fetchAll(PDO::FETCH_ASSOC);
            return $matrix;
        }
    }

    public function getBrands ($id, $search = '') {
        $query = $this->modx->newQuery("slStoresBrands");
        $query->leftJoin("msVendor", "Vendor", "Vendor.id = slStoresBrands.brand_id");
        $query->select(array(
            "`Vendor`.`name` as name",
            "`slStoresBrands`.brand_id as id"
        ));
        if($search){
            $query->where(array(
                "`Vendor`.`name`:LIKE" => "%{$search}%"
            ));
        }
        if($id){
            $query->where(array(
                "`slStoresBrands`.`store_id`:=" => $id
            ));
        }
        $query->prepare();
        $this->modx->log(1, $query->toSQL());
        if ($query->prepare() && $query->stmt->execute()) {
            $brands = $query->stmt->fetchAll(PDO::FETCH_ASSOC);
            return $brands;
        }
    }

    public function getStores ($search = '') {
        $query = $this->modx->newQuery("slStores");
        $query->select(array(
            "`slStores`.*"
        ));
        if($search){
            $query->where(array(
                "`slStores`.`name`:LIKE" => "%{$search}%"
            ));
        }
        $query->where(array(
            "`slStores`.`active`:=" => 1,
            "AND:`slStores`.`type`:=" => 1
        ));
        if ($query->prepare() && $query->stmt->execute()) {
            $stores = $query->stmt->fetchAll(PDO::FETCH_ASSOC);
            return $stores;
        }
    }

    public function getWarehouses ($search = '') {
        $query = $this->modx->newQuery("slStores");
        $query->select(array(
            "`slStores`.*"
        ));
        if($search){
            $query->where(array(
                "`slStores`.`name`:LIKE" => "%{$search}%"
            ));
        }
        $query->where(array(
            "`slStores`.`active`:=" => 1,
            "AND:`slStores`.`type`:=" => 2
        ));
        if ($query->prepare() && $query->stmt->execute()) {
            $stores = $query->stmt->fetchAll(PDO::FETCH_ASSOC);
            return $stores;
        }
    }

    public function linkDocsProducts($doc_id, $products){
        $response = array();
        $criteria = array(
            "doc_id" => $doc_id
        );
        // remove old products
        $old_prods = $this->modx->getCollection('slStoreDocsProducts', $criteria);
        foreach ($old_prods as $old_prod) {
            $old_prod->remove();
        }
        foreach ($products as $k => $product) {
            $prod = $this->modx->newObject('slStoreDocsProducts');
            $prod->set("doc_id", $doc_id);
            $prod->set("guid", $product['guid']);
            $prod->set("count", abs($product['count']));
            $prod->set("price", $product['price']);
            if ($product['count'] <= 0) {
                $prod->set("type", 2);
            } else {
                $prod->set("type", 1);
            }
            $prod->set('createdon', time());
            // check exist product
            $guid = '';
            if($product['guid']){
                $guid = $product['guid'];
            }
            if($product['GUID']){
                $guid = $product['GUID'];
            }
            $remain = false;
            if($guid){
                $criteria = array(
                    "guid" => $guid
                );
                $remain = $this->modx->getObject('slStoresRemains', $criteria);
            }
            if ($remain) {
                $prod->set("remain_id", $remain->get('id'));
                $p = $remain->get("price");
                if ($p != $product['price']) {
                    $response[$product['guid']][] = "Цена товара отличается от системной.";
                }
            } else {
                // TODO: создаем или нет?
                $response[$product['guid']][] = "Товара нет в системе.";
            }
            $prod->save();
        }
        return $response;
    }

    /**
     * Импорт документов
     *
     * @param $data
     * @return array|array[]
     */
    public function importDocs($data){
        $response = array(
            'success_info' => array(),
            'failed_info' => array(),
            'products_info' => array()
        );
        $store = $this->getStore($data['key'], "date_docs_update");
        if($data['version']){
            $this->setVersion($store['id'], $data['version']);
        }
        foreach ($data['docs'] as $k => $doc) {
            if($doc['GUID']){
                $key = $doc['GUID'];
            }else{
                $key = $k;
            }
            // если удаление
            if ($doc['delete']) {
                $criteria = array(
                    "guid" => $key
                );
                $criteria['store_id'] = $store['id'];
                $doc = $this->modx->getObject('slStoreDocs', $criteria);
                if ($doc) {
                    if (!$doc->remove()) {
                        $response['failed_info'][] = array(
                            'guid' => $key,
                            'message' => "Произошла ошибка при удалении документа"
                        );
                    } else {
                        $response['success_info'][] = array(
                            'guid' => $key,
                            'message' => 'Документ удален'
                        );
                    }
                } else {
                    $response['failed_info'][] = array(
                        'guid' => $key,
                        'message' => "Указанный документ не найден"
                    );
                }
            } else {
                // создание
                $required = array('number', 'date', 'products');
                $error = false;
                foreach ($required as $req) {
                    if (!$doc[$req]) {
                        $response['failed_info'][] = array(
                            'guid' => $key,
                            'message' => "Не все обязательные поля переданы. Проверьте наличие: " . implode(",", $required)
                        );
                        $error = true;
                    }
                }
                if (!$error) {
                    // create doc
                    $criteria = array(
                        "guid" => $key,
                        'store_id' => $store['id']
                    );
                    // check dublicate
                    $document = $this->modx->getObject('slStoreDocs', $criteria);
                    if ($document) {
                        $document->set('guid', $key);
                        $document->set('phone', $doc['phone']);
                        $document->set('doc_number', $doc['number']);
                        $document->set('date', strtotime($doc['date']));
                        if($data['base_GUID']){
                            $document->set("base_guid", $data['base_GUID']);
                        }
                        $document->set('createdon', time());
                        $document->set('store_id', $store['id']);
                        if ($document->save()) {
                            $doc_id = $document->get('id');
                            $response[$key]['products_info'] = $this->linkDocsProducts($doc_id, $doc['products']);
                            $response['success_info'][] = array(
                                'guid' => $key,
                                'message' => 'Документ обновлен'
                            );
                        } else {
                            $response['failed_info'][] = array(
                                'guid' => $key,
                                'message' => "Произошла ошибка при обновлении документа. Check API!"
                            );
                        }
                    } else {
                        // create new
                        $document = $this->modx->newObject('slStoreDocs');
                        $document->set('guid', $key);
                        $document->set('phone', $doc['phone']);
                        if($data['base_GUID']){
                            $document->set("base_guid", $data['base_GUID']);
                        }
                        $document->set('doc_number', $doc['number']);
                        $document->set('date', strtotime($doc['date']));
                        $document->set('createdon', time());
                        $document->set('store_id', $store['id']);
                        if ($document->save()) {
                            // link products
                            $doc_id = $document->get('id');
                            $response[$key]['products_info'] = $this->linkDocsProducts($doc_id, $doc['products']);
                            $response['success_info'][] = array(
                                'guid' => $key,
                                'message' => 'Документ создан'
                            );
                        } else {
                            $response['failed_info'][] = array(
                                'guid' => $key,
                                'message' => "Произошла ошибка при создании документа. Check API!"
                            );
                        }
                    }

                    if($doc['phone'] && !$doc['isReturn']){
                        $phone = $this->sl->tools->phoneFormat($doc['phone']);


                        $c = $this->modx->newQuery('modUser');
                        $c->leftJoin('modUserProfile', 'Profile');
                        $filter = array('Profile.mobilephone:=' => $phone);
                        $c->where($filter);
                        $c->select('modUser.id');
                        if (!$customer = $this->modx->getObject('modUser', $c)) {
                            $customer = $this->modx->newObject('modUser', array('username' => $phone, 'password' => md5(rand())));
                            $profile = $this->modx->newObject('modUserProfile', array(
                                'email' => $phone,
                                'fullname' => $phone,
                                'mobilephone' => $phone
                            ));
                            $customer->addOne($profile);
                            $setting = $this->modx->newObject('modUserSetting');
                            $setting->fromArray(array(
                                'key' => 'cultureKey',
                                'area' => 'language',
                                'value' => $this->modx->getOption('cultureKey', null, 'en', true),
                            ), '', true);
                            $customer->addMany($setting);
                            if (!$customer->save()) {
                                $customer = null;
                            } elseif ($groups = $this->modx->getOption('ms2_order_user_groups', null, false)) {
                                $groupRoles = array_map('trim', explode(',', $groups));
                                foreach ($groupRoles as $groupRole) {
                                    $groupRole = explode(':', $groupRole);
                                    if (count($groupRole) > 1 && !empty($groupRole[1])) {
                                        if (is_numeric($groupRole[1])) {
                                            $roleId = (int)$groupRole[1];
                                        } else {
                                            $roleId = $groupRole[1];
                                        }
                                    } else {
                                        $roleId = null;
                                    }
                                    $customer->joinGroup($groupRole[0], $roleId);
                                }
                            }
                        }

                        $user_id = $customer->get('id');

                        //Есть ли у пользователя бонусный счёт?
                        $bonus = $this->modx->getObject('slBonusAccount', array("user_id" => $user_id));

                        //Если нет, создаём
                        if(!$bonus){
                            $bonus = $this->modx->newObject("slBonusAccount");
                            $bonus->set("user_id", $user_id);
                            $bonus->set("value", 0);
                            $bonus->save();
                        }

                        $q = $this->modx->newQuery("slStoreDocsProducts");
                        $q->where(array(
                            "slStoreDocsProducts.doc_id:=" => $document->get('id'),
                        ));
                        $q->select(array(
                            "slStoreDocsProducts.remain_id",
                            "slStoreDocsProducts.price",
                            "slStoreDocsProducts.count"
                        ));
                        if($q->prepare() && $q->stmt->execute()){
                            $order_products = $q->stmt->fetchAll(PDO::FETCH_ASSOC);

                            foreach ($order_products as $key => $product){
                                //Количество бонусов за покупку
                                $bonus_count = round((($product['price'] * $product['count']) / 100) * $this->modx->getOption('shoplogistic_bonus_percent_store'));

                                $dateBonus = date('Y-m-d H:i:s');

                                //Новая операция начисления
                                $bonusOperations = $this->modx->newObject("slBonusOperations");
                                $bonusOperations->set("bonus_id", $bonus->get("id"));
                                $bonusOperations->set("type", "plus");
                                $bonusOperations->set("value", $bonus_count);
                                $bonusOperations->set("comment", "Начисление бонусов за покупку товара в магазине. Документ: " . $document->get('id'));
                                $bonusOperations->set("context_type", "store");
                                $bonusOperations->set("date", $dateBonus);
                                $bonusOperations->set("store_id", $store['id']);
                                $bonusOperations->save();

                                $bonus->set("value", $bonus->get("value") + $bonus_count);
                                $bonus->save();
                            }
                        }
                    }
                }
            }
        }
        return $response;
    }

    public function getRemain ($store_id, $guid) {
        $criteria = array(
            'guid' => $guid,
            'store_id' => $store_id
        );
        $o = $this->modx->getObject('slStoresRemains', $criteria);
        if($o){
            return $o->get("id");
        }else{
            return false;
        }
    }

    /**
     * Смена статусов наличия при манипуляциях с магазином
     *
     * @param $store_id
     * @param $available
     * @return void
     */
    public function changeAvailableStatus ($store_id, $available) {
        $query = $this->modx->newQuery("slStoresRemains");
        $query->where(array(
            "slStoresRemains.store_id:=" => $store_id,
            "slStoresRemains.published:=" => 1,
            "AND:slStoresRemains.product_id:>" => 0
        ));
        $query->select(array("slStoresRemains.product_id"));
        if($query->prepare() && $query->stmt->execute()){
            $products = $query->stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach($products as $product){
                // проверяем остаток в других магазинах, если нужно проставить НЕ в наличии
                if(($available == 99) || ($product['remains'] == 0 && $product['price'] == 0)){
                    if($this->getAvailableStatus($product['product_id'])){
                        $status = 1;
                    }else{
                        $status = 99;
                    }
                }else{
                    $status = 1;
                }
                $this->setProductStatus($product['product_id'], $status);
            }
        }
    }

    /**
     * Генерация отчета о сопоставлении
     *
     * @param $store_id
     * @return void
     */
    public function generateCopoReport($store_id, $generate = false){
        $store = $this->sl->getObject($store_id);
        if($store){
            $name = "Отчет по сопоставлению магазина {$store['name']}";
            $copoReport = $this->modx->getObject("slStoresRemainsReports", array("store_id" => $store_id));
            if(!$copoReport){
                $copoReport = $this->modx->newObject("slStoresRemainsReports");
                $copoReport->set("store_id", $store_id);
                $copoReport->set("createdon", time());
            }else{
                $copoReport->set("updatedon", time());
            }
            $copoReport->set("name", $name);
        }
        if($generate){
            $copoReport->save();
            $this->generateCopoVendorsReport($copoReport->get("id"));
        }
        return $copoReport->toArray();
    }

    /**
     * Генерация отчета по сопоставлению побрендово
     *
     * @param $report_id
     * @return void
     */
    public function generateCopoVendorsReport($report_id){
        $output = array();
        $copoReport = $this->sl->getObject($report_id, "slStoresRemainsReports");
        if($copoReport){
            $brands = array();
            $criteria = array(
                "report_id:=" => $report_id
            );
            $this->modx->removeCollection("slStoresRemainsVendorReports", $criteria);
            $store_id = $copoReport["store_id"];
            $query = $this->modx->newQuery("slStoresRemains");
            $query->leftJoin("msProductData", "msProductData", "msProductData.id = slStoresRemains.product_id");
            $query->leftJoin("modResource", "modResource", "modResource.id = slStoresRemains.product_id");
            $query->select(array("DISTINCT(slStoresRemains.brand_id) AS vendor"));
            $query->where(array("slStoresRemains.store_id:=" => $store_id));
            // $query->limit(30, 10);
            if($query->prepare() && $query->stmt->execute()){
                $results = $query->stmt->fetchAll(PDO::FETCH_ASSOC);
                foreach($results as $v){
                    $criteria = array(
                        "vendor_id:=" => $v["vendor"],
                        "report_id:=" => $report_id
                    );
                    $vendor = $this->modx->getObject("slStoresRemainsVendorReports", $criteria);
                    if(!$vendor){
                        $vendor = $this->modx->newObject("slStoresRemainsVendorReports");
                        $vendor->set("vendor_id", $v["vendor"]);
                        $vendor->set("report_id", $report_id);
                        $vendor->set("createdon", time());
                    }else{
                        $vendor->set("updatedon", time());
                    }

                    // кол-во найденных
                    $query = $this->modx->newQuery("slStoresRemains");
                    $query->where(array(
                        "slStoresRemains.store_id:=" => $store_id,
                        "slStoresRemains.brand_id:=" => $v["vendor"],
                    ));
                    $find_count = $this->modx->getCount("slStoresRemains", $query);
                    $query->where(array("slStoresRemains.remains:>" => 0));
                    $find_count_in_stock = $this->modx->getCount("slStoresRemains", $query);
                    $vendor->set("find", $find_count);
                    $vendor->set("find_in_stock", $find_count_in_stock);

                    // кол-во идентифицированных
                    $query = $this->modx->newQuery("slStoresRemains");
                    $query->where(array(
                        "slStoresRemains.store_id:=" => $store_id,
                        "slStoresRemains.brand_id:=" => $v["vendor"],
                        "slStoresRemains.product_id:>" => 0,
                    ));
                    $ident_count = $this->modx->getCount("slStoresRemains", $query);
                    $query->where(array("slStoresRemains.remains:>" => 0));
                    $ident_in_stock = $this->modx->getCount("slStoresRemains", $query);
                    $vendor->set("identified", $ident_count);
                    $vendor->set("identified_in_stock", $ident_in_stock);

                    // Процент найденных
                    if ($find_count) {
                        $percent = $ident_count / $find_count * 100;
                        $vendor->set("percent_identified", $percent);
                    }
                    if ($find_count_in_stock) {
                        $percent = $ident_in_stock / $find_count_in_stock * 100;
                        $vendor->set("percent_identified_in_stock", $percent);
                    }

                    // сумма товара
                    $query = $this->modx->newQuery("slStoresRemains");
                    $query->where(array(
                        "slStoresRemains.store_id:=" => $store_id,
                        "slStoresRemains.brand_id:=" => $v["vendor"],
                    ));
                    $query->select(array("SUM(slStoresRemains.remains * slStoresRemains.price) as summ"));
                    if($v["vendor"] == 0){
                        $query->prepare();
                        $this->sl->tools->log($query->toSQL(), "generate_copo");
                    }
                    if($query->prepare() && $query->stmt->execute()){
                        $summ = $query->stmt->fetch(PDO::FETCH_ASSOC);
                        if($summ["summ"]){
                            $vendor->set("summ", $summ["summ"]);
                        }
                    }

                    // сумма товара сопоставленного
                    $query = $this->modx->newQuery("slStoresRemains");
                    $query->where(array(
                        "slStoresRemains.store_id:=" => $store_id,
                        "slStoresRemains.brand_id:=" => $v["vendor"],
                        "slStoresRemains.product_id:>" => 0
                    ));
                    $query->select(array("SUM(slStoresRemains.remains * slStoresRemains.price) as summ"));
                    if($query->prepare() && $query->stmt->execute()){
                        $summ = $query->stmt->fetch(PDO::FETCH_ASSOC);
                        if($summ["summ"]){
                            $vendor->set("summ_copo", $summ["summ"]);
                        }
                    }

                    if($v["vendor"]){
                        // кол-во карточек
                        $q = $this->modx->newQuery("msProductData");
                        $q->where(array("vendor:=" => $v["vendor"]));
                        $total = $this->modx->getCount('msProductData', $q);
                        $vendor->set("cards", $total);
                    }else{
                        $vendor->set("identified", 0);
                        $vendor->set("percent_identified", 0);
                        $vendor->set("identified_in_stock", 0);
                        $vendor->set("percent_identified_in_stock", 0);
                    }

                    $vendor->save();
                    $output[$v["vendor"]] = $vendor->toArray();
                    $brands[] = $v["vendor"];
                }
                // очистка от брендов, которые мы не нашли
                $query = $this->modx->newQuery("slStoresRemainsVendorReports");
                $query->where(array(
                    "slStoresRemainsVendorReports.vendor_id:NOT IN" => $brands,
                    "slStoresRemainsVendorReports.report_id:=" => $report_id
                ));
                $query->select(array("slStoresRemainsVendorReports.id"));
                if($query->prepare() && $query->stmt->execute()){
                    $results = $query->stmt->fetchAll(PDO::FETCH_ASSOC);
                    foreach($results as $brand) {
                        $obj = $this->modx->getObject('slStoresRemainsVendorReports', $brand["id"]);
                        if (!$obj->remove()) {
                            $this->modx->log(1, "Ошибка удаления объекта slStoresRemainsVendorReports {$brand["id"]}");
                        }
                    }
                }
            }
        }
        return $output;
    }

    /**
     * Установка значения наличия
     *
     * @param $product_id
     * @param $available
     * @return void
     */
    public function setProductStatus($product_id, $available){
        $data = array(
            "available" => $available
        );
        if ($available == 99){
            $backtrace = $this->sl->tools->backtrace();
            $this->sl->tools->log(print_r($backtrace, 1), "available_backtrace");
        }
        $this->sl->api->update("msProductData", $data, $product_id);
    }

    /**
     * Проверка остатка в активных магазинах
     *
     * @param $product_id
     * @return bool
     */
    public function getAvailableStatus($product_id){
        $query = $this->modx->newQuery("slStoresRemains");
        $query->leftJoin("slStores", "slStores", "slStores.id = slStoresRemains.store_id");
        $query->where(array(
            "slStoresRemains.product_id:=" => $product_id,
            "slStoresRemains.remains:>" => 0,
            "slStoresRemains.price:>" => 0,
            "slStoresRemains.published:=" => 1,
            "slStores.active:=" => 1
        ));
        $query->select(array("slStoresRemains.*"));
        if($query->prepare() && $query->stmt->execute()){
            $remain = $query->stmt->fetch(PDO::FETCH_ASSOC);
            if($remain["remains"] > 0){
                return true;
            }
        }
        return false;
    }

    /**
     * Импорт остатков на определенный день
     *
     * @param $data
     * @return int[]
     * @throws Exception
     */
    public function importRemainsCheckpoints($data) {
        $response = array(
            'created' => 0,
            'failed' => 0,
            'updated' => 0
        );
        $prices = array();
        if($data['promo_prices_list']){
            foreach($data['promo_prices_list'] as $price){
                $prices[$price['price_id']] = array(
                    "name" => $price['price_Name']
                );
            }
        }
        $store = $this->getStore($data['key'], "remains_checkpoint_update");
        if($store){
            foreach ($data['product_archive'] as $k => $archive) {
                $today = new DateTime();
                $dp = new DateTime($archive['date']);
                $dd = $dp->getTimestamp();
                $today->setTime(0,0,0);
                $date_from = $today->getTimestamp();
                $today->setTime(23,59,59);
                $date_to = $today->getTimestamp();
                foreach($archive['products'] as $product){
                    if($data["base_GUID"]){
                        $product["base_GUID"] = $data["base_GUID"];
                    }
                    $remain_id = $this->getRemain($store['id'], $product['GUID']);
                    if(!$remain_id){
                        $remain_id = $this->importRemainSingle($store['id'], $product);
                    }
                    if($product['catalog_id']){
                        $remain = $this->modx->getObject("slStoresRemains", $remain_id);
                        if($remain){
                            $remain->set("updatedon", time());
                            $remain->set("catalog_guid", $product['catalog_id']);
                            $remain->save();
                        }
                    }
                    // если дата сегодня
                    if($dd >= $date_from && $dd <= $date_to){
                        $remain = $this->modx->getObject("slStoresRemains", $remain_id);
                        $remain->set("updatedon", time());
                        $remain->set("count", $product['count_current']);
                        $remain->set("price", $product['price']);
                        $remain->set("remains", $product['count_current']);
                        $remain->set("reserved", 0);
                        $remain->set("available",$product['count_free']);
                        $remain->save();
                        if($product['promo_prices']){
                            foreach($product['promo_prices'] as $pr){
                                if(!$product_price = $this->modx->getObject("slStoresRemainsPrices", array("remain_id" => $remain_id, "key" => $pr["price_id"]))){
                                    $product_price = $this->modx->newObject("slStoresRemainsPrices");
                                }
                                $product_price->set("remain_id", $remain_id);
                                $product_price->set("name", $prices[$pr["price_id"]]['name']);
                                $product_price->set("key", $pr["price_id"]);
                                $product_price->set("price", $pr["value"]);
                                $product_price->set("active", 1);
                                $product_price->save();
                            }
                        }
                    }
                    if($remain_id){
                        $criteria = array(
                            "remain_id" => $remain_id,
                            "date:>=" => date('Y-m-d H:i:s', $date_from),
                            "date:<=" => date('Y-m-d H:i:s', $date_to),
                        );
                        $setter = $this->modx->getObject("slStoresRemainsHistory", $criteria);
                        if(!$setter){
                            $setter = $this->modx->newObject("slStoresRemainsHistory");
                            $setter->set("remain_id", $remain_id);
                            $setter->set("createdon", time());
                            $setter->set("date", $dd);
                            $response['created']++;
                        }else{
                            $setter->set("updatedon", time());
                            $response['updated']++;
                        }
                        $setter->set("count", $product['count_current']);
                        $setter->set("price", $product['price']);
                        $setter->set("remains", $product['count_current']);
                        $setter->set("reserved", 0);
                        $setter->set("available",$product['count_free']);
                        $setter->save();
                    }
                }
            }
        }
        return $response;
    }

    /**
     * Берем дни Out Of Stock за месяц
     *
     * @param $remain_id
     * @return void
     */
    public function getOutOfStockDays($remain_id){
        $days = 0;
        $today = date_create();
        $month_ago = date_create("-1 MONTH");
        date_time_set($month_ago, 00, 00);
        // проверяем начало промежутка
        $query = $this->modx->newQuery("slStoresRemainsHistory");
        $query->where(array(
            "slStoresRemainsHistory.remain_id" => $remain_id,
            "slStoresRemainsHistory.date:>=" => date_format($month_ago, 'Y-m-d H:i:s'),
        ));
        $query->sortby("slStoresRemainsHistory.date", "DESC");
        $query->select(array("slStoresRemainsHistory.*"));
        if($query->prepare() && $query->stmt->execute()){
            $time = $query->stmt->fetch(PDO::FETCH_ASSOC);
            if($time){
                if($time["remains"] == 0){
                    // если нашли нулевой остаток, то ищем до какого числа был
                    $query = $this->modx->newQuery("slStoresRemainsHistory");
                    $query->where(array(
                        "slStoresRemainsHistory.remain_id" => $remain_id,
                        "slStoresRemainsHistory.date:>=" => date_format($month_ago, 'Y-m-d H:i:s'),
                        "slStoresRemainsHistory.remains:>" => 0,
                    ));
                    $query->sortby("slStoresRemainsHistory.date", "ASC");
                    $query->select(array("slStoresRemainsHistory.*"));
                    if($query->prepare() && $query->stmt->execute()){
                        $time = $query->stmt->fetch(PDO::FETCH_ASSOC);
                        if($time){
                            $days += $this->sl->tools->getDiffDates(date_format($month_ago, 'Y-m-d H:i:s'), $time["date"]);
                        }
                    }
                }
            }
        }

        // остатки с нулем внутри промежутка
        $query = $this->modx->newQuery("slStoresRemainsHistory");
        $query->where(array(
            "slStoresRemainsHistory.remain_id" => $remain_id,
            "slStoresRemainsHistory.date:>=" => date_format($month_ago, 'Y-m-d H:i:s'),
            "slStoresRemainsHistory.date:<=" => date_format($today, 'Y-m-d H:i:s'),
            "slStoresRemainsHistory.remains:=" => 0,
        ));
        $query->select(array("slStoresRemainsHistory.*"));
        if($query->prepare() && $query->stmt->execute()){
            $times = $query->stmt->fetchAll(PDO::FETCH_ASSOC);
            if($times){
                foreach($times as $time){
                    // теперь узнаем когда остаток был не нулевой
                    $q = $this->modx->newQuery("slStoresRemainsHistory");
                    $q->where(array(
                        "slStoresRemainsHistory.remain_id" => $remain_id,
                        "slStoresRemainsHistory.date:>=" => $time["date"],
                        "slStoresRemainsHistory.date:<=" => date_format($today, 'Y-m-d H:i:s'),
                        "slStoresRemainsHistory.remains:>" => 0,
                    ));
                    $q->sortby("slStoresRemainsHistory.date", "ASC");
                    $q->select(array("slStoresRemainsHistory.*"));
                    if($q->prepare() && $q->stmt->execute()){
                        $tt = $q->stmt->fetch(PDO::FETCH_ASSOC);
                        // узнали ближайший, если он есть
                        if($tt){
                            $days += $this->sl->tools->getDiffDates($time["date"], $tt["date"]);
                        }else{
                            $days += $this->sl->tools->getDiffDates($time["date"], date_format($today, 'Y-m-d H:i:s'));
                        }
                    }
                }
            }
        }
        return $days;
    }

    /**
     * считаем скорость продаж за последний месяц
     *
     * @param $remain_id
     * @return array
     */
    public function getPurchaseSpeed($remain_id){
        $this->modx->log(1, "_PURCHASES_");
        $remain = $this->sl->getObject($remain_id, "slStoresRemains");
        $today = date_create();
        $month_ago = date_create("-1 MONTH");
        date_time_set($month_ago, 00, 00);
        $query = $this->modx->newQuery("slStoreDocsProducts");
        $query->leftJoin("slStoreDocs", "slStoreDocs", "slStoreDocs.id = slStoreDocsProducts.doc_id");
        $query->where(array(
            "slStoreDocsProducts.remain_id" => $remain_id,
            "slStoreDocs.date:>=" => date_format($month_ago, 'Y-m-d H:i:s'),
            "slStoreDocs.date:<=" => date_format($today, 'Y-m-d H:i:s'),
        ));
        $query->select("slStoreDocsProducts.remain_id, SUM(slStoreDocsProducts.count) as sales");
        $query->groupby("slStoreDocsProducts.remain_id");
        if($query->prepare() && $query->stmt->execute()){
            $result = $query->stmt->fetch(PDO::FETCH_ASSOC);
            $this->modx->log(1, print_r($result, 1));
            if($result){
                if($result["sales"]){
                    $all_days = $this->sl->tools->getDiffDates(date_format($month_ago, 'Y-m-d H:i:s'), date_format($today, 'Y-m-d H:i:s'));
                    $this->modx->log(1, "Все дни: ".$all_days);
                    $outOfStockDays = $this->getOutOfStockDays($remain_id);
                    $this->modx->log(1, "Out Of Stock дни: ".$outOfStockDays);
                    $times = $all_days - $outOfStockDays;
                    $result["out_of_stock"] = $outOfStockDays;
                    $result["times"] = $times;
                    $this->modx->log(1, "Times: ".$times);
                    if($times > 0){
                        $result['speed'] = $result['sales'] / $times;
                        $result['speed'] = round($result['speed'], 2);
                    }else{
                        $result['speed'] = 0;
                    }
                }else{
                    $result['speed'] = 0;
                }
                $this->modx->log(1, "Speed: ".$result['speed']);
            }else{
                $result['remain_id'] = $remain_id;
                $result['sales'] = 0;
                $result['speed'] = 0;
            }
            $result['price'] = $remain['price'];
            // дополнительно выставляем дней с out of stock, если кол-во равно нулю

            if($remain["remains"] == 0){
                // если сейчас остаток 0, ищем ближайшую точку с положительным остатком
                $q = $this->modx->newQuery("slStoresRemainsHistory");
                $q->where(array(
                    "slStoresRemainsHistory.remain_id" => $remain_id,
                    "slStoresRemainsHistory.date:<=" => date_format($today, 'Y-m-d H:i:s'),
                    "slStoresRemainsHistory.date:>=" => date_format($month_ago, 'Y-m-d H:i:s'),
                    "slStoresRemainsHistory.remains:>" => 0,
                ));
                $q->sortby("slStoresRemainsHistory.date", "DESC");
                $q->select(array("slStoresRemainsHistory.*"));
                if($q->prepare() && $q->stmt->execute()) {
                    $tt = $q->stmt->fetch(PDO::FETCH_ASSOC);
                    if($tt){
                        $daysOut = $this->sl->tools->getDiffDates(date_format($today, 'Y-m-d H:i:s'), $tt["date"]);
                        if($daysOut > 0){
                            $result['out_of_stock_days'] = $daysOut;
                            $result['no_money'] = $remain['price'] * $daysOut * $result['speed'];
                        }else{
                            $result['out_of_stock_days'] = 0;
                            $result['no_money'] = 0;
                        }
                    }else{
                        $result['out_of_stock_days'] = 0;
                        $result['no_money'] = 0;
                    }
                }
            }else{
                $result['out_of_stock_days'] = 0;
                $result['no_money'] = 0;
            }
            return $result;
        }
    }

    /**
     * Берем актуальное дерево категорий (рекурсивно)
     *
     * @param $parent
     * @return array
     */
    public function getActualProductCategories($vendor){
        $categories = array();
        $query = $this->modx->newQuery("msProductData");
        $query->leftJoin("modResource", "modResource", "modResource.id = msProductData.id");
        $query->select(array("DISTINCT(modResource.parent) as id"));
        $query->where(array(
            "msProductData.vendor:=" => $vendor
        ));
        if ($query->prepare() && $query->stmt->execute()) {
            $cats = $query->stmt->fetchAll(PDO::FETCH_ASSOC);
            if($cats){
                foreach($cats as $cat){
                    $cat = $this->sl->getObject($cat["id"], "modResource");
                    if($cat){
                        $categories[] = $cat;
                    }
                }
            }
        }
        return $categories;
    }

    /**
     * Генерация YML файлов магазина
     *
     * @param $store_id
     * @return array[]
     */
    public function generateStoreYML($store_id){
        $this->modx->query( '
          SET `low_priority_updates` = `ON`
        ' );
        $output = array(
            'categories' => array(),
            'vendors' => array(),
            'products' => array()
        );
        $store = $this->sl->store->getStore($store_id);
        if($store){
            // проверяем остатки магазина
            $query = $this->modx->newQuery("slStoresRemains");
            $query->select(array("slStoresRemains.*"));
            $query->where(array("store_id:=" => $store_id));
            $all_data = $this->modx->getCount("slStoresRemains", $query);
            if($all_data){
                $file_path = $this->modx->getOption("base_path").'assets/files/organization/'.$store['id'].'/ymls/';
                $filename = $file_path.'remains.yml';
                $file_url = '/assets/files/organization/'.$store['id'].'/ymls/remains.yml';
                if(!file_exists($file_path)){
                    mkdir($file_path, 0755, true);
                }
                $fd = fopen($filename, 'w');
                $base_content = $this->sl->pdoTools->getChunk("@FILE chunks/catalog_yml_base.tpl", $output);
                fwrite($fd, $base_content);
                fseek($fd, 0, SEEK_END);
                $limit = 500;
                for($i = 0; $i <= $all_data; $i += $limit) {
                    $query = $this->modx->newQuery("slStoresRemains");
                    $query->select(array("slStoresRemains.*"));
                    $query->where(array("store_id:=" => $store_id));
                    $query->limit($limit, $i);
                    if ($query->prepare() && $query->stmt->execute()) {
                        $products = $query->stmt->fetchAll(PDO::FETCH_ASSOC);
                        foreach ($products as $key => $product) {
                            // записываем в файл
                            $offer_content = "\r\n" . $this->sl->pdoTools->getChunk("@FILE chunks/catalog_yml_remain.tpl", $products[$key]) . "\r\n";
                            fwrite($fd, $offer_content);
                        }
                    }
                }
                $end_file = "</offers>\r\n</shop>\r\n</yml_catalog>";
                fwrite($fd, $end_file);
                fclose($fd);
                $st = $this->modx->getObject("slStores", $store['id']);
                if($st){
                    $st->set("yml_file", $file_url);
                    $st->save();
                }
            }
        }
        $this->modx->query( '
           SET `low_priority_updates` = `OFF`
        ' );
        return $output;
    }

    /**
     * Генерация YML файлов каталога товаров по брендам
     *
     * @return array[]
     */
    public function generateVendorsYMLs(){
        $this->modx->query( '
          SET `low_priority_updates` = `ON`
        ' );
        $output = array(
            'categories' => array(),
            'vendors' => array(),
            'products' => array()
        );
        $output['categories'] = $this->getActualProductCategories(2);
        $cats = array();
        foreach($output['categories'] as $c){
            $cats[] = $c['id'];
        }
        $query = $this->modx->newQuery("msVendor");
        $query->select(array("msVendor.id as id, msVendor.name as name"));
        $query->where(array("msVendor.id:IN" => array(1,3)));
        $all_data = $this->modx->getCount("msVendor", $query);
        // ограничение на память
        $limit = 500;
        for($i = 0; $i <= $all_data; $i += $limit) {
            $query = $this->modx->newQuery("msVendor");
            $query->select(array("msVendor.id as id, msVendor.name as name"));
            $query->where(array("msVendor.id:IN" => array(1,3)));
            $query->limit($limit, $i);
            if ($query->prepare() && $query->stmt->execute()) {
                $vendors = $query->stmt->fetchAll(PDO::FETCH_ASSOC);
                $output['vendors'] = array_merge($output['vendors'], $vendors);
                // получили производителей, создаем файл и записываем туда товары

                foreach($vendors as $vendor){
                    $products_data = 0;
                    $query = $this->modx->newQuery("msProductData");
                    $query->leftJoin("modResource", "modResource", "modResource.id = msProductData.id");
                    $query->select(array("COUNT(*) as count"));
                    $query->where(array(
                        "msProductData.vendor:=" => $vendor['id'],
                        "AND:modResource.parent:IN" => $cats
                    ));
                    if ($query->prepare() && $query->stmt->execute()) {
                        $prods = $query->stmt->fetch(PDO::FETCH_ASSOC);
                        $products_data = $prods['count'];
                    }
                    $limit = 500;
                    // проверяем есть ли товары и начинаем собирать файл
                    if($products_data){
                        // assets/files/ymls/
                        $res = $this->modx->newObject("modResource");
                        $name = $res->cleanAlias($vendor['name']);
                        $file_path = $this->modx->getOption("base_path").'assets/files/ymls/';
                        $filename = $file_path.$name.'_'.$vendor['id'].'.yml';
                        if(!file_exists($file_path)){
                            mkdir($file_path, 0755, true);
                        }
                        // if (!file_exists($filename)) {
                            // открываем файл и пишем данные
                            $fd = fopen($filename, 'w');
                            $base_content = $this->sl->pdoTools->getChunk("@FILE chunks/catalog_yml_base.tpl", $output);
                            fwrite($fd, $base_content);
                            fseek($fd, 0, SEEK_END);
                            for ($i = 0; $i <= $products_data; $i += $limit) {
                                $query = $this->modx->newQuery("msProductData");
                                $query->leftJoin("modResource", "modResource", "modResource.id = msProductData.id");
                                $query->select(array("modResource.pagetitle, modResource.introtext, modResource.content, modResource.parent, msProductData.*"));
                                $query->where(array(
                                    "msProductData.vendor:=" => $vendor['id'],
                                    "AND:modResource.parent:IN" => $cats
                                ));
                                $query->limit($limit, $i);
                                if ($query->prepare() && $query->stmt->execute()) {
                                    $products = $query->stmt->fetchAll(PDO::FETCH_ASSOC);
                                    foreach ($products as $key => $product) {
                                        $file_url = "assets/files/products/" . $product['id'] . '/';
                                        $dir = $this->modx->getOption("base_path") . $file_url;
                                        $files = array();
                                        if ($handle = opendir($dir)) {
                                            $index = 1;
                                            while (false !== ($file = readdir($handle))) {
                                                if ($file != "." && $file != "..") {
                                                    $path_parts = pathinfo($file_url . $file);
                                                    $file_data = array(
                                                        "url" => $file_url . $file,
                                                        "name" => $product['pagetitle'],
                                                        "product_id" => $product['id'],
                                                        "file_id" => $product['id'] . '_' . $index
                                                    );
                                                    $files[$path_parts['filename']] = $file_data;
                                                    $index++;
                                                }
                                            }
                                        }
                                        $products[$key]["images"] = $files;
                                        $products[$key]["vendor_name"] = $vendor['name'];
                                        // записываем в файл
                                        $offer_content = "\r\n" . $this->sl->pdoTools->getChunk("@FILE chunks/catalog_yml_product.tpl", $products[$key]) . "\r\n";
                                        fwrite($fd, $offer_content);
                                    }
                                    $output['products'] = array_merge($output['products'], $products);
                                }
                            }
                            $end_file = "</offers>\r\n</shop>\r\n</yml_catalog>";
                            fwrite($fd, $end_file);
                            fclose($fd);
                        // }
                    }
                }
            }
        }
        $this->modx->query( '
           SET `low_priority_updates` = `OFF`
        ' );
        return $output;
    }

    public function isJson($string) {
        json_decode($string);
        return json_last_error() === JSON_ERROR_NONE;
    }

    public function tolog($id, $data) {
        $this->modx->log(xPDO::LOG_LEVEL_ERROR, print_r($data, 1), array(
            'target' => 'FILE',
            'options' => array(
                'filename' => 'import_1c_'.$id.'.log'
            )
        ));
    }
}