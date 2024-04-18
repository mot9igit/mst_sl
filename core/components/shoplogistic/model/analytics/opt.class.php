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

    public function getVendors($properties){
        $query = $this->modx->newQuery("slStores");
        $query->select(array(
            "`slStores`.*"
        ));
        $query->where(array(
            "`slStores`.`warehouse`:=" => true,
            "`slStores`.`active`:=" => true
        ));
        if ($query->prepare() && $query->stmt->execute()) {
            $vendors = $query->stmt->fetchAll(PDO::FETCH_ASSOC);
            $count = 0;
            $urlMain = $this->modx->getOption("site_url");

            foreach ($vendors as $key => $value) {
                $vendors[$key]['image'] = $urlMain . "assets/content/" . $value['image'];
                $count++;
            }

            $data['count'] = $count;
            $data['items'] = $vendors;

            return $data;
        }
    }

    public function getProducts($properties) {

        $query = $this->modx->newQuery("msProductData");
        //$query->leftJoin("msProductData", "msProductData", "msProductData.id = slStoresRemains.product_id");
        $query->leftJoin("modResource", "modResource", "modResource.id = msProductData.id");
        $query->select(array(
            "`msProductData`.*",
            "`modResource`.*"
        ));

        $query->where(array(
            "`msProductData`.`available`:=" => 1,
            "`modResource`.`parent`:=" => $properties['category_id'],
        ));

        // Подсчитываем общее число записей
        $data['total'] = $this->modx->getCount('modResource', $query);

        // Устанавливаем лимит 1/10 от общего количества записей
        // со сдвигом 1/20 (offset)
        if($properties['page'] && $properties['perpage']){
            $limit = $properties['perpage'];
            $offset = ($properties['page'] - 1) * $properties['perpage'];
            $query->limit($limit, $offset);
        }

        //$query->prepare();
        //$this->modx->log(1, $query->toSQL());
        $urlMain = $this->modx->getOption("site_url");

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
                "`slStoresRemains`.`guid`: !=" => ""
            ));

            if ($q->prepare() && $q->stmt->execute()) {
                $data['items'][$key]['stores'] = $q->stmt->fetchAll(PDO::FETCH_ASSOC);


                // Подсчитываем общее число записей
                $data['items'][$key]['total_stores'] = count($data['items'][$key]['stores']);
            }
        }

        $object = $this->modx->getObject("modResource", $properties['category_id']);

        if($object) {
            $data['page'] = $object->toArray();
        }

        return $data;

    }
}