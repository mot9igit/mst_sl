<?php

require_once dirname(__FILE__) . '/../libs/vendor/autoload.php';

use KSamuel\FacetedSearch\Index\Factory;
use KSamuel\FacetedSearch\Search;
use KSamuel\FacetedSearch\Filter\ValueFilter;
use KSamuel\FacetedSearch\Filter\ExcludeValueFilter;
use KSamuel\FacetedSearch\Filter\ValueIntersectionFilter;
use KSamuel\FacetedSearch\Filter\RangeFilter;
use KSamuel\FacetedSearch\Query\SearchQuery;
use KSamuel\FacetedSearch\Query\AggregationQuery;
use KSamuel\FacetedSearch\Query\Order;

class filters
{
    public $modx;
    public $sl;
    public $config;
    public $output;
    public $category;
    public $cache_time = 3600;

    // Список доступных фильтров по типу
    protected array $rangeFilters = ['price', 'weight'];
    protected array $valueFilters = ['category', 'made_in'];
    protected array $booleanFilters = ['new', 'popular', 'favorite'];

    public function __construct(shopLogistic &$sl, modX &$modx, $category = null)
    {

        $this->sl =& $sl;
        $this->modx =& $modx;
        $this->sl->loadServices();
        $this->modx->lexicon->load('shoplogistic:default');
        $this->category = $category;
        $this->limit = 100;
        $this->filtertv = 3;

        $dir = dirname(__FILE__);

        $this->output = array(
            "categories" => array(),
            "offers" => array()
        );
    }

    /**
     * @param $category
     * @param $records
     * @return array
     */
    public function getRangePrices($category, $records = array()){
        // 1. Берем местоположение пользователя.
        $checked_store = array();
        $all_stores = array();
        $output = array();
        $location = $this->sl->getLocationData('web');
        $store_id = $location["pls"]["store_id"];
        if($store_id){
            $query = $this->modx->newQuery("slStoresRemains");
            $query->leftJoin("msProductData", "msProductData", "msProductData.id = slStoresRemains.product_id");
            $query->leftJoin("modResource", "modResource", "modResource.id = slStoresRemains.product_id");
            $query->where(array(
                "slStoresRemains.store_id:=" => $store_id,
                "modResource.parent:=" => $category,
                "slStoresRemains.price:>" => 0,
                "slStoresRemains.remains:>" => 0
            ));
            if($records){
                $query->where(array("modResource.id:IN" => $records));
            }
            $query->select(array("MIN(slStoresRemains.price) as min", "MAX(slStoresRemains.price) as max"));
            if($query->prepare() && $query->stmt->execute()){
                $checked_store = $query->stmt->fetch(PDO::FETCH_ASSOC);
            }
        }
        $query = $this->modx->newQuery("slStoresRemains");
        $query->leftJoin("msProductData", "msProductData", "msProductData.id = slStoresRemains.product_id");
        $query->leftJoin("modResource", "modResource", "modResource.id = slStoresRemains.product_id");
        $query->where(array(
            "slStoresRemains.store_id:!=" => $store_id,
            "modResource.parent:=" => $category,
            "slStoresRemains.price:>" => 0,
            "slStoresRemains.remains:>" => 0
        ));
        if($records){
            $query->where(array("modResource.id:IN" => $records));
        }
        $query->select(array("MIN(slStoresRemains.price) as min", "MAX(slStoresRemains.price) as max"));
        if($query->prepare() && $query->stmt->execute()){
            $all_stores = $query->stmt->fetch(PDO::FETCH_ASSOC);
            if(isset($checked_store['min'])){
                if($checked_store['min'] < $all_stores['min']){
                    $output[0] = $checked_store['min'];
                }else{
                    $output[0] = $all_stores['min'];
                }
            }else{
                $output[0] = $all_stores['min'];
            }
            if(isset($checked_store['max'])){
                if($checked_store['max'] > $all_stores['max']){
                    $output[1] = $checked_store['max'];
                }else{
                    $output[1] = $all_stores['max'];
                }
            }else{
                $output[1] = $all_stores['max'];
            }
        }
        if(!$output[0]){
            $output[0] = 0;
        }
        if(!$output[1]){
            $output[1] = 0;
        }
        return $output;
    }

