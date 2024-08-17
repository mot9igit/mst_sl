<?php
class optAnalyticsHandler
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
            case 'get/mainpage':
                $response = $this->getMainPage($properties);
                break;
            case 'get/catalog':
                $response = $this->getCatalog($properties);
                break;
            case 'get/cart':
                $response = $this->getCart($properties);
                break;
            case 'get/warehouse':
                $response = $this->getWarehouse($properties);
                break;
            case 'get/vendors':
                $response = $this->getVendors($properties);
                break;
            case 'get/products':
                $response = $this->getProducts($properties);
                break;
            case 'basket/add':
                $response = $this->addBasket($properties);
                break;
            case 'basket/get':
                $response = $this->getBasket($properties);
                break;
            case 'basket/update':
                $response = $this->updateBasket($properties);
                break;
            case 'basket/clear':
                $response = $this->clearBasket($properties);
                break;
            case 'order/opt/submit':
                $response = $this->orderSubmit($properties);
                break;
            case 'get/orders/seller':
                $response = $this->getOrdersSeller($properties);
                break;
            case 'complect/set':
                $response = $this->setComplect($properties);
                break;
            case 'complects/get':
                $response = $this->getComplects($properties);
                break;
            case 'complect/approve':
                $response = $this->approveComplect($properties);
                break;
            case 'complect/delete':
                $response = $this->deleteComplect($properties);
                break;
            case 'action/user/off/on':
                $response = $this->userAction($properties);
                break;
            case 'get/info/product':
                $response = $this->getInfoProduct($properties);
                break;
            case 'upload/products/file':
                $response = $this->uploadProductsFile($properties);
                break;
            case 'upload/products/file/b2b':
                $response = $this->uploadProductsFileB2B($properties);
                break;
            case 'get/type/prices':
                $response = $this->getPrices($properties);
                break;
            case 'get/product/prices':
                $response = $this->getRemainPrices($properties);
                break;
            case 'generate/xslx':
                $response = $this->generateOptXslx($properties);
                break;
        }
        return $response;
    }

    /**
     * Получаем элементы главной страници
     * @return array
     */
    public function getMainPage($properties){
        $object = $this->modx->getObject("modResource", $this->modx->getOption("analytics_start_page"));
        if($object){
            $data = $object->toArray();

            $urlMain = $this->modx->getOption("site_url");

            $count_mains = 1;

            $warehouses = $this->sl->store->getWarehouses($properties['id']);
            $av = array();
            foreach($warehouses as $wh){
                $av[] = $wh['id'];
            }

            $query = $this->modx->newQuery("slActions");
            $query->leftJoin("slStores", "slStores", "slStores.id = slActions.store_id");
            $query->where(array(
                "slActions.type:=" => 1,
                "slActions.active:=" => 1,
                "slActions.store_id:IN" => $av,
                "slStores.active:=" => 1
            ));
            $query->select(array("slActions.*"));
            if($query->prepare() && $query->stmt->execute()){
                $actions = $query->stmt->fetchAll(PDO::FETCH_ASSOC);
                $max = count($actions);
                for($i = 0; $i < $count_mains; $i++){
                    $rand = rand(0, $max);
                    $data["main_slider_big"][] = $actions[$rand];
                    unset($actions[$rand]);
                }
                $data["main_slider_small"] = $actions;
            }

            //Слайдер "Готовимся к сезону"

            $season_slider = json_decode($object->getTVValue("season_slider"));
            foreach ($season_slider as $key => $value) {
                $data["season_slider"][$key]['id'] = $value->MIGX_id;
                $data["season_slider"][$key]['image'] = $urlMain . "assets/content/" . $value->image;
                $data["season_slider"][$key]['resource'] = $value->resource;
            }

            //Слайдер "Новинки"
            $new_slider = json_decode($object->getTVValue("new_slider"));
            foreach ($new_slider as $key => $value) {
                $data["new_slider"][$key]['id'] = $value->MIGX_id;
                $data["new_slider"][$key]['description'] = $value->description;
                $data["new_slider"][$key]['image'] = $urlMain . "assets/content/" . $value->image;
                $data["new_slider"][$key]['resource'] = $value->resource;
            }
        }
        return $data;
    }

    /**
     * Берем цены
     *
     * @param $remain_id
     * @return array
     */
    public function getRemainPrices($properties){
        $prices = array();
        $remain = $this->sl->getObject($properties['remain_id'], "slStoresRemains");
        $query = $this->modx->newQuery("slStoresRemainsPrices");
        $query->where(array(
            "remain_id" => $properties['remain_id']
        ));
        $query->select(array("slStoresRemainsPrices.*"));
        if($query->prepare() && $query->stmt->execute()){
            $prices = $query->stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach($prices as $key => $price){
                $prices[$key]["guid"] = $price['key'];
            }
        }
        array_unshift($prices, array(
            "name" => "Розничная",
            "price" => $remain['price'],
            "guid" => "0"
        ));
        return $prices;
    }

    /**
     * Берем цены поставщика
     * @return array
     */
    public function getPrices($properties){
        $output = array(

        );
        $query = $this->modx->newQuery("slStoresRemainsPrices");
        $query->leftJoin("slStoresRemains", "slStoresRemains", "slStoresRemains.id = slStoresRemainsPrices.remain_id");
        $query->leftJoin("slStores", "slStores", "slStores.id = slStoresRemains.store_id");
        $query->where(array(
            "slStoresRemains.store_id" => $properties['store_id']
        ));
        $query->select(array("DISTINCT slStoresRemainsPrices.key as guid, slStoresRemainsPrices.name"));
        if($query->prepare() && $query->stmt->execute()){
            $output = $query->stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        array_unshift($output, array(
            "name" => "Розничная",
            "guid" => "0"
        ));
        return $output;
    }

    /**
     * Берем категории
     *
     * @param $warehouse_id
     * @return void
     */
    public function getOptCategories($warehouse_id){
        $categories = array();
        $query = $this->modx->newQuery("slStoresRemainsCategories");
        $query->select(array(
            "`slStoresRemainsCategories`.*",
            "`slStoresRemainsCategories`.name as pagetitle",
            "`slStoresRemainsCategories`.name as label"
        ));
        $query->where(array(
            "`slStoresRemainsCategories`.`store_id`:=" => $warehouse_id,
            "`slStoresRemainsCategories`.`guid`:!=" => '00000000-0000-0000-0000-000000000000',
        ));
        $query->sortby("name", "ASC");
        if ($query->prepare() && $query->stmt->execute()) {
            $categories = $query->stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($categories as $key => $category){
                $categories[$key]['key'] = $category['id'];
            }
        }
        return $categories;
    }

    /**
     * Билдим рекурсивно дерево категорий
     *
     */
    public function buildOptCategoriesTree (array $categories, $parentGuid = '00000000-0000-0000-0000-000000000000', $idKey = 'guid', $level = 2) {
        $branch = array();
        foreach ($categories as $element) {
            if ($element['parent_guid'] == $parentGuid) {
                if($level){
                    $children = $this->buildOptCategoriesTree($categories, $element[$idKey], $idKey, $level - 1);
                    if ($children) {
                        $element['children'] = $children;
                    }
                }
                // $elem_id = $element['id'];
                unset($element['guid']);
                unset($element['parent_guid']);
                $branch[] = $element;
                unset($element);
            }
        }
        return $branch;
    }

    /**
     * Берем каталог и строим меню
     *
     * @param $properties
     * @return array
     */
    public function getCatalog($properties){
        if($properties['warehouse_id']){
            $options = array(
                xPDO::OPT_CACHE_KEY => 'default/stores_catalogs',
            );
            $str = $this->modx->cacheManager->get($properties['warehouse_id'], $options);
            if($str){
                $data = $str;
            }else{
                $categories = $this->getOptCategories($properties['warehouse_id']);
                $data = $this->buildOptCategoriesTree($categories);

                $this->modx->cacheManager->set($properties['warehouse_id'], $data, 86400, $options);
            }
        }else{
            $data = $this->modx->runSnippet('pdoMenu', array(
                "parents" => 4,
                "level" => 2,
                "where" => '{"class_key":"msCategory"}',
                "includeTVs" => "menu_image",
                "processTVs" => 1,
                "return" => "data",
                "context" => "web"
            ));

            $urlMain = $this->modx->getOption("site_url");

            foreach ($data as $key => $value) {
                if($data[$key]['menu_image']){
                    $data[$key]['menu_image'] = $urlMain . $value['menu_image'];
                }
                /*
                foreach ($value['children'] as $k => $v) {
                    $data[$key]['children'][$k]['children'] = $urlMain . "assets/content/" . $v['menu_image'];
                }*/
            }
        }
        return $data;
    }

    /**
     * Берем поставщика
     *
     * @param $properties
     * @return array
     */
    public function getWarehouse($properties){
        if($properties['warehouse_id']){
            return $this->sl->store->getStore($properties['warehouse_id']);
        }else{
            return array();
        }
    }

    /**
     * Поставщики работает на основе флага VISIBLE в slWarehouseStores
     *
     * @param $properties
     * @return array|void
     */
    public function getVendors($properties){
        $data = array();
        $iids = array();
        $count = 0;
        $urlMain = $this->modx->getOption("site_url");
        // get selected
        $query = $this->modx->newQuery("slWarehouseStores");
        $query->leftJoin("slStores", "slStores", "slStores.id = slWarehouseStores.warehouse_id");
        $query->select(array(
            "`slStores`.*"
        ));
        $query->where(array(
            "`slWarehouseStores`.`org_id`:=" => $properties['id'],
            "`slWarehouseStores`.`visible`:=" => 1,
            "`slStores`.`active`:=" => true
        ));
        if ($query->prepare() && $query->stmt->execute()) {
            $selected = $query->stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($selected as $key => $value) {
                $iids[] = $value['id'];
                $selected[$key]['image'] = $urlMain . "assets/content/" . $value['image'];
                if($selected[$key]['coordinats']){
                    $selected[$key]['mapcoordinates'] = explode(",", $selected[$key]['coordinats']);
                    foreach($selected[$key]['mapcoordinates'] as $k => $coord){
                        $selected[$key]['mapcoordinates'][$k] = floatval(trim($coord));
                    }
                    $selected[$key]['mapcoordinates'] = array_reverse($selected[$key]['mapcoordinates']);
                }
                unset($selected[$key]['apikey']);
                $count++;
            }

            $data['selected_count'] = $count;
            $data['selected'] = $selected;
        }
        $query = $this->modx->newQuery("slWarehouseStores");
        $query->leftJoin("slStores", "slStores", "slStores.id = slWarehouseStores.warehouse_id");
        $query->select(array(
            "`slStores`.*"
        ));
        $query->where(array(
            "`slWarehouseStores`.`org_id`:=" => $properties['id'],
            "`slStores`.`active`:=" => true
        ));
        $query->where(array(
            "`slStores`.`warehouse`:=" => 1,
            "OR:`slStores`.`vendor`:=" => 1
        ));
        $data['available_count'] = $this->modx->getCount('slWarehouseStores', $query);
        $query->where(array(
            "`slWarehouseStores`.`visible`:=" => 0
        ));
        if($properties['filter']){
            $query->where(array(
                "`slStores`.`name`:LIKE" => "%".$properties['filter']."%",
                "OR:`slStores`.`address`:LIKE" => "%".$properties['filter']."%"
            ));
        }
        if($iids){
            $query->where(array(
                "`slWarehouseStores`.`warehouse_id`:NOT IN" => $iids
            ));
        }
        // $query->prepare();
        // $this->modx->log(1, $query->toSQL());
        if ($query->prepare() && $query->stmt->execute()) {
            $vendors = $query->stmt->fetchAll(PDO::FETCH_ASSOC);
            $count = 0;

            foreach ($vendors as $key => $value) {
                if(!in_array($value['id'], $iids)){
                    $vendors[$key]['image'] = $urlMain . "assets/content/" . $value['image'];
                    if($vendors[$key]['coordinats']){
                        $vendors[$key]['mapcoordinates'] = explode(",", $vendors[$key]['coordinats']);
                        foreach($vendors[$key]['mapcoordinates'] as $k => $coord){
                            $vendors[$key]['mapcoordinates'][$k] = floatval(trim($coord));
                        }
                        $vendors[$key]['mapcoordinates'] = array_reverse($vendors[$key]['mapcoordinates']);
                    }
                    unset($vendors[$key]['apikey']);
                }else{
                    unset($vendors[$key]);
                }
                $count++;
            }
            // $data['available_count'] = $count;
            $data['available'] = $vendors;
            return $data;
        }
    }

    /**
     * Берем товары из категории
     *
     * @param $properties
     * @return array
     */
    public function getProducts($properties) {
        $urlMain = $this->sl->config["urlMain"];
        if($properties["warehouse_id"]){
            $properties['category_id'] = 'all';
            $av[] = $properties["warehouse_id"];
        }else{
            $warehouses = $this->sl->orgHandler->getWarehouses($properties['id'], 1);
            $av = array();
            foreach($warehouses as $wh){
                $av[] = $wh['id'];
            }
        }
        $data = array(
            "categories" => array()
        );
        if($properties['category_id'] != 'all'){
            $query = $this->modx->newQuery("modResource");
            $query->leftJoin("modTemplateVarResource", "modTemplateVarResource", "modTemplateVarResource.contentid = modResource.id AND modTemplateVarResource.tmplvarid = 8");
            $query->where(array(
                "modResource.class_key:=" => "msCategory",
                "modResource.published:=" => 1,
                "modResource.deleted:=" => 0,
                "modResource.parent" => $properties['category_id']
            ));
            $query->select(array("modResource.*, modTemplateVarResource.value as image"));
            if($query->prepare() && $query->stmt->execute()){
                $data["categories"] = $query->stmt->fetchAll(PDO::FETCH_ASSOC);
                foreach($data["categories"] as $key => $res){
                    if($res['image']){
                        $data["categories"][$key]['image'] = $urlMain . 'assets/content/' . $res['image'];
                    }else{
                        $data["categories"][$key]['image'] = $urlMain . $this->modx->getPlaceholder("+conf_noimage");
                    }
                    $data["categories"][$key]['image'] = str_replace("//assets", "/assets", $data["categories"][$key]['image']);
                }
            }
        }

        $remain_catalog = 0;
        // $this->modx->log(1, print_r($properties, 1));
        if($properties["warehouse_cat_id"]){
            if($cat = $this->modx->getObject("slStoresRemainsCategories", $properties["warehouse_cat_id"])){
                $remain_catalog = $cat->get("guid");
            }
            if($remain_catalog){
                $properties['category_id'] = 'all';
                $query = $this->modx->newQuery("slStoresRemainsCategories");
                $query->where(array(
                    "slStoresRemainsCategories.parent_guid:=" => $remain_catalog,
                    "slStoresRemainsCategories.store_id:=" => $properties["warehouse_id"]
                ));
                $query->select(array("slStoresRemainsCategories.*"));
                $query->sortby("name", "ASC");
                if($query->prepare() && $query->stmt->execute()){
                    $data["categories"] = $query->stmt->fetchAll(PDO::FETCH_ASSOC);
                    foreach($data["categories"] as $key => $res){
                        $data["categories"][$key]['pagetitle'] = $res['name'];
                        $data["categories"][$key]['image'] = $urlMain . $this->modx->getPlaceholder("+conf_noimage");
                        $data["categories"][$key]['image'] = str_replace("//assets", "/assets", $data["categories"][$key]['image']);
                    }
                }
            }
        }

        // нашли поставщиков
        if(count($av)){
            $remains = array();
            if($properties['search']){
                foreach($av as $warehouse){
                    $res = $this->sl->search->getOptBigResults($properties['search'], array("store_id" => array($warehouse)), 99000, 0);
                    if($res["matches"]){
                        foreach($res["matches"] as $key => $v){
                            $remains[] = $key;
                        }
                    }
                }
            }
            if(count($remains)){
                $properties['category_id'] = 'all';
            }
            $query = $this->modx->newQuery("slStoresRemains");
            $query->leftJoin("msProductData", "msProductData", "msProductData.id = slStoresRemains.product_id");
            $query->leftJoin("modResource", "modResource", "modResource.id = slStoresRemains.product_id");
            if($properties['category_id'] != "all"){
                $query->where(array(
                    "`msProductData`.`available`:=" => 1,
                ));
            }
            $query->where(array(
                "`slStoresRemains`.`store_id`:IN" => $av,
                "`slStoresRemains`.`remains`:>" => 0,
                "`slStoresRemains`.`price`:>" => 0
            ));
            if(count($remains)){
                $query->where(array(
                    "`slStoresRemains`.`id`:IN" => $remains,
                ));
            }
            if($properties['category_id'] != "all" && !$remain_catalog){
                $query->where(array(
                    "`modResource`.`parent`:=" => $properties['category_id']
                ));
            }else{
                if($remain_catalog){
                    $query->where(array(
                        "`slStoresRemains`.`catalog_guid`:=" => $remain_catalog
                    ));
                }
            }

            $query->select(array(
                "`slStoresRemains`.*",
                "`slStoresRemains`.`id` as remain_id",
                "`msProductData`.*",
                "`modResource`.*",
                "COALESCE(`modResource`.pagetitle, `slStoresRemains`.name) as pagetitle",
                "COALESCE(`msProductData`.vendor_article, `slStoresRemains`.article) as article",
                "`slStoresRemains`.price as price",
            ));
            if($properties['category_id'] != "all") {
                $query->groupby("slStoresRemains.product_id");
            }else{
                $query->groupby("slStoresRemains.id");
            }
            // Подсчитываем общее число записей
            $data['total'] = $this->modx->getCount('slStoresRemains', $query);

            // Устанавливаем лимит 1/10 от общего количества записей
            // со сдвигом 1/20 (offset)
            if($properties['page'] && $properties['perpage']) {
                $limit = $properties['perpage'];
                $offset = ($properties['page'] - 1) * $properties['perpage'];
                $query->limit($limit, $offset);
            }

            if ($query->prepare() && $query->stmt->execute()) {
                $data['items'] = $query->stmt->fetchAll(PDO::FETCH_ASSOC);
            }

            foreach ($data['items'] as $key => $value) {
                if($value['image']){
                    $data['items'][$key]['image'] = $urlMain . $value['image'];
                }else{
                    $data['items'][$key]['image'] = $urlMain . $this->modx->getPlaceholder("+conf_noimage");
                }

                $q = $this->modx->newQuery("slStoresRemains");
                $q->leftJoin("slStores", "slStores", "slStores.id = slStoresRemains.store_id");
                $q->select(array(
                    "`slStoresRemains`.*",
                    "`slStores`.name_short as store_name"
                ));
                if($properties['category_id'] != "all" && !$remain_catalog) {
                    $q->where(array("`slStoresRemains`.`product_id`:=" => $value['product_id']));
                }else{
                    $q->where(array("`slStoresRemains`.`id`:=" => $value['remain_id']));
                    if($remain_catalog){
                        $q->where(array("`slStoresRemains`.`catalog_guid`:=" => $remain_catalog));
                    }
                }
                $q->where(array(
                    "`slStoresRemains`.`remains`:>" => 0,
                    "`slStoresRemains`.`guid`:!=" => "",
                    "`slStoresRemains`.`store_id`:IN" => $av
                ));
                if ($q->prepare() && $q->stmt->execute()) {
                    $data['items'][$key]['stores'] = $q->stmt->fetchAll(PDO::FETCH_ASSOC);
                    foreach ($data['items'][$key]['stores'] as $key_store => $value_store) {
                        $data['items'][$key]['stores'][$key_store]['old_price'] = $data['items'][$key]['stores'][$key_store]['price'];
                        $data['items'][$key]['stores'][$key_store]['remain_id'] = $data['items'][$key]['remain_id'];
                        $data['items'][$key]['stores'][$key_store]['payer'] = $this->getPayer($value_store['store_id'], $data['items'][$key]['remain_id'], $properties['id']);
                        $data['items'][$key]['stores'][$key_store]['delay'] = $this->getOffsetPay($value_store['store_id'], $data['items'][$key]['remain_id'], $properties['id']);
                        $data['items'][$key]['stores'][$key_store]['remains'] = $this->getRemainRemains($data['items'][$key]['remain_id']);
                        if($data['items'][$key]['remain_id'] == 786194){
                            $this->modx->log(1, $value_store['store_id'].' || '.$data['items'][$key]['remain_id']." || ".$properties['id']);
                        }
                        $actions = $this->getAvailableActions($value_store['store_id'], $data['items'][$key]['remain_id'], $properties['id']);

                        foreach ($actions as $key_action => $value_action) {
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

                            $data['items'][$key]['stores'][$key_store]['action'] = $this->getInfoProduct(array(
                                "remain_id" => $value_action['remain_id'],
                                "store_id" => $value_action['store_id']
                            ))['action'];

                            $actions[$key_action]['conflicts'] = $this->getConflicts(array("store_id" => $value_action['store_id'], "remain_id" => $value_action['remain_id']));

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
                                $actions[$key_action]['image'] = "assets/content/" . $value_action['image'];
                            } else{
                                $actions[$key_action]['image'] = "assets/files/img/nopic.png";
                            }
                            if($value_action['icon']){
                                $actions[$key_action]['icon'] = "assets/content/" . $actions[$key_action]['icon'];
                            }else{
                                $actions[$key_action]['icon'] = "assets/files/img/nopic.png";
                            }
                        }
                        $new_price = $this->getPrice($data['items'][$key]['stores'][$key_store]['store_id'], $data['items'][$key]['stores'][$key_store]['id'], 1, $properties['id']);
                        if($new_price < $data['items'][$key]['stores'][$key_store]['price']){
                            $data['items'][$key]['stores'][$key_store]['old_price'] = $data['items'][$key]['stores'][$key_store]['price'];
                        }
                        $data['items'][$key]['stores'][$key_store]['price'] = $new_price;


                        //Проверка, есть ли товар в корзине
                        if($_SESSION['basket'][$properties['id']][$value_store['store_id']]['products'][$value_store['id']] != null) {
                            $data['items'][$key]['stores'][$key_store]['basket'] = array(
                                "availability" => true,
                                "count" => $_SESSION['basket'][$properties['id']][$value_store['store_id']]['products'][$value_store['id']]['count']
                            );
                        } else{
                            $data['items'][$key]['stores'][$key_store]['basket'] = array(
                                "availability" => false,
                                "count" => 1
                            );
                        }

                        $data['items'][$key]['stores'][$key_store]['actions'] = $actions;
                        $data['items'][$key]['stores'][$key_store]['delivery'] = $this->sl->cart->getNearShipment($data['items'][$key]['remain_id'], $data['items'][$key]['stores'][$key_store]['store_id']);
                        $data['items'][$key]['stores'][$key_store]['delivery_day'] = date("Y-m-d", time()+60*60*24*$data['items'][$key]['stores'][$key_store]['delivery']);
                    }

                    //Комлпекты товара
                    $data['items'][$key]['complects'] = $this->getProductComplects(array(
                        "remain_id" => $data['items'][$key]['remain_id'],
                        "store_id" => $data['items'][$key]['store_id'],
                        "id" => $properties['id'])
                    );
                    // Подсчитываем общее число записей
                    $data['items'][$key]['total_stores'] = count($data['items'][$key]['stores']);
                }
            }
        }else{
            $data['total'] = 0;
            $data['items'] = array();
        }

        $object = $this->modx->getObject("modResource", $properties['category_id']);
        if($object) {
            $data['page'] = $object->toArray();
        }
        return $data;
    }

    /**
     * Если остатки у кого-то скрыты
     *
     * @param $remain_id
     * @return mixed|string|void
     */
    public function getRemainRemains ($remain_id) {
        $query = $this->modx->newQuery("slStoresRemains");
        $query->where(array("slStoresRemains.id:=" => $remain_id));
        $query->select(array("slStoresRemains.*"));
        if($query->prepare() && $query->stmt->execute()){
            $remain = $query->stmt->fetch(PDO::FETCH_ASSOC);
            $remain_config = $this->sl->store->getStoreSetting($remain['store_id'], "hide_remains");
            if($remain_config["value"]){
                return $this->getRemainAbstract($remain['remains']);
            }else{
                return $remain['remains'];
            }
        }
    }

    /**
     * Абстракция остатков
     *
     * @param $remains
     * @return string|void
     */
    public function getRemainAbstract($remains){
        if($remains == 0){
            return "Нет в наличии";
        }
        if($remains > 0 && $remains < 10){
            return "Мало";
        }
        if($remains >= 10 && $remains < 30){
            return "Достаточно";
        }
        if($remains >= 30 && $remains < 50){
            return "Много";
        }
        if($remains >= 50){
            return "Очень много";
        }
    }

    /**
     * Получить информацию о товаре с учётом его активных акций
     *
     * @param $properties
     * @return array
     */

    public function getInfoProduct($properties){
        $q_a = $this->modx->newQuery("slActions");
        $q_a->leftJoin("slActionsProducts", "slActionsProducts", "slActions.id = slActionsProducts.action_id");
        $q_a->select(array(
            "`slActions`.*",
            "`slActionsProducts`.*",
            "`slActions`.description as description",
        ));

        $q_a->where(array(
            "`slActionsProducts`.`remain_id`:=" => $properties['remain_id'],
            "`slActions`.`store_id`:=" => $properties['store_id'],
            "`slActions`.`active`:=" => 1,
            "`slActions`.`type`:=" => 1,
        ));

        if ($q_a->prepare() && $q_a->stmt->execute()) {
            $actions = $q_a->stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($actions as $key_action => $value_action) {
                if ($key_action != 0) {
                    //Вот тут обработка совместимости
                    if ($main_compatibility == '1' && $value_action['compatibility_discount'] == '1') {
                        $actions[$key_action]['enabled'] = true;
                    } else {
                        $actions[$key_action]['enabled'] = false;
                    }
                } else {
                    //Первая попавшая акция - АКТИВНАЯ
                    $actions[$key_action]['enabled'] = true;
                    $main_compatibility = $value_action['compatibility_discount'];
                }

                if ($_SESSION['actions'][$value_action['store_id']][$value_action['remain_id']][$value_action['action_id']] == false || $_SESSION['actions'][$value_action['store_id']][$value_action['remain_id']][$value_action['action_id']] == true) {
                    $actions[$key_action]['enabled'] = $_SESSION['actions'][$value_action['store_id']][$value_action['remain_id']][$value_action['action_id']];
                }

                if ($actions[$key_action]['enabled']) {
                    $result['action'] = $value_action;
                }
            }
        }

        $result['conflicts'] = $this->getConflicts($properties);

        return $result;

    }

    /**
     * Чекаем остаток
     *
     * @param $id_remain
     * @param $store_id
     * @return array
     */
    public function getRemain($id_remain, $store_id){
        $output = array();
        //Проверяем, есть ли нужное количество товаров в магазине
        $q = $this->modx->newQuery("slStoresRemains");
        $q->select(array(
            "`slStoresRemains`.*"
        ));
        $q->where(array(
            "`slStoresRemains`.`id`:=" => $id_remain,
            "`slStoresRemains`.`remains`:>" => 0,
            "`slStoresRemains`.`guid`:!=" => "",
            "`slStoresRemains`.`store_id`:=" => $store_id
        ));

        if ($q->prepare() && $q->stmt->execute()) {
            $output = $q->stmt->fetch(PDO::FETCH_ASSOC);
        }
        return $output;
    }

    /**
     * Добавление товаров в корзину
     *
     * @param $properties
     * @return array
     */
    public function addBasket($properties) {
        //Проверяем, есть ли нужное количество товаров в магазине
        // unset($_SESSION['analytics_user']['basket']);
        $this->modx->log(1, print_r($properties, 1));
        if($properties['id_remain']){
            $remain = $this->getRemain($properties['id_remain'], $properties['store_id']);
            if($remain){
                $valueBasket = $_SESSION['basket'][$properties['id']][$properties['store_id']]['products'][$properties['id_remain']];
                //Проверяем, есть ли у пользователя в сесии такой товар в нужном магазине
                if($_SESSION['basket'][$properties['id']][$properties['store_id']]['products'][$properties['id_remain']]) {
                    //Проверяем, хватает ли товаров на складе
                    //Если хватает, кладём в корзину. Если не хватает, кладём всё, что осталось на складе
                    if($valueBasket['count'] + $properties['value'] <= $remain['remains']){
                        $_SESSION['basket'][$properties['id']][$properties['store_id']]['products'][$properties['id_remain']] = array(
                            "count" => $valueBasket['count'] + $properties['value'],
                            "price" => $this->getPrice($properties['store_id'], $properties['id_remain'], $valueBasket['count'] + $properties['value'], $properties['id'])
                        );
                    }else{
                        $_SESSION['basket'][$properties['id']][$properties['store_id']]['products'][$properties['id_remain']] = array(
                            "count" => (int) $remain['remains'],
                            "price" => $this->getPrice($properties['store_id'], $properties['id_remain'], (int) $remain['remains'], $properties['id'])
                        );
                    }
                }else{
                    if($properties['value'] <= $remain['remains']){
                        $_SESSION['basket'][$properties['id']][$properties['store_id']]['products'][$properties['id_remain']] = array(
                            "count" => (int) $properties['value'],
                            "price" => $this->getPrice($properties['store_id'], $properties['id_remain'], (int) $remain['value'], $properties['id'])
                        );
                    }else{
                        $_SESSION['basket'][$properties['id']][$properties['store_id']]['products'][$properties['id_remain']] = array(
                            "count" => (int) $remain['remains'],
                            "price" => $this->getPrice($properties['store_id'], $properties['id_remain'], (int) $remain['remains'], $properties['id'])
                        );
                    }
                }
                foreach($_SESSION['basket'][$properties['id']][$properties['store_id']]['products'] as $k => $v){
                    $_SESSION['basket'][$properties['id']][$properties['store_id']]['products'][$k] = array(
                        "count" => $v['count'],
                        "price" => $this->getPrice($properties['store_id'], $k, $v['count'], $properties['id'])
                    );
                }
            }
        }
        if ($properties['id_complect']) {
            $complect_data = $this->getRemainComplect($properties['store_id'], $properties['id_complect']);
            // обрабатываем комплект
            if($_SESSION['basket'][$properties['id']][$properties['store_id']]['complects'][$properties['id_complect']]) {
                $valueBasket = $_SESSION['basket'][$properties['id']][$properties['store_id']]['complects'][$properties['id_complect']];
                //TODO пофиксить проблему с количеством, всегда true
                $needle = $valueBasket['count'] + $properties['value'];
                if($needle < $complect_data['min_count']) {
                    $_SESSION['basket'][$properties['id']][$properties['store_id']]['complects'][$properties['id_complect']] = array(
                        "count" => $valueBasket['count'] + $properties['value'],
                        "price" => $this->getPriceComplect($properties['store_id'], $properties['id_complect']),
                        "complect_data" => $complect_data,
                        "id" => $properties['id_complect']
                    );
                }else{
                    $_SESSION['basket'][$properties['id']][$properties['store_id']]['complects'][$properties['id_complect']] = array(
                        "count" => $complect_data['min_count'],
                        "price" => $this->getPriceComplect($properties['store_id'], $properties['id_complect']),
                        "complect_data" => $complect_data,
                        "id" => $properties['id_complect']
                    );
                }
            }else{
                if($properties['value'] <= $complect_data['min_count']) {
                    $_SESSION['basket'][$properties['id']][$properties['store_id']]['complects'][$properties['id_complect']] = array(
                        "count" => $properties['value'],
                        "price" => $this->getPriceComplect($properties['store_id'], $properties['id_complect']),
                        "complect_data" => $complect_data,
                        "id" => $properties['id_complect']
                    );
                }else{
                    $_SESSION['basket'][$properties['id']][$properties['store_id']]['complects'][$properties['id_complect']] = array(
                        "count" => $complect_data['min_count'],
                        "price" => $this->getPriceComplect($properties['store_id'], $properties['id_complect']),
                        "complect_data" => $complect_data,
                        "id" => $properties['id_complect']
                    );
                }
            }
        }
        $this->modx->log(1, print_r($_SESSION['basket'][$properties['id']], 1));
        // TODO: предусмотреть, если произошла ошибка
        return $this->getBasket(array(
            "id" => $properties['id'],
        ));
    }

    /**
     * Получаем остатки на складе комплектов
     *
     * @param $properties
     * @return array|float|int
     */

    public function getRemainComplect($store_id, $id_complect){
        $output = array();
        $complect = $this->modx->getObject("slComplects", $id_complect);
        if ($complect) {
            if($complect->get("store_id") == $store_id){
                $q = $this->modx->newQuery("slComplectsProducts");
                $q->leftJoin('slStoresRemains', 'slStoresRemains', 'slStoresRemains.id = slComplectsProducts.remain_id');
                $q->where(array("`slComplectsProducts`.`complect_id`:=" => $id_complect));
                $q->select(array(
                    'slComplectsProducts.*',
                    'slStoresRemains.*',
                    "`slStoresRemains`.`id` as remain_id"
                ));
                $minCount = 0;
                if ($q->prepare() && $q->stmt->execute()) {
                    $products = $q->stmt->fetchAll(PDO::FETCH_ASSOC);
                    $output["products"] = $products;
                    foreach ($products as $product){
                        if($minCount == 0){
                            $minCount = intdiv($product['remains'], $product['multiplicity']);
                        } elseif($minCount > intdiv($product['remains'], $product['multiplicity'])) {
                            $minCount = intdiv($product['remains'], $product['multiplicity']);
                        }
                    }
                    $output["min_count"] = $minCount;
                    $remain_config = $this->sl->store->getStoreSetting($store_id, "hide_remains");
                    if($remain_config){
                        $output["min_count_abstract"] = $this->getRemainAbstract($output["min_count"]);
                    }
                    return $output;
                }
            }
        }
    }


    /**
     * Получаем полную цену коплекта
     *
     * @param $properties
     * @return array
     */
    public function getPriceComplect($store_id, $id_complect){
        $complect = $this->modx->getObject("slComplects", $id_complect);
        if ($complect) {
            if($complect->get("store_id") == $store_id) {
                $q = $this->modx->newQuery("slComplectsProducts");
                $q->where(array("`slComplectsProducts`.`complect_id`:=" => $id_complect));
                $q->select(array(
                    'slComplectsProducts.*',
                ));
                $sum = 0;
                if ($q->prepare() && $q->stmt->execute()) {
                    $products = $q->stmt->fetchAll(PDO::FETCH_ASSOC);
                    foreach ($products as $product) {
                        $sum = $sum + $product['new_price'] * $product['multiplicity'];
                    }
                }
            }
            return $sum;
        }
    }

    /**
     * Данные подключенного клиента и базовая скидка
     *
     * @param $client_id
     * @param $warehouse_id
     * @return array
     */
    public function getClientData($client_id, $warehouse_id){
        $data = array();
        $query = $this->modx->newQuery("slWarehouseStores");
        $query->where(array(
            "org_id:=" => $client_id,
            "warehouse_id:=" => $warehouse_id
        ));
        $query->select(array("slWarehouseStores.*"));
        if($query->prepare() && $query->stmt->execute()){
            $data = $query->stmt->fetch(PDO::FETCH_ASSOC);
        }
        return $data;
    }

    /**
     * Централизованное взятие плательщика
     *
     * @param $store_id
     * @param $remain_id
     * @param $count
     * @param $owner_id
     * @return array
     */
    public function getPayer ($store_id, $remain_id, $owner_id) {
        // По умолчанию плательщик за доставку магазин
        $payer = 2;
        $actions = $this->getAvailableActions($store_id, $remain_id, $owner_id);
        foreach($actions as $action){
            if($remain_id == 784356) {
                $this->modx->log(1, print_r($action, 1));
            }
            // без условий
            if($action['delivery_payment_terms'] == 0){
                $payer = $action['payer'];
            }else{
                $elements = $this->sl->analyticsSales->getActionProducts($action['action_id']);
                $products = array();
                if(count($elements['products'])) {
                    foreach ($elements['products'] as $k => $v) {
                        $products[] = $k;
                    }
                }
                $cart = $this->getBasketSimple(array("id" => $owner_id));
                $fact_cost = 0;
                $sku = array();
                foreach($cart["stores"][$store_id]['products'] as $k => $v){
                    if(in_array($k, $products)){
                        $fact_cost += $v['info']['price'] * $v['info']['count'];
                        $sku[] = $k;
                    }
                }
                $fact_sku = count($sku);
                if($remain_id == 784356) {
                    $this->modx->log(1, $action['delivery_payment_terms'].' => '.$action['delivery_payment_value']);
                    $this->modx->log(1, $fact_sku.' => '.$fact_cost);
                }
                // проверяем условие плательщика
                if($action['delivery_payment_terms'] == 1){
                    // условие на рубли
                    if($action['delivery_payment_value'] <= $fact_cost){
                        $payer = $action['payer'];
                    }
                }
                if($action['delivery_payment_terms'] == 2){
                    // условие на шт (Уникальность SKU)
                    if($action['delivery_payment_value'] <= $fact_sku){
                        $payer = $action['payer'];
                    }
                }
                $this->modx->log(1, 'PAYER::'.$payer);
            }
        }
        return $payer;
    }

    /**
     * Считаем отсрочку
     *
     * @param $action_id
     * @return float|int
     */
    public function getDelay($action_id){
        $delay = 0;
        $q_g = $this->modx->newQuery("slActionsDelay");
        $q_g->select(array(
            "`slActionsDelay`.*",
        ));
        $q_g->where(array(
            "`slActionsDelay`.`action_id`:=" => $action_id
        ));
        if ($q_g->prepare() && $q_g->stmt->execute()) {
            $elems = $q_g->stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach($elems as $k => $v){
                $delay += ($v['percent'] / 100) * $v['day'];
            }
        }
        return $delay;
    }

    /**
     * Централизованное взятие отсрочки
     *
     * @param $store_id
     * @param $remain_id
     * @param $count
     * @param $owner_id
     * @return array
     */
    public function getOffsetPay ($store_id, $remain_id, $owner_id) {
        // По умолчанию плательщик за доставку магазин
        $delay = 0;
        $actions = $this->getAvailableActions($store_id, $remain_id, $owner_id);
        foreach($actions as $action){
            // без условий
            if($action['delay_condition'] == 0){
                $delay = $this->getDelay($action['action_id']);
            }else{
                $elements = $this->sl->analyticsSales->getActionProducts($action['action_id']);
                $products = array();
                if(count($elements['products'])) {
                    foreach ($elements['products'] as $k => $v) {
                        $products[] = $k;
                    }
                }
                $cart = $this->getBasketSimple(array("id" => $owner_id));
                $fact_cost = 0;
                $sku = array();
                foreach($cart["stores"][$store_id]['products'] as $k => $v){
                    if(in_array($k, $products)){
                        $fact_cost += $v['info']['price'] * $v['info']['count'];
                        $sku[] = $k;
                    }
                }
                $fact_sku = count($sku);
                // проверяем условие плательщика
                if($action['delay_condition'] == 1){
                    // условие на рубли
                    if($action['delay_condition_value'] <= $fact_cost){
                        $delay = $this->getDelay($action['action_id']);
                    }
                }
                if($action['delay_condition'] == 2){
                    // условие на шт (Уникальность SKU)
                    if($action['delay_condition_value'] <= $fact_sku){
                        $delay = $this->getDelay($action['action_id']);
                    }
                }
            }
        }
        return $delay;
    }

    /**
     * Берем доступные акции
     *
     * @param $store_id
     * @param $remain_id
     * @param $owner_id
     * @return array
     */
    public function getAvailableActions ($store_id, $remain_id, $owner_id) {
        $actions = array();
        $today = date_create();
        $date = date_format($today, 'Y-m-d H:i:s');
        // TODO: set multiple cities & regions
        $store_data = $this->sl->tools->getStoreInfo($owner_id);
        $q_a = $this->modx->newQuery("slActions");
        $q_a->leftJoin("slActionsProducts", "slActionsProducts", "slActionsProducts.action_id = slActions.id");
        $q_a->leftJoin("slActionsStores", "slActionsStores", "slActionsStores.action_id = slActions.id AND slActionsStores.store_id = ".$owner_id);
        $q_a->leftJoin("slStores", "slStores", "slStores.id = slActions.store_id");
        $q_a->select(array(
            "`slActions`.*",
            "`slActionsProducts`.*",
        ));
        $q_a->where(array(
            "`slActionsStores`.`active`:=" => 1,
            "FIND_IN_SET('".$store_data["city_id"]."', REPLACE(REPLACE(REPLACE(`slActions`.`cities`, '\"', ''), '[', ''), ']','')) > 0",
            "FIND_IN_SET('".$store_data["region_id"]."', REPLACE(REPLACE(REPLACE(`slActions`.`regions`, '\"', ''), '[', ''), ']','')) > 0",
            "slActions.participants_type:=" => 3
        ), xPDOQuery::SQL_OR);
        $q_a->where(array(
            "`slActionsProducts`.`remain_id`:=" => $remain_id,
            "`slActions`.`store_id`:=" => $store_id,
            "`slActions`.`active`:=" => 1,
            "`slActions`.`type`:=" => 1,
            "`slStores`.`opt_marketplace`:=" => 1,
            "`slActions`.`date_from`:<=" => $date,
            "`slActions`.`date_to`:>=" => $date
        ), xPDOQuery::SQL_AND);
        $q_a->prepare();
        $this->modx->log(1, $q_a->toSQL());
        if ($q_a->prepare() && $q_a->stmt->execute()) {
            $actions = $q_a->stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        return $actions;
    }

    /**
     * Цена товара с учетом акций
     *
     * @param $store_id
     * @param $remain_id
     * @param $count
     * @return float|int|mixed|void
     */
    public function getPrice($store_id, $remain_id, $count, $owner_id) {
        // TODO: учесть нововведенные параметры
        // $this->modx->log(1, print_r($owner_id, 1));
        // "slStoresRemainsPrices"
        $price = $this->sl->store->getStoreSetting($store_id, "opt_price");
        $q = $this->modx->newQuery("slStoresRemains");
        if($price['value']){
            $q->leftJoin("slStoresRemainsPrices", "slStoresRemainsPrices", "slStoresRemainsPrices.remain_id = slStoresRemains.id AND slStoresRemainsPrices.key = '{$price['value']}'");
            $q->select(array(
                "`slStoresRemains`.*",
                "COALESCE(slStoresRemainsPrices.price, slStoresRemains.price, 0) as price",
            ));
        }else{
            $q->select(array(
                "`slStoresRemains`.*",
            ));
        }
        $q->where(array(
            "`slStoresRemains`.`id`:=" => $remain_id,
            "`slStoresRemains`.`remains`:>" => 0,
            "`slStoresRemains`.`guid`:!=" => "",
            "`slStoresRemains`.`store_id`:=" => $store_id
        ));
        if ($q->prepare() && $q->stmt->execute()) {
            $storeRemains = $q->stmt->fetch(PDO::FETCH_ASSOC);
            $actions = $this->getAvailableActions($store_id, $remain_id, $owner_id);
            $base_koef = 1;
            $client_data = $this->getClientData($owner_id, $store_id);
            if($client_data){
                if($client_data['base_sale']){
                    $base_koef = 1 - ($client_data['base_sale'] * 0.01);
                }
            }
            $min_price = $storeRemains['price'];
            foreach ($actions as $key_action => $value_action) {
                // 0 - скидка без условий, 1 - Купи X товаров по Y цене (с кратностью)
                $this->modx->log(1, print_r($value_action, 1));
                if($value_action["not_sale_client"]){
                    $base_koef = 1;
                }

                if($value_action['condition_type'] == 0 || $value_action['condition_type'] == 1){
                    if((int) $value_action['multiplicity'] > 1){

                        // Ограничение по сумме в акции
//                        if($value_action['limit_type'] == 1){
                            //Расчёт цены с кратность
                            //Количество товаров, на которое не действует скидка
                            $remain_multiplicity = $count % $value_action['multiplicity'];
                            //Количество товаров, на которое действует скидка
                            $sale_multiplicity = $count - $remain_multiplicity;

                            $calc_price = ($remain_multiplicity * $value_action['old_price'] + $sale_multiplicity * $value_action['new_price']) / $count;
                            if($calc_price < $min_price) {
                                $min_price = $calc_price;
                            }
//                        }
//                        else if ($value_action['limit_type'] == 2) {
//                            $q = $this->modx->newQuery("slActionsProducts");
//                            $q->select(array(
//                                'slActionsProducts.*'
//                            ));
//                            $q->where(array("`slActionsProducts`.`action_id`:=" => $value_action['action_id']));
//                            $q->prepare();
//                            $this->modx->log(1, $q->toSQL());
//                            if ($q->prepare() && $q->stmt->execute()) {
//                                $products = $q->stmt->fetchAll(PDO::FETCH_ASSOC);
//
//                                //Сумма товаров по этой акции в корзине пользователя
//                                $sum_basket = 0;
//
//                                foreach ($products as $k => $product){
//                                    if($_SESSION['basket'][$owner_id][$store_id]['products'][$product['remain_id']] && $remain_id != $product['remain_id']){
//                                        $sum_basket = $sum_basket + $_SESSION['basket'][$owner_id][$store_id]['products'][$product['remain_id']]['price'] * $_SESSION['basket'][$owner_id][$store_id]['products'][$product['remain_id']]['count'];
//                                    }
//                                }
//                        TODO нужно считать товары в корзине по скидке, а не все подряд
//
//                                //Расчёт цены с кратность
//                                //Количество товаров, на которое не действует скидка
//                                $remain_multiplicity = $count % $value_action['multiplicity'];
//                                //Количество товаров, на которое действует скидка
//                                $sale_multiplicity = $count - $remain_multiplicity;
//
//                                $calc_price = ($remain_multiplicity * $value_action['old_price'] + $sale_multiplicity * $value_action['new_price']) / $count;
//                                if($calc_price < $min_price && $value_action['limit_sum'] >  ($calc_price * $count) + $sum_basket) {
//                                    $min_price = $calc_price;
//                                }
//
//                                $this->modx->log(1, "__________________ calc_price {$calc_price}");
//                                $this->modx->log(1, "__________________ calc_price {$count}");
//                                $this->modx->log(1, "__________________ calc_price + sum_basket" . ($calc_price + $sum_basket));
//
//                            }
//                        }

                    }else{
                        if($calc_price < $min_price) {
                            $min_price = $value_action['new_price'];
                        }
                    }
                }
                // 4 - Купи на X рублей и получи скидку на Y%
                if($value_action['condition_type'] == 4){
                    // TODO: учесть кратность? Комплекты?
                    // сначала проверим условие акции
                    $m_action_price = $value_action['condition_min_sum'];
                    $m_action_sku = $value_action['condition_SKU'];
                    // берем наши сущности
                    $elements = $this->sl->analyticsSales->getActionProducts($value_action['action_id']);
                    $this->modx->log(1, print_r($elements, 1));
                    // $complects = array();
                    $products = array();
                    if(count($elements['products'])){
                        foreach($elements['products'] as $k => $v){
                            $products[] = $k;
                        }
                        $cart = $this->getBasket(array("id" => $owner_id));
                        $fact_cost = 0;
                        $sku = array();
                        $this->modx->log(1, print_r($cart["stores"][$store_id]['products'], 1));
                        foreach($cart["stores"][$store_id]['products'] as $k => $v){
                            if(in_array($k, $products)){
                                $fact_cost += $v['info']['price'] * $v['info']['count'];
                                $sku[] = $k;
                            }
                        }
                        $this->modx->log(1, "FACT COST: {$fact_cost} FACT SKU: {$fact_cost} ACTION COST: {$m_action_price} ACTION SKU: {$m_action_sku}");
                        $fact_sku = count($sku);
                        if($fact_cost >= $m_action_price && $fact_sku >= $m_action_sku){
                            $min_price = $elements['products'][$remain_id]['new_price'];
                        }
                    }
                }
            }
            $storeRemains['price'] = $min_price * $base_koef;
            foreach ($actions as $key_action => $value_action) {
                //Применяется последней (от стоимости товара по всем акциям)
                if($value_action['action_last']){
                    $storeRemains['price'] = $storeRemains['price'] - ($storeRemains['price'] * ((($value_action['old_price'] - $value_action['new_price']) / ($value_action['old_price'] / 100)) / 100));
                }
            }
            return $storeRemains['price'];
        }
    }

    /**
     * FIX рекурсии
     *
     * @param $properties
     * @return array|void|null
     */
    public function getBasketSimple($properties) {
        $result = array();
        $total_cost = 0;
        $total_weight = 0;
        $total_volume = 0;
        if($properties['id']){
            // $this->modx->log(1, print_r($_SESSION['basket'][$properties['id']], 1));
            if($_SESSION['basket'][$properties['id']]){
                $urlMain = $this->modx->getOption("site_url");
                foreach ($_SESSION['basket'][$properties['id']] as $key => $value){
                    $q = $this->modx->newQuery("slStores");
                    $q->select(array(
                        "`slStores`.*"
                    ));
                    $q->where(array(
                        "`slStores`.`id`:=" => $key
                    ));
                    if ($q->prepare() && $q->stmt->execute()) {
                        $store = $q->stmt->fetch(PDO::FETCH_ASSOC);
                        $colors = $this->modx->getOption('shoplogistic_store_colors');
                        $colors = trim($colors);
                        $colorsArray = explode(",", $colors);
                        $result['stores'][$key] = $store;
                        $cost = 0;
                        $weight = 0;
                        $volume = 0;
                        foreach ($value['products'] as $k => $v){
                            $query = $this->modx->newQuery("slStoresRemains");
                            $query->leftJoin("modResource", "modResource", "modResource.id = slStoresRemains.product_id");
                            $query->leftJoin("msProductData", "msProductData", "msProductData.id = slStoresRemains.product_id");

                            $query->select(array(
                                "`msProductData`.*",
                                "`slStoresRemains`.*",
                                "`slStoresRemains`.id as id_remain"
                            ));
                            $query->where(array(
                                "`slStoresRemains`.`id`:=" => $k,
                                "`slStoresRemains`.`remains`:>" => 0,
                                "`slStoresRemains`.`guid`:!=" => "",
                                "`slStoresRemains`.`store_id`:=" => $key
                            ));

                            if ($query->prepare() && $query->stmt->execute()) {
                                $product = $query->stmt->fetch(PDO::FETCH_ASSOC);
                                $total_cost = $total_cost + $v['count'] * $v['price'];
                                $cost = $cost + $v['count'] * $v['price'];

                                $params = $this->sl->product->getProductParams($product['id']);
                                $weight += $params[0]['product']["weight_brutto"] * $v['count'];
                                $volume += $params[0]['product']["length"] * $params[0]['product']["width"] * $params[0]['product']["height"] * $v['count'];

                                $total_weight += $params[0]['product']["weight_brutto"] * $v['count'];
                                $total_volume += $params[0]['product']["length"] * $params[0]['product']["width"] * $params[0]['product']["height"] * $v['count'];

                                $product['info'] = $v;
                                $result['stores'][$key]['products'][$k] = $product;
                            }
                        }

                        foreach ($value['complects'] as $k => $v){
                            $q_c = $this->modx->newQuery("slComplectsProducts");
                            $q_c->leftJoin('slStoresRemains', 'slStoresRemains', 'slStoresRemains.id = slComplectsProducts.remain_id');
                            $q_c->leftJoin('msProductData', 'msProduct', 'msProduct.id = slStoresRemains.product_id');
                            $q_c->leftJoin('modResource', 'modResource', 'modResource.id = slStoresRemains.product_id');

                            $q_c->where(array("`slComplectsProducts`.`complect_id`:=" => $k));

                            $q_c->select(array(
                                'slComplectsProducts.*',
                                'slStoresRemains.price as price',
                                'COALESCE(modResource.pagetitle, slStoresRemains.name) as name',
                                'COALESCE(msProduct.image, "/assets/files/img/nopic.png") as image',
                                'COALESCE(msProduct.vendor_article, slStoresRemains.article) as article',
                                "`slStoresRemains`.`id` as remain_id"
                            ));

                            if ($q_c->prepare() && $q_c->stmt->execute()) {
                                $products = $q_c->stmt->fetchAll(PDO::FETCH_ASSOC);

                                foreach ($products as $key_p => $product){
                                    if($product['image']){
                                        $product['image'] = $urlMain . $product['image'];
                                    }else{
                                        $product['image'] = $urlMain . $this->modx->getPlaceholder("+conf_noimage");
                                    }
                                    $product['image'] = str_replace("//assets", "/assets", $product['image']);
                                    $total_cost = $total_cost + $v['count'] * $product['multiplicity'] * $product['new_price'];
                                    $cost = $cost + $v['count'] * $product['multiplicity'] * $product['new_price'];

                                    //$result['test'] = $v['count'];
                                    //$result['test'][$key_p] = $product['new_price'];


                                    $params = $this->sl->product->getProductParams($product['id']);
                                    $weight += $params[0]['product']["weight_brutto"] * $v['count'];
                                    $volume += $params[0]['product']["length"] * $params[0]['product']["width"] * $params[0]['product']["height"] * $v['count'];

                                    $total_weight += $params[0]['product']["weight_brutto"] * $v['count'];
                                    $total_volume += $params[0]['product']["length"] * $params[0]['product']["width"] * $params[0]['product']["height"] * $v['count'];

                                    $product['info']['count'] = $v['count'] * $product['multiplicity'];
                                    $product['info']['price'] = $product['multiplicity'] * $product['new_price'];

                                    $result['stores'][$key]['complects'][$k]['info'] = $v;
                                    $result['stores'][$key]['complects'][$k]['products'][$key_p] = $product;
                                }
                            }
                        }

                        $result['stores'][$key]['cost'] = $cost;
                        $result['stores'][$key]['weight'] = $weight;
                        $result['stores'][$key]['volume'] = round(($volume / 1000000), 3);
                    }
                }

                $result['cost'] = $total_cost;
                $result['weight'] = $total_weight;
                $result['volume'] = round(($total_volume / 1000000), 3);
                return $result;
            }else{
                return null;
            }
        }
    }

    /**
     * Получить корзину
     *
     * @param $properties
     * @return stdClass
     */
    public function getBasket($properties) {
        $result = array();
        $total_cost = 0;
        $total_weight = 0;
        $total_volume = 0;
        if($properties['id']){
            // $this->modx->log(1, print_r($_SESSION['basket'][$properties['id']], 1));
            if($_SESSION['basket'][$properties['id']]){
                $urlMain = $this->modx->getOption("site_url");
                foreach ($_SESSION['basket'][$properties['id']] as $key => $value){
                    $q = $this->modx->newQuery("slStores");
                    $q->select(array(
                        "`slStores`.*"
                    ));
                    $q->where(array(
                        "`slStores`.`id`:=" => $key
                    ));
                    if ($q->prepare() && $q->stmt->execute()) {
                        $store = $q->stmt->fetch(PDO::FETCH_ASSOC);
                        $colors = $this->modx->getOption('shoplogistic_store_colors');
                        $colors = trim($colors);
                        $colorsArray = explode(",", $colors);
                        if(($store['id'] % 4) == 0){
                            if($colorsArray[0]){
                                $store['color'] = $colorsArray[0];
                            }
                            $store['color'] = "#50C0E6";
                        }elseif($store['id'] % 3){
                            if($colorsArray[1]){
                                $store['color'] = $colorsArray[1];
                            }
                            $store['color'] = "#6CA632";
                        }elseif($store['id'] % 2){
                            if($colorsArray[2]){
                                $store['color'] = $colorsArray[2];
                            }
                            $store['color'] = "#3237A6";
                        }else{
                            if($colorsArray[3]){
                                $store['color'] = $colorsArray[3];
                            }
                            $store['color'] = "#A63232";
                        }
                        $result['stores'][$key] = $store;
                        $cost = 0;
                        $weight = 0;
                        $volume = 0;
                        foreach ($value['products'] as $k => $v){
                            $query = $this->modx->newQuery("slStoresRemains");
                            $query->leftJoin("modResource", "modResource", "modResource.id = slStoresRemains.product_id");
                            $query->leftJoin("msProductData", "msProductData", "msProductData.id = slStoresRemains.product_id");

                            $query->select(array(
                                "`msProductData`.*",
                                "`slStoresRemains`.*",
                                "`slStoresRemains`.id as id_remain"
                            ));
                            $query->where(array(
                                "`slStoresRemains`.`id`:=" => $k,
                                "`slStoresRemains`.`remains`:>" => 0,
                                "`slStoresRemains`.`guid`:!=" => "",
                                "`slStoresRemains`.`store_id`:=" => $key
                            ));

                            if ($query->prepare() && $query->stmt->execute()) {
                                $product = $query->stmt->fetch(PDO::FETCH_ASSOC);
                                $product['payer'] = $this->getPayer($key, $k, $properties['id']);
                                $product['delay'] = $this->getOffsetPay($product['store_id'], $product['id_remain'], $properties['id']);


                                $actions = $this->getAvailableActions($product['store_id'], $product['id_remain'], $properties['id']);

                                foreach ($actions as $ka => $action) {
                                    if($action['icon']){
                                        $actions[$ka]['icon'] = "assets/content/" . $actions[$ka]['icon'];
                                    }
                                    $actions[$ka]['conflicts'] = $this->getConflicts(array("store_id" => $action['store_id'], "remain_id" => $action['remain_id']));
                                }
                                $product['actions'] = $actions;



                                if($product['image']){
                                    $product['image'] = $urlMain . $product['image'];
                                }else{
                                    $product['image'] = $urlMain . $this->modx->getPlaceholder("+conf_noimage");
                                }

                                $total_cost = $total_cost + $v['count'] * $v['price'];
                                $cost = $cost + $v['count'] * $v['price'];

                                $params = $this->sl->product->getProductParams($product['id']);
                                $weight += $params[0]['product']["weight_brutto"] * $v['count'];
                                $volume += $params[0]['product']["length"] * $params[0]['product']["width"] * $params[0]['product']["height"] * $v['count'];

                                $total_weight += $params[0]['product']["weight_brutto"] * $v['count'];
                                $total_volume += $params[0]['product']["length"] * $params[0]['product']["width"] * $params[0]['product']["height"] * $v['count'];

                                $product['info'] = $v;
                                $result['stores'][$key]['products'][$k] = $product;
                            }
                        }

                        foreach ($value['complects'] as $k => $v){
                            $q_c = $this->modx->newQuery("slComplectsProducts");
                            $q_c->leftJoin('slStoresRemains', 'slStoresRemains', 'slStoresRemains.id = slComplectsProducts.remain_id');
                            $q_c->leftJoin('msProductData', 'msProduct', 'msProduct.id = slStoresRemains.product_id');
                            $q_c->leftJoin('modResource', 'modResource', 'modResource.id = slStoresRemains.product_id');

                            $q_c->where(array("`slComplectsProducts`.`complect_id`:=" => $k));

                            $q_c->select(array(
                                'slComplectsProducts.*',
                                'slStoresRemains.price as price',
                                'COALESCE(modResource.pagetitle, slStoresRemains.name) as name',
                                'COALESCE(msProduct.image, "/assets/files/img/nopic.png") as image',
                                'COALESCE(msProduct.vendor_article, slStoresRemains.article) as article',
                                "`slStoresRemains`.`id` as remain_id"
                            ));

                            if ($q_c->prepare() && $q_c->stmt->execute()) {
                                $products = $q_c->stmt->fetchAll(PDO::FETCH_ASSOC);

                                foreach ($products as $key_p => $product){
                                    if($product['image']){
                                        $product['image'] = $urlMain . $product['image'];
                                    }else{
                                        $product['image'] = $urlMain . $this->modx->getPlaceholder("+conf_noimage");
                                    }
                                    $product['image'] = str_replace("//assets", "/assets", $product['image']);
                                    $total_cost = $total_cost + $v['count'] * $product['multiplicity'] * $product['new_price'];
                                    $cost = $cost + $v['count'] * $product['multiplicity'] * $product['new_price'];

                                    //$result['test'] = $v['count'];
                                    //$result['test'][$key_p] = $product['new_price'];


                                    $params = $this->sl->product->getProductParams($product['id']);
                                    $weight += $params[0]['product']["weight_brutto"] * $v['count'];
                                    $volume += $params[0]['product']["length"] * $params[0]['product']["width"] * $params[0]['product']["height"] * $v['count'];

                                    $total_weight += $params[0]['product']["weight_brutto"] * $v['count'];
                                    $total_volume += $params[0]['product']["length"] * $params[0]['product']["width"] * $params[0]['product']["height"] * $v['count'];

                                    $product['info']['count'] = $v['count'] * $product['multiplicity'];
                                    $product['info']['price'] = $product['multiplicity'] * $product['new_price'];

                                    $result['stores'][$key]['complects'][$k]['info'] = $v;
                                    $result['stores'][$key]['complects'][$k]['products'][$key_p] = $product;
                                }
                            }
                        }

                        $result['stores'][$key]['cost'] = $cost;
                        $result['stores'][$key]['weight'] = $weight;
                        $result['stores'][$key]['volume'] = round(($volume / 1000000), 3);
                    }
                }

                $result['cost'] = $total_cost;
                $result['weight'] = $total_weight;
                $result['volume'] = round(($total_volume / 1000000), 3);
                $this->modx->log(1, print_r($result, 1));
                return $result;
            }else{
                return null;
            }
        }
    }

    /**
     * Обновление корзины
     *
     * @param $properties
     * @return array|stdClass|null
     */
    public function updateBasket($properties)
    {
        //Проверяем, есть ли нужное количество товаров в магазине
        // unset($_SESSION['basket']);
        if($properties['id_remain']){
            $remain = $this->getRemain($properties['id_remain'], $properties['store_id']);
            //Проверяем, есть ли у пользователя в сесии такой товар в нужном магазине
            if($_SESSION['basket'][$properties['id']][$properties['store_id']]['products'][$properties['id_remain']]){
                $valueBasket = $_SESSION['basket'][$properties['id']][$properties['store_id']]['products'][$properties['id_remain']];
                //Проверяем, хватает ли товаров на складе
                //Если хватает, кладём в корзину. Если не хватает, кладём всё, что осталось на складе
                if($properties['value'] <= $remain['remains']){
                    $_SESSION['basket'][$properties['id']][$properties['store_id']]['products'][$properties['id_remain']] = array(
                        "count" => $properties['value'],
                        "price" => $this->getPrice($properties['store_id'], $properties['id_remain'], $properties['value'], $properties['id'])
                    );
                }else{
                    $_SESSION['basket'][$properties['id']][$properties['store_id']]['products'][$properties['id_remain']] = array(
                        "count" => $remain['remains'],
                        "price" => $this->getPrice($properties['store_id'], $properties['id_remain'], $properties['value'], $properties['id'])
                    );
                }
            }
        }
        if($properties["id_complect"]){
            if($_SESSION['basket'][$properties['id']][$properties['store_id']]['complects'][$properties['id_complect']]){
                $complect_data = $this->getRemainComplect($properties['store_id'], $properties["id_complect"]);
                //Проверяем, хватает ли товаров на складе
                //Если хватает, кладём в корзину. Если не хватает, кладём всё, что осталось на складе
                if($properties['value'] <= $complect_data['min_count']){
                    $_SESSION['basket'][$properties['id']][$properties['store_id']]['complects'][$properties['id_complect']] = array(
                        "count" => $properties['value'],
                        "price" => $this->getPriceComplect($properties['store_id'], $properties['id_complect']),
                        "complect_data" => $complect_data,
                        "id" => $properties['id_complect']
                    );
                }else{
                    $_SESSION['basket'][$properties['id']][$properties['store_id']]['complects'][$properties['id_complect']] = array(
                        "count" => $properties['value'],
                        "price" => $this->getPriceComplect($properties['store_id'], $properties['id_complect']),
                        "complect_data" => $complect_data,
                        "id" => $properties['id_complect']
                    );
                }
            }
        }
        return $this->getBasket(array(
            "id" => $properties['id'],
        ));
    }

    /**
     * Удаление товара из корзины
     *
     * @param $properties
     * @return array|stdClass|null
     */
    public function clearBasket($properties, $store_id = 'all')
    {
        if($properties['id']){
            if($properties['store_id'] && $properties['id_remain'] || $properties['store_id'] && $properties['id_complect']){
                if($properties['id_remain']){
                    unset($_SESSION['basket'][$properties['id']][$properties['store_id']]['products'][$properties['id_remain']]);
                }
                if($properties['id_complect']){
                    unset($_SESSION['basket'][$properties['id']][$properties['store_id']]['complects'][$properties['id_complect']]);
                }
                if(!count($_SESSION['basket'][$properties['id']][$properties['store_id']]['products']) &&
                    !count($_SESSION['basket'][$properties['id']][$properties['store_id']]['complects'])){
                    unset($_SESSION['basket'][$properties['id']][$properties['store_id']]);
                }
                foreach($_SESSION['basket'][$properties['id']][$properties['store_id']]['products'] as $k => $v){
                    $_SESSION['basket'][$properties['id']][$properties['store_id']]['products'][$k] = array(
                        "count" => $v['count'],
                        "price" => $this->getPrice($properties['store_id'], $k, $v['count'], $properties['id'])
                    );
                }
            }else{
                if($store_id == 'all'){
                    unset($_SESSION['basket'][$properties['id']]);
                }else{
                    unset($_SESSION['basket'][$properties['id']][$store_id]);
                    if(!count($_SESSION['basket'][$properties['id']])){
                        unset($_SESSION['basket'][$properties['id']]);
                    }
                }
            }
        }
        return $this->getBasket(array(
            "id" => $properties['id'],
        ));
    }

    /**
     * Субмит заказа
     *
     * @param $properties
     * @return mixed
     */
    public function orderSubmit($properties){
        if($properties["id"]){
            $basket = $this->getBasket($properties);
            $this->modx->log(1, print_r($basket, 1));
            if($properties["store_id"] != 'all'){
                // TODO: чистка корзины только выбранного поставщика
                $order_data['products'] = $basket['stores'][$properties["store_id"]]["products"];
                $order_data['complects'] = $basket['stores'][$properties["store_id"]]["complects"];
                $order_data["warehouse_id"] = $properties["store_id"];
                $order_data["store_id"] = $properties["id"];
                $response[] = $this->orderSave($order_data);
                $this->clearBasket($properties, $properties["store_id"]);
            }else{
                foreach($basket['stores'] as $key => $val){
                    $order_data['products'] = $val["products"];
                    $order_data['complects'] = $val["complects"];
                    $order_data["warehouse_id"] = $key;
                    $order_data["store_id"] = $properties["id"];
                    $response[] = $this->orderSave($order_data);
                }
                $this->clearBasket($properties);
            }
        }

        return $this->sl->tools->success("", $response);
    }

    /**
     * Сохранение заказа
     *
     * @param $data
     * @return mixed
     */
    public function orderSave($data){
        $order_data = $this->orderGetCost($data);
        $order = $this->modx->newObject("slOrderOpt");
        $order->set("warehouse_id", $data["warehouse_id"]);
        $order->set("store_id", $data["store_id"]);
        $order->set("weight", $order_data["weight"]);
        $order->set("volume", $order_data["volume"]);
        $order->set("cost", $order_data["cost"]);
        $order->set("cart_cost", $order_data["cost"]);
        $order->set("createdon", time());
        $order->set("date", time());
        $order->save();
        foreach($data['products'] as $k => $item){
            $remain = $this->modx->getObject("slStoresRemains", $k);
            if($remain){
                $product_id = $remain->get("product_id");
                if($product_id){
                    $params = $this->sl->product->getProductParams($product_id);
                }else{
                    $params = array(
                        array(
                            "weight_brutto" => 0,
                            "name" => $remain->get("name"),
                            "length" => 0,
                            "width" => 0,
                            "height" => 0
                        )
                    );
                }
                $product = $this->modx->newObject("slOrderOptProduct");
                $product->set("remain_id", $k);
                $product->set("order_id", $order->get("id"));
                $product->set("name", $params[0]['name']);
                $product->set("count", $item['count']);
                $product->set("price", $item['price']);
                $product->set("weight", $params[0]['product']["weight_brutto"]);
                $product->set("cost", ($item['count'] * $item['price']));
                $product->save();
            }
        }
        // TODO: чекнуть правильно расчитает заказ
        foreach($data['complects'] as $kk => $complect){
            foreach($complect['products'] as $k => $prod){
                $product = $this->modx->newObject("slOrderOptProduct");
                $product->set("remain_id", $prod['remain_id']);
                $product->set("complect_id", $prod['complect_id']);
                $product->set("order_id", $order->get("id"));
                $product->set("name",  $prod['name']);
                $product->set("count", $prod['info']['count']);
                $product->set("price", $prod['price']);
                $product->set("weight", 0);
                $product->set("cost", $prod['info']['price']);
                $product->save();
            }
        }

        return $order->toArray();
    }

    /**
     * Формируем данные по заказу
     *
     * @param $data
     * @return int[]
     */
    public function orderGetCost($data){
        $output = array(
            "cost" => 0,
            "count" => 0,
            "weight" => 0,
            "volume" => 0
        );
        foreach($data['products'] as $k => $item){
            $output['cost'] += $item["price"] * $item['count'];
            $output['count'] += $item['count'];
            $params = $this->sl->product->getProductParams($k);
            $output['weight'] += $params[0]['product']["weight_brutto"] * $item['count'];
            $output['volume'] += $params[0]['product']["length"] * $params[0]['product']["width"] * $params[0]['product']["height"] * $item['count'];
        }
        foreach($data['complects'] as $kr => $complect){
            foreach($complect['products'] as $k => $item){
                $output['cost'] += $item['info']['count'] * $item['price'];
                $output['count'] += $item['count'];
                foreach($complect['products']['info']['complect_data']['products'] as $i => $t){
                    if($t['id'] == $item['id']){
                        if($t['product_id']){
                            $params = $this->sl->product->getProductParams($k);
                            $output['weight'] += $params[0]['product']["weight_brutto"] * $item['count'];
                            $output['volume'] += $params[0]['product']["length"] * $params[0]['product']["width"] * $params[0]['product']["height"] * $item['count'];
                        }
                    }
                }
            }
        }
        $output['volume'] = $output['volume'] / 1000000;
        return $output;
    }

    public function getOrdersSeller($properties) {
        if($properties["store_id"]){
            $result = array();

            if($properties["order_id"]){
                $query = $this->modx->newQuery("slOrderOpt");
                $query->rightJoin("slStores", "slStores", "slStores.id = slOrderOpt.store_id");
                $query->select(array(
                    "`slOrderOpt`.*",
                    "`slStores`.name_short",
                    "`slStores`.phone",
                ));
                $query->where(array(
                    "`slOrderOpt`.`warehouse_id`:=" => $properties['store_id'],
                    "`slOrderOpt`.`id`:=" => $properties['order_id']
                ));

//                $query->prepare();
//                $this->modx->log(1, $query->toSQL());
                if ($query->prepare() && $query->stmt->execute()) {
                    $result["order"] = $query->stmt->fetch(PDO::FETCH_ASSOC);

                    $query_p = $this->modx->newQuery("slOrderOptProduct");
                    $query_p->leftJoin("slStoresRemains", "slStoresRemains", "slStoresRemains.id = slOrderOptProduct.remain_id");
                    $query_p->leftJoin("msProductData", "msProductData", "msProductData.id = slStoresRemains.product_id");
                    $query_p->leftJoin("modResource", "modResource", "modResource.id = slStoresRemains.product_id");
                    $query_p->select(array(
                        "`slOrderOptProduct`.*",
                        "`msProductData`.*",
                        "`modResource`.*",
                        "COALESCE(msProductData.image, '/assets/files/img/nopic.png') as image",
                        "`slOrderOptProduct`.price as price",
                    ));
                    $query_p->where(array(
                        "`slOrderOptProduct`.`order_id`:=" => $result["order"]['id']
                    ));

                    if ($query_p->prepare() && $query_p->stmt->execute()) {
                        $result["order"]['products'] = $query_p->stmt->fetchAll(PDO::FETCH_ASSOC);
                    }
                }
            }
            else{
                $query = $this->modx->newQuery("slOrderOpt");
                $query->leftJoin("slStores", "slStores", "slStores.id = slOrderOpt.store_id");

                $query->select(array(
                    "`slOrderOpt`.*",
                    "`slStores`.name_short as store_name"
                ));
                $query->where(array(
                    "`slOrderOpt`.`warehouse_id`:=" => $properties['store_id']
                ));

                if($properties['filter']){
                    $criteria = array();
                    $criteria['slStores.name_short:LIKE'] = '%'.$properties['filter'].'%';
                    $criteria['OR:slOrderOpt.date:LIKE'] = '%'.$properties['filter'].'%';
                    $query->where($criteria);
                }

                $query->sortby("slOrderOpt.id", "DESC");
                // Подсчитываем общее число записей
                $result['total'] = $this->modx->getCount('slOrderOpt', $query);

                // Устанавливаем лимит 1/10 от общего количества записей
                // со сдвигом 1/20 (offset)
                if($properties['page'] && $properties['perpage']){
                    $limit = $properties['perpage'];
                    $offset = ($properties['page'] - 1) * $properties['perpage'];
                    $query->limit($limit, $offset);
                }

                if ($query->prepare() && $query->stmt->execute()) {
                    $result["orders"] = $query->stmt->fetchAll(PDO::FETCH_ASSOC);
                }
            }

            return $result;

        }
    }

    /**
     * Удаление комплекта
     */
    public function deleteComplect($properties){
        if($properties["complect_id"]){
            // TODO: сделать проверку прав доступа
            $complect = $this->modx->getObject('slComplects', $properties['complect_id']);
            if($complect){
                $complect_id = $complect->get("id");
                $crit = array(
                    "complect_id" => $complect_id
                );
                $this->modx->removeCollection("slComplectsProducts", $crit);
                $complect->remove();
            }
        }
    }

    /**
     * Создание/Редактирование комлектов
     *
     * @param $data
     * @return int[]
     */
    public function setComplect($properties)
    {
        $properties['dates'][0] = date('Y-m-d H:i:s', strtotime($properties['dates'][0]));
        $properties['dates'][1] = date('Y-m-d H:i:s', strtotime($properties['dates'][1]));
        $start = new DateTime($properties['dates'][0]);
        $start->setTime(00,00);
        $end = new DateTime($properties['dates'][1]);
        $end->setTime(23,59);

        if($properties['complect_id']){
            $complect = $this->modx->getObject('slComplects', $properties['complect_id']);
        }else{
            $complect = $this->modx->newObject('slComplects');
        }

        if($complect){
            $complect->set("store_id", $properties['store_id']);
            $complect->set("name", $properties['name']);
            $complect->set("date_from", $start->format('Y-m-d H:i:s'));
            $complect->set("date_to", $end->format('Y-m-d H:i:s'));

            $complect->save();

            if($complect->get('id')) {
                if ($properties['complect_id']) {
                    $crit = array(
                        "complect_id" => $properties['complect_id']
                    );
                    $this->modx->removeCollection("slComplectsProducts", $crit);
                }

                foreach($properties['products'] as $product){
                    $complect_p = $this->modx->newObject("slComplectsProducts");
                    $complect_p->set("complect_id", $complect->get('id'));
                    $complect_p->set("remain_id", $product['id']);
                    $price = (float)$product['price'];
                    $complect_p->set("old_price", $price);
                    $complect_p->set("new_price", $product['finalPrice']);
                    $complect_p->set("multiplicity", $product['multiplicity']);

                    //Тип цен
                    //$complect_p->set("type_price", $product['typePrice']['key']);

                    $complect_p->save();
                }

            }
            return $complect->toArray();
        }

    }

    /**
     * Включение/выключение комплекта
     *
     * @param $properties
     * @return void
     */
    public function approveComplect($properties){
        if($properties['store_id'] && $properties['complect_id']) {
            $complect = $this->modx->getObject("slComplects", array('id' => $properties['complect_id'], 'store_id' => $properties['store_id']));

            if($complect) {
                if($complect->active){
                    $complect->set("active", 0);
                }else{
                    $complect->set("active", 1);
                }
                $complect->save();
                return $complect->toArray();
            }
        }

        $result = array(
            "status" => false
        );
        return $result;
    }

    /**
     * Найти все/один комлпеты
     *
     * @param $properties
     * @return
     */
    public function getComplects($properties)
    {
        if($properties['complect_id']) {
            $complect = $this->modx->getObject("slComplects", $properties['complect_id']);
            if ($complect) {
                $data = $complect->toArray();
                $data['date_from'] = date('Y/m/d H:i:s', strtotime($data['date_from']));
                $data['date_to'] = date('Y/m/d H:i:s', strtotime($data['date_to']));

                $q = $this->modx->newQuery("slComplectsProducts");
                $q->leftJoin('slStoresRemains', 'slStoresRemains', 'slStoresRemains.id = slComplectsProducts.remain_id');
                $q->leftJoin('msProductData', 'msProduct', 'msProduct.id = slStoresRemains.product_id');
                $q->leftJoin('modResource', 'modResource', 'modResource.id = slStoresRemains.product_id');

                $q->where(array("`slComplectsProducts`.`complect_id`:=" => $properties['complect_id']));

                $q->select(array(
                    'slComplectsProducts.*',
                    'slStoresRemains.price as price',
                    'COALESCE(modResource.pagetitle, slStoresRemains.name) as name',
                    'COALESCE(msProduct.image, "/assets/files/img/nopic.png") as image',
                    'COALESCE(msProduct.vendor_article, slStoresRemains.article) as article',
                    "`slStoresRemains`.`id` as remain_id"
                ));


                if ($q->prepare() && $q->stmt->execute()) {
                    $products = $q->stmt->fetchAll(PDO::FETCH_ASSOC);

                    $selected = new stdClass();

                    foreach($products as $product){
                        $id = $product['remain_id'];
                        $product['id'] = $product['remain_id'];
                        $product['price'] = (float)$product['old_price'];
                        $product['discountInRubles'] = (float)$product['old_price'] - $product['new_price'];
                        $product['discountInterest'] = $product['discountInRubles'] / ($product['old_price'] / 100);
                        $product['finalPrice'] = (float)$product['new_price'];

                        $selected->$id = $product;
                    }

                    $data['products'] = $selected;
                }

                return $data;
            }
        } else{
            $q = $this->modx->newQuery("slComplects");
            $q->select(array(
                'slComplects.*'
            ));
            $q->where(array("`slComplects`.`store_id`:=" => $properties['store_id']));

            $idsComplects = array();
            //Если нет выбранных, выдаём весь список
            if($properties['selected']){

                foreach ($properties['selected'] as $key => $value) {
                    array_push($idsComplects, $value['id']);
                }

                //for($i = 0; $i < count($properties['selected']); $i++){
                //$idsProducts[$i] = $properties['selected'][$i]['id'];
                //}

                $q->where(array(
                    "slComplects.id:NOT IN" => $idsComplects
                ));
            }

            if($properties['filter']){
                $words = explode(" ", $properties['filter']);
                foreach($words as $word){
                    $criteria = array();
                    $criteria['slComplects.name:LIKE'] = '%'.trim($word).'%';
                    $q->where($criteria);
                }
            }

            $result = array();
            // Подсчитываем общее число записей
            $result['total'] = $this->modx->getCount("slComplects", $q);

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
            // $q->prepare();
            // $this->modx->log(1, $q->toSQL());
            if ($q->prepare() && $q->stmt->execute()) {
                $out = $q->stmt->fetchAll(PDO::FETCH_ASSOC);
                foreach($out as $key => $val){
                    $date_from = strtotime($val['date_from']);
                    $out[$key]['date_from'] = date("d.m.Y H:i", $date_from);
                    $date_to = strtotime($val['date_to']);
                    $out[$key]['date_to'] = date("d.m.Y H:i", $date_to);

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
                    $q_p->where(array("`slComplectsProducts`.`complect_id`:=" => $val['id']));

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
                    }

                }
                if(!$properties['selected']){
                    $result['complects'] = $out;
                }else{
                    $result['complects'] = $out;
                    $result['selected'] = $properties['selected'];
                }
                return $result;
            }
        }
    }

    /**
     * Найти комплекты определённого товара в определённом магазине
     *
     * @param $properties
     * @return
     */
    public function getProductComplects($properties){
        if($properties['remain_id'] && $properties['store_id']) {
            $q = $this->modx->newQuery("slActionsComplects");
            $q->leftJoin("slComplects", "slComplects", "slComplects.id = slActionsComplects.complect_id");
            $q->leftJoin("slComplectsProducts", "slComplectsProducts", "slComplects.id = slComplectsProducts.complect_id");

            $q->select(array(
                "`slComplects`.*",
                "`slActionsComplects`.*",
                "`slComplectsProducts`.*",
                "`slComplects`.id as id",
            ));

            $q->where(array(
                "`slComplectsProducts`.`remain_id`:=" => $properties['remain_id'],
                "`slComplects`.`store_id`:=" => $properties['store_id'],
                "`slComplects`.`active`:=" => 1,
            ));


            if ($q->prepare() && $q->stmt->execute()) {
                $complects = $q->stmt->fetchAll(PDO::FETCH_ASSOC);
                $result = [];
                $urlMain = $this->modx->getOption("site_url");


                foreach($complects as $key => $complect){
                    $query = $this->modx->newQuery("slComplects");
                    $query->leftJoin("slComplectsProducts", "slComplectsProducts", "slComplects.id = slComplectsProducts.complect_id");
                    $query->leftJoin("slStoresRemains", "slStoresRemains", "slStoresRemains.id = slComplectsProducts.remain_id");
                    $query->leftJoin("slStores", "slStores", "slStores.id = slStoresRemains.store_id");
                    $query->leftJoin("msProductData", "msProductData", "msProductData.id = slStoresRemains.product_id");
                    $query->leftJoin("modResource", "modResource", "modResource.id = slStoresRemains.product_id");

                    $query->select(array(
                        "`slComplects`.*",
                        "`slComplectsProducts`.*",
                        "`msProductData`.*",
                        "`modResource`.*",
                        "COALESCE(msProductData.image, '/assets/files/img/nopic.png') as image",
                        "`slComplects`.id as id",
                        "`slStores`.name_short as store_name"
                    ));

                    $query->where(array(
                        "`slComplectsProducts`.`complect_id`:=" => $complect['id'],
                    ));

                    if ($query->prepare() && $query->stmt->execute()) {
                        $compl = $query->stmt->fetchAll(PDO::FETCH_ASSOC);


                        foreach ($compl as $k => $compl_product){
                            $complect_data = $this->getRemainComplect($compl_product['store_id'], $compl_product['complect_id']);
                            $compl[$k]['delivery'] = $this->sl->cart->getNearShipment($compl_product['remain_id'], $compl_product['store_id']);
                            $compl[$k]['delivery_day'] = date("Y-m-d", time()+60*60*24 * $compl[$k]['delivery']);
                            $compl[$k]['remain_complect'] = $complect_data["min_count"];
                            $compl[$k]['image'] = $urlMain . $compl[$k]['image'];

                            //Проверка, есть ли комплект в корзине
                            if($_SESSION['basket'][$properties['id']][$compl_product['store_id']]['complects'][$compl_product['complect_id']] != null) {
                                $compl[$k]['basket'] = array(
                                    "availability" => true,
                                    "count" => $_SESSION['basket'][$properties['id']][$compl_product['store_id']]['complects'][$compl_product['complect_id']]['count']
                                );
                            } else{
                                $compl[$k]['basket'] = array(
                                    "availability" => false,
                                    "count" => 1
                                );
                            }
                        }
                        foreach ($compl as $k => $compl_product){
                            if($compl_product['remain_complect'] == 0){
                                unset($compl[$k]);
                            }
                        }
                        $remain = $this->getRemainComplect($compl_product['store_id'], $complect['id']);
                        if($remain)
                        $result[] = $compl;
                    }
                }

                return $result;
            }

        }
    }

    /**
     * Включение/выключение акции для пользователя
     *
     * @param $properties
     * @return
     */
    public function userAction($properties) {
        if($properties['remain_id'] && $properties['store_id'] && $properties['action_id']){
            //Проверяем, есть ли у пользователя в сесии такой товар в нужном магазине
            $_SESSION['actions'][$properties['store_id']][$properties['remain_id']][$properties['action_id']] = $properties['status'];
        }
        return $_SESSION['actions'];
    }

    public function getConflicts($properties)
    {
        $q = $this->modx->newQuery("slActions");
        $q->leftJoin("slActionsProducts", "slActionsProducts", "slActions.id = slActionsProducts.action_id");
        $q->select(array(
            "`slActions`.*",
            "`slActionsProducts`.*"
        ));

        $q->where(array(
            "`slActionsProducts`.`remain_id`:=" => $properties['remain_id'],
            "`slActions`.`store_id`:=" => $properties['store_id'],
            "`slActions`.`active`:=" => 1,
            "`slActions`.`type`:=" => 1,
        ));


        if ($q->prepare() && $q->stmt->execute()) {
            $actions = $q->stmt->fetchAll(PDO::FETCH_ASSOC);
            $conflict_global = false;

            foreach ($actions as $key => $value) {
                $conflict_temp = [];
                foreach ($actions as $k => $v) {
                    if($value['compatibility_discount'] == '2' && $key != $k) {
                        if($_SESSION['actions'][$v['store_id']][$v['remain_id']][$v['action_id']]) {
                            $conflict_temp[] = array(
                                "id" => $v['action_id'],
                                "name" => $v['name']
                            );
                            $conflict_global = true;
                        }
                    } else if($v['compatibility_discount'] == '2' && $key != $k) {
                        if($_SESSION['actions'][$v['store_id']][$v['remain_id']][$v['action_id']]) {
                            $conflict_temp[] = array(
                                "id" => $v['action_id'],
                                "name" => $v['name']
                            );
                            $conflict_global = true;
                        }
                    } else if($value['compatibility_discount'] == '3' || $v['compatibility_discount'] == '3') {
                        if($_SESSION['actions'][$v['store_id']][$v['remain_id']][$v['action_id']]) {
                            $big_sale_actions = substr($value['big_sale_actions'],0,-2);
                            $big_sale_actions = substr($big_sale_actions,2);
                            $big_sale_actions = explode('","', $big_sale_actions);
                                $isKey = in_array($v['action_id'], $big_sale_actions);
                                if(!$isKey && $key != $k) {
                                    $conflict_temp[] = array(
                                        "id" => $v['action_id'],
                                        "name" => $v['name']
                                    );
                                    $conflict_global = true;
                                }
                        }
                    } else if($value['compatibility_discount'] == '4' || $v['compatibility_discount'] == '4') {
                        if($_SESSION['actions'][$v['store_id']][$v['remain_id']][$v['action_id']]) {
                            $big_sale_actions = substr($value['big_sale_actions'],0,-2);
                            $big_sale_actions = substr($big_sale_actions,2);
                            $big_sale_actions = explode('","', $big_sale_actions);
                            $isKey = in_array($v['action_id'], $big_sale_actions);
                            if(!$isKey && $key != $k) {
                                $conflict_temp[] = array(
                                    "id" => $v['action_id'],
                                    "name" => $v['name']
                                );
                                $conflict_global = true;
                            }
                        }
                    }
                }
                $result['items'][$value['action_id']] = $conflict_temp;
            }
            $result['global'] = $conflict_global;
            return $result;
        }
    }

    public function generateOptXslx($properties){
        $basket = $this->getBasket($properties);

        $path = $this->sl->xslx->generateOptOrder($basket, $properties);
        $urlMain = $this->modx->getOption("site_url");

        return $urlMain . $path;
    }

    public function uploadProductsFile($properties){
        return $this->sl->xslx->processActionFile($properties['store_id'], $properties['file'], $properties['type']);
    }
}