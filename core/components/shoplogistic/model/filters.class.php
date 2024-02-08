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
                $ms_fields = array("msProductData.id");
                $opt_filters = array();
                $resource_filters = array();
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
                if(!$indexData){
                    $this->createIndex($this->category);
                    $indexData = $this->getCache();
                }
                // $this->modx->log(1, print_r($indexData, 1));
                $search = (new Factory)->create(Factory::ARRAY_STORAGE);
                $search->setData($indexData);
                $filters = json_decode($category->getTVValue("filters"), 1);
                foreach($filters as $filter){
                    $tmp = $filter;
                    $tmp["values"] = array();
                    foreach($indexData as $key => $val){
                        if($key == $tmp["filter_field"]){
                            if($tmp["filter_type"] == "number"){
                                $min = 99999999999;
                                $max = 0;
                                foreach($val as $k => $v){
                                    if($k < $min){
                                        $min = $k;
                                    }
                                    if($k > $max){
                                        $max = $k;
                                    }
                                }
                                $numbers[] = $tmp["filter_field"];
                                $tmp["values"] = array($min, $max);
                            }else{
                                foreach($val as $k => $v){
                                    $tmp["values"][] = $k;
                                }
                            }
                        }
                    }
                    $output["filters"][] = $tmp;
                    if($tmp["main"] == "1"){
                        $output["main_filters"][] = $tmp;
                    }
                }
                $filters_values = $_POST["filters"];
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
                    if(in_array($k, $numbers)){
                        $keys = array_keys($v);
                        $output["aggregate"][$k] = array(
                            "max" => array_shift($keys),
                            "min" => array_pop($keys)
                        );
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
                $this->modx->log(1, print_r($config, 1));
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
        $this->modx->log(1, print_r($pls, 1));
        $chunk = "sl.pagination";
        $pdo = $this->modx->getService('pdoFetch');
        if($pdo){
            $output = $pdo->getChunk($chunk, $pls);
            $this->modx->log(1, print_r($output, 1));
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