    /**
     * Индексирование опции, загружается по крону при необходимости
     *
     * @param $category
     * @return void
     */
    public function createIndex($category = null){
        $this->category = $category;
        $search = (new Factory)->create(Factory::ARRAY_STORAGE);
        $storage = $search->getStorage();

        if($this->category) {
            $category = $this->modx->getObject("modResource", $this->category);

            if($category){
                $filters = json_decode($category->getTVValue("filters"), 1);
                $ms_fields = array("msProductData.id", "msProductData.vendor", "msProductData.available");
                $opt_filters = array();
                $resource_filters = array("modResource.pagetitle", "modResource.parent");
                foreach($filters as $filter){
                    // проверяем типы фильтров
                    if($filter["filter_table"] == "ms"){
                        if($filter["filter_field"] == "vendor"){
                            $ms_fields[] = "msVendor.name as vendor";
                        }else{
                            $ms_fields[] = "msProductData.".$filter["filter_field"];
                        }
                    }
                    if($filter["filter_table"] == "msoption"){
                        $opt_filters[] = $filter["filter_field"];
                    }
                    if($filter["filter_table"] == "resource"){
                        $resource_filters[] = "modResource.".$filter["filter_field"];
                    }
                }

                $query = $this->modx->newQuery("msProductData");
                $query->leftJoin("modResource", "modResource", "modResource.id = msProductData.id");
                $query->leftJoin("msVendor", "msVendor", "msVendor.id = msProductData.vendor");
                $query->select(array(implode(",", $ms_fields).",".implode(",", $resource_filters)));
                if($this->category){
                    $query->where(array("modResource.parent:=" => $this->category));
                }

                $total = $this->modx->getCount("msProductData", $query);

                $limit = $this->limit;
                for($i = 0; $i <= $total; $i += $limit){
                    $query = $this->modx->newQuery("msProductData");
                    $query->leftJoin("modResource", "modResource", "modResource.id = msProductData.id");
                    $query->leftJoin("msVendor", "msVendor", "msVendor.id = msProductData.vendor");
                    if(count($ms_fields)){
                        $query->select(array(implode(",", $ms_fields)));
                    }
                    if(count($resource_filters)){
                        $query->select(array(implode(",", $resource_filters)));
                    }
                    if($this->category){
                        $query->where(array("modResource.parent:=" => $this->category));
                    }
					$query->where(array("msProductData.image:!=" => ""));
					$query->where(array("msProductData.vendor_article:!=" => ""));
                    $query->limit($limit, $i);
                    if($query->prepare() && $query->stmt->execute()){
                        $products = $query->stmt->fetchAll(PDO::FETCH_ASSOC);
                        foreach($products as $product){
                            if(count($opt_filters)){
                                // берем опции
                                $query = $this->modx->newQuery("msProductOption");
                                $query->where(array(
                                    "product_id:=" => $product["id"],
                                    "AND:msProductOption.key:IN" => $opt_filters
                                ));
                                $query->select(array(
                                    "msProductOption.*"
                                ));
                                if($query->prepare() && $query->stmt->execute()){
                                    $options = $query->stmt->fetchAll(PDO::FETCH_ASSOC);
                                    foreach($options as $option){
                                        $product[$option["key"]] = $option["value"];
                                    }
                                }
                            }
                            // активные магазины
                            $stores = $this->sl->store->getActiveStores();
                            foreach($stores as $store){
                                $remain = $this->sl->product->getRemainAndPriceForStore($store, $product['id']);
                                if(count($remain)){
                                    if(!$remain['remains']){
                                        $product["remains_".$store] = 0;
                                    }
                                    if(!$remain["price"]){
                                        $product["price_".$store] = 0;
                                    }
                                    $product["price_".$store] = $remain["price"];
                                    $product["remains_".$store] = $remain['remains'];
                                }
                            }
                            $recordId = $product['id'];
                            unset($product['id']);
                            $storage->addRecord($recordId, $product);
                        }
                    }
                }
                $storage->optimize();
                $indexData = $storage->export();
                $this->setCache($indexData);
                return $indexData;
            }
        }
    }

