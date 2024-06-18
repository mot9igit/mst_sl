<?php
class salesAnalyticsHandler
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
                $response = $this->setAction($properties);
                break;
            case 'get':
                $response = $this->getAction($properties);
                break;
            case 'delete':
                $response = $this->deleteAction($properties);
                break;
            case 'off/on':
                $response = $this->offAndOnAction($properties);
                break;
        }
        return $response;
    }

    /**
     * Создание/редактирование акции
     * @return array
     */
    public function setAction($properties){
        if($properties['type'] == "b2b"){
            $store_id = $properties['id'];

            $properties['dates'][0] = date('Y-m-d H:i:s', strtotime($properties['dates'][0]));
            $properties['dates'][1] = date('Y-m-d H:i:s', strtotime($properties['dates'][1]));
            $this->modx->log(1, print_r($properties['dates'], 1));
            $start = new DateTime($properties['dates'][0]);
            $start->setTime(00,00);
            $end = new DateTime($properties['dates'][1]);
            $end->setTime(23,59);

            if($properties['action_id']){
                $action = $this->modx->getObject('slActions', $properties['action_id']);
            }else{
                $action = $this->modx->newObject('slActions');
            }

            if($action){
                $action->set("store_id", $store_id);
                $action->set("name", $properties['name']);
                $action->set("description", $properties['description']);
                $action->set("award", $properties['award']);

                if(!file_exists($this->modx->getOption('base_path') . "assets/content/banners/")){
                    mkdir($this->modx->getOption('base_path') . "assets/content/banners/", 0777, true);
                }

                if($properties['files']['max']['name']) {
                    $image = $this->modx->getOption('base_path') . "assets/content/banners/" . $properties['files']['max']['name'];

                    if(rename($this->modx->getOption('base_path') . $properties['files']['max']['original'], $image)){
                        $action->set("image", "banners/" . $properties['files']['max']['name']);
                    }
                }

                if($properties['files']['min']['name']){
                    $image_inner = $this->modx->getOption('base_path') . "assets/content/banners/" . $properties['files']['min']['name'];

                    if(rename($this->modx->getOption('base_path') . $properties['files']['min']['original'], $image_inner)){
                        $action->set("image_inner", "banners/" . $properties['files']['min']['name']);
                    }
                }

                if ($properties['files']['icon']['name']) {
                    $icon = $this->modx->getOption('base_path') . "assets/content/banners/" . $properties['files']['icon']['name'];

                    if (rename($this->modx->getOption('base_path') . $properties['files']['icon']['original'], $icon)) {
                        $action->set("icon", "banners/" . $properties['files']['icon']['name']);
                    }
                }

                $action->set("compatibility_discount", $properties['compatibilityDiscount']);
                $action->set("compatibility_postponement", $properties['compatibilityPost']);
                $action->set("date_from", $start->format('Y-m-d H:i:s'));
                $action->set("date_to", $end->format('Y-m-d H:i:s'));
                $action->set("createdon", time());
                $action->set("active", 0); //TODO поменять на 1, после тестов!
                $action->set("type", 1); //b2b

                $action->set("shipment_type", $properties['shipment_type']);
                $action->set("shipment_date", $properties['shipment_date']);
                $action->set("payer", $properties['payer']);
                $action->set("delivery_payment_terms", $properties['delivery_payment_terms']);
                $action->set("delivery_payment_value", $properties['delivery_payment_value']);
                $action->set("global", false);

                if($properties['delay']){
                    $action->set("delay", $properties['delay']);
                    $action->set("delay_condition", $properties['delay_condition']);
                    $action->set("delay_condition_value", $properties['delay_condition_value']);
                }

                $action->set("condition_type", $properties['condition_type']);

                if($properties['condition_type'] == 3 || $properties['condition_type'] == 4) {
                    $action->set("condition_min_sum", $properties['condition_min_sum']);
                }

                if($properties['condition_type'] == 3) {
                    $action->set("condition_SKU", $properties['condition_SKU']);
                }

                $action->set("participants_type", $properties['participants_type']);
                $action->set("method_adding_products", $properties['method_adding_products']);
                $action->set("available_stores", $properties['available_stores']);
                $action->set("available_opt", $properties['available_opt']);
                $action->set("available_vendors", $properties['available_vendors']);

                if($properties['participants_type'] == "1"){

                    $regions = array();

                    foreach ($properties['regions_select'] as $key => $value) {
                        $elem = explode("_", $value['code']);

                        $regions[] = $elem[1];
                    }


                    $action->set("regions", $regions);

                }


                $action->save();

                if($action->get('id')){
                    $action_id = $action->get('id');
                    if($properties['action_id']){
                        $crit = array(
                            "action_id" => $properties['action_id']
                        );
                        $this->modx->removeCollection("slActionsProducts", $crit);
                        $this->modx->removeCollection("slActionsStores", $crit);
                        $this->modx->removeCollection("slActionsDelay", $crit);
                        $this->modx->removeCollection("slActionsComplects", $crit);
                    }

                    //График отсрочки
                    if($properties['delay_graph']){
                        foreach($properties['delay_graph'] as $delay){
                            $action_d = $this->modx->newObject("slActionsDelay");
                            $action_d->set("action_id", $action_id);
                            $action_d->set("percent", $delay['percent']);
                            $action_d->set("day", $delay['day']);
                            $action_d->save();
                        }
                    }

                    foreach($properties['products'] as $product){
                        $action_p = $this->modx->newObject("slActionsProducts");
                        $action_p->set("action_id", $action->get('id'));
                        $action_p->set("remain_id", $product['id']);
                        $price = (float)$product['price'];
                        $action_p->set("old_price", $price);
                        $action_p->set("new_price", $product['finalPrice']);
                        $action_p->set("multiplicity", $product['multiplicity']);

                        //Тип цен
                        $action_p->set("type_price", $product['typePrice']['key']);

                        $action_p->save();
                    }

                    foreach($properties['complects'] as $complect){
                        $action_c = $this->modx->newObject("slActionsComplects");
                        $action_c->set("action_id", $action->get('id'));
                        $action_c->set("complect_id", $complect['id']);


                        $action_c->save();
                    }

                    if ($properties['participants_type'] == '2') {
                        foreach ($properties['organizations'] as $organization) {
                            $action_o = $this->modx->newObject("slActionsStores");
                            $action_o->set("action_id", $action->get('id'));
                            $action_o->set("store_id", $organization['id']);

                            $action_o->save();
                        }
                    }

                    return $action->toArray();
                }
            }
        } elseif ($properties['type'] == "b2c") {
            $store_id = $properties['id'];

            $properties['dates'][0] = date('Y-m-d H:i:s', strtotime($properties['dates'][0]));
            $properties['dates'][1] = date('Y-m-d H:i:s', strtotime($properties['dates'][1]));
            $this->modx->log(1, print_r($properties['dates'], 1));
            $start = new DateTime($properties['dates'][0]);
            $start->setTime(00,00);
            $end = new DateTime($properties['dates'][1]);
            $end->setTime(23,59);

            if ($properties['action_id']) {
                $action = $this->modx->getObject('slActions', $properties['action_id']);
            } else {
                $action = $this->modx->newObject('slActions');
            }

            if ($action) {
                $action->set("store_id", $store_id);
                $action->set("name", $properties['name']);
                $action->set("conditions", $properties['conditions']);

                if (!file_exists($this->modx->getOption('base_path') . "assets/content/banners/")) {
                    mkdir($this->modx->getOption('base_path') . "assets/content/banners/", 0777, true);
                }

                if (!file_exists($this->modx->getOption('base_path') . "assets/content/rules/")) {
                    mkdir($this->modx->getOption('base_path') . "assets/content/rules/", 0777, true);
                }

                if ($properties['files']['max']['name']) {
                    $image = $this->modx->getOption('base_path') . "assets/content/banners/" . $properties['files']['max']['name'];

                    if (rename($this->modx->getOption('base_path') . $properties['files']['max']['original'], $image)) {
                        $action->set("image", "banners/" . $properties['files']['max']['name']);
                    }
                }

                if ($properties['files']['min']['name']) {
                    $image_inner = $this->modx->getOption('base_path') . "assets/content/banners/" . $properties['files']['min']['name'];

                    if (rename($this->modx->getOption('base_path') . $properties['files']['min']['original'], $image_inner)) {
                        $action->set("image_inner", "banners/" . $properties['files']['min']['name']);
                    }
                }

                if ($properties['files']['icon']['name']) {
                    $icon = $this->modx->getOption('base_path') . "assets/content/banners/" . $properties['files']['icon']['name'];

                    if (rename($this->modx->getOption('base_path') . $properties['files']['icon']['original'], $icon)) {
                        $action->set("icon", "banners/" . $properties['files']['icon']['name']);
                    }
                }

                if ($properties['files']['file']['name']) {
                    $icon = $this->modx->getOption('base_path') . "assets/content/rules/" . $properties['files']['file']['name'];

                    if (rename($this->modx->getOption('base_path') . $properties['files']['file']['original'], $icon)) {
                        $action->set("rules_file", "rules/" . $properties['files']['file']['name']);
                    }
                }

                if ($properties['files']['file']['xlsx']) {
                    $icon = $this->modx->getOption('base_path') . "assets/content/upload_products/" . $properties['files']['xlsx']['name'];

                    if (rename($this->modx->getOption('base_path') . $properties['files']['xlsx']['original'], $icon)) {
                        $action->set("file_upload_products", "upload_products/" . $properties['files']['xlsx']['name']);
                    }
                }

                $action->set("date_from", $start->format('Y-m-d H:i:s'));
                $action->set("date_to", $end->format('Y-m-d H:i:s'));
                $action->set("createdon", time());
                $action->set("active", 0); //TODO Поставить 1!
                $action->set("status", 2); //Статус на модерации
                $action->set("type", 2); //b2c

                if($properties['region_all']){
                    $action->set("global", true);
                }else{
                    $action->set("global", false);

                    $regions = array();
                    $citys = array();

                    foreach ($properties['regins'] as $key => $value) {
                        $elem = explode("_", $key);

                        if($elem[0] == "region"){
                            if($value['checked']){
                                $regions[] = $elem[1];
                            }
                        } else if($elem[0] == "city"){
                            if($value['checked']){
                                $citys[] = $elem[1];
                            }
                        }
                    }

                    if($regions){
                        $action->set("regions", $regions);
                    }

                    if($citys){
                        $action->set("cities", $citys);
                    }
                }

                $action->save();
                if($action->get('id')){
                    if($properties['action_id']){
                        $crit = array(
                            "action_id" => $properties['action_id']
                        );
                        $this->modx->removeCollection("slActionsProducts", $crit);
                        $this->modx->removeCollection("slActionsStores", $crit);
                    }

                    foreach($properties['products'] as $product){
                        $action_p = $this->modx->newObject("slActionsProducts");
                        $action_p->set("action_id", $action->get('id'));
                        $action_p->set("product_id", $product['id']);
                        $price = (float) $product['price'];
                        $action_p->set("old_price", $price);
                        $action_p->set("new_price", $product['finalPrice']);


                        $action_p->save();
                    }

                    return $action->toArray();
                }
            }
        }

    }

    /**
     * Просмотр акций
     * @return array
     */
    public function getAction($properties){
        if($properties['action_id']){
            $action = $this->modx->getObject("slActions", $properties['action_id']);
            if($action){
                $data = $action->toArray();
                $data['date_from'] = date('Y/m/d H:i:s', strtotime($data['date_from']));
                $data['date_to'] = date('Y/m/d H:i:s', strtotime($data['date_to']));

                $q_s = $this->modx->newQuery("slActionsStatus");
                $q_s->select(array(
                    'slActionsStatus.*'
                ));

                $q_s->where(array("`slActionsStatus`.`id`:=" => $data['status']));

                if ($q_s->prepare() && $q_s->stmt->execute()) {
                    $status = $q_s->stmt->fetch(PDO::FETCH_ASSOC);
                    $data['status'] = $status['name'];
                }

                $q_c = $this->modx->newQuery("slActionsComplects");
                $q_c->leftJoin('slComplects', 'slComplects', 'slComplects.id = slActionsComplects.complect_id');
                $q_c->select(array(
                    'slActionsComplects.*',
                    'slComplects.name',
                ));
                $q_c->where(array("`slActionsComplects`.`action_id`:=" => $data['id']));

                if ($q_c->prepare() && $q_c->stmt->execute()) {
                    $out = $q_c->stmt->fetchAll(PDO::FETCH_ASSOC);
                    $complects = array();

                    foreach($out as $key => $val){
                        $q_p = $this->modx->newQuery("slComplectsProducts");
                        $q_p->leftJoin('slStoresRemains', 'slStoresRemains', 'slStoresRemains.id = slComplectsProducts.remain_id');
                        $q_p->leftJoin('msProductData', 'msProduct', 'msProduct.id = slStoresRemains.product_id');
                        $q_p->leftJoin('modResource', 'modResource', 'modResource.id = slStoresRemains.product_id');

                        $q_p->select(array(
                            'slComplectsProducts.*',
                            'COALESCE(modResource.pagetitle, slStoresRemains.name) as name',
                            'COALESCE(msProduct.image, "/assets/files/img/nopic.png") as image',
                            'COALESCE(msProduct.vendor_article, slStoresRemains.article) as article',
                        ));
                        $q_p->where(array("`slComplectsProducts`.`complect_id`:=" => $val['complect_id']));

                        if ($q_p->prepare() && $q_p->stmt->execute()) {
                            $out[$key]['products'] = $q_p->stmt->fetchAll(PDO::FETCH_ASSOC);
                            $urlMain = $this->modx->getOption("site_url");
                            $sum = 0;
                            $articles = "";
                            $max = 0;
                            $image = "";

                            foreach ($out[$key]['products'] as $product){
                                if($max < $product['new_price'] * $product['multiplicity']) {
                                    $max = $product['new_price'] * $product['multiplicity'];
                                    $image = $urlMain . $product['image'];
                                }
                                $sum += $product['new_price'] * $product['multiplicity'];
                                $articles = $articles . $product['article'] . ", ";
                            }

                            $articles = substr($articles,0,-2);

                            $out[$key]['cost'] = $sum;
                            $out[$key]['articles'] = $articles;
                            $out[$key]['image'] = $image;
                            $out[$key]['id'] = $out[$key]['complect_id'];
                        }

                        $complects[$val['complect_id']] = $out[$key];
                    }

                    $data['complects'] = $complects;
                }


                $q = $this->modx->newQuery("slActionsProducts");
                $q->leftJoin('slStoresRemains', 'slStoresRemains', 'slStoresRemains.id = slActionsProducts.remain_id');
                $q->leftJoin('msProductData', 'msProduct', 'msProduct.id = slStoresRemains.product_id');
                $q->leftJoin('modResource', 'modResource', 'modResource.id = slStoresRemains.product_id');

                $q->where(array("`slActionsProducts`.`action_id`:=" => $data['id']));

                $q->select(array(
                    'slActionsProducts.*',
                    'slStoresRemains.price as price',
                    'COALESCE(modResource.pagetitle, slStoresRemains.name) as name',
                    'COALESCE(msProduct.image, "/assets/files/img/nopic.png") as image',
                    'COALESCE(msProduct.vendor_article, slStoresRemains.article) as article',
                    "`slStoresRemains`.`id` as remain_id"
                ));


                if ($q->prepare() && $q->stmt->execute()) {
                    $products = $q->stmt->fetchAll(PDO::FETCH_ASSOC);
                    $selected = new stdClass();

                    $count_products = 0;

                    $helpArray = array(
                        "checked" => true,
                        "partialChecked" => false
                    );

                    foreach($products as $product){
                        $id = $product['remain_id'];
                        $product['id'] = $product['remain_id'];
                        $product['price'] = (float)$product['old_price'];
                        $product['discountInRubles'] = (float)$product['old_price'] - $product['new_price'];
                        $product['discountInterest'] = $product['discountInRubles'] / ($product['old_price'] / 100);
                        $product['finalPrice'] = (float)$product['new_price'];

                        $regions_and_sities = array();

                        $regions = explode(",", $product['regions']);
                        $citys = explode(",", $product['cities']);

                        foreach($regions as $region){
                            $regions_and_sities['region_'.$region] = $helpArray;
                        }

                        foreach($citys as $city){
                            $regions_and_sities['city_'.$city] = $helpArray;
                        }

                        $product['regions_and_sities'] = $regions_and_sities;

                        $selected->$id = $product;
                        $count_products++;
                    }

                    $data['products'] = $selected;
                    $data['total_products'] = $count_products;
                }

                $q_d = $this->modx->newQuery("slActionsDelay");
                $q_d->select(array(
                    'slActionsDelay.*',
                ));
                $q_d->where(array("`slActionsDelay`.`action_id`:=" => $data['id']));

                if ($q_d->prepare() && $q_d->stmt->execute()) {
                    $data['delay_graph'] = $q_d->stmt->fetchAll(PDO::FETCH_ASSOC);
                }

                $q_r = $this->modx->newQuery("dartLocationRegion");
                $q_r->select(array(
                    'dartLocationRegion.*',
                ));
                $q_r->where(array("`dartLocationRegion`.`id`:IN" => $data['regions']));

                if ($q_r->prepare() && $q_r->stmt->execute()) {
                    $regions_all = $q_r->stmt->fetchAll(PDO::FETCH_ASSOC);

                    $regions_temp = array();

                    foreach($regions_all as $region){
                        $regions_temp[] = array(
                            "name" => $region['name'],
                            "code" => "region_" . $region['id']
                        );
                    }

                    $data['regions'] = $regions_temp;
                }

                $query = $this->modx->newQuery("slActionsStores");
                $query->leftJoin("slStores", "slStores", "slStores.id = slActionsStores.store_id");
                $query->select(array(
                    'slActionsStores.*',
                    'slStores.name',
                    'slStores.image'
                ));

                $query->where(array("`slActionsStores`.`action_id`:=" => $data['id']));

                if ($query->prepare() && $query->stmt->execute()) {
                    $organizations = $query->stmt->fetchAll(PDO::FETCH_ASSOC);
                    $organizations_selected = new stdClass();

                    $urlMain = $this->modx->getOption("site_url");

                    foreach($organizations as $organization){
                        $id = $organization['store_id'];
                        $organization['id'] = $id;
                        $organization['image'] = $urlMain . "assets/content/" . $organization['image'];
                        $organizations_selected->$id = $organization;
                    }

                    $data['organization'] = $organizations_selected;
                }

                if($data['image']){
                    $data['image'] = "assets/content/" . $data['image'];
                }

                if($data['image_inner']){
                    $data['image_inner'] = "assets/content/" . $data['image_inner'];
                }

                if($data['icon']){
                    $data['icon'] = "assets/content/" . $data['icon'];
                }

                if($data['rules_file']){
                    $data['rules_file'] = "assets/content/" . $data['rules_file'];
                }

                $regions_and_sities = array();
                $helpArray = array(
                    "checked" => true,
                    "partialChecked" => false
                );

                $regions = explode(",", $data['regions']);
                $citys = explode(",", $data['cities']);

                foreach($regions as $region){
                    $regions_and_sities['region_'.$region] = $helpArray;
                }

                foreach($citys as $city){
                    $regions_and_sities['city_'.$city] = $helpArray;
                }

                $data['regions_and_sities'] = $regions_and_sities;



                //$products = $this->modx->getCollection("slActionsProducts", array("action_id" => $data['id']));
                //$data['products'] = $products;
                //$properties["sel_arr"] = array();
                //foreach($products as $product){
                    //$properties["sel_arr"][] = $product->get("product_id");
                //}
                //$data['products'][] = $this->getAvailableProducts($data['store_id'], $properties, 0);
                //$data['products'][] = $this->getAvailableProducts($data['store_id'], $properties, 1);
                return $data;
            }
        }else{
            $q = $this->modx->newQuery("slActions");
            $q->select(array(
                'slActions.*'
            ));
            $q->where(array("`slActions`.`store_id`:=" => $properties['id']));

            if($properties['type'] == 'b2b'){
                $q->where(array("`slActions`.`type`:=" => 1));
            }else if($properties['type'] == 'b2c'){
                $q->where(array("`slActions`.`type`:=" => 2));
            }

            if($properties['filtersdata']){
                if(isset($properties['filtersdata']['range'])){
                    if($properties['filtersdata']['range'][0] && $properties['filtersdata']['range'][1]){
                        $from = date('Y-m-d H:i:s', strtotime($properties['filtersdata']['range'][0]));
                        $to = date('Y-m-d H:i:s', strtotime($properties['filtersdata']['range'][1]));
                        $q->where(array("`slActions`.`date_from`:<=" => $from, "`slStoresMatrix`.`date_to`:>=" => $to));
                    }
                    if($properties['filtersdata']['range'][0] && !$properties['filtersdata']['range'][1]){
                        $from = date('Y-m-d H:i:s', strtotime($properties['filtersdata']['range'][0]));
                        $q->where(array("`slActions`.`date_from`:<=" => $from));
                    }
                }
                if($properties['filter']){
                    $words = explode(" ", $properties['filter']);
                    foreach($words as $word){
                        $criteria = array();
                        $criteria['slActions.name:LIKE'] = '%'.trim($word).'%';
                        $q->where($criteria);
                    }
                }
            }
            $result = array();
            // Подсчитываем общее число записей
            $result['total'] = $this->modx->getCount("slActions", $q);

            // Устанавливаем лимит 1/10 от общего количества записей
            // со сдвигом 1/20 (offset)
            if($properties['page'] && $properties['perpage']){
                $limit = $properties['perpage'];
                $offset = ($properties['page'] - 1) * $properties['perpage'];
                $q->limit($limit, $offset);
            }

            // И сортируем по ID в обратном порядке
            if($properties['sort']){
                // $this->modx->log(1, print_r($properties, 1));
                $keys = array_keys($properties['sort']);
                // нужно проверить какому объекту принадлежит поле
                $q->sortby($keys[0], $properties['sort'][$keys[0]]['dir']);
            }else{
                $q->sortby('id', "DESC");
            }

            if ($q->prepare() && $q->stmt->execute()) {
                $output = array();
                $result['items'] = $q->stmt->fetchAll(PDO::FETCH_ASSOC);
                foreach($result['items'] as $key => $val){
                    $date_from = strtotime($val['date_from']);
                    $result['items'][$key]['date_from'] = date("d.m.Y H:i", $date_from);
                    $date_to = strtotime($val['date_to']);
                    $result['items'][$key]['date_to'] = date("d.m.Y H:i", $date_to);

                    $q_s = $this->modx->newQuery("slActionsStatus");
                    $q_s->select(array(
                        'slActionsStatus.*'
                    ));

                    $q_s->where(array("`slActionsStatus`.`id`:=" => $val['status']));

                    if ($q_s->prepare() && $q_s->stmt->execute()) {
                        $status = $q_s->stmt->fetch(PDO::FETCH_ASSOC);
                        $result['items'][$key]['status'] = $status['name'];
                    }

                    if($result['items'][$key]['image']){
                        $result['items'][$key]['image'] = "assets/content/" . $result['items'][$key]['image'];
                    }

                    if($result['items'][$key]['image_inner']){
                        $result['items'][$key]['image_inner'] = "assets/content/" . $result['items'][$key]['image_inner'];
                    }

                    if($result['items'][$key]['icon']){
                        $result['items'][$key]['icon'] = "assets/content/" . $result['items'][$key]['icon'];
                    }

                    if($result['items'][$key]['rules_file']){
                        $result['items'][$key]['rules_file'] = "assets/content/" . $result['items'][$key]['rules_file'];
                    }
                }
                $this->modx->log(1, print_r($output, 1));
                return $result;
            }
        }
    }

    /**
     * Удаление акции
     * @return array
     */
    public function deleteAction($properties) {
        if($properties['store_id'] && $properties['action_id']){
            $action = $this->modx->getObject("slActions", array('id' => $properties['action_id'], 'store_id' => $properties['store_id']));
            if($action){
                $action->remove();

                $result = array(
                    "status" => true
                );

                return $result;
            }
        }

        $result = array(
            "status" => false
        );
        return $result;
    }

    /**
     * Удаление акции
     * @return array
     */
    public function offAndOnAction($properties){
        if($properties['store_id'] && $properties['action_id']) {
            $action = $this->modx->getObject("slActions", array('id' => $properties['action_id'], 'store_id' => $properties['store_id']));

            if($action) {
                if($action->active){
                    $action->set("active", 0);
                }else{
                    $action->set("active", 1);
                }
                $action->save();
                return $action->toArray();
            }
        }

        $result = array(
            "status" => false
        );
        return $result;
    }
}