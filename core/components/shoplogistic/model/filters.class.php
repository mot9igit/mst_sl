<?php

require_once dirname(__FILE__) . '/../libs/vendor/autoload.php';

use KSamuel\FacetedSearch\Index\Factory;
use KSamuel\FacetedSearch\Search;
use KSamuel\FacetedSearch\Filter\ValueFilter;
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
                $ms_fields = array("msProductData.id", "msProductData.vendor");
                $opt_filters = array();
                $resource_filters = array("modResource.parent");
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
        if($this->category) {
            $category = $this->modx->getObject("modResource", $this->category);
            if ($category) {
                $indexData = $this->getCache();
                if (!$indexData) {
                    $indexData = $this->createIndex($this->category);
                }
                // $this->modx->log(1, print_r($indexData, 1));
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
                    unset($_POST["filters"]['price']);
                }
                $filters_values = $_POST["filters"];
                $config["records"] = array();
                if($filters_values){
                    $ff = $this->prepareFilters($filters, $filters_values);
                    $query = (new AggregationQuery())->filters($ff)->countItems();
                    $output["aggregate"] =  $search->aggregate($query);
                    $query = (new SearchQuery())->filters($ff);
                    $records = $search->query($query);
                    $config["total"] = count($records);
                    $config["records"] = $records;
                    if($records){
                        $config["resources"] = implode(",", $records);
                    }
                }else{
                    $query = (new AggregationQuery())->countItems();
                    $output["aggregate"] = $search->aggregate($query);
                    $query = (new SearchQuery());
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
                    "records" => $config["records"]
                );
                $output["pagination"] = $this->preparePagination($config);
                // $this->modx->log(1, print_r($config, 1));
                $config['sortby'] = "Data.available";
                $config['sortdir'] = "ASC";
                if($filter_price){
                    if($filter_price["min"] != $filter_price["default_min"] && $filter_price["max"] != $filter_price["default_max"]){
                        $location = $this->sl->getLocationData('web');
                        $store_id = $location["pls"]["store_id"];
                        $config['loadModels'] = 'shoplogistic';
                        $config['leftJoin'] = array(
                            "slStoresRemainsStore" => [
                                "class" => "slStoresRemains",
                                "on" => "msProduct.id = slStoresRemains.product_id AND slStoresRemains.store_id = {$store_id}"
                            ],
                            "slStoresRemainsMIN" => [
                                "class" => "slStoresRemains",
                                "on" => "msProduct.id = slStoresRemains.product_id AND slStoresRemains.store_id != {$store_id} AND slStoresRemains.price = (
                                select min(price)
                                from slStoresRemains
                                where msProduct.id = slStoresRemains.product_id AND slStoresRemains.remains > 0 AND slStoresRemains.price > 0
                            )"
                            ]
                        );
                        $config['select'] = array(
                            "msProduct" => "*",
                            "slStoresRemainsStore" => "slStoresRemainsStore.remains as sl_remains, coalesce(slStoresRemainsStore.price, slStoresRemainsMIN.price, 0) AS sl_price"
                        );
                        $config['where'] = array(
                            "sl_price:>=" => $filter_price["min"],
                            "AND:sl_price:<=" => $filter_price["max"]
                        );
                    }
                }
                $output["products"] = $this->modx->runSnippet("msProducts", $config);
            }
        }
        // $this->modx->log(1, print_r($output, 1));
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
        if(count($filters)){
            foreach($filters as $filter){
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
                            $ffs[] = new RangeFilter($filter['filter_field'], ["min" => $filters_values[$filter['filter_field']]['min'], "max" => $filters_values[$filter['filter_field']]['max']]);
                        }
                    }
                }
            }
        }
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