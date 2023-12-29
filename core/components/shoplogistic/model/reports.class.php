<?php
class reportsHandler
{
    public $modx;
    public $sl;
    public $config;

    public function __construct(shopLogistic &$sl, modX &$modx)
    {
        $this->sl =& $sl;
        $this->modx =& $modx;
        $assetsPath = $modx->getOption('shoplogistic_core_path', array(), $modx->getOption('assets_path') . 'components/shoplogistic/');
        $this->config = array(
            "tmp_path" => $assetsPath.'tmp/'
        );
        $this->modx->lexicon->load('shoplogistic:default');
    }

    public function checkFileBlock($id){
        $reports_tmp = $this->config['tmp_path'].'reports/'.$id.'/lock.file';
        if(!file_exists($reports_tmp)){
            return false;
        }else{
            // проверяем не прошло ли время блокировки
            $json = file_get_contents($reports_tmp);
            $array = json_decode($json, true);
            if($array['deadline'] < time()){
                $this->deleteFileBlock($id);
                return false;
            }
        }
        return true;
    }

    public function deleteFileBlock($id){
        $reports_tmp = $this->config['tmp_path'].'reports/'.$id.'/lock.file';
        unlink($reports_tmp);
    }

    public function createFileBlock($id){
        $reports_tmp = $this->config['tmp_path'].'reports/'.$id.'/';
        if(!file_exists($reports_tmp)){
            mkdir($reports_tmp, 0755, true);
        }
        $data = array(
            "deadline" => time() + 7200 // по умолчанию ставим блокировку на 2 часа
        );
        $json = json_encode($data, JSON_UNESCAPED_UNICODE);
        file_put_contents($reports_tmp . 'lock.file', $json);
    }

    /**
     *
     * Обработка отчетов по крону
     * TODO: проверить нагрузку и оптимизировать
     * TODO: переделать на очередь
     *
     * @return void
     */
    public function handleReports(){
        // берем отчеты со статусом 1
        $reports = $this->modx->getCollection('slReports', array('status' => 1));
        foreach($reports as $report){
            // так как отчет собирается относительно медленно, нужно предусмотреть блокировку дублей
            $id = $report->get('id');
            if(!$this->checkFileBlock($id)){
                $this->createFileBlock($id);
            }
            // теперь запускаем формирование отчетов
            if($report->get("type") == 1){
                $report->set("status", 2);
                $report->save();
                if($this->generateTopSales($id)){
                    $report->set("status", 3);
                    $report->save();
                    $this->deleteFileBlock($id);
                }
            }
            if($report->get("type") == 2){
                $report->set("status", 2);
                $report->save();
                if($this->generatePresent($id)) {
                    $report->set("status", 3);
                    $report->save();
                    $this->deleteFileBlock($id);
                }
            }
            if($report->get("type") == 3){
                $report->set("status", 2);
                $report->save();
                if($this->generateRRC($id)){
                    $report->set("status", 3);
                    $report->save();
                    $this->deleteFileBlock($id);
                }
            }
            if($report->get("type") == 4){
                $report->set("status", 2);
                $report->save();
                if($this->generateWeekSales($id)){
                    $report->set("status", 3);
                    $report->save();
                    $this->deleteFileBlock($id);
                }
            }
        }
    }

    /**
     * Установка продаж номенклатуры по дням
     *
     * @return array
     */
    public function checkSalesDates(){
        $output = array(
            "created" => 0,
            "updated" => 0
        );
        $stores = $this->getSales('slStores');
        // $warehouses = $this->getSales('slWarehouse');
        // $result = array_merge($stores, $warehouses);
        foreach($stores as $item){
            $criteria = array(
                "date:=" => $item['date']
            );
            if(isset($item['remain_store_id'])){
                $data = $this->getAdditionData($item['remain_store_id'], 'slStores');
                $criteria["remain_store_id:="] = $item['remain_store_id'];
                $data["remain_store_id"] = $item['remain_store_id'];
                $data["remain_warehouse_id"] = 0;
                $data['store_id'] = $data['obj_id'];
                $data['warehouse_id'] = 0;
                unset($data['obj_id']);
            }
            $data["sales"] = $item['sales'];
            $data["price"] = $item['price'];
            $data["date"] = $item['date'];
            $da = $this->modx->getObject("slSalesDays", $criteria);
            if(!$da){
                $da = $this->modx->newObject("slSalesDays");
                $output['created']++;
            }else{
                $output['updated']++;
            }
            $da->fromArray($data);
            $da->save();
        }
        return $output;
    }

    public function getAdditionData($remain_id, $type = 'slStores'){
        if($type == 'slStores'){
            $obj = "slStoresRemains";
            $coloumn = 'store_id';
        }
        if($type == 'slWarehouse'){
            $obj = "slWarehouseRemains";
            $coloumn = 'warehouse_id';
        }
        $q = $this->modx->newQuery($obj);
        $q->leftJoin($type, $type, $obj.'.'.$coloumn.' = '.$type.'.id');
        $q->leftJoin('dartLocationCity', 'dartLocationCity', $type.'.city = dartLocationCity.id');
        $q->leftJoin('dartLocationRegion', 'dartLocationRegion', 'dartLocationCity.region = dartLocationRegion.id');
        $q->select(array("{$obj}.{$coloumn} as obj_id,dartLocationCity.id as city_id,dartLocationRegion.id as region_id,{$obj}.product_id as product_id"));
        $q->where(array("`{$obj}`.`id`:=" => $remain_id));
        //$q->prepare();
        //echo $q->toSQL();
        if ($q->prepare() && $q->stmt->execute()) {
            //echo $q->toSQL();
            $remains = $q->stmt->fetch(PDO::FETCH_ASSOC);
            return $remains;
        }
    }

    /**
     * Берем продажи номенклатуры по типу
     *
     * @param $type
     * @return array
     * @throws Exception
     */

    public function getSales($type = 'slStores'){
        $dates = array();
        if($type == 'slStores'){
            $obj = "slStoreDocsProducts";
            $doc_obj = 'slStoreDocs';
            $coloumn = 'store_id';
            $prefix = 's_';
        }
        if($type == 'slWarehouse'){
            $obj = "slWarehouseDocsProducts";
            $doc_obj = 'slWarehouseDocs';
            $coloumn = 'warehouse_id';
            $prefix = 'w_';
        }
        $q = $this->modx->newQuery($obj);
        $q->leftJoin($doc_obj, 'Docs', $obj.'.doc_id = Docs.id');
        $q->select(array("{$obj}.*,Docs.date as date,Docs.{$coloumn} as {$coloumn}"));
        $q->where(array("`{$obj}`.`type`:=" => 1,"`{$obj}`.`remain_id`:>" => 0));
        if ($q->prepare() && $q->stmt->execute()) {
            $this->modx->log(1, $q->toSQL());
            $remains = $q->stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach($remains as $remain){
                $date_from = new DateTime($remain['date']);
                $date_from->setTime(0,0);
                $key = $date_from->getTimestamp();

                $date_to = new DateTime($remain['date']);
                $date_to->setTime(23,59);

                $from = $date_from->format('Y-m-d H:i:s');
                $to = $date_to->format('Y-m-d H:i:s');

                $qq = $this->modx->newQuery($obj);
                $qq->leftJoin($doc_obj, 'Docs', $obj.'.doc_id = Docs.id');
                $qq->select(array("SUM({$obj}.count) AS sales, SUM({$obj}.price) AS price"));
                $qq->where(array(
                    "`{$obj}`.`type`:=" => 1,
                    "`{$obj}`.`remain_id`:=" => $remain['remain_id'],
                    "`{$obj}`.`remain_id`:>" => 0,
                    "`Docs`.`date`:>=" => $from,
                    "`Docs`.`date`:<=" => $to,
                ));
                if ($qq->prepare() && $qq->stmt->execute()) {
                    $data = $qq->stmt->fetch(PDO::FETCH_ASSOC);

                    $dates[$key.'_'.$prefix.$remain['remain_id']] = array(
                        "remain_".$coloumn => $remain['remain_id'],
                        "sales" => $data["sales"],
                        "price" => $data["price"],
                        "date" => $from
                    );

                }
            }
        }
        return $dates;
    }

    public function getPreQueryTS($properties, $type){
        $prefix = $this->modx->getOption('table_prefix');
        $object = false;
        if($type == "slStores"){
            $object = 'slStoresRemains';
        }
        if($type == "slWarehouse"){
            $object = 'slWarehouseRemains';
        }
        if($object){
            $where = '';
            $q = $this->modx->newQuery("slSalesDays");
            // $q->leftJoin($object, $object,  $object.'.id = slSalesDays.'.$key_field);
            if(isset($properties['filtersdata']['range'])){
                if($properties['filtersdata']['range'][0] && $properties['filtersdata']['range'][1]){
                    $from = date('Y-m-d H:i:s', strtotime($properties['filtersdata']['range'][0]));
                    $to = date('Y-m-d H:i:s', strtotime($properties['filtersdata']['range'][1]));
                    $q->where(array("`slSalesDays`.`date`:>" => $from, "`slSalesDays`.`date`:<" => $to));
                }
                if($properties['filtersdata']['range'][0] && !$properties['filtersdata']['range'][1]){
                    $from = date('Y-m-d H:i:s', strtotime($properties['filtersdata']['range'][0]));
                    $q->where(array("`slSalesDays`.`date`:>" => $from));
                }
            }
            if(isset($properties['filtersdata']['stores']) && $type == "slStores"){
                $q->where(array(
                    "slSalesDays.store_id:=" => $properties['filtersdata']['stores']
                ));
            }
            if(isset($properties['filtersdata']['warehouses']) && $type == "slWarehouse"){
                $q->where(array(
                    "slSalesDays.warehouse_id:=" => $properties['filtersdata']['warehouses']
                ));
            }
            $q->select(array(
                "SUM(COALESCE(slSalesDays.sales, 0)) as sales"
            ));
            $q->where(array("id:>" => 0));
            if(isset($properties['filtersdata']['region'])){
                $cities = array();
                $regions = array();
                foreach($properties['filtersdata']['region'] as $key => $val){
                    if($val['checked']){
                        $k_r = explode("_", $key);
                        if($k_r[0] == 'region'){
                            $regions[] = $k_r[1];
                        }
                        if($k_r[0] == 'city') {
                            $cities[] = $k_r[1];
                        }
                    }
                }
                if(count($regions)){
                    $q->where(array(
                        "`slSalesDays`.`region_id`:IN" => $regions
                    ));
                }
                if(count($cities)){
                    $q->where(array(
                        "`slSalesDays`.`city_id`:IN" => $cities
                    ));
                }
            }
            //$q->groupby($ob.'.id');
            $q->prepare();
            return $q->toSQL();
        }
    }