    public function getData($category = null, $config = array()){
        $output = array();
        $numbers = array();
        $this->category = $category;
        $location = $this->sl->getLocationData('web');
        $store_id = $location["pls"]["store_id"];
        $start = microtime(true);
        if($this->category) {
            $category = $this->modx->getObject("modResource", $this->category);
            if ($category) {
                $indexData = $this->getCache();
                if (!$indexData) {
                    $indexData = $this->createIndex($this->category);
                }
                $search = (new Factory)->create(Factory::ARRAY_STORAGE);
                $search->setData($indexData);
                $filters = json_decode($category->getTVValue("filters"), 1);
                foreach ($filters as $filter) {
                    $tmp = $filter;
                    $tmp["values"] = array();
                    if ($filter["filter_field"] == 'price') {
                        $tmp["values"] = $this->getRangePrices($this->category);
                    } else {
                        foreach ($indexData as $key => $val) {
                            if ($key == $tmp["filter_field"]) {
                                if ($tmp["filter_type"] == "number") {
                                    $min = 99999999999;
                                    $max = 0;
                                    foreach ($val as $k => $v) {
                                        if ($k < $min) {
                                            $min = $k;
                                        }
                                        if ($k > $max) {
                                            $max = $k;
                                        }
                                    }
                                    $numbers[] = $tmp["filter_field"];
                                    $tmp["values"] = array($min, $max);
                                } else {
                                    foreach ($val as $k => $v) {
                                        $tmp["values"][] = $k;
                                    }
                                }
                            }
                        }
                    }
                    $output["filters"][] = $tmp;
                    if ($tmp["main"] == "1") {
                        $output["main_filters"][] = $tmp;
                    }
                }
                if (isset($_POST["filters"]['price'])){
                    $filter_price = $_POST["filters"]['price'];
                    // unset($_POST["filters"]['price']);
                }
                $filters_values = $_POST["filters"];
                if($_POST["instock"]){
                    $filters_values["instock"] = $_POST["instock"];
                }
                $config["records"] = array();
                if($filters_values){
                    // $this->sl->tools->log(round(microtime(true) - $start, 4)."c Начало выборки c фильтрами", "filter");
                    $ff = $this->prepareFilters($filters, $filters_values);
                    if($_POST["sortby"] == "sl_price"){
                        $ff[] = new ValueFilter("available", 1);
                    }
                    $query = (new AggregationQuery())->filters($ff)->countItems();
                    $output["aggregate"] =  $search->aggregate($query);
                    $query = (new SearchQuery())->filters($ff);
                    if($_POST["sortby"] && $_POST["sortdir"]){
                        if($_POST["sortdir"] === "desc") {
                            $dir = Order::SORT_DESC;
                        } else {
                            $dir = Order::SORT_ASC;
                        }
                        if($_POST["sortby"] == "sl_price"){
                            $query->order('price_'.$store_id, $dir);
                        }else{
                            $query->order($_POST["sortby"], $dir);
                        }
                    }else{
                        // $query->order('available', Order::SORT_ASC, SORT_NUMERIC);
                        $query->order('price_'.$store_id, Order::SORT_ASC, SORT_NUMERIC);
                    }
                    $records = $search->query($query);
                    $config["total"] = count($records);
                    $config["recordеуs"] = $records;
                    if($records){
                        $config["resources"] = implode(",", $records);
                    }
                }else{
                    $ff = array();
                    if($_POST["sortby"] == "sl_price"){
                        $ff[] = new ValueFilter("available", 1);
                    }
                    if(count($ff)){
                        $query = (new AggregationQuery())->filters($ff)->countItems();
                    }else{
                        $query = (new AggregationQuery())->countItems();
                    }
                    $output["aggregate"] = $search->aggregate($query);
                    $query = (new SearchQuery());
                    if($_POST["sortby"] && $_POST["sortdir"]){
                        if($_POST["sortdir"] === "desc") {
                            $dir = Order::SORT_DESC;
                        } else {
                            $dir = Order::SORT_ASC;
                        }
                        if($_POST["sortby"] == "sl_price"){
                            $query->order('price_'.$store_id, $dir);
                        }else{
                            $query->order($_POST["sortby"], $dir);
                        }
                    }else{
                        // $query->order('available', Order::SORT_ASC, SORT_NUMERIC);
                        $query->order('price_'.$store_id, Order::SORT_ASC, SORT_NUMERIC);
                    }
                    $records = $search->query($query);
                    $config["total"] = count($records);
                }
                foreach($output["aggregate"] as $k => $v){
                    if($k == "price"){
                        $tmp = $this->getRangePrices($this->category, $config["records"]);
                        $output["aggregate"][$k] = array(
                            "min" => $tmp[0],
                            "max" => $tmp[1]
                        );
                    }else{
                        if(in_array($k, $numbers)){
                            $keys = array_keys($v);
                            $output["aggregate"][$k] = array(
                                "min" => array_shift($keys),
                                "max" => array_pop($keys)
                            );
                        }
                    }
                }
                if($_GET["page"]){
                    $_POST["filter_page"] = $_GET["page"];
                }
                if($_POST["filter_page"] > 1){
                    $config["page"] = $_POST["filter_page"];
                    $config["offset"] = ($_POST["filter_page"] - 1) * $config['limit'];
                }else{
                    $config["page"] = 1;
                    $config["offset"] = 0;
                }
                $config["pages"] = ceil($config["total"] / $config['limit']);
                $output["config"] = array(
                    "page" => $config["page"],
                    "total" => $config["total"],
                    "pages" => $config["pages"],
                    "limit" => $config["limit"],
                    "category" => $this->category,
                    "first" => 1,
                    "last" => $config["pages"],
                    "records" => $records
                );
                $output["pagination"] = $this->preparePagination($output["config"]);
                if(count($records)){
                    // проблема с сортировкой результатов. Используем методы фильтрации
                    $pdo = $this->modx->getParser()->pdoTools;
                    $product = $this->modx->newObject('msProductData');
                    if($config["offset"] == 0){
                        // $config["limit"] = $config["limit"] + 1;
                    }
                    for($i = $config["offset"]; $i < ($config["offset"] + $config["limit"]); $i++){
                        if(isset($output["config"]['records'][$i])) {
                            $query = $this->modx->newQuery("modResource");
                            $query->leftJoin("msProductData", "msProductData", "msProductData.id = modResource.id");
                            $query->where(array(
                                "modResource.id:=" => $output["config"]['records'][$i],
                                // "msProductData.available:=" => 1
                            ));
                            $query->select(array("modResource.*", "msProductData.*"));
                            $query->prepare();
                            // $this->modx->log(1, print_r($query->toSQL(), 1));
                            if ($query->prepare() && $query->stmt->execute()) {
                                $prod = $query->stmt->fetch(PDO::FETCH_ASSOC);
                                if ($prod) {
                                    $arr['price'] = $product->getPrice($prod);
                                    $arr['weight'] = $product->getWeight($prod);
                                    $arr = $product->modifyFields($prod);
                                    $arr['index'] = $i;
                                    $arr['category'] = $category->get("id");
                                    $arr['total'] = $config["total"];

                                    $products .= $pdo->getChunk($config["tpl"], $arr);
                                }
                            }
                        }
                    }
                    $output["config"]["total"] = $config["total"];
                    $output["config"]["pages"] = ceil($output["config"]["total"] / $output["config"]['limit']);
                    $this->modx->setPlaceholder("total", $config["total"]);
                    $output["pagination"] = $this->preparePagination($output["config"]);
                    $output["products"] = $products;
                }else{
                    $output["products"] = '<div class="dart-alert dart-alert-info" style="width: 100%;">Нет карточек, подходящих под условия фильтра.</div>';
                }
            }
        }
        return $output;
    }

