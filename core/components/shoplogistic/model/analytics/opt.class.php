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
            case 'get/vendors':
                $response = $this->getVendors($properties);
                break;
            case 'get/products':
                $response = $this->getProducts($properties);
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
     * Берем каталог и строим меню
     *
     * @param $properties
     * @return array
     */
    public function getCatalog($properties){
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
            $data[$key]['menu_image'] = $urlMain . $value['menu_image'];

            foreach ($value['children'] as $k => $v) {
                $data[$key]['children'][$k]['children'] = $urlMain . "assets/content/" . $v['menu_image'];
            }
        }

        return $data;
    }

    public function getCart($properties){

    }

    /**
     * Поставщики
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
            "`slWarehouseStores`.`store_id`:=" => $properties['id'],
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
        $query = $this->modx->newQuery("slStores");
        $query->select(array(
            "`slStores`.*"
        ));
        $query->where(array(
            "`slStores`.`warehouse`:=" => true,
            "`slStores`.`active`:=" => true
        ));
        $data['available_count'] = $this->modx->getCount('slStores', $query);
        if($properties['filter']){
            $query->where(array(
                "`slStores`.`name`:LIKE" => "%".$properties['filter']."%",
                "OR:`slStores`.`address`:LIKE" => "%".$properties['filter']."%"
            ));
        }
        if($iids){
            $query->where(array(
                "`slStores`.`warehouse`:NOT IN" => $iids
            ));
        }
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
        // TODO: выбранные поставщики
        $data = array();
        $urlMain = $this->modx->getOption("site_url");
        $warehouses = $this->sl->store->getWarehouses($properties['id']);
        $av = array();
        foreach($warehouses as $wh){
            $av[] = $wh['id'];
        }

        // нашли поставщиков
        if(count($av)){
            $query = $this->modx->newQuery("slStoresRemains");
            $query->leftJoin("msProductData", "msProductData", "msProductData.id = slStoresRemains.product_id");
            $query->leftJoin("modResource", "modResource", "modResource.id = slStoresRemains.product_id");
            $query->where(array(
                "`msProductData`.`available`:=" => 1,
                "`modResource`.`parent`:=" => $properties['category_id'],
                "`slStoresRemains`.`store_id`:IN" => $av,
                "`slStoresRemains`.`remains`:>" => 0,
                "`slStoresRemains`.`price`:>" => 0
            ));
            $query->select(array(
                "`msProductData`.*",
                "`modResource`.*"
            ));
            $query->groupby("slStoresRemains.product_id");
            // Подсчитываем общее число записей
            $data['total'] = $this->modx->getCount('slStoresRemains', $query);

            // Устанавливаем лимит 1/10 от общего количества записей
            // со сдвигом 1/20 (offset)
            if($properties['page'] && $properties['perpage']) {
                $limit = $properties['perpage'];
                $offset = ($properties['page'] - 1) * $properties['perpage'];
                $query->limit($limit, $offset);
            }
            $query->prepare();
            $this->modx->log(1, $query->toSQL());
            if ($query->prepare() && $query->stmt->execute()) {
                $data['items'] = $query->stmt->fetchAll(PDO::FETCH_ASSOC);
            }

            foreach ($data['items'] as $key => $value) {
                $data['items'][$key]['image'] = $urlMain . $value['image'];
                $q = $this->modx->newQuery("slStoresRemains");
                $q->select(array(
                    "`slStoresRemains`.*"
                ));
                $q->where(array(
                    "`slStoresRemains`.`product_id`:=" => $data['items'][$key]['id'],
                    "`slStoresRemains`.`remains`:>" => 0,
                    "`slStoresRemains`.`guid`:!=" => "",
                    "`slStoresRemains`.`store_id`:IN" => $av
                ));

                if ($q->prepare() && $q->stmt->execute()) {
                    $data['items'][$key]['stores'] = $q->stmt->fetchAll(PDO::FETCH_ASSOC);
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
}