    public function getCities($region){
        $cities = array();
        $q = $this->modx->newQuery('dartLocationCity');
        $q->where(array(
           "region:=" => $region
        ));
        $q->select(array(
            "dartLocationCity.id"
        ));
        if ($q->prepare() && $q->stmt->execute()) {
            $cities = $q->stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        return $cities;
    }

    // Берем товары с упущенной выручкой
    public function getLostRevenue ($properties) {

    }

    public function getAvailable($properties = array()){
        $output = array();
        if(isset($properties['filtersdata']['region'])) {
            $cities = array();
            $regions = array();
            foreach ($properties['filtersdata']['region'] as $key => $val) {
                if ($val['checked']) {
                    $k_r = explode("_", $key);
                    if ($k_r[0] == 'region') {
                        $regions[] = $k_r[1];
                    }
                    if ($k_r[0] == 'city') {
                        $cities[] = $k_r[1];
                    }
                }
            }
        }
        if($properties['region_id']){
            $object = 'slStores';
            $name = "`slStores`.`name` as name";
        }else{
            if(count($regions) > 1){
                $object = 'dartLocationRegion';
                $name = "`dartLocationRegion`.`name` as name";
            }else{
                if(count($cities) || $properties['filtersdata']['stores'] || $properties['filtersdata']['warehouses']){
                    $object = 'dartLocationCity';
                    $name = "`dartLocationCity`.`city` as name";
                }else{
                    $object = 'dartLocationRegion';
                    $name = "`dartLocationRegion`.`name` as name";
                }
            }
        }
        $q = $this->modx->newQuery('slStoresRemains');
        $q->leftJoin('msProductData', 'msProductData', 'msProductData.id = slStoresRemains.product_id');
        $q->leftJoin('msVendor', 'Vendor', 'Vendor.id = msProductData.vendor');
        $q->leftJoin('modResource', 'modResource', 'modResource.id = slStoresRemains.product_id');
        $q->leftJoin('slStores', 'slStores', 'slStores.id = slStoresRemains.store_id');
        $q->leftJoin('dartLocationCity', 'dartLocationCity', 'dartLocationCity.id = slStores.city');
        $q->leftJoin('dartLocationRegion', 'dartLocationRegion', 'dartLocationRegion.id = dartLocationCity.region');
        $q->where(array(
            "slStoresRemains.available:>" => 0
        ));
        if($properties['filtersdata']['store_type']){
            $q->where(array(
                "slStores.type:=" => $properties['filtersdata']['store_type']
            ));
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
                "msProductData.vendor:=" => $properties['filtersdata']['vendor']
            ));
        }
        if(isset($properties['filtersdata']['stores'])){
            $criteria = array(
                "slStores.id:=" => $properties['filtersdata']['stores']
            );
            if(isset($properties['filtersdata']['warehouses'])) {
                $criteria["OR:slStores.id:="] = $properties['filtersdata']['warehouses'];
            }
            $q->where($criteria);
        }
        if(isset($properties['filtersdata']['warehouses']) && !isset($properties['filtersdata']['stores'])){
            $criteria = array(
                "slStores.id:=" => $properties['filtersdata']['warehouses']
            );
            $q->where($criteria);
        }
        // регионы
        if(count($regions)){
            $q->where(array(
                "`dartLocationCity`.`region`:IN" => $regions
            ));
        }
        if(count($cities)){
            $q->where(array(
                "`dartLocationCity`.`id`:IN" => $cities
            ));
        }
        $q->where(array(
            "`slStores`.`active`:=" => 1
        ));
        if($properties['region_id']){
            $q->where(array(
                "`dartLocationCity`.`region`:=" => $properties['region_id']
            ));
        }
        // $stores = $this->getPreQueryTS($properties, 'slStores');
        // $where по фильтрам
        $q->select(
            array("{$object}.*,{$name}, dartLocationRegion.id as id,COUNT(DISTINCT store_id) as available_dots,COALESCE(SUM(slStoresRemains.available),0) as count, COALESCE(SUM(slStoresRemains.available * slStoresRemains.price)) AS price")
        );
        $q->groupby("{$object}.id");
        if(isset($properties['sort'])){
            // $this->modx->log(1, print_r($properties, 1));
            $keys = array_keys($properties['sort']);
            // нужно проверить какому объекту принадлежит поле
            $q->sortby($keys[0], $properties['sort'][$keys[0]]['dir']);
        }
        if($properties['page'] && $properties['perpage']){
            $limit = $properties['perpage'];
            $offset = ($properties['page'] - 1) * $properties['perpage'];
            $q->limit($limit, $offset);
        }