    /**
     * preparePagination
     *
     * @param $config
     * @return string
     */
    public function preparePagination($config){
        $output = "";
        $pls = array(
            "page" => $config["page"],
            "total" => $config["total"],
            "pages" => $config["pages"],
            "limit" => $config["limit"],
            "category" => $this->category,
            "first" => 1,
            "last" => $config["pages"]
        );
        // $this->modx->log(1, print_r($pls, 1));
        if($pls["page"] - 1 != 0){
            $pls["prev"] = $pls["page"] - 1;
        }else{
            $pls["prev"] = 0;
        }
        if($pls["page"] + 1 <= $pls["pages"]){
            $pls["next"] = $pls["page"] + 1;
        }else{
            $pls["next"] = 0;
        }
        // $this->modx->log(1, print_r($pls, 1));
        $chunk = "sl.pagination";
        $pdo = $this->modx->getService('pdoFetch');
        if($pdo){
            $output = $pdo->getChunk($chunk, $pls);
            // $this->modx->log(1, print_r($output, 1));
        }
        return $output;
    }

    /**
     * Подготавливаем фильтры для работы
     *
     * @param $filters
     * @param $filters_values
     * @return array
     */
    public function prepareFilters($filters = array(), $filters_values = array()){
        $ffs = array();
        $location = $this->sl->getLocationData('web');
        $store_id = $location["pls"]["store_id"];
        $price = false;
        if(count($filters)){
            foreach($filters as $key => $filter){
                if($filter["filter_type"] == "default" || $filter["filter_type"] == "vendors"){
                    if($filters_values[$filter['filter_field']]){
                        $ffs[] = new ValueFilter($filter['filter_field'], $filters_values[$filter['filter_field']]);
                    }
                }
                if($filter["filter_type"] == "number"){
                    if($filters_values[$filter['filter_field']]){
                        if($filters_values[$filter['filter_field']]["min"] != $filters_values[$filter['filter_field']]["default_min"]
                            || $filters_values[$filter['filter_field']]["max"] != $filters_values[$filter['filter_field']]["default_max"]
                        ){
                            $filter_name = $filter['filter_field'];
                            if($filter['filter_field'] == "price"){
                                $filter['filter_field'] = $filter['filter_field'].'_'.$store_id;
                                $filter_name = "price";
                                $price = true;
                            }
                            $ffs[] = new RangeFilter($filter['filter_field'], ["min" => $filters_values[$filter_name]['min'], "max" => $filters_values[$filter_name]['max']]);
                        }
                    }
                }
            }
        }
        if($filters_values["instock"]){
            $ffs[] = new RangeFilter('remains_'.$store_id, ['min'=>1, 'max'=>99999999]);
            // $ffs[] = new ExcludeValueFilter('remains_'.$store_id, 0);
        }
        // $this->modx->log(1, print_r($ffs, 1));
        return $ffs;
    }

    /**
     *  Устанавливаем кеш
     *
     * @param $data
     * @return void
     */
    public function setCache($data){
        $cache = $this->modx->getCacheManager();
        if($this->category){
            $parent = 'cat_'.$this->category;
        }else{
            $parent = 'all';
        }
        $options = array( xPDO::OPT_CACHE_KEY=> 'default/dart_filters/');
        $cache->set($parent, $data, $this->cache_time, $options);
    }

    /**
     * Берем кеш
     *
     * @return mixed
     */
    public function getCache (){
        $cache = $this->modx->getCacheManager();
        if($this->category){
            $parent = 'cat_'.$this->category;
        }else{
            $parent = 'all';
        }
        $options = array( xPDO::OPT_CACHE_KEY=> 'default/dart_filters/');
        $data = $cache->get($parent, $options);
        return $data;
    }
}