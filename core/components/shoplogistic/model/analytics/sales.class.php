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
            case 'get/all':
                $response = $this->getActionAll($properties);
                break;
            case 'get/banners':
                $response = $this->getBanners($properties);
                break;
            case 'get/adv/pages':
                $response = $this->getAdvPages($properties);
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
            $org_id = $properties['id'];
            $store_id = $properties['store_id'];

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
                $action->set("org_id", $org_id);
                $action->set("name", $properties['name']);
                $action->set("description", $properties['description']);
                $action->set("limit_type", $properties['limit_type']);
                $action->set("action_last", $properties['actionLast']);


                if($properties['limit_type'] != '1'){
                    $action->set("limit_sum", $properties['limit_sum']);
                }else{
                    $action->set("limit_sum", 0);
                }

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

                if ($properties['files']['small']['name']) {
                    $icon = $this->modx->getOption('base_path') . "assets/content/banners/" . $properties['files']['small']['name'];

                    if (rename($this->modx->getOption('base_path') . $properties['files']['small']['original'], $icon)) {
                        $action->set("image_small", "banners/" . $properties['files']['small']['name']);
                    }
                }

                if($properties['page_places']){
                    $list_places = implode(", ", $properties['page_places']);
                    $action->set("page_places", $list_places);
                }

                $action->set("page_geo", $properties['page_geo']);
                $action->set("page_place_position", $properties['page_place_position']);
                $action->set("page_create", $properties['page_create']);

                $action->set("compatibility_discount", $properties['compatibilityDiscount']);
                $action->set("compatibility_postponement", $properties['compatibilityPost']);
                $action->set("compatibility_discount_mode", $properties['compabilityMode']);
                $action->set("compatibility_postponement_mode", $properties['compabilityModePost']);
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

                $action->set("condition_SKU", $properties['condition_SKU']);

                $action->set("participants_type", $properties['participants_type']);
                $action->set("method_adding_products", $properties['method_adding_products']);
                $action->set("available_stores", $properties['available_stores']);
                $action->set("available_opt", $properties['available_opt']);
                $action->set("available_vendors", $properties['available_vendors']);

                $action->set("not_sale_client", $properties['not_sale_client']);
                $action->set("is_all_products", $properties['is_all_products']);

                if($properties['big_sale_actions']){
                    $big_sale_actions = array();

                    foreach ($properties['big_sale_actions'] as $key => $value) {
                        $big_sale_actions[] = $value['code'];
                    }

                    $action->set("big_sale_actions", $big_sale_actions);
                }


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
                        $this->modx->removeCollection("slActionsGift", $crit);
                    }


                    if($properties['condition_type'] == 2){
                        foreach ($properties['gifts'] as $key => $gift){
                            $action_g = $this->modx->newObject("slActionsGift");
                            $action_g->set("action_id", $action_id);
                            $action_g->set("remain_id", $gift['id']);
                            $action_g->set("multiplicity", $gift['multiplicity']);
                            $action_g->save();
                        }
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
                        if($properties['products_data'][$product]){
                            if($properties['products_data'][$product]['price'] != $properties['products_data'][$product]['finalPrice'] || $properties['products_data'][$product]['multiplicity'] != 1){
                                $action_p = $this->modx->newObject("slActionsProducts");
                                $action_p->set("action_id", $action->get('id'));
                                $action_p->set("remain_id", $product);
                                $price = (float)$properties['products_data'][$product]['price'];
                                $action_p->set("old_price", $price);
                                $action_p->set("new_price", $properties['products_data'][$product]['finalPrice']);
                                $action_p->set("multiplicity", $properties['products_data'][$product]['multiplicity']);

                                //Тип цен
                                $action_p->set("type_price", $properties['products_data'][$product]['typePrice']['key']);

                                $action_p->save();
                            }
                        }
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
                    } else if($properties['participants_type'] == '3'){
                        //Обнуляем компании
                        $crit = array(
                            "action_id" => $properties['action_id']
                        );
                        $this->modx->removeCollection("slActionsStores", $crit);

                        //Обнуляем регионы и города
                        $action->set("regions", null);
                    }

                    return $action->toArray();
                }
            }
        } elseif ($properties['type'] == "b2c") {
            $org_id = $properties['id'];
            $store_id = $properties['store_id'];

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
                $action->set("org_id", $org_id);
                $action->set("name", $properties['name']);
                $action->set("description", $properties['description']);
                $action->set("conditions", $properties['conditions']);

                if($properties['page_places']){
                    $list_places = implode(", ", $properties['page_places']);
                    $action->set("page_places", $list_places);
                }

                $action->set("page_geo", $properties['page_geo']);
                $action->set("page_place_position", $properties['page_place_position']);
                $action->set("page_create", $properties['page_create']);

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

                if ($properties['files']['small']['name']) {
                    $icon = $this->modx->getOption('base_path') . "assets/content/banners/" . $properties['files']['small']['name'];

                    if (rename($this->modx->getOption('base_path') . $properties['files']['small']['original'], $icon)) {
                        $action->set("image_small", "banners/" . $properties['files']['small']['name']);
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


                if($properties['page_create']){
                    $query_status = $this->modx->newQuery("slActionsStatus");
                    $query_status->select(array(
                        'slActionsStatus.*'
                    ));
                    $query_status->where(array("`slActionsStatus`.`name`:=" => "Модерация"));

                    if ($query_status->prepare() && $query_status->stmt->execute()) {
                        $status_id = $query_status->stmt->fetch(PDO::FETCH_ASSOC);
                        $action->set("status", $status_id['id']); //Статус на модерации
                    }
                } else {
                    $query_status = $this->modx->newQuery("slActionsStatus");
                    $query_status->select(array(
                        'slActionsStatus.*'
                    ));
                    $query_status->where(array("`slActionsStatus`.`name`:=" => "Активна"));

                    if ($query_status->prepare() && $query_status->stmt->execute()) {
                        $status_id = $query_status->stmt->fetch(PDO::FETCH_ASSOC);
                        $action->set("status", $status_id['id']); //Статус на модерации
                    }
                }

                $action->set("type", 2); //b2c

                if($properties['region_all']){
                    $action->set("global", true);
                }else{
                    $action->set("global", false);

                    $regions = array();

                    foreach ($properties['regins'] as $key => $value) {
                        $elem = explode("_", $value['code']);

                        $regions[] = $elem[1];
                    }


                    $action->set("regions", $regions);
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
                        if($properties['products_data'][$product]){
                            if($properties['products_data'][$product]['price'] != $properties['products_data'][$product]['finalPrice'] || $properties['products_data'][$product]['multiplicity'] != 1){
                                $action_p = $this->modx->newObject("slActionsProducts");
                                $action_p->set("action_id", $action->get('id'));
                                $action_p->set("remain_id", $product);
                                $price = (float)$properties['products_data'][$product]['price'];
                                $action_p->set("old_price", $price);
                                $action_p->set("new_price", $properties['products_data'][$product]['finalPrice']);

                                //Тип цен
                                $action_p->set("type_price", $properties['products_data'][$product]['typePrice']['key']);

                                $action_p->save();
                            }
                        }
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
                $q_access = $this->modx->newQuery("slWarehouseStores");
                $q_access->select(array(
                    'slWarehouseStores.*'
                ));
                $q_access->where(array(
                    "`slWarehouseStores`.`warehouse_id`:=" => $data['store_id'],
                    "`slWarehouseStores`.`store_id`:=" => $properties['id'],
                ));

                $q_access->prepare();
                $this->modx->log(1, $q_access->toSQL());

                if ($q_access->prepare() && $q_access->stmt->execute()) {
                    $status = $q_access->stmt->fetch(PDO::FETCH_ASSOC);

                    if(!$status && $data['store_id'] != $properties['id']){
                        return array(
                            "access" => false,
                            "message" => "Доступ к данной акции запрещён!"
                        );
                    } else{
                        $data['access'] = true;
                    }
                }


                $data['date_from'] = date('Y/m/d H:i:s', strtotime($data['date_from']));
                $data['date_to'] = date('Y/m/d H:i:s', strtotime($data['date_to']));

                $this->modx->log(1, print_r($data, 1));

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
                $q_c->prepare();
                $this->modx->log(1, $q_c->toSQL());
                if ($q_c->prepare() && $q_c->stmt->execute()) {
                    $out = $q_c->stmt->fetchAll(PDO::FETCH_ASSOC);
                    $complects = array();
                    if($out) {
                        foreach ($out as $key => $val) {
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

                                foreach ($out[$key]['products'] as $key_complect => $product) {
                                    if ($max < $product['new_price'] * $product['multiplicity']) {
                                        $max = $product['new_price'] * $product['multiplicity'];
                                        $image = $urlMain . $product['image'];
                                    }
                                    $sum += $product['new_price'] * $product['multiplicity'];
                                    $articles = $articles . $product['article'] . ", ";

                                    if($product['image']) {
                                        $out[$key]['products'][$key_complect]['image'] = $urlMain . $product['image'];
                                    }

                                    $out[$key]['products'][$key_complect]['prices'] = $this->sl->analyticsOpt->getRemainPrices(array("remain_id" => $out[$key]['products'][$key_complect]['remain_id']));

                                    //Проверка, есть ли товар в корзине
                                    if($_SESSION['basket'][$properties['id']][$data['store_id']]['complects'][$product['complect_id']] != null) {
                                        $out[$key]['products'][$key_complect]['basket'] = array(
                                            "availability" => true,
                                            "count" => $_SESSION['basket'][$properties['id']][$data['store_id']]['complects'][$product['complect_id']]['count']
                                        );
                                    } else{
                                        $out[$key]['products'][$key_complect]['basket'] = array(
                                            "availability" => false,
                                            "count" => 1
                                        );
                                    }

                                    $out[$key]['products'][$key_complect]['remain'] = $this->sl->analyticsOpt->getRemainComplect($data['store_id'], $val['complect_id']);
                                }

                                $articles = substr($articles, 0, -2);


                                $out[$key]['cost'] = $sum;
                                $out[$key]['articles'] = $articles;
                                $out[$key]['image'] = $image;
                                $out[$key]['id'] = $out[$key]['complect_id'];
                            }

                            $out[$key]['remain'] = $this->sl->analyticsOpt->getRemainComplect($data['store_id'], $val['complect_id']);
                            $complects[$val['complect_id']] = $out[$key];
                        }
                    }
                    $data['complects'] = $complects;
                }
                $this->modx->log(1, print_r($data['complects'], 1));

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
                    $urlMain = $this->modx->getOption("site_url");

                    $products_data = [];

                    foreach($products as $key_products => $product){
                        $id = $product['remain_id'];
                        $products_data[$id]['price'] = (float)$product['old_price'];
                        $products_data[$id]['finalPrice'] = (float)$product['new_price'];
                        $products_data[$id]['multiplicity'] = (float)$product['multiplicity'];

                        $product['id'] = (int) $product['remain_id'];
                        $product['price'] = (float)$product['old_price'];
                        if($product['price'] == 0){
                            $product['discountInRubles'] = 0;
                            $product['discountInterest'] = 100;
                            $products_data[$id]['discountInRubles'] = 0;
                            $products_data[$id]['discountInterest'] = 100;

                        }else{
                            $product['discountInRubles'] = (float)$product['old_price'] - $product['new_price'];
                            $product['discountInterest'] = $product['discountInRubles'] / ($product['old_price'] / 100);
                            $products_data[$id]['discountInRubles'] = (float)$product['old_price'] - $product['new_price'];
                            $products_data[$id]['discountInterest'] = $product['discountInRubles'] / ($product['old_price'] / 100);
                        }
                        $product['finalPrice'] = (float)$product['new_price'];
                        $product['prices'] = $this->sl->analyticsOpt->getRemainPrices(array("remain_id" => $product['remain_id']));
                        $product['remain'] = $this->sl->analyticsOpt->getRemain($product['remain_id'], $data['store_id']);


                        if($product['type_price'] == "0"){
                            $product['typePrice'] = array(
                                "key" => '0',
                                "name" => "Розничная"
                            );
                        } else {
                            $query_prices = $this->modx->newQuery("slStoresRemainsPrices");
                            $query_prices->leftJoin("slStoresRemains", "slStoresRemains", "slStoresRemains.id = slStoresRemainsPrices.remain_id");
                            $query_prices->leftJoin("slStores", "slStores", "slStores.id = slStoresRemains.store_id");
                            $query_prices->where(array(
                                "slStoresRemains.store_id" => $properties['store_id']
                            ));
                            $query_prices->select(array("DISTINCT slStoresRemainsPrices.key, slStoresRemainsPrices.name"));
                            if($query_prices->prepare() && $query_prices->stmt->execute()){
                                $type_price = $query_prices->stmt->fetch(PDO::FETCH_ASSOC);

                                $product['typePrice'] = array(
                                    "key" => $type_price['key'],
                                    "name" => $type_price['name']
                                );
                            }
                        }

                        //Проверка, есть ли товар в корзине
                        if($_SESSION['basket'][$properties['id']][$data['store_id']]['products'][$product['remain_id']] != null) {
                            $product['basket'] = array(
                                "availability" => true,
                                "count" => $_SESSION['basket'][$properties['id']][$data['store_id']]['products'][$product['remain_id']]['count']
                            );
                        } else{
                            $product['basket'] = array(
                                "availability" => false,
                                "count" => 1
                            );
                        }

                        //Получаем акции
                        $q_a = $this->modx->newQuery("slActions");
                        $q_a->leftJoin("slActionsProducts", "slActionsProducts", "slActions.id = slActionsProducts.action_id");
                        $q_a->select(array(
                            "`slActions`.*",
                            "`slActionsProducts`.*",
                            "`slActions`.description as description",
                        ));

                        $q_a->where(array(
                            "`slActionsProducts`.`remain_id`:=" => $product['remain_id'],
                            "`slActions`.`store_id`:=" => $product['store_id'],
                            "`slActions`.`active`:=" => 1,
                            "`slActions`.`type`:=" => 1,
                        ));

                        $main_compatibility = '0';

                        if ($q_a->prepare() && $q_a->stmt->execute()) {
                            $actions = $q_a->stmt->fetchAll(PDO::FETCH_ASSOC);

                            foreach ($actions as $key_action => $value_action) {
                                if($value_action['icon']){
                                    $actions[$key_action]['icon'] = "assets/content/" . $value_action['icon'];
                                }

                                if($value_action['image_small']){
                                    $actions[$key_action]['image_small'] = "assets/content/" . $value_action['image_small'];
                                }

                                if($key_action != 0){
                                    //Вот тут обработка совместимости
                                    if($main_compatibility == '1' && $value_action['compatibility_discount'] == '1'){
                                        $actions[$key_action]['enabled'] = true;
                                    }else{
                                        $actions[$key_action]['enabled'] = false;
                                    }
                                }else{
                                    //Первая попавшая акция - АКТИВНАЯ
                                    $actions[$key_action]['enabled'] = true;
                                    $main_compatibility = $value_action['compatibility_discount'];
                                }

                                if($_SESSION['actions'][$value_action['store_id']][$value_action['remain_id']][$value_action['action_id']] != null) {
                                    $actions[$key_action]['enabled'] = $_SESSION['actions'][$value_action['store_id']][$value_action['remain_id']][$value_action['action_id']];
                                }

//                                $data['items'][$key]['stores'][$key_store]['action'] = $this->getInfoProduct(array(
//                                    "remain_id" => $value_action['remain_id'],
//                                    "store_id" => $value_action['store_id']
//                                ))['action'];

//                                $actions[$key_action]['conflicts'] = $this->getConflicts(array("store_id" => $value_action['store_id'], "remain_id" => $value_action['remain_id']));

                                $q_g = $this->modx->newQuery("slActionsDelay");
                                $q_g->select(array(
                                    "`slActionsDelay`.*",
                                ));

                                $q_g->where(array(
                                    "`slActionsDelay`.`action_id`:=" => $value_action['action_id']
                                ));

                                if ($q_g->prepare() && $q_g->stmt->execute()) {
                                    $actions[$key_action]['delay_graph'] = $q_g->stmt->fetchAll(PDO::FETCH_ASSOC);
                                }


                                if($value_action['image']) {
                                    $actions[$key_action]['image'] = $urlMain . "assets/content/" . $value_action['image'];
                                } else{
                                    $actions[$key_action]['image'] = $urlMain . "/assets/files/img/nopic.png";
                                }
                            }

                            $product['actions'] = $actions;


                            if($product['image']) {
                                $product['image'] = $urlMain . $product['image'];
                            }
                        }

                        $selected->$id = $product;
                        $count_products++;
                    }

                    $data['products'] = $selected;
                    $data['products_data'] = $products_data;
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

                $q_g = $this->modx->newQuery("slActionsGift");
                $q_g->leftJoin('slStoresRemains', 'slStoresRemains', 'slStoresRemains.id = slActionsGift.remain_id');
                $q_g->leftJoin('msProductData', 'msProduct', 'msProduct.id = slStoresRemains.product_id');
                $q_g->leftJoin('modResource', 'modResource', 'modResource.id = slStoresRemains.product_id');

                $q_g->select(array(
                    'slActionsGift.remain_id',
                    'slActionsGift.multiplicity',
                    'slStoresRemains.price as price',
                    'COALESCE(modResource.pagetitle, slStoresRemains.name) as name',
                    'COALESCE(msProduct.image, "/assets/files/img/nopic.png") as image',
                    'COALESCE(msProduct.vendor_article, slStoresRemains.article) as article',
                    "`slStoresRemains`.`id` as remain_id"
                ));
                $q_g->where(array("`slActionsGift`.`action_id`:=" => $data['id']));

                if ($q_g->prepare() && $q_g->stmt->execute()) {
                    $gift = $q_g->stmt->fetchAll(PDO::FETCH_ASSOC);
                    $urlMain = $this->modx->getOption("site_url");

                    $giftArr = [];
                    foreach($gift as $key_gift => $value_gift){
                        $giftArr[$value_gift['remain_id']] = $value_gift;
                        $giftArr[$value_gift['remain_id']]['image'] = $urlMain . $value_gift['image'];
                        $giftArr[$value_gift['remain_id']]['id'] = $value_gift['remain_id'];
                    }

                    $data['gift'] = $giftArr;
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

                if($data['image_small']){
                    $data['image_small'] = "assets/content/" . $data['image_small'];
                }

                if($data['rules_file']){
                    $data['rules_file'] = "assets/content/" . $data['rules_file'];
                }

                $big_sale_actions = array();

                foreach($data['big_sale_actions'] as $action_id){

                    $qa = $this->modx->newQuery("slActions");
                    $qa->select(array(
                        'slActions.name',
                    ));

                    $qa->where(array("`slActions`.`id`:=" => $action_id));

                    if ($qa->prepare() && $qa->stmt->execute()) {
                        $action = $qa->stmt->fetch(PDO::FETCH_ASSOC);

                        $big_sale_actions[] = array(
                            "code" => $action_id,
                            "name" => $action['name']
                        );
                    }
                }

                $data['big_sale_actions'] = $big_sale_actions;


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

                $page_places_array = explode( ', ', $data['page_places']);
                $page_places_array_result = array();
                foreach($page_places_array as $page){
                    $q = $this->modx->newQuery("slPlaceBanners");

                    $q->select(array(
                        'slPlaceBanners.id as code',
                        'slPlaceBanners.name',
                    ));

                    $q->where(array(
                        "`slPlaceBanners`.`active`:=" => 1,
                        "`slPlaceBanners`.`id`:=" => $page,
                        "`slPlaceBanners`.`type`:=" => $data['type'],
                    ));

                    if ($q->prepare() && $q->stmt->execute()) {
                        $pages = $q->stmt->fetch(PDO::FETCH_ASSOC);
                        $page_places_array_result[] = $pages;
                    }
                }

                $data['page_places'] = $page_places_array_result;


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
                $this->modx->log(1, print_r($data, 1));
                return $data;
            }
        }else{
            $ids = array();
            $stores = $this->sl->orgHandler->getStoresOrg(array("id" => $properties['id']));
            foreach($stores["items"] as $store){
                $ids[] = $store["id"];
            }
            $q = $this->modx->newQuery("slActions");
            $q->leftJoin("slStores", "slStores", "slStores.id = slActions.store_id");
            $q->select(array(
                'slActions.*',
                "slStores.name_short as store_name"
            ));
            $q->where(array("`slActions`.`store_id`:IN" => $ids));

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
                // $this->modx
                $keys = array_keys($properties['sort']);
                // нужно проверить какому объекту принадлежит поле
                $q->sortby($keys[0], $properties['sort'][$keys[0]]['dir']);
            }else{
                $q->sortby('id', "DESC");
            }

            if ($q->prepare() && $q->stmt->execute()) {
                $output = array();
                $result['items'] = $q->stmt->fetchAll(PDO::FETCH_ASSOC);
                $urlMain = $this->modx->getOption("site_url");
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
                        $result['items'][$key]['image'] = $urlMain . "assets/content/" . $result['items'][$key]['image'];
                    }

                    if($result['items'][$key]['image_inner']){
                        $result['items'][$key]['image_inner'] =  $urlMain . "assets/content/" . $result['items'][$key]['image_inner'];
                    }

                    if($result['items'][$key]['image_small']){
                        $result['items'][$key]['image_small'] =  $urlMain . "assets/content/" . $result['items'][$key]['image_small'];
                    }

                    if($result['items'][$key]['icon']){
                        $result['items'][$key]['icon'] = $urlMain . "assets/content/" . $result['items'][$key]['icon'];
                    }

                    if($result['items'][$key]['rules_file']){
                        $result['items'][$key]['rules_file'] = $urlMain . "assets/content/" . $result['items'][$key]['rules_file'];
                    }
                }
                $this->modx->log(1, print_r($output, 1));
                return $result;
            }
        }
    }

    /**
     * Получаем акции на маркетплейсе
     * @return array
     */
    public function getActionMarketplace($properties){
        if($properties['action_id']){
            $action = $this->modx->getObject("slActions", $properties['action_id']);
            if($action){
                $data = $action->toArray();
                $urlMain = $this->modx->getOption("site_url");


                $data['image'] = $urlMain . "/assets/content/" . $data['image'];

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
                    "`slStoresRemains`.`id` as remain_id",
                    "slStoresRemains.product_id as id_product"
                ));


                if ($q->prepare() && $q->stmt->execute()) {
                    $products = $q->stmt->fetchAll(PDO::FETCH_ASSOC);
                    $idsProducts = "";

                    foreach ($products as $key_products => $product) {
                        if($idsProducts == ""){
                            $idsProducts = $idsProducts . $product['id_product'];
                        }else{
                            $idsProducts = $idsProducts . ", " . $product['id_product'];
                        }

                    }

                    $data['products'] = $products;
                    $data['id_products'] = $idsProducts;

                }
                return $data;
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
     * Берем подключенные к акции сущности
     *
     * @param $action_id
     * @return array
     */
    public function getActionProducts($action_id){
        $data = array();
        // Комплекты
        $q_c = $this->modx->newQuery("slActionsComplects");
        $q_c->leftJoin('slComplects', 'slComplects', 'slComplects.id = slActionsComplects.complect_id');
        $q_c->select(array(
            'slActionsComplects.*',
            'slComplects.name',
        ));
        $q_c->where(array("`slActionsComplects`.`action_id`:=" => $action_id));
        if ($q_c->prepare() && $q_c->stmt->execute()) {
            $out = $q_c->stmt->fetchAll(PDO::FETCH_ASSOC);
            $complects = array();
            if($out) {
                foreach ($out as $key => $val) {
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
                        foreach ($out[$key]['products'] as $key_complect => $product) {
                            if ($max < $product['new_price'] * $product['multiplicity']) {
                                $max = $product['new_price'] * $product['multiplicity'];
                                $image = $urlMain . $product['image'];
                            }
                            $sum += $product['new_price'] * $product['multiplicity'];
                            $articles = $articles . $product['article'] . ", ";

                            if($product['image']) {
                                $out[$key]['products'][$key_complect]['image'] = $urlMain . $product['image'];
                            }
                        }
                        $articles = substr($articles, 0, -2);
                        $out[$key]['cost'] = $sum;
                        $out[$key]['articles'] = $articles;
                        $out[$key]['image'] = $image;
                        $out[$key]['id'] = $out[$key]['complect_id'];
                    }
                    $complects[$val['complect_id']] = $out[$key];
                }
            }
            $data['complects'] = $complects;
            $data['total_complects'] = count($complects);
        }

        $q = $this->modx->newQuery("slActionsProducts");
        $q->leftJoin('slStoresRemains', 'slStoresRemains', 'slStoresRemains.id = slActionsProducts.remain_id');
        $q->leftJoin('msProductData', 'msProduct', 'msProduct.id = slStoresRemains.product_id');
        $q->leftJoin('modResource', 'modResource', 'modResource.id = slStoresRemains.product_id');

        $q->where(array("`slActionsProducts`.`action_id`:=" => $action_id));
        $q->select(array(
            'slActionsProducts.*',
            'slStoresRemains.price as price',
            'COALESCE(modResource.pagetitle, slStoresRemains.name) as name',
            'COALESCE(msProduct.image, "/assets/files/img/nopic.png") as image',
            'COALESCE(msProduct.vendor_article, slStoresRemains.article) as article',
            "`slStoresRemains`.`id` as remain_id"
        ));
        $q->prepare();
        $this->modx->log(1, $q->toSQL());
        if ($q->prepare() && $q->stmt->execute()) {
            $products = $q->stmt->fetchAll(PDO::FETCH_ASSOC);
            $selected = array();
            $count_products = 0;
            $urlMain = $this->modx->getOption("site_url");
            foreach($products as $key_products => $product){
                $id = $product['remain_id'];
                $product['id'] = (int) $product['remain_id'];
                $product['price'] = (float) $product['old_price'];
                if($product['price'] == 0){
                    $product['discountInRubles'] = 0;
                    $product['discountInterest'] = 100;
                }else{
                    $product['discountInRubles'] = (float)$product['old_price'] - $product['new_price'];
                    $product['discountInterest'] = $product['discountInRubles'] / ($product['old_price'] / 100);
                }
                $product['finalPrice'] = (float)$product['new_price'];
                if($product['image']) {
                    $product['image'] = $urlMain . $product['image'];
                }
                $selected[$id] = $product;
                $count_products++;
            }
            $data['products'] = $selected;
            $data['total_products'] = count($selected);
        }
        return $data;
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

    /**
     * Получаем весь список со всеми акциями продавца
     * @return array
     */
    public function getActionAll($properties){
        if($properties['id']){
            $result['items'][] =
            $q = $this->modx->newQuery("slActions");
            $q->select(array(
                'slActions.*'
            ));
            $q->where(array("`slActions`.`store_id`:=" => $properties['id']));
            $q->where(array("`slActions`.`type`:=" => 1)); // Только b2b

            if ($q->prepare() && $q->stmt->execute()) {
                $result['items'] = $q->stmt->fetchAll(PDO::FETCH_ASSOC);
            }
            $base_sale = array(
                "id" => 0,
                "name" => "Базовая скидка клиента"
            );
            array_unshift($result['items'], $base_sale);

            return $result;
        }
    }

    public function getBanners($properties) {
        if($properties['store_id']){
            $store_data = $this->sl->tools->getStoreInfo($properties['store_id']);
            $today = date_create();
            $date = date_format($today, 'Y-m-d H:i:s');
            $q = $this->modx->newQuery("slActions");
            $q->leftJoin('slWarehouseStores', 'slWarehouseStores', 'slActions.store_id = slWarehouseStores.warehouse_id');
            $q->leftJoin('slStores', 'slStores', 'slStores.id = slWarehouseStores.warehouse_id');
            $q->leftJoin("slActionsStores", "slActionsStores", "slActionsStores.action_id = slActions.id AND slActionsStores.store_id = ".$properties['store_id']);
            $q->select(array(
                'slActions.*',
                'slActions.id as action_id',
                'slWarehouseStores.*',
                'slStores.*',
                'slActions.image as image'
            ));
            $q->where(array(
                "`slActionsStores`.`active`:>" => 1,
                "FIND_IN_SET('".$store_data["city_id"]."', REPLACE(REPLACE(REPLACE(`slActions`.`cities`, '\"', ''), '[', ''), ']','')) > 0",
                "FIND_IN_SET('".$store_data["region_id"]."', REPLACE(REPLACE(REPLACE(`slActions`.`regions`, '\"', ''), '[', ''), ']','')) > 0",
                "slActions.participants_type:=" => 3
            ), xPDOQuery::SQL_OR);
            $q->where(array(
                "`slStores`.`opt_marketplace`:=" => 1,
                "`slStores`.`active`:=" => 1,
                "`slActions`.`type`:=" => 1,
                "`slWarehouseStores`.`store_id`:=" => $properties['store_id'],
                // "`slWarehouseStores`.`visible`:=" => 1,
                "`slActions`.`active`:=" => 1,
                "`slActions`.`date_from`:<=" => $date,
                "`slActions`.`date_to`:>=" => $date
            ), xPDOQuery::SQL_AND);

            if ($q->prepare() && $q->stmt->execute()) {
                $result['items'] = $q->stmt->fetchAll(PDO::FETCH_ASSOC);

                foreach ($result['items'] as $key => $item){
                    if($item['image']) {
                        $result['items'][$key]['image'] = $this->sl->config['urlMain'] . "assets/content/" . $item['image'];
                    } else {
                        $result['items'][$key]['image'] = $this->sl->config['urlMain'] . "assets/images/templates/action-imge-base.png";
                    }

                    if($item['image_inner']) {
                        $result['items'][$key]['image_inner'] = $this->sl->config['urlMain'] . "assets/content/" . $item['image_inner'];
                    } else {
                        $result['items'][$key]['image_inner'] = $this->sl->config['urlMain'] . "assets/images/templates/action-imge-base-mini.png";
                    }
                }

                shuffle($result['items']);
            }

            $result['count'] = $this->modx->getCount('slStoresRemains', $q);

            return $result;
        }
    }

    public function getAdvPages($properties){
        $q = $this->modx->newQuery("slPlaceBanners");

        $q->select(array(
            'slPlaceBanners.id as code',
            'slPlaceBanners.name',
        ));

        $q->where(array("`slPlaceBanners`.`active`:=" => 1));

        if($properties['type']){
            $q->where(array("`slPlaceBanners`.`type`:=" => $properties['type']));
        }


        if ($q->prepare() && $q->stmt->execute()) {
            $pages = $q->stmt->fetchAll(PDO::FETCH_ASSOC);

//            foreach ($pages as $k => $page){
//                $pages[$k]['key'] = $page['id'];
//                unlink($pages[$k]['id']);
//            }
            return $pages;
        }
    }

    /**
     * Получаем баннеры для маркетплейса
     * @return array
     */

    public function getAdv($properties){

        $city = 0;
        $region = 0;
        $location = $this->sl->getLocationData('web');
        if($location['city_id']){
            $city = $this->sl->getObject($location['city_id'], "dartLocationCity");
            if($city){
                $region = $city['region'];
            }
        }
        if($location['region_type_full'] && $location['region']){
            // сначала чекаем fias
            $criteria = array(
                "fias_id:=" => $location['region_fias_id']
            );
            $object = $this->modx->getObject("dartLocationRegion", $criteria);
            if(!$object){
                $criteria = array(
                    "name:LIKE" => "%{$location['region_type_full']} {$location['region']}%",
                    "OR:name:LIKE" => "%{$location['region']} {$location['region_type_full']}%"
                );
                $object = $this->modx->getObject("dartLocationRegion", $criteria);
                if($object){
                    if(!$object->get("fias_id") && $location['region_fias_id']){
                        $object->set("fias_id", $location['region_fias_id']);
                        $object->save();
                    }
                }
            }
            if($object){
                $region = $object->get("id");
            }else{
                // $region = 44;
            }
        }
        // регион должен 100% определиться
        if($region){

            $query = $this->modx->newQuery("slActionsStatus");
            $query->select(array(
                '`slActionsStatus`.*'
            ));

            $query->where(array(
                "`slActionsStatus`.`name`:=" => "Активна",
            ));

            if ($query->prepare() && $query->stmt->execute()) {
                $status = $query->stmt->fetch(PDO::FETCH_ASSOC);

                $q = $this->modx->newQuery("slActions");
                $q->select(array(
                    '`slActions`.*'
                ));

                $criteria = array(
                    "slActions.global:=" => 1,
                    "FIND_IN_SET({$region}, REPLACE(REPLACE(REPLACE(slActions.regions, '\"', ''),'[', ''),']','')) > 0"
                );
                if($city){
                    $criteria[] = "FIND_IN_SET({$city['id']}, REPLACE(REPLACE(REPLACE(slActions.cities, '\"', ''),'[', ''),']','')) > 0";
                }
                $q->where($criteria, xPDOQuery::SQL_OR);

                $q->where(array(
                    "`slActions`.`active`:=" => 1,
                    "`slActions`.`status`:=" => $status['id'],
                    "`slActions`.`type`:=" => 2,
                    "`slActions`.`date_from`:<=" => date('Y-m-d'),
                    "`slActions`.`date_to`:>=" => date('Y-m-d')
                ));

                $q->sortby("slActions.page_place_position", "ASC");

                if($properties['page_places'] && $properties['page_places'] != "actions"){
                    //$page_places = "' " . $properties['page_places']."'";
                    $q->where(array(
                        "FIND_IN_SET({$properties['page_places']}, REPLACE(`slActions`.`page_places`, ' ', '')) > 0"
                    ));
                }

                $this->modx->log(1, $q->toSQL());
                if ($q->prepare() && $q->stmt->execute()) {
                    $result = $q->stmt->fetchAll(PDO::FETCH_ASSOC);

                    if($properties['page_places'] == "actions"){
                        $new_result = array();
                        foreach ($result as $key => $action) {
                            $arrayPlaces = explode(", ", $action['page_places']);

                            foreach ($arrayPlaces as $place){
                                if($place == 1 || $place == 2){
                                    $new_result[] = $action;
                                    break;
                                }
                            }
                        }
                    }

                    if($properties['page_places'] == "actions"){
                        $result = $new_result;
                    }

                    foreach ($result as $key => $action) {
                        $result[$key]['image'] = 'assets/content/' . $action['image'];
                        $result[$key]['image_small'] = 'assets/content/' . $action['image_small'];
                        $result[$key]['image_inner'] = 'assets/content/' . $action['image_inner'];
                    }

                    if($properties['products']){
                        $result_products = array();
                        foreach ($result as $k => $action) {
                            $query_products = $this->modx->newQuery("slActionsProducts");
                            $query_products->leftJoin('slStoresRemains', 'slStoresRemains', 'slStoresRemains.id = slActionsProducts.remain_id');
                            $query_products->leftJoin('msProductData', 'msProduct', 'msProduct.id = slStoresRemains.product_id');
                            $query_products->leftJoin('modResource', 'modResource', 'modResource.id = slStoresRemains.product_id');


                            $query_products->select(array(
                                '`slStoresRemains`.product_id as id',
                                '`slStoresRemains`.remains',
                                '`slActionsProducts`.remain_id',
                                '`modResource`.parent',
                                '`slActionsProducts`.old_price',
                                '`slActionsProducts`.new_price as price',
                                'COALESCE(modResource.pagetitle, slStoresRemains.name) as pagetitle',
                                'COALESCE(msProduct.image, "/assets/files/img/nopic.png") as image',
                                'COALESCE(msProduct.vendor_article, slStoresRemains.article) as vendor_article',
                            ));

                            $query_products->where(array(
                                "`slActionsProducts`.`action_id`:=" => $action['id']
                            ));

                            if ($query_products->prepare() && $query_products->stmt->execute()) {
                                $products = $query_products->stmt->fetchAll(PDO::FETCH_ASSOC);

                                foreach ($products as $p => $product){
                                    $this_product = $product;

                                    //$this_product['parent'] = 3;
                                    $this_product['price'] = (float) $this_product['price'];
                                    $this_product['old_price'] = (float) $this_product['old_price'];
                                    $result_products[] = $this_product;
                                }
                            }
                        }

                        $result = $result_products;
                    }
                }
            }


            return $result;
        }
    }

    /**
     * Доступна ли акция в конкретном регионе
     * @return array
     */
    public function isAdvAvailable($properties)
    {
        if($properties['id']){
            $city = 0;
            $region = 0;
            $location = $this->sl->getLocationData('web');
            if ($location['city_id']) {
                $city = $this->sl->getObject($location['city_id'], "dartLocationCity");
                if ($city) {
                    $region = $city['region'];
                }
            }
            if ($location['region_type_full'] && $location['region']) {
                // сначала чекаем fias
                $criteria = array(
                    "fias_id:=" => $location['region_fias_id']
                );
                $object = $this->modx->getObject("dartLocationRegion", $criteria);
                if (!$object) {
                    $criteria = array(
                        "name:LIKE" => "%{$location['region_type_full']} {$location['region']}%",
                        "OR:name:LIKE" => "%{$location['region']} {$location['region_type_full']}%"
                    );
                    $object = $this->modx->getObject("dartLocationRegion", $criteria);
                    if ($object) {
                        if (!$object->get("fias_id") && $location['region_fias_id']) {
                            $object->set("fias_id", $location['region_fias_id']);
                            $object->save();
                        }
                    }
                }
                if ($object) {
                    $region = $object->get("id");
                } else {
                    // $region = 44;
                }
            }
            // регион должен 100% определиться
            if ($region) {

                $query = $this->modx->newQuery("slActionsStatus");
                $query->select(array(
                    '`slActionsStatus`.*'
                ));

                $query->where(array(
                    "`slActionsStatus`.`name`:=" => "Активна",
                ));

                if ($query->prepare() && $query->stmt->execute()) {
                    $status = $query->stmt->fetch(PDO::FETCH_ASSOC);

                    $q = $this->modx->newQuery("slActions");
                    $q->select(array(
                        '`slActions`.*'
                    ));

                    $criteria = array(
                        "slActions.global:=" => 1,
                        "FIND_IN_SET({$region}, REPLACE(REPLACE(REPLACE(slActions.regions, '\"', ''),'[', ''),']','')) > 0"
                    );
                    if ($city) {
                        $criteria[] = "FIND_IN_SET({$city['id']}, REPLACE(REPLACE(REPLACE(slActions.cities, '\"', ''),'[', ''),']','')) > 0";
                    }
                    $q->where($criteria, xPDOQuery::SQL_OR);

                    $q->where(array(
                        "`slActions`.`id`:=" => $properties['id'],
                        "`slActions`.`active`:=" => 1,
                        "`slActions`.`status`:=" => $status['id'],
                        "`slActions`.`type`:=" => 2,
                        "`slActions`.`date_from`:<=" => date('Y-m-d'),
                        "`slActions`.`date_to`:>=" => date('Y-m-d')
                    ));

                    $q->sortby("slActions.page_place_position", "ASC");

                    if ($properties['page_places'] && $properties['page_places'] != "actions") {
                        //$page_places = "' " . $properties['page_places']."'";
                        $q->where(array(
                            "FIND_IN_SET({$properties['page_places']}, REPLACE(`slActions`.`page_places`, ' ', '')) > 0"
                        ));
                    }

                    $q->prepare();
                    $this->modx->log(1, "________________");
                    $this->modx->log(1, $q->toSQL());
                    if ($q->prepare() && $q->stmt->execute()) {
                        $result = $q->stmt->fetch(PDO::FETCH_ASSOC);

                        if($result){
                            return true;
                        }else{
                            return false;
                        }
                    }
                }
            }
        }
    }
}