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
                $response = $this->sl->orgHandler->getVendorsStores($properties);
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
            case 'get/orders/buyer':
                $response = $this->getOrdersBuyer($properties);
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
                $response = $this->getActionsAll($properties);
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
            case 'get/active/basket/warehouse':
                $response = $this->getWarehouseBasket($properties);
                break;
            case 'set/active/basket/warehouse':
                $response = $this->setWarehouseBasket($properties);
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
     * Собираем дерево каталогов рекурсивно
     * @param $parent
     * @return array
     */
    public function getParentsBlock($parent, $our_catalog = 1, $properties = array()){
        $parents = array($parent);
        if($our_catalog){
            $query = $this->modx->newQuery("modResource");
            $query->where(array(
                "modResource.class_key:=" => "msCategory",
                "modResource.parent:=" => $parent,
                "modResource.published:=" => 1,
                "modResource.deleted:=" => 0
            ));
            $query->select(array("modResource.id"));
            if($query->prepare() && $query->stmt->execute()){
                $items = $query->stmt->fetchAll(PDO::FETCH_ASSOC);
                if($items){
                    foreach($items as $p){
                        $parents[] = $p['id'];
                        $pars = $this->getParentsBlock($p['id']);
                        $parents = array_merge($parents, $pars);
                    }
                }
            }
        }else{
            $query = $this->modx->newQuery("slStoresRemainsCategories");
            $query->where(array(
                "slStoresRemainsCategories.store_id:=" => $properties["warehouse_id"],
                "slStoresRemainsCategories.parent_guid:=" => $parent,
                // "slStoresRemainsCategories.published:=" => 1,
                // "slStoresRemainsCategories.active:=" => 1
            ));
            $query->select(array("slStoresRemainsCategories.guid"));
            if($query->prepare() && $query->stmt->execute()){
                $items = $query->stmt->fetchAll(PDO::FETCH_ASSOC);
                if($items){
                    foreach($items as $p){
                        $parents[] = $p['guid'];
                        $pars = $this->getParentsBlock($p['guid'], 0, $properties);
                        $parents = array_merge($parents, $pars);
                    }
                }
            }
        }
        return array_unique($parents);
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
        if(is_array($properties['store_id'])){
            $query->where(array(
                "slStoresRemains.store_id:IN" => $properties['store_id']
            ));
        }else{
            $query->where(array(
                "slStoresRemains.store_id:=" => $properties['store_id']
            ));
        }

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
            "`slStoresRemainsCategories`.`active`:=" => 1,
            "`slStoresRemainsCategories`.`published`:=" => 1,
        ));
        $query->sortby("name", "ASC");
        if ($query->prepare() && $query->stmt->execute()) {
            $categories = $query->stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($categories as $key => $category){
                $categories[$key]['key'] = $category['id'];
                if(trim($category['name_alt']) != ''){
                    $categories[$key]['name'] = $category['name_alt'];
                    $categories[$key]['pagetitle'] = $category['name_alt'];
                    $categories[$key]['label'] = $category['name_alt'];
                }
                if($category["parent_guid"] != "00000000-0000-0000-0000-000000000000"){
                    $query = $this->modx->newQuery("slStoresRemainsCategories");
                    $query->select(array(
                        "`slStoresRemainsCategories`.*"
                    ));
                    $query->where(array(
                        "`slStoresRemainsCategories`.`store_id`:=" => $warehouse_id,
                        "`slStoresRemainsCategories`.`guid`:=" => $category["parent_guid"]
                    ));
                    if($query->prepare() && $query->stmt->execute()){
                        $par = $query->stmt->fetch(PDO::FETCH_ASSOC);
                        if($par){
                            $categories[$key]['parent'] = $par['id'];
                        }
                    }
                }else{
                    $categories[$key]['parent'] = 0;
                }
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
        if($properties['warehouse']){
            $orgs = $this->sl->orgHandler->getVendorsStores($properties);
            $result = array();
            if($orgs){
                foreach ($orgs['selected'] as $key => $select){
                    $result[$key]['image'] = $select['image'];
                    $result[$key]['id'] = $select['id'];
                    $result[$key]['pagetitle'] = $select['name_short'];

                    $options = array(
                        xPDO::OPT_CACHE_KEY => 'default/stores_catalogs',
                    );
                    $str = $this->modx->cacheManager->get($select['id'], $options);
                    if($str){
                        $result[$key]['children'] = $str;
                    }else{
                        $categories = $this->getOptCategories($select['id']);
                        $result[$key]['children'] = $this->buildOptCategoriesTree($categories);

                        $this->modx->cacheManager->set($select['id'], $result[$key]['children'], 86400, $options);
                    }
                }
            }

            return $result;
//            $options = array(
//                xPDO::OPT_CACHE_KEY => 'default/stores_catalogs',
//            );
//            $str = $this->modx->cacheManager->get($properties['warehouse_id'], $options);
//            if($str){
//                $data = $str;
//            }else{
//                $categories = $this->getOptCategories($properties['warehouse_id']);
//                $data = $this->buildOptCategoriesTree($categories);
//
//                $this->modx->cacheManager->set($properties['warehouse_id'], $data, 86400, $options);
//            }
        }else{
            $data = $this->modx->runSnippet('pdoMenu', array(
                "parents" => 4,
                "level" => 3,
                "where" => '{"class_key":"msCategory"}',
                "includeTVs" => "menu_image",
                "processTVs" => 1,
                "return" => "data",
                "context" => "web"
            ));
            foreach ($data as $key => $value) {
                if($data[$key]['menu_image']){
                    $images = $this->sl->tools->prepareImage($value['menu_image']);
                    $data[$key]['menu_image'] = $images["image"];
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
                "modResource.parent:=" => $properties["category_id"]
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
                foreach($av as $k => $warehouse){
                    $av[$k] = intval($warehouse);
                }
                // 1. Ищем четкие вхождения по артикулу
                $q_art = $this->modx->newQuery("slStoresRemains");
                $q_art->where(array(
                    "`slStoresRemains`.`remains`:>" => 0,
                    "`slStoresRemains`.`price`:>" => 1,
                    "`slStoresRemains`.`guid`:!=" => "",
                    "`slStoresRemains`.`store_id`:IN" => $av,
                    "`slStoresRemains`.`published`:=" => 1,
                    "`slStoresRemains`.`article`:=" => $properties['search'],
                ));
                $q_art->select(array("slStoresRemains.*"));
                if ($q_art->prepare() && $q_art->stmt->execute()) {
                    $rems = $q_art->stmt->fetchAll(PDO::FETCH_ASSOC);
                    if(count($rems)){
                        foreach($rems as $key => $v){
                            $remains[] = $v['id'];
                        }
                    }
                }
                // 2. Используем фразы
                if(!count($remains)){
                    $res = $this->sl->search->getOptBigResults($properties['search'], array("store_id" => $av), 99000, 0);
                    // $this->modx->log(1, print_r($res, 1));
                    if($res["matches"]){
                        foreach($res["matches"] as $key => $v){
                            $remains[] = $key;
                        }
                    }
                }
                $remains = array_unique($remains);
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
                "`slStoresRemains`.`price`:>" => 1
            ));
            if(count($remains)){
                $query->where(array(
                    "`slStoresRemains`.`id`:IN" => $remains,
                ));
            }
            if($properties['category_id'] != "all" && !$remain_catalog){
                $categories = $this->getParentsBlock($properties['category_id']);
                $query->where(array(
                    "`modResource`.`parent`:IN" => $categories,
                    "OR:`slStoresRemains`.`our_category_id`:IN" => $categories
                ));
            }else{
                if($remain_catalog){
                    $categories = $this->getParentsBlock($remain_catalog, 0, array("warehouse_id" => $properties["warehouse_id"]));
                    $query->where(array(
                        "`slStoresRemains`.`catalog_guid`:IN" => $categories
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
                $q->leftJoin("dartLocationCity", "dartLocationCity", "dartLocationCity.id = slStores.city");
                $q->select(array(
                    "`slStoresRemains`.*",
                    "`slStores`.name_short as store_name",
                    "`slStores`.image as store_image",
                    "`dartLocationCity`.city as store_city"
                ));
                if($properties['category_id'] != "all" && !$remain_catalog && $value['product_id'] > 0) {
                    $q->where(array("`slStoresRemains`.`product_id`:=" => $value['product_id']));
                }else{
                    $q->where(array("`slStoresRemains`.`id`:=" => $value['remain_id']));
                    if($remain_catalog){
                        $categories = $this->getParentsBlock($remain_catalog, 0, array("warehouse_id" => $properties["warehouse_id"]));
                        $q->where(array(
                            "`slStoresRemains`.`catalog_guid`:IN" => $categories
                        ));
                    }
                }
                $q->where(array(
                    "`slStoresRemains`.`available`:>" => 0,
                    "`slStoresRemains`.`price`:>" => 1,
                    "`slStoresRemains`.`guid`:!=" => "",
                    "`slStoresRemains`.`store_id`:IN" => $av
                ));
                if ($q->prepare() && $q->stmt->execute()) {
                    $data['items'][$key]['stores'] = $q->stmt->fetchAll(PDO::FETCH_ASSOC);
                    $id_warehouse = $this->getWarehouseBasket(array("id" => $properties['id']));
                    foreach ($data['items'][$key]['stores'] as $key_store => $value_store) {
                        $q_o = $this->modx->newQuery("slOrgStores");
                        $q_o->leftJoin("slOrg", "slOrg", "slOrg.id = slOrgStores.org_id");
                        $q_o->select(array(
                            "`slOrg`.name as org_name",
                        ));
                        $q_o->where(array("`slOrgStores`.`store_id`:=" => $value_store['store_id']));
                        if ($q_o->prepare() && $q_o->stmt->execute()) {
                            $org = $q_o->stmt->fetch(PDO::FETCH_ASSOC);
                            if($org) {
                                $data['items'][$key]['stores'][$key_store]['org_name'] = $org['org_name'];
                            }
                        }
                        $data['items'][$key]['stores'][$key_store]['store_image'] = $this->sl->tools->prepareImage($value_store['store_image'])['image'];
                        $data['items'][$key]['stores'][$key_store]['old_price'] = $data['items'][$key]['stores'][$key_store]['price'];
                        $data['items'][$key]['stores'][$key_store]['remain_id'] = $value_store['id'];
                        $data['items'][$key]['stores'][$key_store]['payer'] = $this->getPayer($value_store['store_id'], $value_store['id'], $properties['id']);
                        $data['items'][$key]['stores'][$key_store]['delay'] = $this->getOffsetPay($value_store['store_id'], $value_store['id'], $properties['id']);
                        $data['items'][$key]['stores'][$key_store]['max'] = $data['items'][$key]['stores'][$key_store]['available'];
                        $data['items'][$key]['stores'][$key_store]['remains'] = $this->getRemainRemains($value_store['id']);
                        $actions = array();
                        $actions = $this->getAvailableActions($value_store['store_id'], $value_store['id'], $properties['id']);

//                        if($value_store['id'] == 166020){
//                            $this->modx->log(1, "KENOST Action");
//                            $this->modx->log(1, "{$value_store['store_id']} | {$value_store['id']} | {$properties['id']}");
//                            $this->modx->log(1, print_r($actions, 1));
//                        }
                        $this->sl->tools->log($actions, "actions_pre");
//                        $this->modx->log(1, "KENOST DEVELOPER ДО:");
//                        $this->modx->log(1, print_r($actions, 1));
                        $actions = $this->setActionProduct(array("actions" => $actions, "id" => $properties['id'], "remain_id" => $value_store['id']));
//                        if($value_store['id'] == 165868){
//                            $this->modx->log(1, print_r(array("actions" => $actions, "store_id" => $value_store['store_id'], "id" => $value_store['id']), 1));
//                            $this->modx->log(1, "KENOST setActionProduct");
//                        }

//                        if($value_store['id'] == 166020){
//                            $this->modx->log(1, "KENOST Action 2");
//                            $this->modx->log(1, print_r($actions, 1));
//                        }
//                        $this->sl->tools->log($actions, "actions_post");
                        $conflicts = $this->getConflicts(array("id" => $properties['id'], "store_id" => $value_store['store_id'], "remain_id" => $value_store['id']));
                        $data['items'][$key]['stores'][$key_store]['conflicts'] = $conflicts;


//                        $this->modx->log(1, "KENOST DEVELOPER ПОСЛЕ:");
//                        $this->modx->log(1, print_r($actions, 1));

                        foreach ($actions as $key_action => $value_action) {
                            $data['items'][$key]['stores'][$key_store]['action'] = $this->getActionsAll(array(
                                "id" => $properties['id'],
                                "remain_id" => $value_action['remain_id'],
                                "store_id" => $value_action['store_id']
                            ))['action'];

                            $actions[$key_action]['tags'] = array();

                            //Получаем признаки акции
                            if(floatval($value_action['delay']) > 0){
                                $actions[$key_action]['tags'][] = array(
                                    "type" => "delay",
                                    "value" => floatval($value_action['delay'])
                                );
                            }
                            if(floatval($value_action['condition_min_sum']) > 0){
                                $actions[$key_action]['tags'][] = array(
                                    "type" => "min_sum",
                                    "value" => floatval($value_action['condition_min_sum'])
                                );
                            }
                            //Если выбрана доставка поставщиком
                            if($value_action['payer'] == 1){
                                //Если без условий
                                if($value_action['delivery_payment_terms'] == 0){
                                    $actions[$key_action]['tags'][] = array(
                                        "type" => "free_delivery",
                                        "condition" => $value_action['delivery_payment_terms'],
                                        "value" => 0
                                    );
                                }
                                //Если "Купи на Х рублей"
                                else if($value_action['delivery_payment_terms'] == 1){
                                    $actions[$key_action]['tags'][] = array(
                                        "type" => "free_delivery",
                                        "condition" => $value_action['delivery_payment_terms'],
                                        "value" => floatval($value_action['delivery_payment_value'])
                                    );
                                }
                                //Если "При покупке Х шт товара"
                                else if($value_action['delivery_payment_terms'] == 2){
                                    $actions[$key_action]['tags'][] = array(
                                        "type" => "free_delivery",
                                        "condition" => $value_action['delivery_payment_terms'],
                                        "value" => floatval($value_action['delivery_payment_value'])
                                    );
                                }
                            }

                            //Подарок
                            if($value_action['condition_type'] == 2){
                                $actions[$key_action]['tags'][] = array(
                                    "type" => "gift"
                                );
                            }

                            //Базовая скидка
                            if(floatval($value_action['type']) == 3){
                                if($value_action['old_price'] > 0){
                                    $actions[$key_action]['tags'][] = array(
                                        "type" => "sale",
                                        "value" => round(((floatval($value_action['old_price']) - floatval($value_action['new_price'])) / (floatval($value_action['old_price']) / 100)), 0),
                                        "min_count" => $value_action['min_count']
                                    );
                                }
                            }

                            //Кратность
                            $q_m = $this->modx->newQuery("slActionsProducts");
                            $q_m->select(array(
                                "`slActionsProducts`.*",
                            ));
                            $q_m->where(array(
                                "`slActionsProducts`.`action_id`:=" => $value_action['action_id'],
                                "`slActionsProducts`.`remain_id`:=" => $value_action['remain_id'],
                            ));

                            if ($q_m->prepare() && $q_m->stmt->execute()) {
                                $productAction = $q_m->stmt->fetch(PDO::FETCH_ASSOC);
                                if($productAction){
                                    if(floatval($productAction['multiplicity']) > 1){
                                        $actions[$key_action]['tags'][] = array(
                                            "type" => "multiplicity",
                                            "value" => floatval($productAction['multiplicity'])
                                        );
                                    }

                                    if($value_action['type'] != 3){
                                        if(floatval($productAction['old_price']) > floatval($productAction['new_price'])){
                                            $actions[$key_action]['tags'][] = array(
                                                "type" => "sale",
                                                "value" => round(((floatval($productAction['old_price']) - floatval($productAction['new_price'])) / (floatval($productAction['old_price']) / 100)), 0),
                                                "min_count" => $productAction['min_count']
                                            );
                                        }
                                    }
                                }
                            }

                            //Минимальное кол-во
                            if($value_action['min_count'] > 1){
                                $actions[$key_action]['tags'][] = array(
                                    "type" => "min",
                                    "value" => $value_action['min_count'],
                                );
                            }


//                            $actions[$key_action]["conflicts"] = $conflicts;


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
                                //$actions[$key_action]['image'] = $urlMain . "assets/content/" . $value_action['image'];
                                $actions[$key_action]['image'] = $this->sl->tools->prepareImageAction($value_action['image'], "w=1684&h=492&zc=1&q=99", "w=1442&h=422&zc=1&q=99", "w=708&h=208&zc=1&q=99");
                            } else{
                                $actions[$key_action]['image'] = $urlMain . "assets/files/img/nopic.png";
                            }
//                            if($value_action['icon']){
//                                $actions[$key_action]['icon'] = $urlMain . "assets/content/" . $actions[$key_action]['icon'];
//                            }else{
//                                $actions[$key_action]['icon'] = $urlMain . "assets/files/img/nopic.png";
//                            }
                        }

                        //Проверка, есть ли товар в корзине
                        if($_SESSION['basket'][$properties['id']][$id_warehouse][$value_store['store_id']]['products'][$value_store['id']] != null) {
                            $ids_actions = array();
                            $test_actions = $this->getAvailableActions($value_store['store_id'], $value_store['id'], $properties['id'], true);


                            foreach ($test_actions as $action){
                                $ids_actions[] = (int) $action['action_id'];
                            }
                            //Сортируем массив по возрастанию
                            sort($ids_actions);

                            //Проверить, есть ли товар с такими акциями в корзине?
                            $index = -1;

                            foreach ($_SESSION['basket'][$properties['id']][$id_warehouse][$value_store['store_id']]['products'][$value_store['id']] as $k_el => $elem){
                                if($elem['actions'] == $ids_actions){
                                    $index = $k_el;
                                    break;
                                }
                            }

                            if($index == -1){
                                $data['items'][$key]['stores'][$key_store]['basket'] = array(
                                    "availability" => false,
                                    "count" => $data['items'][$key]['stores'][$key_store]['action']['multiplicity']?:1,
                                    "ids_actions" => $ids_actions
                                );

                            } else{
                                $data['items'][$key]['stores'][$key_store]['basket'] = array(
                                    "availability" => true,
                                    "count" => $_SESSION['basket'][$properties['id']][$id_warehouse][$value_store['store_id']]['products'][$value_store['id']][$index]['count'],
                                    "ids_actions" => $ids_actions
                                );
                            }

                        } else{
                            $data['items'][$key]['stores'][$key_store]['basket'] = array(
                                "availability" => false,
                                "count" => $data['items'][$key]['stores'][$key_store]['action']['multiplicity']?:1,
                                "ids_actions" => []
                            );
                        }

                        $new_price = $this->getPrice($value_store['store_id'], $value_store['id'], $data['items'][$key]['stores'][$key_store]['basket']['count'], $properties['id']);

                        if($value_store['id'] == 784036){

                            $this->modx->log(1, "{$value_store['store_id']}");
                            $this->modx->log(1, "{$value_store['id']}");
                            $this->modx->log(1, "{$data['items'][$key]['stores'][$key_store]['basket']['count']}");
                            $this->modx->log(1, "{$properties['id']}");
                            $this->modx->log(1, "price: {$new_price}");
                            $this->modx->log(1, print_r($actions, 1));
                            $this->modx->log(1, "KENOST FIX PRICE");
                        }

                        if($new_price < $value_store['price']){
                            $data['items'][$key]['stores'][$key_store]['old_price'] = $data['items'][$key]['stores'][$key_store]['price'];
                        }
                        $data['items'][$key]['stores'][$key_store]['price'] = $new_price;

                        $data['items'][$key]['stores'][$key_store]['actions'] = $actions;
                        $data['items'][$key]['stores'][$key_store]['delivery'] = $this->sl->cart->getNearShipment($data['items'][$key]['stores'][$key_store]['store_id'], $this->getWarehouseBasket($properties));
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
                return $this->getRemainAbstract($remain['available']);
            }else{
                return $remain['available'];
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

    public function getActionsAll($properties){
        $actions = $this->getAvailableActions($properties['store_id'], $properties['remain_id'], $properties['id'], true);

//        if($properties['remain_id'] == 786194){
//            $this->modx->log(1, "store_id: {$properties['store_id']} | remain_id: {$properties['remain_id']} | id: {$properties['id']}");
//            $this->modx->log(1, print_r($actions, 1));
//            $this->modx->log(1, "Kenost Actions");
//        }

        foreach ($actions as $key_action => $value_action) {
            if ($key_action != 0) {
                //Вот тут обработка совместимости
                if ($main_compatibility == '1' && $value_action['compatibility_discount'] == '1') {
                    $actions[$key_action]['enabled'] = true;
                } else {
                    $actions[$key_action]['enabled'] = false;
                }
            } else {
                $actions[$key_action]['enabled'] = true;
                $main_compatibility = $value_action['compatibility_discount'];
            }

            if ($_SESSION['actions'][$properties['id']][$value_action['remain_id']][$value_action['action_id']] == false || $_SESSION['actions'][$properties['id']][$value_action['remain_id']][$value_action['action_id']] == true) {
                $actions[$key_action]['enabled'] = $_SESSION['actions'][$value_action['id']][$value_action['remain_id']][$value_action['action_id']];
            }

            if ($actions[$key_action]['enabled']) {
                $result['action'] = $value_action;
            }
        }
        if($properties['count']){
            $result['price'] = $this->getPrice($properties['store_id'], $properties['remain_id'], $properties['count'], $properties['id']);
        }

        $result['delay'] = $this->getOffsetPay($properties['store_id'], $properties['remain_id'], $properties['id']);

        $id_warehouse = $this->getWarehouseBasket(array("id" => $properties['id']));

        //Проверка товара в корзине
        if($_SESSION['basket'][$properties['id']][$id_warehouse][$properties['store_id']]['products'][$properties['remain_id']] != null) {
            $ids_actions = array();
            $test_actions = $this->getAvailableActions($properties['store_id'], $properties['remain_id'], $properties['id'], true);


            foreach ($test_actions as $action){
                $ids_actions[] = (int) $action['action_id'];
            }
            //Сортируем массив по возрастанию
            sort($ids_actions);

            //Проверить, есть ли товар с такими акциями в корзине?
            $index = -1;

            foreach ($_SESSION['basket'][$properties['id']][$id_warehouse][$properties['store_id']]['products'][$properties['remain_id']] as $k_el => $elem){
                if($elem['actions'] == $ids_actions){
                    $index = $k_el;
                    break;
                }
            }

            if($index == -1){
                $result['basket'] = array(
                    "availability" => false,
                    "count" => 1
                );
            } else{
                $result['basket'] = array(
                    "availability" => true,
                    "count" => $_SESSION['basket'][$properties['id']][$id_warehouse][$properties['store_id']]['products'][$properties['remain_id']][$index]['count']
                );
            }

        } else{
            $result['basket'] = array(
                "availability" => false,
                "count" => 1
            );
        }
        return $result;
    }

    /**
     * Чекаем остаток
     *
     * @param $id_remain
     * @param $store_id
     * @return array
     */
    public function getRemain($id_remain){
        $output = array();
        //Проверяем, есть ли нужное количество товаров в магазине
        $q = $this->modx->newQuery("slStoresRemains");
        $q->select(array(
            "`slStoresRemains`.*"
        ));
        $q->where(array(
            "`slStoresRemains`.`id`:=" => $id_remain,
            //"`slStoresRemains`.`remains`:>" => 0,
            "`slStoresRemains`.`guid`:!=" => ""
        ));

        if ($q->prepare() && $q->stmt->execute()) {
            $output = $q->stmt->fetch(PDO::FETCH_ASSOC);
        }
        return $output;
    }

    /**
     * Получаем активный склад корзины (id)
     *
     * @param $properties
     * @return array
     */
    public function getWarehouseBasket($properties){
        if($properties['id']){
            if($_SESSION['warehouses'][$properties['id']] != null){
                return $_SESSION['warehouses'][$properties['id']];
            } else {
                //Получаем все склады организации
                $warehouses = $this->sl->orgHandler->getStoresOrg($properties);

                //TODO: брать выбранный склад (СЕЙЧАС ПЕРВЫЙ)
                if($warehouses){
                    $id = $warehouses['items'][0]['id'];
                    $this->setWarehouseBasket(array('id' => $properties['id'], 'id_warehouse' => $id));
                    return $id;
                } else {
                    //TODO: А может быть что склада нет?!
                    return null;
                }
            }
        }
    }

    public function setWarehouseBasket($properties){
        if($properties['id'] && $properties['id_warehouse']){
            $_SESSION['warehouses'][$properties['id']] = $properties['id_warehouse'];
        }
        return $properties['id_warehouse'];
    }

    /**
     * Добавление товаров в корзину
     *
     * @param $properties
     * @return array
     */
    public function addBasket($properties) {
        //id склада
        $id_warehouse = $this->getWarehouseBasket($properties);

        //Проверяем, есть ли нужное количество товаров в магазине
        // unset($_SESSION['analytics_user']['basket']);
        // $this->modx->log(1, print_r($properties, 1));
            if($properties['id_remain']){
                $ids_actions = array();
                if(!$properties['actions']){
                    $actions = $this->getAvailableActions($properties['store_id'], $properties['id_remain'], $properties['id'], true);
                    foreach ($actions as $action){
                        $ids_actions[] = (int) $action['action_id'];
                    }
                    //Сортируем массив по возрастанию
                } else {
                    $ids_actions = $properties['actions'];
                }
                sort($ids_actions);


                $remain = $this->getRemain($properties['id_remain'], $properties['store_id']);
                if($remain){
                    // $valueBasket - Количество товаров с id_remain во всей корзине.
                    $valueBasket = 0;

                    //Проверяем, есть ли у пользователя в сессии такой товар в нужном магазине
                    if($_SESSION['basket'][$properties['id']][$id_warehouse][$properties['store_id']]['products'][$properties['id_remain']]) {

                        //Считаем количество товаров во всех корзине
                        foreach ($_SESSION['basket'][$properties['id']][$id_warehouse][$properties['store_id']]['products'][$properties['id_remain']] as $item){
                            $valueBasket = $valueBasket + $item['count'];
                        }

                        //Проверяем, хватает ли товаров на складе
                        //Если хватает, кладём в корзину. Если не хватает, кладём всё, что осталось на складе
                        if($valueBasket + $properties['value'] <= $remain['remains']){

                            //Проверить, есть ли товар с такими акциями в корзине?
                            $index = -1;
                            foreach ($_SESSION['basket'][$properties['id']][$id_warehouse][$properties['store_id']]['products'][$properties['id_remain']] as $key => $elem){
                                if($elem['actions'] == $ids_actions){
                                    $index = $key;
                                    break;
                                }
                            }

                            if($index != -1){
                                $_SESSION['basket'][$properties['id']][$id_warehouse][$properties['store_id']]['products'][$properties['id_remain']][$index] = array(
                                    "count" => $properties['value'],
                                    "price" => $this->getPrice($properties['store_id'], $properties['id_remain'], $properties['value'], $properties['id']),
                                    "actions" => $ids_actions,
                                    "delay" => $this->getOffsetPay($properties['store_id'], $properties['id_remain'], $properties['id'])
                                );
                            } else{
                                $_SESSION['basket'][$properties['id']][$id_warehouse][$properties['store_id']]['products'][$properties['id_remain']][] = array(
                                    "count" => $properties['value'],
                                    "price" => $this->getPrice($properties['store_id'], $properties['id_remain'], $properties['value'], $properties['id']),
                                    "actions" => $ids_actions,
                                    "delay" => $this->getOffsetPay($properties['store_id'], $properties['id_remain'], $properties['id'])
                                );
                            }
                        } else{
                            // TODO: Выдать сообщение, что нет столько товара
                        }
                    }else{
                        if($properties['value'] <= $remain['remains']){
                            $_SESSION['basket'][$properties['id']][$id_warehouse][$properties['store_id']]['products'][$properties['id_remain']][] = array(
                                "count" => (int) $properties['value'],
                                "price" => $this->getPrice($properties['store_id'], $properties['id_remain'], $properties['value'], $properties['id']),
                                "actions" => $ids_actions,
                                "delay" => $this->getOffsetPay($properties['store_id'], $properties['id_remain'], $properties['id'])
                            );
                        } else {
                            // TODO: Выдать сообщение, что нет столько товара
                        }
                    }
//                    foreach($_SESSION['basket'][$properties['id']][$id_warehouse][$properties['store_id']]['products'] as $k => $v){
//                        $_SESSION['basket'][$properties['id']][$id_warehouse][$properties['store_id']]['products'][$k] = array(
//                            "count" => $v['count'],
//                            "price" => $this->getPrice($properties['store_id'], $k, $v['count'], $properties['id'])
//                        );
//                    }
                }
            }
//        if($properties['id_remain']){
//            $remain = $this->getRemain($properties['id_remain'], $properties['store_id']);
//            if($remain){
//                $valueBasket = $_SESSION['basket'][$properties['id']][$properties['store_id']]['products'][$properties['id_remain']];
//                //Проверяем, есть ли у пользователя в сесии такой товар в нужном магазине
//                if($_SESSION['basket'][$properties['id']][$properties['store_id']]['products'][$properties['id_remain']]) {
//                    //Проверяем, хватает ли товаров на складе
//                    //Если хватает, кладём в корзину. Если не хватает, кладём всё, что осталось на складе
//                    if($valueBasket['count'] + $properties['value'] <= $remain['remains']){
//                        $_SESSION['basket'][$properties['id']][$properties['store_id']]['products'][$properties['id_remain']] = array(
//                            "count" => $valueBasket['count'] + $properties['value'],
//                            "price" => $this->getPrice($properties['store_id'], $properties['id_remain'], $valueBasket['count'] + $properties['value'], $properties['id'])
//                        );
//                    }else{
//                        $_SESSION['basket'][$properties['id']][$properties['store_id']]['products'][$properties['id_remain']] = array(
//                            "count" => (int) $remain['remains'],
//                            "price" => $this->getPrice($properties['store_id'], $properties['id_remain'], (int) $remain['remains'], $properties['id'])
//                        );
//                    }
//                }else{
//                    if($properties['value'] <= $remain['remains']){
//                        $_SESSION['basket'][$properties['id']][$properties['store_id']]['products'][$properties['id_remain']] = array(
//                            "count" => (int) $properties['value'],
//                            "price" => $this->getPrice($properties['store_id'], $properties['id_remain'], (int) $remain['value'], $properties['id'])
//                        );
//                    }else{
//                        $_SESSION['basket'][$properties['id']][$properties['store_id']]['products'][$properties['id_remain']] = array(
//                            "count" => (int) $remain['remains'],
//                            "price" => $this->getPrice($properties['store_id'], $properties['id_remain'], (int) $remain['remains'], $properties['id'])
//                        );
//                    }
//                }
//                foreach($_SESSION['basket'][$properties['id']][$properties['store_id']]['products'] as $k => $v){
//                    $_SESSION['basket'][$properties['id']][$properties['store_id']]['products'][$k] = array(
//                        "count" => $v['count'],
//                        "price" => $this->getPrice($properties['store_id'], $k, $v['count'], $properties['id'])
//                    );
//                }
//            }
//        }
        if ($properties['id_complect']) {
            $complect_data = $this->getRemainComplect($properties['store_id'], $properties['id_complect']);
            // обрабатываем комплект
            if($_SESSION['basket'][$properties['id']][$id_warehouse][$properties['store_id']]['complects'][$properties['id_complect']]) {
                $valueBasket = $_SESSION['basket'][$properties['id']][$id_warehouse][$properties['store_id']]['complects'][$properties['id_complect']];
                //TODO пофиксить проблему с количеством, всегда true
                $needle = $valueBasket['count'] + $properties['value'];
                if($needle < $complect_data['min_count']) {
                    $_SESSION['basket'][$properties['id']][$id_warehouse][$properties['store_id']]['complects'][$properties['id_complect']] = array(
                        "count" => $valueBasket['count'] + $properties['value'],
                        "price" => $this->getPriceComplect($properties['store_id'], $properties['id_complect']),
                        "complect_data" => $complect_data,
                        "id" => $properties['id_complect']
                    );
                }else{
                    $_SESSION['basket'][$properties['id']][$id_warehouse][$properties['store_id']]['complects'][$properties['id_complect']] = array(
                        "count" => $complect_data['min_count'],
                        "price" => $this->getPriceComplect($properties['store_id'], $properties['id_complect']),
                        "complect_data" => $complect_data,
                        "id" => $properties['id_complect']
                    );
                }
            }else{
                if($properties['value'] <= $complect_data['min_count']) {
                    $_SESSION['basket'][$properties['id']][$id_warehouse][$properties['store_id']]['complects'][$properties['id_complect']] = array(
                        "count" => $properties['value'],
                        "price" => $this->getPriceComplect($properties['store_id'], $properties['id_complect']),
                        "complect_data" => $complect_data,
                        "id" => $properties['id_complect']
                    );
                }else{
                    $_SESSION['basket'][$properties['id']][$id_warehouse][$properties['store_id']]['complects'][$properties['id_complect']] = array(
                        "count" => $complect_data['min_count'],
                        "price" => $this->getPriceComplect($properties['store_id'], $properties['id_complect']),
                        "complect_data" => $complect_data,
                        "id" => $properties['id_complect']
                    );
                }
            }
        }

//        if ($properties['id_complect']) {
//            $complect_data = $this->getRemainComplect($properties['store_id'], $properties['id_complect']);
//            // обрабатываем комплект
//            if($_SESSION['basket'][$properties['id']][$properties['store_id']]['complects'][$properties['id_complect']]) {
//                $valueBasket = $_SESSION['basket'][$properties['id']][$properties['store_id']]['complects'][$properties['id_complect']];
//                //TODO пофиксить проблему с количеством, всегда true
//                $needle = $valueBasket['count'] + $properties['value'];
//                if($needle < $complect_data['min_count']) {
//                    $_SESSION['basket'][$properties['id']][$properties['store_id']]['complects'][$properties['id_complect']] = array(
//                        "count" => $valueBasket['count'] + $properties['value'],
//                        "price" => $this->getPriceComplect($properties['store_id'], $properties['id_complect']),
//                        "complect_data" => $complect_data,
//                        "id" => $properties['id_complect']
//                    );
//                }else{
//                    $_SESSION['basket'][$properties['id']][$properties['store_id']]['complects'][$properties['id_complect']] = array(
//                        "count" => $complect_data['min_count'],
//                        "price" => $this->getPriceComplect($properties['store_id'], $properties['id_complect']),
//                        "complect_data" => $complect_data,
//                        "id" => $properties['id_complect']
//                    );
//                }
//            }else{
//                if($properties['value'] <= $complect_data['min_count']) {
//                    $_SESSION['basket'][$properties['id']][$properties['store_id']]['complects'][$properties['id_complect']] = array(
//                        "count" => $properties['value'],
//                        "price" => $this->getPriceComplect($properties['store_id'], $properties['id_complect']),
//                        "complect_data" => $complect_data,
//                        "id" => $properties['id_complect']
//                    );
//                }else{
//                    $_SESSION['basket'][$properties['id']][$properties['store_id']]['complects'][$properties['id_complect']] = array(
//                        "count" => $complect_data['min_count'],
//                        "price" => $this->getPriceComplect($properties['store_id'], $properties['id_complect']),
//                        "complect_data" => $complect_data,
//                        "id" => $properties['id_complect']
//                    );
//                }
//            }
//        }
        //$this->modx->log(1, print_r($_SESSION['basket'][$properties['id']], 1));
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
//            if($remain_id == 784356) {
//                $this->modx->log(1, print_r($action, 1));
//            }
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
//                if($remain_id == 784356) {
//                    $this->modx->log(1, $action['delivery_payment_terms'].' => '.$action['delivery_payment_value']);
//                    $this->modx->log(1, $fact_sku.' => '.$fact_cost);
//                }
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
//                $this->modx->log(1, 'PAYER::'.$payer);
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
        $actions = $this->getAvailableActions($store_id, $remain_id, $owner_id, true);
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
     * @param $owner_id - ID организации
     * @return array
     */
    public function getAvailableActions ($store_id, $remain_id, $owner_id, $active = false) {
        $actions = array();
        $today = date_create();
        $date = date_format($today, 'Y-m-d H:i:s');
        $store = $this->getWarehouseBasket(array("id" => $owner_id));
        $store_data = $this->sl->store->getStore($store, 0);
        $q_a = $this->modx->newQuery("slActions");
        $q_a->leftJoin("slActionsProducts", "slActionsProducts", "slActionsProducts.action_id = slActions.id");
        $q_a->leftJoin("slActionsStores", "slActionsStores", "slActionsStores.action_id = slActions.id AND slActionsStores.store_id = ".$owner_id);
        $q_a->leftJoin("slStores", "slStores", "slStores.id = slActions.store_id");
        $q_a->select(array(
            "`slActions`.*",
            "`slActionsProducts`.*",
            'slStores.opt_marketplace as opt_marketplace',
            "`slActions`.type as type",
            "`slActions`.id",
        ));
        $q_a->where(array(
            "`slActionsStores`.`active`:=" => 1,
            "FIND_IN_SET('".$store_data["city_id"]."', REPLACE(REPLACE(REPLACE(`slActions`.`cities`, '\"', ''), '[', ''), ']','')) > 0",
            "FIND_IN_SET('".$store_data["region_id"]."', REPLACE(REPLACE(REPLACE(`slActions`.`regions`, '\"', ''), '[', ''), ']','')) > 0",
            "slActions.participants_type:=" => 3
        ), xPDOQuery::SQL_OR);
        $q_a->where(array(
            "`slActionsProducts`.`remain_id`:=" => $remain_id,
            "FIND_IN_SET({$store_id}, `slActions`.`store_id`) > 0",
            "`slActions`.`active`:=" => 1,
            "`slActions`.`type`:=" => 1,
            "`slStores`.`opt_marketplace`:=" => 1,
            "`slActions`.`date_from`:<=" => $date,
            "`slActions`.`date_to`:>=" => $date
        ), xPDOQuery::SQL_AND);

        if($remain_id == '165868'){
            $q_a->prepare();
            $this->modx->log(1, $q_a->toSQL());
            $this->modx->log(1, "KENOST ACTIONS");
        }


        if ($q_a->prepare() && $q_a->stmt->execute()) {
            $actions = $q_a->stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($actions as $key => $item){
                //Если стоит галочка "Доступно для магазинов" и не доступно для поставщиков
                if($item['available_stores'] == 1 && $item['available_opt'] == 0){
                    if($item['opt_marketplace'] == 1){
                        unset($actions[$key]);
                    }
                }
            }
            $q_i = $this->modx->newQuery("slActions");
            $q_i->leftJoin("slActionsProducts", "slActionsProducts", "slActionsProducts.action_id = slActions.id AND slActionsProducts.remain_id = {$remain_id}");
            $q_i->select(array(
                "`slActions`.*",
                "`slActions`.type as type",
                 "`slActionsProducts`.*",
                "`slActions`.id as action_id",
                "`slActions`.type_price as type_price",
            ));
            $q_i->where(array(
                "`slActions`.`type`:=" => 3,
                "`slActions`.`store_id`:=" => $store_id,
                "`slActions`.`client_id`:=" => $owner_id,
            ));


            if ($q_i->prepare() && $q_i->stmt->execute()) {
                $actions_individual = $q_i->stmt->fetch(PDO::FETCH_ASSOC);
                //TODO && $actions_individual['remain_id'] != ""
                if($actions_individual){
                    if($actions_individual['remain_id'] != $remain_id){
                        $qr = $this->modx->newQuery("slStoresRemains");
                        $qr->select(array(
                            "`slStoresRemains`.price",
                        ));
                        $qr->where(array(
                            "`slStoresRemains`.`id`:=" => $remain_id,
                        ));

                        if ($qr->prepare() && $qr->stmt->execute()) {
                            $price = $qr->stmt->fetch(PDO::FETCH_ASSOC);
                            $oldPrice = $price['price'];
                            $newPrice = $price['price'];
                        }

                        //Получаем типы цен
//                        $prices = $this->getRemainPrices(array("remain_id" => $remain_id));
//
//                        foreach ($prices as $pk => $price){
//                            if($price['id'] == $actions_individual['type_price']){
//                                $newPrice = $price['price'];
//                            }
//                        }

                        //Скидка по формуле
                        if($actions_individual['type_all_sale'] == 0){
                            //Скидка в рублях
                            if($actions_individual['type_all_sale_symbol'] == 0){
                                $actions_individual['old_price'] = $oldPrice;
                                $actions_individual['new_price'] = $newPrice - $actions_individual['all_sale_value'];
                            }
                            //Скидка в процентах
                            else{
                                $actions_individual['old_price'] = $oldPrice;
                                $actions_individual['new_price'] = $newPrice - (($newPrice / 100) * $actions_individual['all_sale_value']);
                            }
                        }
                        //Тип цены
                        elseif($actions_individual['type_all_sale'] == 1){
                            $actions_individual['old_price'] = $oldPrice;

                            $qp = $this->modx->newQuery("slStoresRemainsPrices");
                            $qp->select(array(
                                "`slStoresRemainsPrices`.price",
                            ));
                            $qp->where(array(
                                "`slStoresRemainsPrices`.`key`:=" => $actions_individual['type_price'],
                                "`slStoresRemainsPrices`.`remain_id`:=" => $remain_id
                            ));

                            if ($qp->prepare() && $qp->stmt->execute()) {
                                $priceStore = $qp->stmt->fetch(PDO::FETCH_ASSOC);

                                if($priceStore){
                                    $actions_individual['new_price'] = $priceStore['price'];
                                } else {
                                    $actions_individual['new_price'] = $newPrice;
                                }
                            }

                        }

                        $actions_individual['remain_id'] = $remain_id;
                        $actions_individual['min_count'] = 1;

                    }

                    $actions = array_merge($actions, array($actions_individual));
                }
            }
        }

        if($active){
            $active_actions = [];
            foreach ($actions as $value_action){
                //Проверяем активна ли акция
                if($_SESSION['actions'][$owner_id][$value_action['remain_id']][$value_action['action_id']]){
                    $active_actions[] = $value_action;
                }
            }
            return $active_actions;
        }

        return $actions;
    }

    /**
     * Выставляем наиболее выгодные для пользователя акции для товара
     */

    public function setActionProduct($properties)
    {

        if(isset($properties['actions']) && isset($properties['id']) && isset($properties['remain_id'])){
            $actions = $properties['actions'];

            //были ли изменения уже на этом товаре?
            if(!$_SESSION['actions'][$properties['id']][$properties['remain_id']]){
                $oneAction = true;
                $main_compatibility = null;
                foreach ($actions as $key => $action){
                    if($oneAction){
                        $actions[$key]['enabled'] = true;
                        $_SESSION['actions'][$properties['id']][$properties['remain_id']][$action['action_id']] = true;
                        $main_compatibility = $action['compatibility_discount'];
                        $oneAction = false;
                    }else{
                        if($main_compatibility == '1' && $action['compatibility_discount'] == '1'){
                            $actions[$key]['enabled'] = true;
                            $_SESSION['actions'][$properties['id']][$properties['remain_id']][$action['action_id']] = true;
                        }else{
                            $actions[$key]['enabled'] = false;
                            $_SESSION['actions'][$properties['id']][$properties['remain_id']][$action['action_id']] = false;
                        }
                    }
                }
            }
            //Выставляем из сессии
            else{
                foreach ($actions as $key => $action){
                    if($_SESSION['actions'][$properties['id']][$properties['remain_id']][$action['action_id']]){
                        $actions[$key]['enabled'] = $_SESSION['actions'][$properties['id']][$properties['remain_id']][$action['action_id']];
                    } else {
                        $actions[$key]['enabled'] = false;
                        $_SESSION['actions'][$properties['id']][$properties['remain_id']][$action['action_id']] = false;
                    }
                }
            }

            return $actions;
        }
    }

    /**
     * Получаем только активные акции пользователя
     */

//    public function

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
//        if($remain_id == "784215"){
//            $this->modx->log(1, "KENOST FIX PRICE");
//            $this->modx->log(1, "store_id: {$store_id} | count: {$count} | owner_id: {$owner_id}");
//        }
        // "slStoresRemainsPrices"
        $price = $this->sl->store->getStoreSetting($store_id, "opt_price");
        $q = $this->modx->newQuery("slStoresRemains");
//        if($remain_id == 822888 || $remain_id == 166051){
//            $this->modx->log(1, $remain_id.' == '.print_r($price, 1));
//        }
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
            //TODO: УВЕДОМЛЕНИЕ, о том что не выбран тип цен в настройках
        }
        $q->where(array(
            "`slStoresRemains`.`id`:=" => $remain_id,
            "`slStoresRemains`.`remains`:>" => 0,
            "`slStoresRemains`.`guid`:!=" => "",
            "`slStoresRemains`.`store_id`:=" => $store_id
        ));
//        if($remain_id == 784036){
//            $q->prepare();
//            $this->modx->log(1, $q->toSQL());
//        }
        if ($q->prepare() && $q->stmt->execute()) {
            $storeRemains = $q->stmt->fetch(PDO::FETCH_ASSOC);
            $min_price = $storeRemains['price'];

            $actions = $this->getAvailableActions($store_id, $remain_id, $owner_id, true);

            if($remain_id == "784215"){
                $this->modx->log(1, "KENOST FIX PRICE 1");
                $this->modx->log(1, print_r($actions, 1));
            }


            $big_sale = 0; //Большая скидка
            $max_sale = 0; //Скидка складывается
            $consistently_sale = $storeRemains['price']; //Скидка выставляется последовательно

            foreach ($actions as $key_action => $value_action) {

                //Минимальное количество товаров для того чтоб акция сработала
                if($value_action['min_count'] <= $count){
                    // 0 - скидка без условий
                    if($value_action['condition_type'] == 0){
                        // TODO: учесть конфликты


                    }
                    // 1 - Купи X товаров по Y цене (с кратностью)
                    if($value_action['condition_type'] == 1){
                        if((int) $value_action['multiplicity'] > 1){
                            $remain_multiplicity = $count % $value_action['multiplicity'];
                            $sale_multiplicity = $count - $remain_multiplicity;
                            $calc_price = ($remain_multiplicity * $value_action['old_price'] + $sale_multiplicity * $value_action['new_price']) / $count;
                            if($calc_price < $min_price) {
                                $min_price = $calc_price;
                                //TODO: При нескольких акциях скидка может быть меньше, нужно отследить. Это связанно с кратностью и с совместимостью акций.
                                return $min_price;
                            }
                        }else{
                            if($calc_price < $min_price) {
                                $min_price = $value_action['old_price'];
                            }
                        }
                    }
                    // TODO: gift action
                    // 3 - Купи на X рублей и получи скидку на Y%
                    if($value_action['condition_type'] == 3){
                        // TODO: учесть кратность? Комплекты?
                        // сначала проверим условие акции
                        $m_action_price = $value_action['condition_min_sum'];
                        $m_action_sku = $value_action['condition_SKU'];
                        // берем наши сущности
                        $elements = $this->sl->analyticsSales->getActionProducts($value_action['action_id']);
                        // $complects = array();
                        $products = array();
                        if(count($elements['products'])){
                            foreach($elements['products'] as $k => $v){
                                $products[] = $k;
                            }
                            $cart = $this->getBasket(array("id" => $owner_id));
                            $fact_cost = 0;
                            $sku = array();
                            foreach($cart["stores"][$store_id]['products'] as $k => $v){
                                if(in_array($k, $products)){
                                    $fact_cost += $v['info']['price'] * $v['info']['count'];
                                    $sku[] = $k;
                                }
                            }
//                            $this->modx->log(1, "FACT COST: {$fact_cost} FACT SKU: {$fact_cost} ACTION COST: {$m_action_price} ACTION SKU: {$m_action_sku}");
                            $fact_sku = count($sku);
                            if($fact_cost >= $m_action_price && $fact_sku >= $m_action_sku){
                                $min_price = $elements['products'][$remain_id]['old_price'];
                            }
                        }
                    }
                }

            }

            $sales_modes = array();

            foreach ($actions as $key_action => $value_action) {
                //TODO: проверяем все ли акции с одинаковым условием "Совместимость скидок".
                //TODO: Если разные условия, то временно "Применяется бóльшая" скидка.
                if($value_action['min_count'] <= $count) {
                    if ($value_action["type"] == 3) {
                        //$actions[$key_action]["old_price"] = $min_price;
                        //$actions[$key_action]["new_price"] = $value_action["new_price"];
                        $actions[$key_action]["sale"] = $actions[$key_action]["old_price"] - $actions[$key_action]["new_price"];
                    } else {
                        $sales_modes[] = $value_action["compatibility_discount_mode"];
                        $actions[$key_action]["sale"] = $value_action["old_price"] - $value_action["new_price"];
                    }

                    $consistently_sale = $consistently_sale * (1 - (($actions[$key_action]["sale"] / ($value_action["old_price"] / 100)) / 100));
                    $big_sale += $actions[$key_action]["sale"];
                    if ($actions[$key_action]["sale"] > $max_sale) {
                        $max_sale = $actions[$key_action]["sale"];
                    }
                }

                //Минимальное количество товаров для того чтоб акция сработала
//                if($value_action['min_count'] <= $count) {
//                    if ($value_action["type"] == 3) {
//                        $actions[$key_action]["old_price"] = $min_price;
//                        $actions[$key_action]["new_price"] = $value_action["new_price"];
//                        $actions[$key_action]["sale"] = $actions[$key_action]["old_price"] - $actions[$key_action]["new_price"];
//                    } else {
//                        $sales_modes[] = $value_action["compatibility_discount_mode"];
//                        $actions[$key_action]["sale"] = $value_action["old_price"] - $value_action["new_price"];
//                    }
//                    $big_sale += $actions[$key_action]["sale"];
//                    if ($actions[$key_action]["sale"] > $max_sale) {
//                        $max_sale = $actions[$key_action]["sale"];
//                    }
//                }
            }

            $sales_modes = array_unique($sales_modes);

//            if($remain_id == 826526){
//                $this->modx->log(1, print_r($sales_modes, 1));
//                $this->modx->log(1, "{$min_price}");
//                $this->modx->log(1, print_r($value_action, 1));
//                $this->modx->log(1, "{$value_action["price"]}, {$value_action["old_price"]}, {$value_action["new_price"]}");
//                $this->modx->log(1, "max_sale {$max_sale}, big_sale {$big_sale}, consistently_sale {$consistently_sale}, price {$storeRemains['price']}");
//                $this->modx->log(1, "KENOST TEST PRICE");
//            }


//            if($remain_id == 784215){
//                $this->modx->log(1, print_r($sales_modes, 1));
//                $this->modx->log(1, print_r($actions, 1));
//
//                $this->modx->log(1, "min_price: {$min_price} max_sale {$max_sale}, big_sale {$big_sale}, consistently_sale {$consistently_sale}, price {$storeRemains['price']}");
//                $this->modx->log(1, "KENOST FIX PRICE 2");
//            }



            //Если у всех разные условия
            if(count($sales_modes) > 1){
                $min_price = $min_price - $max_sale;
            }else{
                if($sales_modes[0] == 0){
                    //Скидка складывается
                    $min_price = $min_price - $big_sale;
                } elseif($sales_modes[0] == 1){
                    //Большая скидка
                    $min_price = $min_price - $max_sale;
                }else{
                    //Скидка выставляется последовательно
                    $min_price = $consistently_sale;
                }
            }
            if($min_price > 0){
                $storeRemains['price'] = $min_price;
            }

//            if($remain_id == 784215){
//                $this->modx->log(1, "ИТОГОВАЯ ЦЕНА {$min_price}");
//            }

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
        return array();
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
        $result = array(
            "props" => $properties
        );
        $total_cost = 0;
        $total_weight = 0;
        $total_volume = 0;
        $sku = 0;
        $warehouses = array();
        if($properties['id']){

            //Достаём все склады
            foreach ($_SESSION['basket'][$properties['id']] as $warkey => $war){
                $queryStore = $this->modx->newQuery("slStores");
                $queryStore->where(array(
                    "`slStores`.`id`:=" => $warkey
                ));

                $queryStore->select(array(
                    'slStores.*',
                ));

                if ($queryStore->prepare() && $queryStore->stmt->execute()) {
                    $warehouses[] = $queryStore->stmt->fetch(PDO::FETCH_ASSOC);
                }
            }

            $result['warehouses'] = $warehouses;


            if($properties['warehouse'] == 'all'){
                foreach ($_SESSION['basket'][$properties['id']] as $id_warehouse => $war){
                    if($_SESSION['basket'][$properties['id']][$id_warehouse]){
                        $urlMain = $this->modx->getOption("site_url");
                        foreach ($_SESSION['basket'][$properties['id']][$id_warehouse] as $key => $value){
                            $q = $this->modx->newQuery("slStores");
                            $q->select(array(
                                "`slStores`.id",
                                "`slStores`.address_short",
                                "`slStores`.name",
                                "`slStores`.name_short",
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
                                $result['basket'][$id_warehouse]['stores'][$key] = $store;
                                $cost = 0;
                                $weight = 0;
                                $volume = 0;
                                foreach ($value['products'] as $k => $v){
                                    $query = $this->modx->newQuery("slStoresRemains");
                                    $query->leftJoin("modResource", "modResource", "modResource.id = slStoresRemains.product_id");
                                    $query->leftJoin("msProductData", "msProductData", "msProductData.id = slStoresRemains.product_id");

                                    $query->select(array(
                                        "modResource.id as id_product",
                                        "`msProductData`.image",
                                        "`slStoresRemains`.article",
                                        "`slStoresRemains`.remains",
                                        "`slStoresRemains`.name",
                                        "`slStoresRemains`.store_id",
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


                                        if($product['image']){
                                            $product['image'] = $urlMain . $product['image'];
                                        }else{
                                            $product['image'] = $urlMain . $this->modx->getPlaceholder("+conf_noimage");
                                        }

                                        $basket = array();

                                        foreach ($v as $item) {
                                            $params = $this->sl->product->getProductParams($product['id_product']);

                                            $weight += $params[0]['product']["weight_brutto"] * $item['count'];
                                            $volume += $params[0]['product']["length"] * $params[0]['product']["width"] * $params[0]['product']["height"] * $item['count'];

                                            $total_weight += $params[0]['product']["weight_brutto"] * $item['count'];
                                            $total_volume += $params[0]['product']["length"] * $params[0]['product']["width"] * $params[0]['product']["height"] * $item['count'];

                                            $cost = $cost + $item['count'] * $item['price'];
                                            $total_cost = $total_cost + $item['count'] * $item['price'];

                                            //Получаем акции для большой корзины
                                            $product_actions = array();
                                            $today = date_create();
                                            $date = date_format($today, 'Y-m-d H:i:s');
                                            $q_a = $this->modx->newQuery("slActions");
                                            $q_a->leftJoin("slActionsProducts", "slActionsProducts", "slActionsProducts.action_id = slActions.id");
//                                    $q_a->leftJoin("slActionsStores", "slActionsStores", "slActionsStores.action_id = slActions.id AND slActionsStores.store_id = ".$properties['id']);
//                                    $q_a->leftJoin("slStores", "slStores", "slStores.id = slActions.store_id");
                                            $q_a->select(array(
                                                "`slActions`.*",
                                                "`slActionsProducts`.*",
                                            ));
                                            $q_a->where(array(
                                                "`slActions`.`id`:IN" => $item['actions'],
                                                "`slActionsProducts`.`remain_id`:=" => $product['id_remain'],
                                            ));
                                            if ($q_a->prepare() && $q_a->stmt->execute()) {
                                                $product_actions = $q_a->stmt->fetchAll(PDO::FETCH_ASSOC);
                                                $client_data = $this->getClientData($properties['id'], $product['store_id']);
                                                if($client_data){
                                                    if($client_data['base_sale'] > 0){
                                                        $base_koef = 1 - ($client_data['base_sale'] * 0.01);
                                                        $client_action = array(
                                                            "action_id" => 0,
                                                            "id" => 0,
                                                            "condition_type" => 0,
                                                            "name" => "Базовая скидка клиента",
                                                            "description" => "Скидка в размере {$client_data['base_sale']}%",
                                                            "icon" => "img/percent.jpg",
                                                            "base_sale" => $client_data['base_sale'],
                                                            "base_koef" => $base_koef,
                                                            "store_id" => $product['store_id'],
                                                            "remain_id" => $product['id_remain']
                                                        );
                                                        array_unshift($product_actions , $client_action);
                                                    }
                                                }
                                            }

                                            $tags = array();
                                            foreach ($product_actions as $key_action => $value_action) {
                                                $tag = array();

                                                //Получаем признаки акции
                                                if(floatval($value_action['delay']) > 0){
                                                    $tag[] = array(
                                                        "type" => "delay",
                                                        "value" => floatval($value_action['delay'])
                                                    );
                                                }
                                                if(floatval($value_action['condition_min_sum']) > 0){
                                                    $tag[] = array(
                                                        "type" => "min_sum",
                                                        "value" => floatval($value_action['condition_min_sum'])
                                                    );
                                                }
                                                //Если выбрана доставка поставщиком
                                                if($value_action['payer'] == 1){
                                                    //Если без условий
                                                    if($value_action['delivery_payment_terms'] == 0){
                                                        $tag[] = array(
                                                            "type" => "free_delivery",
                                                            "condition" => $value_action['delivery_payment_terms'],
                                                            "value" => 0
                                                        );
                                                    }
                                                    //Если "Купи на Х рублей"
                                                    else if($value_action['delivery_payment_terms'] == 1){
                                                        $tag[] = array(
                                                            "type" => "free_delivery",
                                                            "condition" => $value_action['delivery_payment_terms'],
                                                            "value" => floatval($value_action['delivery_payment_value'])
                                                        );
                                                    }
                                                    //Если "При покупке Х шт товара"
                                                    else if($value_action['delivery_payment_terms'] == 2){
                                                        $tag[] = array(
                                                            "type" => "free_delivery",
                                                            "condition" => $value_action['delivery_payment_terms'],
                                                            "value" => floatval($value_action['delivery_payment_value'])
                                                        );
                                                    }
                                                }

                                                //Подарок
                                                if($value_action['condition_type'] == 2){
                                                    $tag[] = array(
                                                        "type" => "gift"
                                                    );
                                                }

                                                //Базовая скидка
                                                if(floatval($value_action['type']) == 3){
                                                    if($value_action['old_price'] > 0) {
                                                        $tag[] = array(
                                                            "type" => "sale",
                                                            "value" => round(((floatval($value_action['old_price']) - floatval($value_action['new_price'])) / (floatval($value_action['old_price']) / 100)), 0),
                                                            "min_count" => $value_action['min_count']
                                                        );
                                                    }
                                                }

                                                //Кратность
                                                $q_m = $this->modx->newQuery("slActionsProducts");
                                                $q_m->select(array(
                                                    "`slActionsProducts`.*",
                                                ));
                                                $q_m->where(array(
                                                    "`slActionsProducts`.`action_id`:=" => $value_action['action_id'],
                                                    "`slActionsProducts`.`remain_id`:=" => $value_action['remain_id'],
                                                ));

                                                if ($q_m->prepare() && $q_m->stmt->execute()) {
                                                    $productAction = $q_m->stmt->fetch(PDO::FETCH_ASSOC);
                                                    if($productAction){
                                                        if(floatval($productAction['multiplicity']) > 1){
                                                            $tag[] = array(
                                                                "type" => "multiplicity",
                                                                "value" => floatval($productAction['multiplicity'])
                                                            );
                                                        }

                                                        if($value_action['type'] != 3){
                                                            if(floatval($productAction['old_price']) > floatval($productAction['new_price'])){
                                                                $tag[] = array(
                                                                    "type" => "sale",
                                                                    "value" => round(((floatval($productAction['old_price']) - floatval($productAction['new_price'])) / (floatval($productAction['old_price']) / 100)), 0),
                                                                    "min_count" => $productAction['min_count']
                                                                );
                                                            }
                                                        }
                                                    }
                                                }

                                                //Минимальное кол-во
                                                if($value_action['min_count'] > 1){
                                                    $tag[] = array(
                                                        "type" => "min",
                                                        "value" => $value_action['min_count'],
                                                    );
                                                }

//                                                //Кратность
//                                                $q_m = $this->modx->newQuery("slActionsProducts");
//                                                $q_m->select(array(
//                                                    "`slActionsProducts`.*",
//                                                ));
//                                                $q_m->where(array(
//                                                    "`slActionsProducts`.`action_id`:=" => $value_action['action_id'],
//                                                    "`slActionsProducts`.`remain_id`:=" => $value_action['remain_id'],
//                                                ));
//
//                                                if ($q_m->prepare() && $q_m->stmt->execute()) {
//                                                    $productAction = $q_m->stmt->fetch(PDO::FETCH_ASSOC);
//                                                    if($productAction){
//                                                        if(floatval($productAction['multiplicity']) > 1){
//                                                            $tag[] = array(
//                                                                "type" => "multiplicity",
//                                                                "value" => floatval($productAction['multiplicity'])
//                                                            );
//                                                        }
//
//                                                        if(floatval($productAction['old_price']) > floatval($productAction['new_price'])){
//                                                            $tag[] = array(
//                                                                "type" => "sale",
//                                                                "value" => round(((floatval($productAction['old_price']) - floatval($productAction['new_price'])) / (floatval($productAction['old_price']) / 100)), 0),
//                                                                "min_count" => $productAction['min_count']
//                                                            );
//                                                        }
//                                                    }
//                                                }

                                                $tags[$value_action['action_id']] = $tag;
                                            }

                                            $basket[] = array(
                                                'count' => $item['count'],
                                                'delay' => $item['delay'],
                                                'price' => $item['price'],
                                                'tags' => $tags,
                                                'actions_ids' => $item['actions'],
                                                'cost' => $item['count'] * $item['price'],
                                                'weight' => $params[0]['product']["weight_brutto"] * $item['count'],
                                                'volume' => round(($params[0]['product']["length"] * $params[0]['product']["width"] * $params[0]['product']["height"] * $item['count'] / 1000000), 3)
                                            );
                                        }

                                        $sku = $sku + 1;
                                        $product['basket'] = $basket;

                                        $result['basket'][$id_warehouse]['stores'][$key]['products'][$k] = $product;
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

                                            $result['basket'][$id_warehouse]['stores'][$key]['complects'][$k]['info'] = $v;
                                            $result['basket'][$id_warehouse]['stores'][$key]['complects'][$k]['products'][$key_p] = $product;
                                        }
                                    }
                                }

                                $result['basket'][$id_warehouse]['stores'][$key]['id_warehouse'] = $id_warehouse;
                                $result['basket'][$id_warehouse]['stores'][$key]['cost'] = $cost;
                                $result['basket'][$id_warehouse]['stores'][$key]['weight'] = $weight;
                                $result['basket'][$id_warehouse]['stores'][$key]['volume'] = round(($volume / 1000000), 3);
                            }
                        }

                        $result['cost'] = $total_cost;
                        $result['count'] = $sku;
                        $result['weight'] = $total_weight;
                        $result['volume'] = round(($total_volume / 1000000), 3);
                    }
                }
            } else{
                $id_warehouse = $this->getWarehouseBasket($properties);

                $result['id_warehouse'] = $id_warehouse;

                if($_SESSION['basket'][$properties['id']][$id_warehouse]){
                    $urlMain = $this->modx->getOption("site_url");
                    foreach ($_SESSION['basket'][$properties['id']][$id_warehouse] as $key => $value){
                        $q = $this->modx->newQuery("slStores");
                        $q->select(array(
                            "`slStores`.id",
                            "`slStores`.address_short",
                            "`slStores`.name",
                            "`slStores`.name_short",
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
                                    "modResource.id as id_product",
                                    "`msProductData`.image",
                                    "`slStoresRemains`.article",
                                    "`slStoresRemains`.remains",
                                    "`slStoresRemains`.name",
                                    "`slStoresRemains`.store_id",
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


                                    if($product['image']){
                                        $product['image'] = $urlMain . $product['image'];
                                    }else{
                                        $product['image'] = $urlMain . $this->modx->getPlaceholder("+conf_noimage");
                                    }

                                    $basket = array();

                                    foreach ($v as $item) {
                                        $params = $this->sl->product->getProductParams($product['id_product']);

                                        $weight += $params[0]['product']["weight_brutto"] * $item['count'];
                                        $volume += $params[0]['product']["length"] * $params[0]['product']["width"] * $params[0]['product']["height"] * $item['count'];

                                        $total_weight += $params[0]['product']["weight_brutto"] * $item['count'];
                                        $total_volume += $params[0]['product']["length"] * $params[0]['product']["width"] * $params[0]['product']["height"] * $item['count'];

                                        $cost = $cost + $item['count'] * $item['price'];
                                        $total_cost = $total_cost + $item['count'] * $item['price'];

                                        //Получаем акции для большой корзины
                                        $product_actions = array();
                                        $today = date_create();
                                        $date = date_format($today, 'Y-m-d H:i:s');
                                        $q_a = $this->modx->newQuery("slActions");
                                        $q_a->leftJoin("slActionsProducts", "slActionsProducts", "slActionsProducts.action_id = slActions.id");
//                                    $q_a->leftJoin("slActionsStores", "slActionsStores", "slActionsStores.action_id = slActions.id AND slActionsStores.store_id = ".$properties['id']);
//                                    $q_a->leftJoin("slStores", "slStores", "slStores.id = slActions.store_id");
                                        $q_a->select(array(
                                            "`slActions`.*",
                                            "`slActionsProducts`.*",
                                        ));
                                        $q_a->where(array(
                                            "`slActions`.`id`:IN" => $item['actions'],
                                            "`slActionsProducts`.`remain_id`:=" => $product['id_remain'],
                                        ));
                                        if ($q_a->prepare() && $q_a->stmt->execute()) {
                                            $product_actions = $q_a->stmt->fetchAll(PDO::FETCH_ASSOC);
                                            $client_data = $this->getClientData($properties['id'], $product['store_id']);
                                            if($client_data){
                                                if($client_data['base_sale'] > 0){
                                                    $base_koef = 1 - ($client_data['base_sale'] * 0.01);
                                                    $client_action = array(
                                                        "action_id" => 0,
                                                        "id" => 0,
                                                        "condition_type" => 0,
                                                        "name" => "Базовая скидка клиента",
                                                        "description" => "Скидка в размере {$client_data['base_sale']}%",
                                                        "icon" => "img/percent.jpg",
                                                        "base_sale" => $client_data['base_sale'],
                                                        "base_koef" => $base_koef,
                                                        "store_id" => $product['store_id'],
                                                        "remain_id" => $product['id_remain']
                                                    );
                                                    array_unshift($product_actions , $client_action);
                                                }
                                            }
                                        }

                                        $tags = array();
                                        foreach ($product_actions as $key_action => $value_action) {
                                            $tag = array();
                                            //Базовая скидка
//                                            if(floatval($value_action['base_sale']) > 0){
//                                                $tag[] = array(
//                                                    "type" => "sale",
//                                                    "value" => round(floatval($value_action['base_sale']), 0)
//                                                );
//                                            }

                                            //Получаем признаки акции
                                            if(floatval($value_action['delay']) > 0){
                                                $tag[] = array(
                                                    "type" => "delay",
                                                    "value" => floatval($value_action['delay'])
                                                );
                                            }
                                            if(floatval($value_action['condition_min_sum']) > 0){
                                                $tag[] = array(
                                                    "type" => "min_sum",
                                                    "value" => floatval($value_action['condition_min_sum'])
                                                );
                                            }
                                            //Если выбрана доставка поставщиком
                                            if($value_action['payer'] == 1){
                                                //Если без условий
                                                if($value_action['delivery_payment_terms'] == 0){
                                                    $tag[] = array(
                                                        "type" => "free_delivery",
                                                        "condition" => $value_action['delivery_payment_terms'],
                                                        "value" => 0
                                                    );
                                                }
                                                //Если "Купи на Х рублей"
                                                else if($value_action['delivery_payment_terms'] == 1){
                                                    $tag[] = array(
                                                        "type" => "free_delivery",
                                                        "condition" => $value_action['delivery_payment_terms'],
                                                        "value" => floatval($value_action['delivery_payment_value'])
                                                    );
                                                }
                                                //Если "При покупке Х шт товара"
                                                else if($value_action['delivery_payment_terms'] == 2){
                                                    $tag[] = array(
                                                        "type" => "free_delivery",
                                                        "condition" => $value_action['delivery_payment_terms'],
                                                        "value" => floatval($value_action['delivery_payment_value'])
                                                    );
                                                }
                                            }

                                            //Подарок
                                            if($value_action['condition_type'] == 2){
                                                $tag[] = array(
                                                    "type" => "gift"
                                                );
                                            }

                                            //Базовая скидка
                                            if(floatval($value_action['type']) == 3){
                                                if($value_action['old_price'] > 0){
                                                    $tag[] = array(
                                                        "type" => "sale",
                                                        "value" => round(((floatval($value_action['old_price']) - floatval($value_action['new_price'])) / (floatval($value_action['old_price']) / 100)), 0),
                                                        "min_count" => $value_action['min_count']
                                                    );
                                                }
                                            }


                                            //Кратность
                                            $q_m = $this->modx->newQuery("slActionsProducts");
                                            $q_m->select(array(
                                                "`slActionsProducts`.*",
                                            ));
                                            $q_m->where(array(
                                                "`slActionsProducts`.`action_id`:=" => $value_action['action_id'],
                                                "`slActionsProducts`.`remain_id`:=" => $value_action['remain_id'],
                                            ));

                                            if ($q_m->prepare() && $q_m->stmt->execute()) {
                                                $productAction = $q_m->stmt->fetch(PDO::FETCH_ASSOC);
                                                if($productAction){
                                                    if(floatval($productAction['multiplicity']) > 1){
                                                        $tag[] = array(
                                                            "type" => "multiplicity",
                                                            "value" => floatval($productAction['multiplicity'])
                                                        );
                                                    }

                                                    if($value_action['type'] != 3){
                                                        if(floatval($productAction['old_price']) > floatval($productAction['new_price'])){
                                                            $tag[] = array(
                                                                "type" => "sale",
                                                                "value" => round(((floatval($productAction['old_price']) - floatval($productAction['new_price'])) / (floatval($productAction['old_price']) / 100)), 0),
                                                                "min_count" => $productAction['min_count']
                                                            );
                                                        }
                                                    }
                                                }
                                            }

                                            //Минимальное кол-во
                                            if($value_action['min_count'] > 1){
                                                $tag[] = array(
                                                    "type" => "min",
                                                    "value" => $value_action['min_count'],
                                                );
                                            }

//                                            //Кратность
//                                            $q_m = $this->modx->newQuery("slActionsProducts");
//                                            $q_m->select(array(
//                                                "`slActionsProducts`.*",
//                                            ));
//                                            $q_m->where(array(
//                                                "`slActionsProducts`.`action_id`:=" => $value_action['action_id'],
//                                                "`slActionsProducts`.`remain_id`:=" => $value_action['remain_id'],
//                                            ));
//
//                                            if ($q_m->prepare() && $q_m->stmt->execute()) {
//                                                $productAction = $q_m->stmt->fetch(PDO::FETCH_ASSOC);
//                                                if($productAction){
//                                                    if(floatval($productAction['multiplicity']) > 1){
//                                                        $tag[] = array(
//                                                            "type" => "multiplicity",
//                                                            "value" => floatval($productAction['multiplicity'])
//                                                        );
//                                                    }
//
//                                                    if(floatval($productAction['old_price']) > floatval($productAction['new_price'])){
//                                                        $tag[] = array(
//                                                            "type" => "sale",
//                                                            "value" => round(((floatval($productAction['old_price']) - floatval($productAction['new_price'])) / (floatval($productAction['old_price']) / 100)), 0),
//                                                            "min_count" => $productAction['min_count']
//                                                        );
//                                                    }
//                                                }
//                                            }

                                            $tags[$value_action['action_id']] = $tag;
                                        }

                                        $basket[] = array(
//                                        'test' => $client_data,
//                                        'test2' => array('id' => $properties['id'], 'store_id' => $product['store_id']),
                                            'count' => $item['count'],
                                            'delay' => $item['delay'],
                                            'price' => $item['price'],
                                            'tags' => $tags,
                                            'actions_ids' => $item['actions'],
                                            'cost' => $item['count'] * $item['price'],
                                            'weight' => $params[0]['product']["weight_brutto"] * $item['count'],
                                            'volume' => round(($params[0]['product']["length"] * $params[0]['product']["width"] * $params[0]['product']["height"] * $item['count'] / 1000000), 3)
                                        );
                                    }

                                    $sku = $sku + 1;
                                    $product['basket'] = $basket;

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
                    $result['count'] = $sku;
                    $result['weight'] = $total_weight;
                    $result['volume'] = round(($total_volume / 1000000), 3);
                }
            }




            return $result;
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
        //id склада

        if($properties['id_warehouse']){
            $id_warehouse = $properties['id_warehouse'];
        } else{
            $id_warehouse = $this->getWarehouseBasket($properties);
        }

        // $valueBasket - Количество товаров с id_remain во всей корзине.
        $valueBasket = 0;

        //Проверяем, есть ли нужное количество товаров в магазине
        // unset($_SESSION['basket']);
        if($properties['id_remain']){
            $remain = $this->getRemain($properties['id_remain'], $properties['store_id']);
            //Проверяем, есть ли у пользователя в сесии такой товар в нужном магазине
            if($_SESSION['basket'][$properties['id']][$id_warehouse][$properties['store_id']]['products'][$properties['id_remain']]){
                $valueBasket = 0;
                //Считаем количество товаров во всех корзине
                foreach ($_SESSION['basket'][$properties['id']][$id_warehouse][$properties['store_id']]['products'][$properties['id_remain']] as $item){
                    if($item['actions'] != $properties['actions']){
                        $valueBasket = $valueBasket + $item['count'];
                    }
                }
                //Проверяем, хватает ли товаров на складе
                //Если хватает, кладём в корзину. Если не хватает, кладём всё, что осталось на складе
                if($valueBasket + $properties['value'] <= $remain['remains']){
                    //Проверить, есть ли товар с такими акциями в корзине?
                    $index = -1;
                    foreach ($_SESSION['basket'][$properties['id']][$id_warehouse][$properties['store_id']]['products'][$properties['id_remain']] as $key => $elem){
                        if($elem['actions'] == $properties['actions']){
                            $index = $key;
                            break;
                        }
                    }
                    if($properties['value'] == 0 && $index != -1){
                        unset($_SESSION['basket'][$properties['id']][$id_warehouse][$properties['store_id']]['products'][$properties['id_remain']][$index]);
                    }else{
                        if($index != -1){
                            $_SESSION['basket'][$properties['id']][$id_warehouse][$properties['store_id']]['products'][$properties['id_remain']][$index] = array(
                                "count" => $properties['value'],
                                "price" => $this->getPrice($properties['store_id'], $properties['id_remain'], $properties['value'], $properties['id']),
                                "actions" => $properties['actions'],
                                "delay" => $this->getOffsetPay($properties['store_id'], $properties['id_remain'], $properties['id'])
                            );
                        } else{
                            $_SESSION['basket'][$properties['id']][$id_warehouse][$properties['store_id']]['products'][$properties['id_remain']][] = array(
                                "count" => $properties['value'],
                                "price" => $this->getPrice($properties['store_id'], $properties['id_remain'], $properties['value'], $properties['id']),
                                "actions" => $properties['actions'],
                                "delay" => $this->getOffsetPay($properties['store_id'], $properties['id_remain'], $properties['id'])
                            );
                        }
                    }
                }else{
                    //TODO: Ошибка пользователю
                }
            }
        }

        //TODO: Обновить комплекты
        if($properties["id_complect"]){
            if($_SESSION['basket'][$properties['id']][$id_warehouse][$properties['store_id']]['complects'][$properties['id_complect']]){
                $complect_data = $this->getRemainComplect($properties['store_id'], $properties["id_complect"]);
                //Проверяем, хватает ли товаров на складе
                //Если хватает, кладём в корзину. Если не хватает, кладём всё, что осталось на складе
                if($properties['value'] <= $complect_data['min_count']){
                    $_SESSION['basket'][$properties['id']][$id_warehouse][$properties['store_id']]['complects'][$properties['id_complect']] = array(
                        "count" => $properties['value'],
                        "price" => $this->getPriceComplect($properties['store_id'], $properties['id_complect']),
                        "complect_data" => $complect_data,
                        "id" => $properties['id_complect']
                    );
                }else{
                    $_SESSION['basket'][$properties['id']][$id_warehouse][$properties['store_id']]['complects'][$properties['id_complect']] = array(
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
        if($properties['clear_all']){
            unset($_SESSION['basket'][$properties['id']]);
        } else{
            $id_warehouse = $this->getWarehouseBasket(array("id" => $properties['id']));
            if($properties['id']){
                if($properties['store_id'] && $properties['id_remain'] || $properties['store_id'] && $properties['id_complect']){
                    if($properties['id_remain']){
                        unset($_SESSION['basket'][$properties['id']][$id_warehouse][$properties['store_id']]['products'][$properties['id_remain']]);
                    }
                    if($properties['id_complect']){
                        unset($_SESSION['basket'][$properties['id']][$id_warehouse][$properties['store_id']]['complects'][$properties['id_complect']]);
                    }
                    if(!count($_SESSION['basket'][$properties['id']][$id_warehouse][$properties['store_id']]['products']) &&
                        !count($_SESSION['basket'][$properties['id']][$id_warehouse][$properties['store_id']]['complects'])){
                        unset($_SESSION['basket'][$properties['id']][$id_warehouse][$properties['store_id']]);
                    }

                    if(!count($_SESSION['basket'][$properties['id']][$id_warehouse])){
                        unset($_SESSION['basket'][$properties['id']][$id_warehouse]);
                    }
//                    foreach($_SESSION['basket'][$properties['id']][$id_warehouse][$properties['store_id']]['products'] as $k => $v){
//                        $_SESSION['basket'][$properties['id']][$id_warehouse][$properties['store_id']]['products'][$k] = array(
//                            "count" => $v['count'],
//                            "price" => $this->getPrice($properties['store_id'], $k, $v['count'], $properties['id'])
//                        );
//                    }
                }else{
                    if($store_id == 'all'){
                        unset($_SESSION['basket'][$properties['id']][$id_warehouse]);
                    }else{
                        unset($_SESSION['basket'][$properties['id']][$id_warehouse][$store_id]);
                        if(!count($_SESSION['basket'][$properties['id']])){
                            unset($_SESSION['basket'][$properties['id']]);
                        }
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
            if($properties["store_id"] != 'all'){
                $basket = $this->getBasket($properties);
                // TODO: чистка корзины только выбранного поставщика
                $order_data['products'] = $basket['stores'][$properties["store_id"]]["products"];
                $order_data['complects'] = $basket['stores'][$properties["store_id"]]["complects"];
                $order_data['cost'] = $basket['stores'][$properties["store_id"]]['cost'];
                $order_data['volume'] = $basket['stores'][$properties["store_id"]]['volume'];
                $order_data['weight'] = $basket['stores'][$properties["store_id"]]['weight'];
                $order_data["store_id"] = $properties["store_id"];
                $order_data["org_id"] = $properties["id"];
//                $this->modx->log(1, "KENOST 11111: basket");
//                $this->modx->log(1, print_r($basket, 1));
                $order_data['id_warehouse'] = $basket['id_warehouse'];
                $response[] = $this->orderSave($order_data);
                $this->clearBasket($properties, $properties["store_id"]);
            }else{
                $new_properties = $properties;
                $new_properties['warehouse'] = true;
                $new_properties['clear_all'] = true;
                $basket = $this->getBasket($new_properties);
                foreach($basket['basket'] as $k => $v){
                    foreach ($v['stores'] as $key => $val){
                        $order_data['products'] = $val["products"];
                        $order_data['complects'] = $val["complects"];
                        $order_data['cost'] = $val["cost"];
                        $order_data['volume'] = $val["volume"];
                        $order_data['weight'] = $val["weight"];
                        $order_data['id_warehouse'] = $val['id_warehouse'];
                        $order_data["store_id"] = $key;
                        $order_data["org_id"] = $properties["id"];
                        $response[] = $this->orderSave($order_data);
                    }
                }
                $this->clearBasket($new_properties);
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
        $order = $this->modx->newObject("slOrderOpt");
        $order->set("store_id", $data["store_id"]);
        $order->set("org_id", $data["org_id"]);
        $order->set("weight", $data["weight"]);
        $order->set("volume", $data["volume"]);
        $order->set("cost", $data["cost"]);
        $order->set("cart_cost", $data["cost"]);
        $order->set("warehouse_id", $data["id_warehouse"]);
        $order->set("status", 1);
        $payer = 0;
        $day_delivery = $this->sl->cart->getNearShipment($data["store_id"], $data["org_id"]);
        $date_delivery = date("Y-m-d", time()+60*60*24* $day_delivery);
        if($data['products']){
            $key_first = array_key_first($data['products']);
            foreach ($data['products'] as $k => $item){
                if($item['payer'] == 1){
                    $payer = 1;
                    break;
                }
            }
        } else if($data['complects']){
            $key_first = array_key_first($data['complects']);
            foreach ($data['complects'] as $k => $item){
                if($item['payer'] == 1){
                    $payer = 1;
                    break;
                }
            }
        }
        //TODO: Не правильно берётся, нужно записывать для каждого товара, а щас общий
        $order->set("delivery_payer", $payer);
        $order->set("day_delivery", $day_delivery);
        $order->set("delivery_date", $date_delivery);
        $order->set("createdon", time());
        $order->set("date", time());
        $order->save();
        foreach($data['products'] as $k => $item){
            foreach ($item['basket'] as $bk => $bitem){
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
                    $product->set("count", $bitem['count']);
                    $product->set("price", $bitem['price']);
                    $product->set("weight", $params[0]['product']["weight_brutto"]);
                    $product->set("cost", ($bitem['count'] * $bitem['price']));
                    $product->set("actions", $bitem['actions_ids']);
//                    $this->modx->log(1, "KENOST 3: product");
//                    $this->modx->log(1, print_r($product->toArray(), 1));
                    $product->save();
                }
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

//                $this->modx->log(1, "KENOST 3: complect");
//                $this->modx->log(1, print_r($product->toArray(), 1));
                $product->save();
            }
        }


        //Узнаём организацию продавца
        $q_o = $this->modx->newQuery("slOrgStores");
        $q_o->where(array(
            "slOrgStores.store_id:=" => $data["store_id"],
        ));
        $q_o->select(array("slOrgStores.org_id"));
        if($q_o->prepare() && $q_o->stmt->execute()) {
            $org = $q_o->stmt->fetch(PDO::FETCH_ASSOC);
        }

        //уведомление продавцу
        $notification = array(
            "org_id" => $org['org_id'],
            "namespace" => 2,
            "link_id" => $order->get("id"),
            "store_id" => $data["org_id"]
        );

        $this->modx->log(1, "KENOST 3: complect");
        $this->modx->log(1, print_r($product->toArray(), 1));

        $this->sl->notification->setNotification(array("data" => $notification));

        $mail_data = array(
            "order" => $order->toArray()
        );
        if($mail_data["order"]["cost"]){
            $mail_data["order"]["cost"] = $this->sl->tools->numberFormat($mail_data["order"]["cost"]);
        }
        if($mail_data["order"]["store_id"]){
            // seller
            $query = $this->modx->newQuery("slStores");
            $query->where(array("slStores.id:=" => $mail_data["order"]["store_id"]));
            $query->select(array("slStores.name_short as name,slStores.address,slStores.email"));
            if($query->prepare() && $query->stmt->execute()){
                $mail_data["seller"] = $query->stmt->fetch(PDO::FETCH_ASSOC);
            }
        }
        if($mail_data["order"]["warehouse_id"]){
            // buyer
            $query = $this->modx->newQuery("slStores");
            $query->leftJoin("slOrg", "slOrg", "slOrg.id = {$mail_data['order']['org_id']}");
            $query->where(array("slStores.id:=" => $mail_data["order"]["warehouse_id"]));
            $query->select(array("slStores.name,slStores.address,slOrg.name as org_name"));
            if($query->prepare() && $query->stmt->execute()){
                $mail_data["buyer"] = $query->stmt->fetch(PDO::FETCH_ASSOC);
                $query = $this->modx->newQuery("slOrgRequisites");
                $query->where(array("slOrgRequisites.org_id:=" => $mail_data["order"]['org_id']));
                $query->select(array("slOrgRequisites.*"));
                if($query->prepare() && $query->stmt->execute()){
                    $mail_data["buyer"]['req'] = $query->stmt->fetch(PDO::FETCH_ASSOC);
                }
                if($mail_data["buyer"]['req']){
                    $mail_data["buyer"]['buyer'] = "ИНН: ".$mail_data["buyer"]['req']['inn'].", ".$mail_data["buyer"]['org_name'];
                }else{
                    $mail_data["buyer"]['buyer'] = $mail_data["buyer"]['org_name'];
                }
            }
        }
        if($mail_data["seller"]["email"]){
            //Получаем массив, кому отправить уведомления
            $emails = $this->sl->notification->getEmailManagers($org['org_id'], $data["org_id"], 2);
            if(count($emails) && $this->sl->config["alert_mode"] == 1){
                $result = $this->sl->tools->sendMail("@FILE chunks/email_order_opt.tpl", $mail_data, $emails, "Новый оптовый заказ №{$mail_data["order"]["id"]}! МС Закупки.");
            }
        }
        // отправляем господам в телегу
        $json = json_encode($mail_data, JSON_UNESCAPED_UNICODE);
        if($this->sl->config["alert_mode"] == 1) {
            $this->sl->darttelegram->sendMessage("INFO", "ЗАКАЗ!!! Оптовый и очень жирный - данные: <pre language=\"json\">" . $json . "</pre>");
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
//        $this->modx->log(1, print_r($data, 1));
        foreach($data['products'] as $k => $item){
            $output['cost'] += $item["info"]["price"] * $item["info"]['count'];
            $output['count'] += $item["info"]['count'];
            $params = $this->sl->product->getProductParams($k);
            $output['weight'] += $params[0]['product']["weight_brutto"] * $item["info"]['count'];
            $output['volume'] += $params[0]['product']["length"] * $params[0]['product']["width"] * $params[0]['product']["height"] * $item["info"]['count'];
        }
        foreach($data['complects'] as $kr => $complect){
            foreach($complect['products'] as $k => $item){
                $output['cost'] += $item['info']['count'] * $item["info"]['price'];
                $output['count'] += $item["info"]['count'];
                foreach($complect['products']['info']['complect_data']['products'] as $i => $t){
                    if($t['id'] == $item['id']){
                        if($t['product_id']){
                            $params = $this->sl->product->getProductParams($k);
                            $output['weight'] += $params[0]['product']["weight_brutto"] * $item["info"]['count'];
                            $output['volume'] += $params[0]['product']["length"] * $params[0]['product']["width"] * $params[0]['product']["height"] * $item["info"]['count'];
                        }
                    }
                }
            }
        }
        $output['volume'] = $output['volume'] / 1000000;
        return $output;
    }

    /**
     * Оптовые заказы
     *
     * @param $properties
     * @return array|void
     */
    public function getOrdersSeller($properties) {
        if($properties["id"]){
            $result = array();

            if($properties["order_id"]){
                $query = $this->modx->newQuery("slOrderOpt");
                $query->rightJoin("slStores", "slStores", "slStores.id = slOrderOpt.store_id");
                $query->rightJoin("slOrg", "slOrg", "slOrg.id = slOrderOpt.org_id");
                $query->leftJoin("slOrderOptStatus", "slOrderOptStatus", "slOrderOptStatus.id = slOrderOpt.status");
                $query->select(array(
                    "`slOrderOpt`.*",
                    "`slStores`.name_short",
                    "`slStores`.inn as seller_inn",
                    "`slStores`.image as seller_image",
                    "`slStores`.kpp as seller_kpp",
                    "`slStores`.phone as seller_phone",
//                    "`slStores`.address as seller_address",
                    "`slOrg`.name as buyer_name",
                    "`slOrg`.image as buyer_image",
                    "`slOrderOptStatus`.name as status_name",
                    "`slOrderOptStatus`.color as status_color",
                ));

                $query->where(array(
                    "`slOrderOpt`.`id`:=" => $properties['order_id']
                ));

                if ($query->prepare() && $query->stmt->execute()) {
                    $result["order"] = $query->stmt->fetch(PDO::FETCH_ASSOC);
                    $urlMain = $this->modx->getOption("site_url");

                    $result["order"]['buyer_image'] = $urlMain . 'assets/content/' . $result["order"]['buyer_image'];
                    $result["order"]['seller_image'] = $urlMain . 'assets/content/' . $result["order"]['seller_image'];

                    $query_p = $this->modx->newQuery("slOrderOptProduct");
                    $query_p->leftJoin("slStoresRemains", "slStoresRemains", "slStoresRemains.id = slOrderOptProduct.remain_id");
                    $query_p->leftJoin("msProductData", "msProductData", "msProductData.id = slStoresRemains.product_id");
                    $query_p->leftJoin("modResource", "modResource", "modResource.id = slStoresRemains.product_id");
                    $query_p->select(array(
                        "`slOrderOptProduct`.*",
                        "`msProductData`.*",
                        "`modResource`.*",
                        "`slOrderOptProduct`.price as price",
                        "COALESCE(msProductData.image, '/assets/files/img/nopic.png') as image",
                        "`slOrderOptProduct`.price as price",
                    ));
                    $query_p->where(array(
                        "`slOrderOptProduct`.`order_id`:=" => $result["order"]['id']
                    ));

                    if ($query_p->prepare() && $query_p->stmt->execute()) {
                        $result["order"]['products'] = $query_p->stmt->fetchAll(PDO::FETCH_ASSOC);
                        foreach ($result["order"]['products'] as $key => $product){
                            $result["order"]['products'][$key]['image'] = $urlMain . $product['image'];
                        }

                        $q = $this->modx->newQuery("slStores");
                        $q->select(array(
                            "`slStores`.address as seller_address",
                        ));
                        $q->where(array(
                            "`slStores`.`id`:=" => $result["order"]['warehouse_id']
                        ));
                        if ($q->prepare() && $q->stmt->execute()) {
                            $warehouse = $q->stmt->fetch(PDO::FETCH_ASSOC);
                            $result["order"]['seller_address'] = $warehouse['seller_address'];
                        }
                    }
                }
            }
            else{
                $query = $this->modx->newQuery("slOrderOpt");
                $query->leftJoin("slStores", "slStores", "slStores.id = slOrderOpt.store_id");
                $query->leftJoin("slOrderOptStatus", "slOrderOptStatus", "slOrderOptStatus.id = slOrderOpt.status");
                $query->rightJoin("slOrg", "slOrg", "slOrg.id = slOrderOpt.org_id");

                $query->select(array(
                    "`slOrderOpt`.*",
                    "`slStores`.name_short as store_name",
                    "`slOrderOptStatus`.name as status_name",
                    "`slOrderOptStatus`.color as status_color",
                    "`slStores`.address as seller_address",
                    "`slStores`.image as seller_image",
                    "`slOrg`.name as buyer_name",
                    "`slOrg`.image as buyer_image",
                    "CONCAT(`slStores`.name_short, ',', `slStores`.address) as seller"
                ));

                //Получить все ids складов организации
                $warehouses = $this->sl->orgHandler->getStoresOrg($properties, 0);

                $warehouse_ids = array();
                foreach ($warehouses['items'] as $kw => $itemw){
                    $warehouse_ids[] = $itemw['id'];
                }

                $query->where(array(
                    "`slOrderOpt`.`store_id`:IN" => $warehouse_ids
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
                $query->prepare();
                $this->modx->log(1, $query->toSQL());
                if ($query->prepare() && $query->stmt->execute()) {
                    $result["orders"] = $query->stmt->fetchAll(PDO::FETCH_ASSOC);
                    foreach ($result["orders"] as $k => $item){
                        $result["orders"][$k]['seller_image'] = $this->sl->tools->prepareImage($item['seller_image'])['image'];
                        $result["orders"][$k]['buyer_image'] = $this->sl->tools->prepareImage($item['buyer_image'])['image'];
                        if($item["delivery_payer"] == 0){
                            $result["orders"][$k]['delivery'] = "Покупатель";
                        }else{
                            $result["orders"][$k]['delivery'] = "Поставщик";
                        }
                        if($item["createdon"]){
                            $result["orders"][$k]['createdon'] = date('d.m.Y H:i', strtotime($item['createdon']));
                        }
                        $q = $this->modx->newQuery("slStores");
                        $q->leftJoin("slOrg", "slOrg", "slOrg.id = {$item['org_id']}");
                        $q->select(array(
                            "`slStores`.address as buyer_address",
                            "`slOrg`.name as buyer_name",
                        ));
                        $q->where(array(
                            "`slStores`.`id`:=" => $item['warehouse_id']
                        ));
                        if ($q->prepare() && $q->stmt->execute()) {
                            $warehouse = $q->stmt->fetch(PDO::FETCH_ASSOC);
                            $result["orders"][$k]['buyer_address'] = $warehouse['buyer_address'];
                            $query = $this->modx->newQuery("slOrgRequisites");
                            $query->where(array("slOrgRequisites.org_id:=" => $item['org_id']));
                            $query->select(array("slOrgRequisites.*"));
                            if($query->prepare() && $query->stmt->execute()){
                                $result["orders"][$k]['buyer_reqs']['req'] = $query->stmt->fetch(PDO::FETCH_ASSOC);
                            }
                            if($result["orders"][$k]['buyer_reqs']['req']){
                                $result["orders"][$k]['buyer'] = "ИНН: ".$result["orders"][$k]['buyer_reqs']['req']['inn'].", ".$warehouse['buyer_name'];
                            }else{
                                $result["orders"][$k]['buyer'] = $warehouse['buyer_name'];
                            }
                        }
                    }
                }
            }
            return $result;
        }
    }

    /**
     * Мои заказы
     */
    public function getOrdersBuyer($properties){
        if($properties["id"]){
            $result = array();

            if($properties["order_id"]){
                $query = $this->modx->newQuery("slOrderOpt");
                $query->rightJoin("slStores", "slStores", "slStores.id = slOrderOpt.store_id");
                $query->leftJoin("slOrderOptStatus", "slOrderOptStatus", "slOrderOptStatus.id = slOrderOpt.status");
                $query->select(array(
                    "`slOrderOpt`.*",
                    "`slStores`.name_short",
                    "`slStores`.phone",
                    "`slStores`.name_short as store_name",
                    "`slOrderOptStatus`.name as status_name",
                    "`slOrderOptStatus`.color as status_color",
//                    "`slStores`.address as seller_address",
                    "`slStores`.image as seller_image",
                ));
                $query->where(array(
                    "`slOrderOpt`.`org_id`:=" => $properties['id'],
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
                        "`slOrderOptProduct`.price as price",
                        "COALESCE(msProductData.image, '/assets/files/img/nopic.png') as image",
                    ));
                    $query_p->where(array(
                        "`slOrderOptProduct`.`order_id`:=" => $result["order"]['id']
                    ));

                    if ($query_p->prepare() && $query_p->stmt->execute()) {
                        $result["order"]['products'] = $query_p->stmt->fetchAll(PDO::FETCH_ASSOC);
                        $urlMain = $this->modx->getOption("site_url");

                        foreach ($result["order"]['products'] as $k => $product){
                            $result["order"]['products'][$k]['image'] = $urlMain . $product['image'];
                        }

                        $q = $this->modx->newQuery("slStores");

                        $q->select(array(
                            "`slStores`.address as seller_address",
                        ));

                        $q->where(array(
                            "`slStores`.`id`:=" => $result["order"]['warehouse_id']
                        ));

                        if ($q->prepare() && $q->stmt->execute()) {
                            $warehouse = $q->stmt->fetch(PDO::FETCH_ASSOC);
                            $result["order"]['seller_address'] = $warehouse['seller_address'];
                        }
                    }
                }
            }
            else{
                $query = $this->modx->newQuery("slOrderOpt");
                $query->leftJoin("slStores", "slStores", "slStores.id = slOrderOpt.store_id");
                $query->leftJoin("slOrderOptStatus", "slOrderOptStatus", "slOrderOptStatus.id = slOrderOpt.status");

                $query->select(array(
                    "`slOrderOpt`.*",
                    "`slStores`.name_short as store_name",
                    "`slOrderOptStatus`.name as status_name",
                    "`slOrderOptStatus`.color as status_color",
                    "`slStores`.address as seller_address",
                    "`slStores`.image as seller_image",
                ));
                $query->where(array(
                    "`slOrderOpt`.`org_id`:=" => $properties['id']
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

                    foreach ($result["orders"] as $k => $item){
                        $q = $this->modx->newQuery("slStores");

                        $q->select(array(
                            "`slStores`.address as seller_address",
                        ));

                        $q->where(array(
                            "`slStores`.`id`:=" => $item['warehouse_id']
                        ));

                        if ($q->prepare() && $q->stmt->execute()) {
                            $warehouse = $q->stmt->fetch(PDO::FETCH_ASSOC);
                            $result["orders"][$k]['seller_address'] = $warehouse['seller_address'];
                        }
                    }
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
                    $urlMain = $this->modx->getOption("site_url");

                    foreach($products as $product){
                        $id = $product['remain_id'];
                        $product['id'] = $product['remain_id'];
                        $product['price'] = (float)$product['old_price'];
                        $product['discountInRubles'] = (float)$product['old_price'] - $product['new_price'];
                        $product['discountInterest'] = $product['discountInRubles'] / ($product['old_price'] / 100);
                        $product['finalPrice'] = (float)$product['new_price'];
                        $product['image'] = $urlMain . $product['image'];

                        $selected->$id = $product;
                    }

                    $data['products'] = $selected;
                }

                return $data;
            }
        } else{

            //Получаем все склады организации
            $ids_warehouses = array();
            $warehouses = $this->sl->orgHandler->getStoresOrg(array("id" => $properties['store_id']));


            foreach ($warehouses['items'] as $warehouse){
                $ids_warehouses[] = $warehouse['id'];
            }

            $q = $this->modx->newQuery("slComplects");
            $q->select(array(
                'slComplects.*'
            ));
            $q->where(array("`slComplects`.`store_id`:IN" => $ids_warehouses));

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
                if(!$result['complects']){
                    $result['complects'] = array();
                    $result['total'] = 0;
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
                    $query->leftJoin("dartLocationCity", "dartLocationCity", "dartLocationCity.id = slStores.city");
                    $query->leftJoin("msProductData", "msProductData", "msProductData.id = slStoresRemains.product_id");
                    $query->leftJoin("modResource", "modResource", "modResource.id = slStoresRemains.product_id");

                    $query->select(array(
                        "`slComplects`.*",
                        "`slComplectsProducts`.*",
                        "`msProductData`.*",
                        "`modResource`.*",
                        "COALESCE(msProductData.image, '/assets/files/img/nopic.png') as image",
                        "`slComplects`.id as id",
                        "`slStores`.name_short as store_name",
                        "`slStores`.image as store_image",
                        "`dartLocationCity`.city as store_city",
                    ));

                    $query->where(array(
                        "`slComplectsProducts`.`complect_id`:=" => $complect['id'],
                    ));

                    if ($query->prepare() && $query->stmt->execute()) {
                        $compl = $query->stmt->fetchAll(PDO::FETCH_ASSOC);

                        $urlMain = $this->sl->config["urlMain"];
                        $id_warehouse = $this->getWarehouseBasket(array("id" => $properties['id']));

                        foreach ($compl as $k => $compl_product){

                            $q_o = $this->modx->newQuery("slOrgStores");
                            $q_o->leftJoin("slOrg", "slOrg", "slOrg.id = slOrgStores.org_id");
                            $q_o->select(array(
                                "`slOrg`.name as org_name",
                            ));
                            $q_o->where(array("`slOrgStores`.`store_id`:=" => $compl_product['store_id']));
                            if ($q_o->prepare() && $q_o->stmt->execute()) {
                                $org = $q_o->stmt->fetch(PDO::FETCH_ASSOC);
                                if($org) {
                                    $compl[$k]['org_name'] = $org['org_name'];
                                }
                            }


                            $compl[$k]['store_image'] =  $this->sl->tools->prepareImage($compl_product['store_image'])['image'];


                            $complect_data = $this->getRemainComplect($compl_product['store_id'], $compl_product['complect_id']);
                            $compl[$k]['delivery'] = $this->sl->cart->getNearShipment($compl_product['store_id'], $this->getWarehouseBasket($properties));
                            $compl[$k]['delivery_day'] = date("Y-m-d", time()+60*60*24 * $compl[$k]['delivery']);
                            $compl[$k]['remain_complect'] = $complect_data["min_count"];
                            $compl[$k]['image'] = $urlMain . $compl[$k]['image'];

                            //TODO: КОПЛЕКТЫ ДОПИЛИТЬ
                            //Проверка, есть ли комплект в корзине
                            if($_SESSION['basket'][$properties['id']][$id_warehouse][$compl_product['store_id']]['complects'][$compl_product['complect_id']] != null) {
                                $compl[$k]['basket'] = array(
                                    "availability" => true,
                                    "count" => $_SESSION['basket'][$properties['id']][$id_warehouse][$compl_product['store_id']]['complects'][$compl_product['complect_id']]['count']
                                );
                            } else{
                                $compl[$k]['basket'] = array(
                                    "availability" => false,
                                    "count" => 1
                                );
                            }
                        }
                        foreach ($compl as $k => $compl_product){
//                            if($compl_product['remain_complect'] == 0){
//                                unset($compl[$k]);
//                            }
                        }
                        $remain = $this->getRemainComplect($compl_product['store_id'], $complect['id']);
//                        if($properties['remain_id'] == 165868){
//                            $this->modx->log(1, print_r($remain, 1));
//                            $this->modx->log(1, "KENOST COMPLECTS");
//                        }
                        if($remain['min_count']){
//                            $compl['remain'] = array(
//                                'min_count' => $remain['min_count'],
//                                'min_count_abstract' => $remain['min_count'],
//                            );
                            $result[] = $compl;
                        }
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
        if(isset($properties['remain_id']) && isset($properties['id']) && isset($properties['action_id'])){
            //Проверяем, есть ли у пользователя в сесии такой товар в нужном магазине
            $_SESSION['actions'][$properties['id']][$properties['remain_id']][$properties['action_id']] = $properties['status'];
        }
        return $_SESSION['actions'];
    }

    /**
     * Берем конфликты акций
     * @param $properties
     * @return array
     */
    public function getConflicts($properties)
    {
        $result['items'] = array();
        $q = $this->modx->newQuery("slActions");
        $q->leftJoin("slActionsProducts", "slActionsProducts", "slActionsProducts.action_id = slActions.id");
        $q->select(array(
            "`slActions`.*"
        ));
        $q->where(array(
            "`slActionsProducts`.`remain_id`:=" => $properties['remain_id'],
//            "`slActions`.`store_id`:=" => $properties['store_id'],
            "FIND_IN_SET({$properties['store_id']}, `slActions`.`store_id`) > 0",
            "`slActions`.`active`:=" => 1,
            "`slActions`.`type`:=" => 1,
        ));


        if ($q->prepare() && $q->stmt->execute()) {
            // получили действующие акции
            $actions = $q->stmt->fetchAll(PDO::FETCH_ASSOC);

            $q_i = $this->modx->newQuery("slActions");
            $q_i->leftJoin("slActionsProducts", "slActionsProducts", "slActionsProducts.action_id = slActions.id AND slActionsProducts.remain_id = {$properties['remain_id']}");
            $q_i->select(array(
                "`slActions`.*",
                "`slActions`.type as type",
                "`slActionsProducts`.*",
                "`slActions`.id as id",
            ));
            $q_i->where(array(
                "`slActions`.`type`:=" => 3,
                "`slActions`.`store_id`:=" => $properties['store_id'],
                "`slActions`.`client_id`:=" => $properties['id'],
            ));

            if ($q_i->prepare() && $q_i->stmt->execute()) {
                // получили действующие акции
                $actions_individual = $q_i->stmt->fetch(PDO::FETCH_ASSOC);

                $actions = array_merge($actions, array($actions_individual));

                $result['test'] = $actions;

                $conflict_global = false;
                $conflict_temp = [];
//                if($properties['remain_id'] == 786194){
//                    $this->modx->log(1, print_r($actions, 1));
//                    $this->modx->log(1, print_r($properties, 1));
//                }
                foreach ($actions as $key => $action) {
                    // разбираем конфликт отсрочек при условии НЕ совместимости
                    if($action["compatibility_postponement"] != 1 && $action["delay"] > 0){
                        // случай НЕ совместимости со всеми акциями
                        if($action["compatibility_postponement"] == 2){
                            foreach($actions as $k => $v){
                                 if($action["id"] != $v["id"] && $v['delay'] > 0) {
//                                if($_SESSION['actions'][$properties['store_id']][$properties['remain_id']][$v['action_id']]) {
                                    $conflict_temp[$v["id"]]["postponement_conflicts"][] = $action["id"];
                                    $conflict_temp[$action["id"]]["postponement_conflicts"][] = $v["id"];
                                    $conflict_global = true;
//                                }
                                 }
                            }
                        }
                        // случай НЕ совместимости со выбранными акциями
                        if($action["compatibility_postponement"] == 3){
                            if($action["big_post_actions"]){
                                $c_actions = json_decode($action["big_post_actions"]);
                                foreach($c_actions as $cv){
//                                    if($cv == 0){
//                                        //Индивидуальные условия
//                                        $conflict_temp[$cv]["postponement_conflicts"][] = $action["id"];
//                                        $conflict_temp[$action["id"]]["postponement_conflicts"][] = $cv;
//                                        $conflict_global = true;
//                                    }
                                    if($action["id"] != $cv) {
//                                    if($_SESSION['actions'][$properties['store_id']][$properties['remain_id']][$cv['action_id']]) {
                                        $conflict_temp[$cv]["postponement_conflicts"][] = $action["id"];
                                        $conflict_temp[$action["id"]]["postponement_conflicts"][] = $cv;
                                        $conflict_global = true;
//                                    }
                                    }
                                }
                            }
                        }
                        // случай совместимости со выбранными акциями
                        if($action["compatibility_postponement"] == 4){
                            if($action["big_post_actions"]){
                                $c_actions = json_decode($action["big_post_actions"]);
                                foreach($actions as $k => $v){
                                    if($action["id"] != $v["id"] && !in_array($v["id"], $c_actions) && $v['delay'] > 0) {
//                                    if($_SESSION['actions'][$properties['store_id']][$properties['remain_id']][$v['action_id']]) {
                                        $conflict_temp[$v]["postponement_conflicts"][] = $action["id"];
                                        $conflict_temp[$action["id"]]["postponement_conflicts"][] = $v;
                                        $conflict_global = true;
//                                    }
                                    }
                                }
                            }
                        }
                    }
                    // разбираем конфликт цен, при условии НЕ совместимости
                    if($action["compatibility_discount"] != 1) {
                        // случай НЕ совместимости со всеми акциями
                        if ($action["compatibility_discount"] == 2) {
                            foreach ($actions as $k => $v) {
                                if ($action["id"] != $v["id"]) {
//                                if($_SESSION['actions'][$properties['store_id']][$properties['remain_id']][$v['action_id']]) {
                                    if(isset($conflict_temp[$v["id"]]["sales_conflicts"])){
                                        if(!in_array($action["id"], $conflict_temp[$v["id"]]["sales_conflicts"])){
                                            $conflict_temp[$v["id"]]["sales_conflicts"][] = $action["id"];
                                        }
                                    }else{
                                        $conflict_temp[$v["id"]]["sales_conflicts"][] = $action["id"];
                                    }
                                    if(isset($conflict_temp[$action["id"]]["sales_conflicts"])) {
                                        if (!in_array($v["id"], $conflict_temp[$action["id"]]["sales_conflicts"])) {
                                            $conflict_temp[$action["id"]]["sales_conflicts"][] = $v["id"];
                                        }
                                    }else{
                                        $conflict_temp[$action["id"]]["sales_conflicts"][] = $v["id"];
                                    }
                                    $conflict_global = true;
                                }
                            }
                        }
                        // случай НЕ совместимости с выбранными акциями
                        if ($action["compatibility_discount"] == 3) {
                            if ($action["big_sale_actions"]) {
                                $c_actions = json_decode($action["big_sale_actions"]);


                                foreach ($actions as $k => $v) {
                                    if ($action["id"] != $v["id"] && in_array($v["id"], $c_actions)) {
                                        if(isset($conflict_temp[$v["id"]]["sales_conflicts"])){
                                            if(!in_array($action["id"], $conflict_temp[$cv]["sales_conflicts"])){
                                                $conflict_temp[$v["id"]]["sales_conflicts"][] = $action["id"];
                                            }
                                        }else{
                                            $conflict_temp[$v["id"]]["sales_conflicts"][] = $action["id"];
                                        }
                                        if(isset($conflict_temp[$action["id"]]["sales_conflicts"])) {
                                            if (!in_array($v["id"], $conflict_temp[$action["id"]]["sales_conflicts"])) {
                                                $conflict_temp[$action["id"]]["sales_conflicts"][] = $v["id"];
                                            }
                                        }else{
                                            $conflict_temp[$action["id"]]["sales_conflicts"][] = $v["id"];
                                        }
                                        $conflict_global = true;
                                    }
                                }

                                //ИНДИВИДУАЛЬНЫЕ СКИДКИ
                                foreach ($c_actions as $keya => $id){
                                    if($id == '0'){
                                        if($actions_individual['id']){
                                            $conflict_temp[$actions_individual['id']]["sales_conflicts"][] = $action["id"];
                                            $conflict_temp[$action["id"]]["sales_conflicts"][] = $actions_individual['id'];
                                        }
                                    }
                                }
                            }
                        }
                        // случай совместимости с выбранными акциями
                        if ($action["compatibility_discount"] == 4) {
                            if($properties['remain_id'] == 784091){
                                $this->modx->log(1, print_r($action, 1));
                                $this->modx->log(1, "KENOST");
                            }
                            if ($action["big_sale_actions"]) {
                                $c_actions = json_decode($action["big_sale_actions"]);

                                //TODO: ПРОВЕРИТЬ РАБОТОСПОСОБНОСТЬ
                                foreach ($actions as $k => $v) {
                                    if ($action["id"] != $v["id"] && !in_array($v["id"], $c_actions)) {
                                        if(isset($conflict_temp[$v["id"]]["sales_conflicts"])){
                                            if(!in_array($action["id"], $conflict_temp[$v["id"]]["sales_conflicts"])){
                                                $conflict_temp[$v["id"]]["sales_conflicts"][] = $action["id"];
                                            }
                                        }else{
                                            $conflict_temp[$v["id"]]["sales_conflicts"][] = $action["id"];
                                        }
                                        if(isset($conflict_temp[$action["id"]]["sales_conflicts"])) {
                                            if (!in_array($v["id"], $conflict_temp[$action["id"]]["sales_conflicts"])) {
                                                $conflict_temp[$action["id"]]["sales_conflicts"][] = $v["id"];
                                            }
                                        }else{
                                            $conflict_temp[$action["id"]]["sales_conflicts"][] = $v["id"];
                                        }
                                        $conflict_global = true;
                                    }
                                }

                                //ИНДИВИДУАЛЬНЫЕ СКИДКИ
                                foreach ($c_actions as $keya => $id){
                                    if($id == '0'){
                                        if($actions_individual['id']){
                                            $conflict_temp[$actions_individual['id']]["sales_conflicts"] = array_diff($conflict_temp[$actions_individual['id']]["sales_conflicts"], [$action["id"]]);
                                            $conflict_temp[$action["id"]]["sales_conflicts"] = array_diff($conflict_temp[$action["id"]]["sales_conflicts"], [$actions_individual['id']]);
                                        }
                                    }
                                }
                            }
                        }
                    }
                    $result['items'] = $conflict_temp;
                }
                $result['global'] = $conflict_global;
            }

            return $result;
        }
    }

    public function generateOptXslx($properties){
        $properties['warehouse'] = 'all';
        $basket = $this->getBasket($properties);

        $path = $this->sl->xslx->generateOptOrder($basket, $properties);
        $urlMain = $this->modx->getOption("site_url");

        return $urlMain . $path;
    }

    public function uploadProductsFile($properties){
        return $this->sl->xslx->processActionFile($properties['store_id'], $properties['file'], $properties['type']);
    }
}