        $q->prepare();
        $this->modx->log(1, $q->toSQL());
        if ($q->prepare() && $q->stmt->execute()) {
            $this->modx->log(1, $q->toSQL());
            $output['regions_data'] = $q->stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach($output['regions_data'] as $key => $region){
                $output['regions_data'][$key]['price'] = number_format($region['price'], 2, '.', ' ');
                $output['regions_data'][$key]['count'] = number_format($region['count'], 0, '.', ' ');
                $output['regions_data'][$key]['population'] = number_format($region['population'], 0, '.', ' ');
            }
        }
        if($properties['region_id']){
            $obj = $this->modx->getObject("dartLocationRegion", $properties['region_id']);
            if($obj){
                $output['region'] = $obj->get("name");
            }
        }
        $output['total'] = count($output['regions_data']);
        return $output;
    }

    public function setReport($properties){
        if($properties['action'] == 'set'){
            $store_id = $properties['id'];
            $start = new DateTime($properties['report']['date_from']);
            $start->setTime(00,00);
            $end = new DateTime($properties['report']['date_to']);
            $end->setTime(23,59);

            $report = $this->modx->newObject('slReports');
            if($report){
                $report->set("store_id", $store_id);
                $report->set("name", $properties['report']['name']);
                $report->set("date_from", $start->format('Y-m-d H:i:s'));
                $report->set("date_to", $end->format('Y-m-d H:i:s'));
                $report->set("type", $properties['report']['type']['code']);
                $report->set("properties", json_encode($properties['report'], JSON_UNESCAPED_UNICODE));
                $report->set("status", 1);
                $report->save();
                return $report->toArray();
            }
        }
        return false;
    }

    public function getRRCReport($properties) {
        if($properties['elem_id']){
            $results = array();
            $store = $this->modx->getObject("slStores", $properties['elem_id']);
            if($store){
                $results['store'] = $store->toArray(); 
            }
            // подробный отчет
            $q = $this->modx->newQuery("slReportsRRCProducts");
            $q->where(array("rrc_store_id:=" => $properties['elem_id']));
            $q->leftJoin("slStoresRemains", "slStoresRemains", "slStoresRemains.id = slReportsRRCProducts.remain_id");
            $q->leftJoin("msProductData", "msProductData", "msProductData.id = slStoresRemains.product_id");
            $q->leftJoin("modResource", "modResource", "modResource.id = slStoresRemains.product_id");
            $q->select(array("slReportsRRCProducts.*, slStoresRemains.price as price_now,msProductData.price_rrc as price_rrc,modResource.pagetitle as product_name,msProductData.image as product_image,msProductData.vendor_article as product_vendor_article"));
            // фильтрация
            if ($properties['filter']) {
                $words = explode(" ", $properties['filter']);
                foreach ($words as $word) {
                    $criteria = array();
                    $criteria['modResource.pagetitle:LIKE'] = '%' . trim($word) . '%';
                    $criteria['msProductData.vendor_article:LIKE'] = '%' . trim($word) . '%';
                    $q->where($criteria);
                }
            }
            // 0 и 1 по ним можно считать
            // Подсчитываем общее число записей
            $results['total'] = $this->modx->getCount("slReportsRRCProducts", $q);

            // Устанавливаем лимит 1/10 от общего количества записей
            // со сдвигом 1/20 (offset)
            if ($properties['page'] && $properties['perpage']) {
                $limit = $properties['perpage'];
                $offset = ($properties['page'] - 1) * $properties['perpage'];
                $q->limit($limit, $offset);
            }

            // И сортируем по ID в обратном порядке
            if ($properties['sort']) {
                $keys = array_keys($properties['sort']);
                $q->sortby($keys[0], $properties['sort'][$keys[0]]['dir']);
            }

            if ($q->prepare() && $q->stmt->execute()) {
                $results['items'] = $q->stmt->fetchAll(PDO::FETCH_ASSOC);
                return $results;
            }
        }else{
            $q = $this->modx->newQuery("slReportsRRCStores");
            $q->where(array("report_id:=" => $properties['report_id']));
            $q->leftJoin("slStores", "slStores", "slStores.id = slReportsRRCStores.store_id");
            $q->select(array("slReportsRRCStores.*,slStores.name as store_name,slStores.address as store_address"));
            // фильтрация
            if ($properties['filter']) {
                $words = explode(" ", $properties['filter']);
                foreach ($words as $word) {
                    $criteria = array();
                    $criteria['slStores.name:LIKE'] = '%' . trim($word) . '%';
                    $q->where($criteria);
                }
            }
            // 0 и 1 по ним можно считать
            // Подсчитываем общее число записей
            $results['total'] = $this->modx->getCount("slReportsRRCStores", $q);

            // Устанавливаем лимит 1/10 от общего количества записей
            // со сдвигом 1/20 (offset)
            if ($properties['page'] && $properties['perpage']) {
                $limit = $properties['perpage'];
                $offset = ($properties['page'] - 1) * $properties['perpage'];
                $q->limit($limit, $offset);
            }

            // И сортируем по ID в обратном порядке
            if ($properties['sort']) {
                $keys = array_keys($properties['sort']);
                $q->sortby($keys[0], $properties['sort'][$keys[0]]['dir']);
            }

            if ($q->prepare() && $q->stmt->execute()) {
                $results['items'] = $q->stmt->fetchAll(PDO::FETCH_ASSOC);
                return $results;
            }
        }
    }

    public function getReports($store_id, $properties = array()){
        $results = array();
        if($properties['report_id']){
            // берем отчет
            $q = $this->modx->newQuery("slReports");
            $q->where(array("id:=" => $properties['report_id']));
            $q->select(array("slReports.*"));
            if ($q->prepare() && $q->stmt->execute()) {
                $report = $q->stmt->fetch(PDO::FETCH_ASSOC);
                $report['properties'] = json_decode($report['properties'], 1);
                $this->modx->log(1, print_r($report, 1));
                if($report['properties']['matrix']){
                    $query = $this->modx->newQuery("slStoresMatrix");
                    $query->where(array("slStoresMatrix.id" => $report['properties']['matrix']));
                    $query->select(array("slStoresMatrix.*"));
                    if ($query->prepare() && $query->stmt->execute()) {
                        $it = $query->stmt->fetch(PDO::FETCH_ASSOC);
                        $report["matrix"] = $it;
                    }
                }
                if($properties['report_data']){
                    // если тип отчета Первичная представленность
                    if($report['type'] == 2) {
                        return $this->getPresent($properties);
                    }
                    // если тип отчета РРЦ
                    if($report['type'] == 3) {
                        return $this->getRRCReport($properties);
                    }
                    // если тип отчета Недельный
                    if($report['type'] == 4) {
                        return $this->getWeekSales($properties);
                    }
                }else{
                    $date_from = strtotime($report['date_from']);
                    $report['date_from'] = date("d.m.Y", $date_from);
                    $date_to = strtotime($report['date_to']);
                    $report['date_to'] = date("d.m.Y", $date_to);
                    $updatedon = strtotime($report['updatedon']);
                    $report['updatedon'] = date("d.m.Y H:i", $updatedon);
                    if($report['file']){
                        $report['file'] = $this->modx->getOption("site_url").$report['file'];
                    }
                    return $report;
                }
            }
        }else{
            $q = $this->modx->newQuery("slReports");
            $q->where(array(
                "store_id:=" => $store_id
            ));
            $q->select(array(
                'slReports.*'
            ));
            if(isset($properties['filtersdata']['type'])) {
                $q->where(array(
                    "type:=" => $properties['filtersdata']['type']
                ));
            }

            if($properties['filter']){
                $words = explode(" ", $properties['filter']);
                foreach($words as $word){
                    $criteria = array();
                    $criteria['slReports.name:LIKE'] = '%'.trim($word).'%';
                    $q->where($criteria);
                }
            }

            // 0 и 1 по ним можно считать
            // Подсчитываем общее число записей
            $results['total'] = $this->modx->getCount("slReports", $q);

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
                $q->sortby($keys[0], $properties['sort'][$keys[0]]['dir']);
            }

            if ($q->prepare() && $q->stmt->execute()) {
                $results['items'] = $q->stmt->fetchAll(PDO::FETCH_ASSOC);
                foreach($results['items'] as $key => $val){
                    $date_from = strtotime($val['date_from']);
                    $results['items'][$key]['date_from'] = date("d.m.Y", $date_from);
                    $date_to = strtotime($val['date_to']);
                    $results['items'][$key]['date_to'] = date("d.m.Y", $date_to);
                    if($results['items'][$key]['createdon']){
                        $createdon = strtotime($val['createdon']);
                        $results['items'][$key]['createdon'] = date("d.m.Y H:i", $createdon);
                    }else{
                        $results['items'][$key]['createdon'] = '-';
                    }
                    if($results['items'][$key]['updatedon']){
                        $updatedon = strtotime($val['updatedon']);
                        $results['items'][$key]['updatedon'] = date("d.m.Y H:i", $updatedon);
                    }else{
                        $results['items'][$key]['createdon'] = '-';
                    }
                    if($results['items'][$key]['status'] == 1){
                        $results['items'][$key]['status'] = "В очереди";
                    }
                    if($results['items'][$key]['status'] == 2){
                        $results['items'][$key]['status'] = "В процессе";
                    }
                    if($results['items'][$key]['status'] == 3){
                        $results['items'][$key]['status'] = "Готов";
                    }
                    if($results['items'][$key]['type'] == 1){
                        $results['items'][$key]['type'] = "ТОПЫ продаж";
                    }
                    if($results['items'][$key]['type'] == 2){
                        $results['items'][$key]['type'] = "План первичной представленности";
                    }
                    if($results['items'][$key]['type'] == 3){
                        $results['items'][$key]['type'] = "Соблюдение РРЦ";
                    }
                    if($results['items'][$key]['type'] == 4){
                        $results['items'][$key]['type'] = "Понедельный отчет по продажам";
                    }
                }
                return $results;
            }
        }
    }

    public function getWeekSales($properties){
        $results = array(
            "total" => 0
        );
        $stores = array();
        $products = array();
        if($properties['report_id']){
            // нужно пробежаться по всем неделям
            $q = $this->modx->newQuery("slReportsWeeks");
            // отсекаем по ID отчета
            $q->where(array(
                "slReportsWeeks.report_id:=" => $properties['report_id']
            ));
            $q->select(array(
                'slReportsWeeks.*'
            ));
            if ($q->prepare() && $q->stmt->execute()) {
                $weeks = $q->stmt->fetchAll(PDO::FETCH_ASSOC);
                $columns = array(
                    array(
                        "key" => "name",
                        "label" => "",
                        "field" => "name",
                        "width" => 350,
                        "html" => "",
                        "expander" => true,
                        "frozen" => true
                    ),
                    array(
                        "key" => "sales",
                        "label" => "Продаж",
                        "field" => "sales",
                        "width" => 150
                    ),
                    array(
                        "key" => "speed_sales",
                        "label" => "Средняя скорость продаж",
                        "field" => "speed_sales",
                        "width" => 150
                    ),
                    array(
                        "key" => "remain",
                        "label" => "Средний остаток на конец недели",
                        "field" => "remain",
                        "width" => 150
                    ),
                    array(
                        "key" => "outofstock",
                        "label" => "Дней Out Of Stock",
                        "field" => "outofstock",
                        "width" => 150
                    )
                );
                $summary = array(
                    0 => array(
                        "key" => 0,
                        "data" => array(
                            "name" => "Сумма по компаниям",
                            // "remain_avg" => 0,
                            "sales" => 0,
                            "speed_sales" => 0,
                            "remain" => 0,
                            "outofstock" => 0
                        ),
                        "children" => array()
                    ),
                    1 => array(
                        "key" => 1,
                        "data" => array(
                            "name" => "Сумма по модулям",
                            // "remain_avg" => 0,
                            "sales" => 0,
                            "speed_sales" => 0,
                            "remain" => 0,
                            "days_out_of_stock" => 0,
                            "outofstock" => 0
                        ),
                        "children" => array()
                    )                    
                );
                foreach($weeks as $key => $week){
                    $week_prefix = "week_".$key."_";
                    $columns[] = array(
                        "key" => $week_prefix."remain",
                        "label" => "Остаток на конец недели",
                        "field" => $week_prefix."remain",
                        "width" => 100
                    );
                    $columns[] = array(
                        "key" => $week_prefix."sales",
                        "label" => "Продаж",
                        "field" => $week_prefix."sales",
                        "width" => 100
                    );
                    $columns[] = array(
                        "key" => $week_prefix."speed_sales",
                        "label" => "Скорость продажи",
                        "field" => $week_prefix."speed_sales",
                        "width" => 100
                    );
                    $columns[] = array(
                        "key" => $week_prefix."outofstock",
                        "label" => "Дней Out Of Stock",
                        "field" => $week_prefix."outofstock",
                        "width" => 100
                    );
                    $summary[0]['data'][$week_prefix."remain"] = 0;
                    $summary[0]['data'][$week_prefix."sales"] = 0;
                    $summary[0]['data'][$week_prefix."speed_sales"] = 0;
                    $summary[0]['data'][$week_prefix."outofstock"] = 0;
                    $summary[1]['data'][$week_prefix."remain"] = 0;
                    $summary[1]['data'][$week_prefix."sales"] = 0;
                    $summary[1]['data'][$week_prefix."speed_sales"] = 0;
                    $summary[1]['data'][$week_prefix."outofstock"] = 0;
                    $q = $this->modx->newQuery("slReportsWeekSales");
                    $q->leftJoin('slStores', 'slStores', 'slStores.id = slReportsWeekSales.store_id');
                    $q->leftJoin('dartLocationCity', 'dartLocationCity', 'dartLocationCity.id = slStores.city');
                    $q->leftJoin('dartLocationRegion', 'dartLocationRegion', 'dartLocationRegion.id = dartLocationCity.region');
                    $q->leftJoin('msProductData', 'msProductData', 'msProductData.id = slReportsWeekSales.product_id');
                    $q->leftJoin('modResource', 'modResource', 'modResource.id = slReportsWeekSales.product_id');
                    $q->where(array(
                        "slReportsWeekSales.week_id" => $week['id']
                    ));
                    $q->select(array(
                        'slReportsWeekSales.*, modResource.pagetitle as product_name, msProductData.*, slStores.name as store_name, slStores.address, dartLocationCity.city as city_name,dartLocationRegion.name as region_name'
                    ));
                    if ($q->prepare() && $q->stmt->execute()) {
                        $weeks[$key]["data"] = $q->stmt->fetchAll(PDO::FETCH_ASSOC);
                        // посчитать данные помагазинно, пономенклатурно
                        foreach($weeks[$key]["data"] as $item){
                            $stores[] = $item['store_id'];
                            $products[] = $item['product_id'];
                        }
                        $stores = array_unique($stores);
                        $products = array_unique($products);
                        foreach($weeks[$key]["data"] as $item){
                            // считаем показатели помагазинно
                            $find = 0;
                            $avg_speed_weeks = $item["sales"] / count($weeks);
                            $avg_speed_days = $item["sales"] / 7;
                            $avg_speed_stores = $item["sales"] / count($stores);
                            $avg_speed_products = $item["sales"] / count($products);
                            foreach($summary[0]["children"] as $k => $val){
                                if($val["key"] == "0_{$item['store_id']}"){
                                    $find = 1;
                                    // summary data
                                    $summary[0]["children"][$k]["data"]["sales"] += $item["sales"];
                                    $summary[0]["children"][$k]["data"]["remain"] += round(($item["remain"] / count($stores)), 2);
                                    $summary[0]["children"][$k]["data"]["outofstock"] += $item["out_of_stock"];
                                    $summary[0]["children"][$k]["data"]["speed_sales"] += round(($avg_speed_days / count($stores)), 2);
                                    $summary[0]["children"][$k]["data"]["speed_sales"] = round($summary[0]["children"][$k]["data"]["speed_sales"], 2);
                                    $summary[0]["children"][$k]["data"]["remain"] = round($summary[0]["children"][$k]["data"]["remain"], 2);

                                    if(isset($summary[0]["children"][$k]["data"][$week_prefix."sales"])) {
                                        $summary[0]["children"][$k]["data"][$week_prefix . "sales"] += $item["sales"];
                                    }else{
                                        $summary[0]["children"][$k]["data"][$week_prefix . "sales"] = $item["sales"];
                                    }
                                    if(isset($summary[0]["children"][$k]["data"][$week_prefix."speed_sales"])) {
                                        $summary[0]["children"][$k]["data"][$week_prefix . "speed_sales"] += round($avg_speed_days, 2);
                                    }else{
                                        $summary[0]["children"][$k]["data"][$week_prefix . "speed_sales"] = round($avg_speed_days, 2);
                                    }
                                    if(isset($summary[0]["children"][$k]["data"][$week_prefix."outofstock"])) {
                                        $summary[0]["children"][$k]["data"][$week_prefix . "outofstock"] += $item["out_of_stock"];
                                    }else{
                                        $summary[0]["children"][$k]["data"][$week_prefix . "outofstock"] = $item["out_of_stock"];
                                    }
                                    if(isset($summary[0]["children"][$k]["data"][$week_prefix."remain"])) {
                                        $summary[0]["children"][$k]["data"][$week_prefix . "remain"] += $item["remain"];
                                    }else{
                                        $summary[0]["children"][$k]["data"][$week_prefix . "remain"] = $item["remain"];
                                    }
                                }
                                $summary[0]["children"][$k]["data"]["speed_sales"] = round($summary[0]["children"][$k]["data"]["speed_sales"], 2);
                                if(isset($summary[0]["children"][$k]["data"][$week_prefix . "speed_sales"])){
                                    $summary[0]["children"][$k]["data"][$week_prefix . "speed_sales"] = round($summary[0]["children"][$k]["data"][$week_prefix . "speed_sales"], 2);
                                }
                            }
                            if(!$find){
                                $store_data = array(
                                    "key" => "0_{$item['store_id']}",
                                    "data" => array(
                                        "name" => $item["store_name"].', '.$item["city_name"],
                                        "sales" => $item["sales"],
                                        "speed_sales" => round(($avg_speed_days / count($stores)), 2),
                                        "remain" => $item["remain"],
                                        "outofstock" => $item["out_of_stock"],
                                        "{$week_prefix}sales" => $item["sales"],
                                        "{$week_prefix}speed_sales" => round($avg_speed_days, 2),
                                        "{$week_prefix}outofstock" => $item["out_of_stock"],
                                        "{$week_prefix}remain" => $item["remain"]
                                    )
                                );
                                $summary[0]["children"][] = $store_data;
                            }
                            // считаем показатели помодульно
                            $find = 0;
                            foreach($summary[1]["children"] as $k => $val){
                                if($val["key"] == "1_{$item['product_id']}"){
                                    $find = 1;
                                    // summary data
                                    $summary[1]["children"][$k]["data"]["sales"] += $item["sales"];
                                    $summary[1]["children"][$k]["data"]["remain"] += $item["remain"];
                                    $summary[1]["children"][$k]["data"]["outofstock"] += $item["out_of_stock"];
                                    $summary[1]["children"][$k]["data"]["speed_sales"] += round($avg_speed_weeks, 2);
                                    if(isset($summary[1]["children"][$k]["data"][$week_prefix."speed_sales"])) {
                                        $summary[1]["children"][$k]["data"][$week_prefix."speed_sales"] += round($avg_speed_days, 2);
                                    }else {
                                        $summary[1]["children"][$k]["data"][$week_prefix."speed_sales"] = round($avg_speed_days, 2);
                                    }
                                    if(isset($summary[1]["children"][$k]["data"][$week_prefix."sales"])) {
                                        $summary[1]["children"][$k]["data"][$week_prefix."sales"] += $item["sales"];
                                    }else {
                                        $summary[1]["children"][$k]["data"][$week_prefix."sales"] = $item["sales"];
                                    }
                                    if(isset($summary[1]["children"][$k]["data"][$week_prefix."outofstock"])) {
                                        $summary[1]["children"][$k]["data"][$week_prefix."outofstock"] += $item["out_of_stock"];
                                    }else {
                                        $summary[1]["children"][$k]["data"][$week_prefix."outofstock"] = $item["out_of_stock"];
                                    }
                                    if(isset($summary[1]["children"][$k]["data"][$week_prefix."remain"])) {
                                        $summary[1]["children"][$k]["data"][$week_prefix."remain"] += $item["remain"];
                                    }else {
                                        $summary[1]["children"][$k]["data"][$week_prefix."remain"] = $item["remain"];
                                    }
                                    $summary[1]["children"][$k]["data"][$week_prefix."speed_sales"] = round($summary[1]["children"][$k]["data"][$week_prefix."speed_sales"], 2);
                                    $summary[1]["children"][$k]["data"]["speed_sales"] = round($summary[1]["children"][$k]["data"]["speed_sales"], 2);
                                    $ff = 0;
                                    foreach($summary[1]["children"][$k]["children"] as $kk => $v){
                                        if($v["key"] == "1_{$item['product_id']}_{$item['store_id']}"){
                                            $ff = 1;
                                            $summary[1]["children"][$k]["children"][$kk]["data"]["sales"] += $item["sales"];
                                            $summary[1]["children"][$k]["children"][$kk]["data"]["remain"] += $item["remain"];
                                            $summary[1]["children"][$k]["children"][$kk]["data"]["outofstock"] += $item["out_of_stock"];
                                            $summary[1]["children"][$k]["children"][$kk]["data"]["speed_sales"] += round($avg_speed_weeks, 2);
                                            if(isset($summary[1]["children"][$k]["children"][$kk]["data"][$week_prefix."sales"])) {
                                                $summary[1]["children"][$k]["children"][$kk]["data"][$week_prefix."sales"] += $item["sales"];
                                            }else {
                                                $summary[1]["children"][$k]["children"][$kk]["data"][$week_prefix . "sales"] = $item["sales"];
                                            }
                                            if(isset($summary[1]["children"][$k]["children"][$kk]["data"][$week_prefix."speed_sales"])) {
                                                $summary[1]["children"][$k]["children"][$kk]["data"][$week_prefix."speed_sales"] += round($avg_speed_days, 2);
                                            }else {
                                                $summary[1]["children"][$k]["children"][$kk]["data"][$week_prefix . "speed_sales"] = round($avg_speed_days, 2);
                                            }
                                            if(isset($summary[1]["children"][$k]["children"][$kk]["data"][$week_prefix."outofstock"])) {
                                                $summary[1]["children"][$k]["children"][$kk]["data"][$week_prefix."outofstock"] += $item["out_of_stock"];
                                            }else {
                                                $summary[1]["children"][$k]["children"][$kk]["data"][$week_prefix . "outofstock"] = $item["out_of_stock"];
                                            }
                                            if(isset($summary[1]["children"][$k]["children"][$kk]["data"][$week_prefix."remain"])) {
                                                $summary[1]["children"][$k]["children"][$kk]["data"][$week_prefix."remain"] += $item["remain"];
                                            }else {
                                                $summary[1]["children"][$k]["children"][$kk]["data"][$week_prefix . "remain"] = $item["remain"];
                                            }
                                            $summary[1]["children"][$k]["children"][$kk]["data"]["speed_sales"] = round($summary[1]["children"][$k]["children"][$kk]["data"]["speed_sales"], 2);
                                            $summary[1]["children"][$k]["children"][$kk]["data"][$week_prefix . "speed_sales"] = round($summary[1]["children"][$k]["children"][$kk]["data"][$week_prefix . "speed_sales"], 2);
                                        }
                                    }
                                    if(!$ff){
                                        $store_data = array(
                                            "key" => "1_{$item['product_id']}_{$item['store_id']}",
                                            "data" => array(
                                                "name" => $item["store_name"].', '.$item["city_name"],
                                                "sales" => $item["sales"],
                                                "speed_sales" => round($avg_speed_weeks, 2),
                                                "remain" => $item["remain"],
                                                "outofstock" => $item["out_of_stock"],
                                                "{$week_prefix}sales" => $item["sales"],
                                                $week_prefix."speed_sales" => round($avg_speed_days, 2),
                                                "{$week_prefix}outofstock" => $item["out_of_stock"],
                                                "{$week_prefix}remain" => $item["remain"],
                                                "dd" => 0
                                            )
                                        );
                                        $store_data['data'][$week_prefix."speed_sales"] = round($store_data['data'][$week_prefix."speed_sales"], 2);
                                        $store_data['data']["speed_sales"] = round($store_data['data']["speed_sales"], 2);
                                        $summary[1]["children"][$k]["children"][] = $store_data;
                                    }
                                }
                            }
                            if(!$find){
                                $store_data = array(
                                    "key" => "1_{$item['product_id']}_{$item['store_id']}",
                                    "data" => array(
                                        "name" => $item["store_name"].', '.$item["city_name"],
                                        "sales" => $item["sales"],
                                        "speed_sales" => round($avg_speed_weeks, 2),
                                        "remain" => $item["remain"],
                                        "outofstock" => $item["out_of_stock"],
                                        $week_prefix."sales" => $item["sales"],
                                        $week_prefix."speed_sales" => round($avg_speed_days, 2),
                                        $week_prefix."outofstock" => $item["out_of_stock"],
                                        $week_prefix."remain" => $item["remain"],
                                        "dd" => 1
                                    )
                                );
                                $store_data['data'][$week_prefix."speed_sales"] = round($store_data['data'][$week_prefix."speed_sales"], 2);
                                $product_data = array(
                                    "key" => "1_{$item['product_id']}",
                                    "data" => array(
                                        "name" => "{$item["product_name"]} (арт. {$item["vendor_article"]})",
                                        "sales" => $item["sales"],
                                        "speed_sales" => round($avg_speed_weeks, 2),
                                        "remain" => $item["remain"],
                                        "outofstock" => $item["out_of_stock"],
                                        $week_prefix."sales" => $item["sales"],
                                        $week_prefix."speed_sales" => round($avg_speed_days, 2),
                                        $week_prefix."outofstock" => $item["out_of_stock"],
                                        $week_prefix."remain" => $item["remain"]
                                    )
                                );
                                $product_data["children"][] = $store_data;
                                $product_data['data'][$week_prefix."speed_sales"] = round($product_data['data'][$week_prefix."speed_sales"], 2);
                                $summary[1]["children"][] = $product_data;
                            }
                            $summary[0]['data']["sales"] += $item["sales"];
                            $summary[0]['data']["speed_sales"] += round($avg_speed_weeks, 2);
                            $summary[0]['data']["remain"] += $item["remain"];
                            $summary[0]['data']["outofstock"] += $item["out_of_stock"];
                            $summary[0]['data'][$week_prefix."sales"] += $item["sales"];
                            $summary[0]['data'][$week_prefix."speed_sales"] += round($avg_speed_days, 2);
                            $summary[0]['data'][$week_prefix."speed_sales"] = round($summary[0]['data'][$week_prefix."speed_sales"], 2);
                            $summary[0]['data'][$week_prefix."outofstock"] += $item["out_of_stock"];
                            $summary[0]['data'][$week_prefix."remain"] += $item["remain"];
                            $summary[1]['data']["sales"] += $item["sales"];
                            $summary[1]['data']["speed_sales"] += round($avg_speed_weeks, 2);
                            $summary[1]['data']["remain"] += $item["remain"];
                            $summary[1]['data']["outofstock"] += $item["out_of_stock"];
                            $summary[1]['data'][$week_prefix."sales"] += $item["sales"];
                            $summary[1]['data'][$week_prefix."speed_sales"] += round($avg_speed_days, 2);
                            $summary[1]['data'][$week_prefix."speed_sales"] = round($summary[1]['data'][$week_prefix."speed_sales"], 2);
                            $summary[1]['data'][$week_prefix."outofstock"] += $item["out_of_stock"];
                            $summary[1]['data'][$week_prefix."remain"] += $item["remain"];
                        }
                    }
                }
                if(count($stores)){
                    $avg_remain_stores = $summary[0]['data']["remain"] / count($stores);
                    $avg_speed_stores = $summary[0]['data']["speed_sales"] / count($stores);
                }else{
                    $avg_remain_stores = 0;
                    $avg_speed_stores = 0;
                }
                if(count($products)){
                    $avg_remain_products = $summary[1]['data']["remain"] / count($products);
                    $avg_speed_products = $summary[1]['data']["speed_sales"] / count($products);
                }else{
                    $avg_remain_products = 0;
                    $avg_speed_products = 0;
                }

                $summary[0]['data']["remain"] = round($avg_remain_stores, 2);
                $summary[1]['data']["remain"] = round($avg_remain_products, 2);
                $summary[0]['data']["speed_sales"] = round($avg_speed_stores, 2);
                $summary[1]['data']["speed_sales"] = round($avg_speed_products, 2);
                $results["items"] = $summary;
                $results["weeks"] = $weeks;
                $count_weeks = count($results["weeks"]);
                $width = 950;
                $html_head = "<div class='head-row'><div>Номер недели</div><div style=\"width: 600px;\"><span>Сводная информация за период</span></div>";
                foreach($results["weeks"] as $index => $week){
                    $date_from = date("d.m.Y", strtotime($week['date_from']));
                    $date_to = date("d.m.Y", strtotime($week['date_to']));
                    $num = $index + 1;
                    $html_head .= "<div style=\"width: 400px;\">{$num} нед.</div>";
                    $width += 400;
                }
                $html_head .= "</div><div class='head-row'><div>Период</div><div style=\"width: 600px;\"><span class='lab'>{$count_weeks} нед.</span></div>";
                foreach($results["weeks"] as $index => $week){
                    $date_from = date("d.m.Y", strtotime($week['date_from']));
                    $date_to = date("d.m.Y", strtotime($week['date_to']));
                    $num = $index + 1;
                    $html_head .= "<div style=\"width: 400px;\"><span class='lab'>{$date_from} - {$date_to}</span></div>";
                }
                $html_head .= "</div>";
                foreach($columns as $key => $column){
                    if($column['key']=="name"){
                        $columns[$key]["html"] = $html_head;
                    }
                }
                $results["head"] = $html_head;
                $results["head_width"] = $width;
                $results["columns"] = $columns;
                $results["total"] = count($summary);
            }
        }
        return $results;
    }

    public function getConnectedStores($store_id){
        $stores = array();
        $query = $this->modx->newQuery("slStoresConnection");
        $query->leftJoin("slStores", "slStores", "slStores.id = slStoresConnection.store_id");
        $query->where(array(
            "slStoresConnection.vendor_id" => $store_id,
            "slStoresConnection.active" => 1,
            "slStores.active" => 1
        ));
        $connections = $this->modx->getCollection("slStoresConnection", $query);
        foreach($connections as $connection){
            $stores[] = $connection->get("store_id");
        }
        return $stores;
    }

    public function generatePresent ($report_id) {
        $report = $this->modx->getObject("slReports", $report_id);
        if($report) {
            $report_data = $report->toArray();
            if ($report_data["type"] == 2) {
                $stores = $this->getConnectedStores($report_data["store_id"]);
                $results = array();
                foreach($stores as $store){
                    // проверка существующего и перегенерация, если необходимо
                    $criteria = array(
                        "report_id" => $report_id,
                        "store_id" => $store
                    );
                    if($report_store = $this->modx->getObject("slReportsPresent", $criteria)){
                        // очищаем все данные и продолжаем формировать
                        $reports_store_id = $report_store->get("id");
                        $this->modx->removeCollection("slReportsPresentProducts", array("present_store_id" => $reports_store_id));
                    }else{
                        $report_store = $this->modx->newObject("slReportsPresent");
                    }
                    // берем матрицу
                    if(isset($report_data['properties']['matrix'])) {
                        $products = array();
                        // считаем значения матрицы
                        $subq = $this->modx->newQuery("slStoresMatrixProducts");
                        $subq->leftJoin('slStoresMatrix', 'slStoresMatrix', 'slStoresMatrix.id = slStoresMatrixProducts.matrix_id');
                        $subq->where(array("matrix_id:=" => $report_data['properties']['matrix']));
                        $subq->select(array("slStoresMatrixProducts.*, slStoresMatrix.percent as percent"));
                        if ($subq->prepare() && $subq->stmt->execute()) {
                            $prods = $subq->stmt->fetchAll(PDO::FETCH_ASSOC);
                            foreach ($prods as $prod) {
                                $products[] = array(
                                    "id" => $prod["product_id"],
                                    "count" => $prod["count"],
                                    "percent_all" => $prod["percent"]
                                );
                            }
                        }
                        // проверяем срок, если месяцев больше, то собираем по месяцам + среднее значение выполнения
                        // если меньше, то берем среднее значение
                        // также корректируем по сроку матрицы
                        foreach ($products as $key => $product) {
                            $subq = $this->modx->newQuery("slStoresRemainsHistory");
                            $subq->leftJoin('slStoresRemains', 'slStoresRemains', 'slStoresRemains.id = slStoresRemainsHistory.remain_id');
                            $subq->where(array("slStoresRemains.product_id:=" => $product['id'], "slStoresRemains.store_id:=" => $store));
                            $subq->where(array("slStoresRemainsHistory.date:>=" => $report_data['date_from'], "slStoresRemainsHistory.date:<=" => $report_data['date_to']));
                            $subq->select(array("AVG(slStoresRemainsHistory.remains) as avg_available"));
                            if ($subq->prepare() && $subq->stmt->execute()) {
                                $res = $subq->stmt->fetch(PDO::FETCH_ASSOC);
                                $results[$store]['products'][$product['id']] = $product;
                                $results[$store]['products'][$product['id']]['average'] = $res['avg_available'];
                                if($product['count'] < $res['avg_available']){
                                    $results[$store]['products'][$product['id']]['percent'] = 100;
                                }else{
                                    if($res['avg_available'] > 0){
                                        $results[$store]['products'][$product['id']]['percent'] = (($product['count'] - $res['avg_available']) * 100) / $product['count'];
                                    }else{
                                        $results[$store]['products'][$product['id']]['percent'] = 0;
                                    }
                                }
                            }
                        }
                        // данные для сводного отчета
                        $summ = 0;
                        $count = 0;
                        $all = 0;
                        foreach($results[$store]['products'] as $key => $val){
                            $summ += $val['percent'];
                            $count++;
                            $all = $val['percent_all'];
                        }
                        if($summ > 0){
                            $results[$store]['percent'] = round($summ/$count, 2);
                        }else{
                            $results[$store]['percent'] = 0;
                        }
                        $results[$store]['percent_delta'] = round(abs($all - $results[$store]['percent']), 2);
                        if($results[$store]['percent'] < $all){
                            $results[$store]['complete'] = 0;
                        }else{
                            $results[$store]['complete'] = 1;
                        }
                        // заносим необходимые значения в БД
                        foreach($results as $store => $val){
                            $report_store->set("report_id", $report_id);
                            $report_store->set("store_id", $store);
                            $report_store->set("percent", $val['percent']);
                            $report_store->set("percent_delta", $val['percent_delta']);
                            $report_store->set("success", $val['complete']);
                            $report_store->save();
                            if($present_store_id = $report_store->get("id")){
                                foreach($val['products'] as $product){
                                    $present_product = $this->modx->newObject("slReportsPresentProducts");
                                    $present_product->set("present_store_id", $present_store_id);
                                    $present_product->set("product_id", $product['id']);
                                    $present_product->set("percent", $product['percent']);
                                    $present_product->save();
                                }
                            }
                        }
                        $report->set("createdon", time());
                        $report->save();
                    }else{
                        // не указана матрица
                    }
                }
                return $results;
            }
        }
    }

    public function getPresent($properties){
        $results = array(
            "total" => 0
        );
        if($properties['report_id']){
            $q = $this->modx->newQuery("slReportsPresent");
            $q->leftJoin('slStores', 'slStores', 'slStores.id = slReportsPresent.store_id');
            $q->leftJoin('slReports', 'slReports', 'slReports.id = slReportsPresent.report_id');
            $q->leftJoin('slStoresConnection', 'slStoresConnection', 'slStoresConnection.store_id = slStores.id AND slStoresConnection.vendor_id = slReports.store_id');
            $q->leftJoin('dartLocationCity', 'dartLocationCity', 'dartLocationCity.id = slStores.city');
            $q->leftJoin('dartLocationRegion', 'dartLocationRegion', 'dartLocationRegion.id = dartLocationCity.region');
            // отсекаем по ID отчета
            $q->where(array(
                "slReportsPresent.report_id:=" => $properties['report_id']
            ));
            // фильтры
            $cities = array();
            $regions = array();
            if (isset($properties['filtersdata']['region'])) {
                foreach ($properties['filtersdata']['region'] as $key => $val) {
                    if ($val['checked']) {
                        $k_r = explode("_", $key);
                        if ($k_r[0] == 'region') {
                            $regions[] = $k_r[1];
                        }
                        if ($k_r[0] == 'city') {
                            $cities[] = $k_r[1];
                        }
                    }
                }
            }
            if (count($regions)) {
                $q->where(array(
                    "`dartLocationCity`.`region`:IN" => $regions
                ));
            }
            if (count($cities)) {
                $q->where(array(
                    "`dartLocationCity`.`id`:IN" => $cities
                ));
            }
            if ($properties['filter']) {
                $words = explode(" ", $properties['filter']);
                foreach ($words as $word) {
                    $criteria = array();
                    $criteria['slStores.name:LIKE'] = '%' . trim($word) . '%';
                    $q->where($criteria);
                }
            }
            $q->where(array(
                "`slStores`.`active`:=" => 1
            ));
            $q->select(array(
                'slReportsPresent.*',
                'slStores.name as name',
                'slStoresConnection.date as date_from',
                'dartLocationCity.city as city',
                'dartLocationRegion.name as region'
            ));
            // 0 и 1 по ним можно считать
            // Подсчитываем общее число записей
            $results['total'] = $this->modx->getCount("slReportsPresent", $q);
            // Устанавливаем лимит 1/10 от общего количества записей
            // со сдвигом 1/20 (offset)
            if ($properties['page'] && $properties['perpage']) {
                $limit = $properties['perpage'];
                $offset = ($properties['page'] - 1) * $properties['perpage'];
                $q->limit($limit, $offset);
            }

            // И сортируем по ID в обратном порядке
            if ($properties['sort']) {
                // $this->modx->log(1, print_r($properties, 1));
                $keys = array_keys($properties['sort']);
                // нужно проверить какому объекту принадлежит поле
                $q->sortby($keys[0], $properties['sort'][$keys[0]]['dir']);
            }
            $q->prepare();
            if ($q->prepare() && $q->stmt->execute()) {
                $results['items'] = $q->stmt->fetchAll(PDO::FETCH_ASSOC);
                foreach ($results['items'] as $key => $val) {
                    $date_from = strtotime($val['date_from']);
                    $results['items'][$key]['date_from'] = date("d.m.Y", $date_from);
                }
            }
        }
        return $results;
    }

    public function generateTopSales ($report_id) {
        $report = $this->modx->getObject("slReports", $report_id);
        if($report) {
            $report_data = $report->toArray();
            if ($report_data["type"] == 1) {
                $query = $this->modx->newQuery("slStoreDocsProducts");
                $query->leftJoin("slStoreDocs", "slStoreDocs", "slStoreDocs.id = slStoreDocsProducts.doc_id");
                $query->leftJoin("slStoresRemains", "slStoresRemains", "slStoresRemains.id = slStoreDocsProducts.remain_id");
                $query->leftJoin("slStores", "slStores", "slStores.id = slStoresRemains.store_id");
                $query->leftJoin("msProductData", "msProductData", "msProductData.id = slStoresRemains.product_id");
                $query->leftJoin("modResource", "modResource", "modResource.id = msProductData.id");
                $query->select(array("modResource.id as product_id, slStoresRemains.store_id as store_id, slStoresRemains.id as remain_id, SUM(slStoreDocsProducts.count) as sales"));
                $query->where(array(
                    "slStoresRemains.product_id:>" => 0,
                    "AND:slStoreDocsProducts.type:=" => 1,
                    "AND:slStoreDocs.date:>=" => $report_data['date_from'],
                    "AND:slStoreDocs.date:<=" => $report_data['date_to']
                ));
                $query->groupby("store_id,slStoresRemains.product_id");
                $query->prepare();
                $this->modx->log(1, $query->toSQL());
                if($query->prepare() && $query->stmt->execute()) {
                    $remains = $query->stmt->fetchAll(PDO::FETCH_ASSOC);
                    $this->modx->removeCollection("slReportsTopSales", array("report_id" => $report_id));
                    foreach($remains as $remain){
                        // TODO: предусмотреть смену привязки товара
                        $topsales = $this->modx->newObject("slReportsTopSales");
                        $topsales->set("report_id", $report_id);
                        $topsales->set("remain_id", $remain['remain_id']);
                        $topsales->set("store_id", $remain['store_id']);
                        $topsales->set("product_id", $remain['product_id']);
                        $topsales->set("sales", $remain['sales']);
                        $topsales->save();
                    }
                    $report->set("createdon", time());
                    $report->save();
                    return true;
                }
            }
        }
    }

    public function getTopSales($properties = array()){
        if($properties['report_id']){
            $q = $this->modx->newQuery('slReportsTopSales');
            $q->leftJoin('msProductData', 'msProductData', 'msProductData.id = slReportsTopSales.product_id');
            $q->leftJoin('modResource', 'Content', 'msProductData.id = Content.id');
            $q->leftJoin('msVendor', 'Vendor', 'Vendor.id = msProductData.vendor');
            $q->leftJoin('slStores', 'slStores', 'slStores.id = slReportsTopSales.store_id');
            $q->leftJoin('slStoresRemains', 'slStoresRemains', 'slStoresRemains.id = slReportsTopSales.remain_id');
            $q->leftJoin('dartLocationCity', 'dartLocationCity', 'dartLocationCity.id = slStores.city');
            $q->leftJoin('dartLocationRegion', 'dartLocationRegion', 'dartLocationRegion.id = dartLocationCity.region');
            $q->select(array(
                'msProductData.*',
                "Content.pagetitle as name",
                "SUM(slReportsTopSales.sales) as roz_sales"
            ));
            // отсекаем по ID отчета
            $q->where(array(
                "slReportsTopSales.report_id:=" => $properties['report_id']
            ));
            if($properties['filtersdata']){
                if($properties['filtersdata']['store_type']){
                    $q->where(array(
                        "slStores.type:=" => $properties['filtersdata']['store_type']
                    ));
                }
                if($properties['filtersdata']['vendor']){
                    $q->where(array(
                        "msProductData.vendor:=" => $properties['filtersdata']['vendor']
                    ));
                }
                if($properties['filtersdata']['catalog']){
                    $catalogs = array();
                    foreach($properties['filtersdata']['catalog'] as $key => $val){
                        if($val['checked']){
                            $catalogs[] = $key;
                        }
                    }
                    if(count($catalogs)){
                        $q->where(array(
                            "Content.parent:IN" => $catalogs
                        ));
                    }
                }
                if($properties['filtersdata']['stores']){
                    $q->where(array(
                        "slReportsTopSales.store_id:=" => $properties['filtersdata']['stores']
                    ));
                }
                if($properties['filtersdata']['warehouses']){
                    $q->where(array(
                        "slReportsTopSales.store_id:=" => $properties['filtersdata']['warehouses']
                    ));
                }
                if(isset($properties['filtersdata']['region'])){
                    $cities = array();
                    $regions = array();
                    foreach($properties['filtersdata']['region'] as $key => $val){
                        if($val['checked']){
                            $k_r = explode("_", $key);
                            if($k_r[0] == 'region'){
                                $regions[] = $k_r[1];
                            }
                            if($k_r[0] == 'city') {
                                $cities[] = $k_r[1];
                            }
                        }
                    }
                    if(count($regions)){
                        $q->where(array(
                            "`dartLocationRegion`.`id`:IN" => $regions
                        ));
                    }
                    if(count($cities)){
                        $q->where(array(
                            "`dartLocationCity`.`id`:IN" => $cities
                        ));
                    }
                }
            }
            if($properties['filter']){
                $words = explode(" ", $properties['filter']);
                foreach($words as $word){
                    $criteria = array();
                    $criteria['Content.pagetitle:LIKE'] = '%'.trim($word).'%';
                    $criteria['OR:msProductData.vendor_article:LIKE'] = '%'.trim($word).'%';
                    $q->where($criteria);
                }
            }
            $q->groupby("slReportsTopSales.product_id");
            $result = array();

            $q->prepare();
            $pre_query = $q->toSQL();

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
                $q->sortby('roz_sales', 'desc');
            }

            if ($q->prepare() && $q->stmt->execute()) {
                $this->modx->log(1, $q->toSQL());
                $result['products'] = $q->stmt->fetchAll(PDO::FETCH_ASSOC);
                // Подсчитываем общее число записей
                $statement = $this->modx->query("SELECT COUNT(*) as count FROM ({$pre_query}) as temp");
                $c = $statement->fetch(PDO::FETCH_ASSOC);
                $result['total'] = $c['count'];
                return $result;
            }
        }
        return array(
            "products" => array(),
            "total" => 0
        );
    }

    public function generateRRC($report_id){
        $report = $this->modx->getObject("slReports", $report_id);
        if($report){
            $report_data = $report->toArray();
            if($report_data["type"] == 3){
                $stores = $this->getConnectedStores($report_data["store_id"]);
                foreach($stores as $store){
                    // проверка существующего и перегенерация, если необходимо
                    $criteria = array(
                        "report_id" => $report_id,
                        "store_id" => $store
                    );
                    if($report_store = $this->modx->getObject("slReportsRRCStores", $criteria)){
                        // очищаем все данные и продолжаем формировать
                        $reports_store_id = $report_store->get("id");
                        $this->modx->removeCollection("slReportsRRCProducts", array("rrc_store_id" => $reports_store_id));
                    }else{
                        $report_store = $this->modx->newObject("slReportsRRCStores");
                    }
                    // echo $store."<br/>";
                    $report_store->set("store_id", $store);
                    $report_store->set("report_id", $report_id);
                    // обнулим показатели сводного отчета, на всякий пожарный
                    $report_store->set("avg_delta_percent", 0);
                    $report_store->set("avg_delta_middle_percent", 0);
                    $report_store->set("avg_days_count", 0);
                    $report_store->set("violation", 0);
                    $report_store->save();
                    // начинаем формирование отчета только по тем позициям, которые в параметрах
                    $brand = $report_data["properties"]["brand"];
                    if($brand){
                        // TODO: проверить, что бренд реально принадлежит нашему вендору или куплен доступ
                        // нам нужно взять все товары этого бренда в магазине
                        $query = $this->modx->newQuery("slStoresRemains");
                        $query->select(array("slStoresRemains.*, msProductData.price_rrc"));
                        $query->leftJoin("msProductData", "msProductData", "msProductData.id = slStoresRemains.product_id");
                        $query->where(array(
                            "msProductData.vendor:=" => $brand,
                            "msProductData.price_rrc:>" => 0,
                            "AND:slStoresRemains.store_id:=" => $store
                        ));
                        $remains_info = array();
                        if($query->prepare() && $query->stmt->execute()) {
                            // echo $query->toSQL()."<br/>";
                            $remains = $query->stmt->fetchAll(PDO::FETCH_ASSOC);
                            // получили все товары
                            foreach($remains as $remain){
                                $remain_info = array(
                                    "remain_id" => $remain['id'],
                                    "current_price" => $remain['price'],
                                    "price_rrc" => $remain['price_rrc'],
                                    "price_max" => 0,
                                    "price_min" => 9999999999,
                                    "non_rrc" => 0,
                                    "avg_price" => 0,
                                    "avg_weighted_price" => 0,
                                    "avg_weighted_price_variation_percent" => 0,
                                    "avg_weighted_price_variation_money" => 0,
                                    "summ_price" => 0,
                                    "avg_variation_money" => 0,
                                    "avg_variation_percent" => 0,
                                    'prices' => array(),
                                    'middle_weight_prices' => array(),
                                    "violation" => 0
                                );
                                // ищем историю изменения на основе дат
                                $query = $this->modx->newQuery("slStoresRemainsHistory");
                                $query->select(array("slStoresRemainsHistory.*"));
                                $query->where(array(
                                    "slStoresRemainsHistory.remain_id:=" => $remain['id'],
                                    "AND:slStoresRemainsHistory.remains:>" => 0,
                                    "AND:slStoresRemainsHistory.date:>=" => $report_data["properties"]['date_from'],
                                    "AND:slStoresRemainsHistory.date:<=" => $report_data["properties"]['date_to'],
                                ));
                                if($query->prepare() && $query->stmt->execute()) {
                                    $history = $query->stmt->fetchAll(PDO::FETCH_ASSOC);
                                    foreach($history as $h) {
                                        if($h['price'] != $remain_info['price_rrc']){
                                            $remain_info['violation'] = 1;
                                            $report_store->set("violation", 1);
                                            $report_store->save();
                                        }
                                        $remain_info['prices'][] = array(
                                            "price" => $h['price'],
                                            "remains" => $h['remains']
                                        );
                                        if(isset($remain_info['middle_weight_prices'][$h['price']])){
                                            $remain_info['middle_weight_prices'][$h['price']]++;
                                        }else{
                                            $remain_info['middle_weight_prices'][$h['price']] = 1;
                                        }
                                        $remain_info['summ_price'] += $h['price'];
                                        if($h['price'] != $remain['price_rrc']){
                                            $remain_info['non_rrc']++;
                                        }
                                        if($h['price'] > $remain_info['price_max']){
                                            $remain_info['price_max'] = $h["price"];
                                        }
                                        if($h['price'] < $remain_info['price_min']){
                                            $remain_info['price_min'] = $h["price"];
                                        }
                                    }
                                    if(count($remain_info['middle_weight_prices'])){
                                        $summ_price = 0;
                                        $summ_days = 0;
                                        foreach($remain_info['middle_weight_prices'] as $key => $val){
                                            $summ_price += $key*$val;
                                            $summ_days += $val;
                                        }
                                        $remain_info['avg_weighted_price'] = round($summ_price / $summ_days, 2);
                                    }else{
                                        $remain_info['avg_weighted_price'] = $remain_info["current_price"];
                                        $remain_info["avg_weighted_price_variation_money"] = abs($remain_info["price_rrc"] - $remain_info['avg_weighted_price']);
                                        $remain_info["avg_weighted_price_variation_percent"] = round(abs(($remain_info["avg_weighted_price"] / $remain_info["price_rrc"]) * 100), 2);
                                    }
                                    if(count($remain_info['prices'])){
                                        $remain_info['avg_price'] = round($remain_info['summ_price'] / count($remain_info['prices']), 2);
                                        $remain_info["avg_variation_money"] = abs($remain_info["price_rrc"] - $remain_info['avg_price']);
                                        $remain_info["avg_variation_percent"] = round(abs(($remain_info["avg_variation_money"] / $remain_info["price_rrc"]) * 100), 2);
                                    }else{
                                        $remain_info['price_max'] = $remain_info["current_price"];
                                        $remain_info['price_min'] = $remain_info["current_price"];
                                    }
                                }
                                $remains_info[] = $remain_info;
                            }
                            // данные собраны, пора упаковать, средние значения посчитаем запросом
                            foreach($remains_info as $r) {
                                $criteria = array(
                                    "rrc_store_id:=" => $report_store->get("id"),
                                    "AND:remain_id:=" => $r["remain_id"]
                                );
                                if($info = $this->modx->getObject("slReportsRRCProducts", $criteria)){

                                }else{
                                    $info = $this->modx->newObject("slReportsRRCProducts");
                                }
                                $info->set("rrc_store_id", $report_store->get("id"));
                                $info->set("remain_id", $r["remain_id"]);
                                $info->set("rrc_price", $r["price_rrc"]);
                                $info->set("avg_price", $r["avg_price"]);
                                $info->set("avg_variation_percent", $r["avg_variation_percent"]);
                                $info->set("avg_variation_money", $r["avg_variation_money"]);
                                $info->set("avg_weighted_price", $r["avg_weighted_price"]);
                                $info->set("avg_weighted_price_variation_percent", $r["avg_weighted_price_variation_percent"]);
                                $info->set("avg_weighted_price_variation_money", $r["avg_weighted_price_variation_money"]);
                                $info->set("not_rrc_days", $r["non_rrc"]);
                                $info->set("min_price", $r["price_min"]);
                                $info->set("max_price", $r["price_max"]);
                                $info->set("violation", $r["violation"]);
                                $info->save();
                            }
                            // теперь посчитаем среднее по кухне
                            $query = $this->modx->newQuery("slReportsRRCProducts");
                            $query->select(array("AVG(avg_variation_percent) as avg_variation_percent,AVG(avg_weighted_price_variation_percent) as avg_weighted_price_variation_percent,AVG(not_rrc_days) as non_rrc"));
                            $query->where(array("rrc_store_id" => $report_store->get("id")));
                            $query->prepare();
                            // echo $query->toSQL()."<br/>";
                            if($query->prepare() && $query->stmt->execute()) {
                                $avg = $query->stmt->fetch(PDO::FETCH_ASSOC);
                                $report_store->set("avg_delta_percent", round($avg["avg_variation_percent"], 2));
                                $report_store->set("avg_delta_middle_percent", round($avg["avg_weighted_price_variation_percent"], 2));
                                $report_store->set("avg_days_count", round($avg["non_rrc"]));
                                $report_store->save();
                            }
                        }
                    }else{
                        // запись ошибки в properties
                        return false;
                    }
                }
                $report->set("createdon", time());
                $report->save();
                return true;
            }else{
                // запись ошибки в properties
                return false;
            }
        }else{
            return false;
        }
    }

    public function generateWeekSales($report_id){
        $report = $this->modx->getObject("slReports", $report_id);
        if($report) {
            $report_data = $report->toArray();
            // generate weeks
            $start = new DateTime($report_data["properties"]['date_from']);
            $start->modify('last Monday');
            $end = new DateTime($report_data["properties"]['date_to']);
            $end->modify('next Monday');

            $interval = new DateInterval('P1D');
            $dateRange = new DatePeriod($start, $interval, $end);

            $weekNumber = 1;
            $weeks = array();
            foreach ($dateRange as $date) {
                $weeks[$weekNumber][] = $date;
                if ($date->format('w') == 0) {
                    $date->setTime(23, 59, 59);
                    $weeks[$weekNumber][] = $date;
                    $weekNumber++;
                }
            }
            if ($report_data["type"] == 4) {
                // проверка существующего и перегенерация, если необходимо
                $criteria = array(
                    "report_id" => $report_id
                );
                $report_weeks = $this->modx->getCollection("slReportsWeeks", $criteria);
                foreach($report_weeks as $week){
                    // очищаем все данные и продолжаем формировать
                    $reports_week_id = $week->get("id");
                    $this->modx->removeCollection("slReportsWeekSales", array("week_id" => $reports_week_id));
                    $week->remove();
                }
                $stores = $this->getConnectedStores($report_data["store_id"]);
                // get matrix
                if (isset($report_data['properties']['matrix'])) {
                    $subq = $this->modx->newQuery("slStoresMatrixProducts");
                    $subq->leftJoin('slStoresMatrix', 'slStoresMatrix', 'slStoresMatrix.id = slStoresMatrixProducts.matrix_id');
                    $subq->where(array("matrix_id:=" => $report_data['properties']['matrix']));
                    $subq->select(array("slStoresMatrixProducts.*"));
                    if ($subq->prepare() && $subq->stmt->execute()) {
                        $prods = $subq->stmt->fetchAll(PDO::FETCH_ASSOC);
                    }
                }
                // set weeks
                foreach($weeks as $week) {
                    $report_week = $this->modx->newObject("slReportsWeeks");
                    $report_week->set("report_id", $report_id);
                    $report_week->set("date_from", $week[0]->format('Y-m-d H:i:s'));
                    $report_week->set("date_to", $week[6]->format('Y-m-d H:i:s'));
                    $report_week->save();
                    foreach($stores as $store) {
                        foreach($prods as $prod) {
                            $product_sales = $this->modx->newObject("slReportsWeekSales");
                            $product_sales->set("week_id", $report_week->get("id"));
                            $product_sales->set("store_id", $store);
                            $product_sales->set("product_id", $prod["product_id"]);
                            // Узнаем кол-во продаж
                            $query = $this->modx->newQuery("slStoreDocsProducts");
                            $query->leftJoin("slStoresRemains", "slStoresRemains", "slStoresRemains.id = slStoreDocsProducts.remain_id");
                            $query->leftJoin("slStoreDocs", "slStoreDocs", "slStoreDocs.id = slStoreDocsProducts.doc_id");
                            $query->where(array(
                                "slStoreDocsProducts.type:=" => 1,
                                "AND:slStoreDocs.date:>=" => $week[0]->format('Y-m-d H:i:s'),
                                "AND:slStoreDocs.date:<=" => $week[6]->format('Y-m-d H:i:s'),
                                "AND:slStoreDocs.store_id:=" => $store,
                                "AND:slStoresRemains.product_id:=" => $prod["product_id"]
                            ));
                            $query->select(array("SUM(slStoreDocsProducts.count) as sales"));
                            $query->prepare();
                            $this->modx->log(1, $query->toSQL());
                            if($query->prepare() && $query->stmt->execute()){
                                $sales = $query->stmt->fetch(PDO::FETCH_ASSOC);
                                if($sales){
                                    if($sales['sales'] > 0){
                                        $product_sales->set("sales", $sales['sales']);
                                    }else{
                                        $product_sales->set("sales", 0);
                                    }
                                }else{
                                    // нет данных или сопоставления
                                    $product_sales->set("sales", 0);
                                }
                            }
                            // Узнаем кол-во OUT_OF_STOCK
                            $query = $this->modx->newQuery("slStoresRemainsHistory");
                            $query->leftJoin("slStoresRemains", "slStoresRemains", "slStoresRemains.id = slStoresRemainsHistory.remain_id");
                            $query->where(array(
                                "AND:slStoresRemainsHistory.date:>=" => $week[0]->format('Y-m-d H:i:s'),
                                "AND:slStoresRemainsHistory.date:<=" => $week[6]->format('Y-m-d H:i:s'),
                                "AND:slStoresRemains.store_id:=" => $store,
                                "AND:slStoresRemainsHistory.count:=" => 0,
                                "AND:slStoresRemains.product_id:=" => $prod["product_id"]
                            ));
                            $query->select(array("COUNT(*) as outofstock"));
                            $query->prepare();
                            $this->modx->log(1, $query->toSQL());
                            if($query->prepare() && $query->stmt->execute()){
                                $sales = $query->stmt->fetch(PDO::FETCH_ASSOC);
                                if($sales){
                                    if($sales['outofstock'] > 0){
                                        $product_sales->set("out_of_stock", $sales['outofstock']);
                                    }else{
                                        $product_sales->set("out_of_stock", 0);
                                    }
                                }else{
                                    // нет данных или сопоставления
                                    $product_sales->set("out_of_stock", 7);
                                }
                            }
                            // Узнаем кол-во на последний день
                            $to = $week[6]->format('Y-m-d H:i:s');
                            $week[6]->setTime(0, 0, 0);
                            $from = $week[6]->format('Y-m-d H:i:s');
                            $query = $this->modx->newQuery("slStoresRemainsHistory");
                            $query->leftJoin("slStoresRemains", "slStoresRemains", "slStoresRemains.id = slStoresRemainsHistory.remain_id");
                            $query->where(array(
                                "slStoresRemainsHistory.date:>=" => $from,
                                "AND:slStoresRemainsHistory.date:<=" => $to,
                                "AND:slStoresRemains.store_id:=" => $store,
                                "AND:slStoresRemains.product_id:=" => $prod["product_id"]
                            ));
                            $query->select(array("slStoresRemainsHistory.count"));
                            $query->prepare();
                            $this->modx->log(1, $query->toSQL());
                            if($query->prepare() && $query->stmt->execute()){
                                $sales = $query->stmt->fetch(PDO::FETCH_ASSOC);
                                if($sales){
                                    if($sales['count'] > 0){
                                        $product_sales->set("remain", $sales['count']);
                                    }else {
                                        $product_sales->set("remain", 0);
                                    }
                                }else{
                                    // нет данных или сопоставления
                                    $product_sales->set("remain", 0);
                                }
                            }
                            $product_sales->save();
                        }
                    }
                }
                $file = $this->sl->xslx->generateWeekSalesFile($report_data['id']);
                if($file){
                    $report->set("createdon", time());
                    $report->set("file", $file);
                    $report->save();
                }
                return true;
            }
        }
    }
}