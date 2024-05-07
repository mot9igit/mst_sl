<?php
class objectsHandler
{
    public $modx;
    public $sl;
    public $config;

    public function __construct(shopLogistic &$sl, modX &$modx)
    {
        $this->config = array(
            "loaddata" => "demo"
        );
        $this->sl =& $sl;
        $this->modx =& $modx;
        $this->sl->loadServices();
        $this->modx->lexicon->load('shoplogistic:default');
    }

    /**
     * Парсим строку фильтров
     *
     * @param $str
     * @return array
     */
    public function filter_parse($str, $delimeter = "||", $delimeter_params = ":[", $clean = 0){
        $out = array();
        $filters = explode($delimeter, $str);
        foreach($filters as $filter){
            $f = explode($delimeter_params, $filter);
            $filter_name = $f[0];
            if($clean){
                $f[1] = str_replace(array("=>", "(", ")", "/"), array(":", "{", "}", ""), $f[1]);
            }else{
                $f[1] = str_replace(array("=>", "[", "]"), array(":", "{", "}"), '['.$f[1]);
            }
            $this->modx->log(1, print_r($f[1], 1));
            $filter_param = json_decode($f[1], 1);
            $out[$filter_name] = $filter_param;
        }
        return $out;
    }

    public function filter($name, $config, $value){
        $field_value = '';
        switch ($name) {
            case 'koef':
                if($config["koef"]){
                    $field_value = floatval($value) * floatval($config["koef"]);
                }
                break;
            case 'numeric':
                if(is_array($value)){
                    $field_value = array();
                    foreach($value as $key => $item){
                        $field_value[$key] = preg_replace('/[^0-9]/', '', $item);
                    }
                }else{
                    $field_value = preg_replace('/[^0-9]/', '', $value);
                }
                break;
            case 'replace':
                if(is_array($value)){
                    $field_value = array();
                    foreach($value as $key => $item){
                        foreach($config as $k => $v){
                            $field_value[$key] = trim(str_replace($k, $v, $item));
                        }
                    }
                }else{
                    foreach($config as $k => $v){
                        $field_value = trim(str_replace($k, $v, $value));
                    }
                }
                break;
        }
        return $field_value;
    }

    /**
     * Проверка производителя
     *
     * @param $str
     * @return mixed
     */
    public function findVendor($str){
        $criteria = array(
            "name" => $str
        );
        $vendor = $this->modx->getObject("msVendor", $criteria);
        if(!$vendor){
            $vendor = $this->modx->newObject("msVendor");
            $vendor->set("name", $str);
        }
        $vendor->save();
        return $vendor->get("id");
    }

    /**
     * Обработка файлов фида (выгрузка в YML)
     *
     * @return void
     */
    public function handleFeeds () {
        // $user_id = $_SESSION['analytics_user']['profile']['id'];
        // черновая обработка
        $feeds = $this->modx->getCollection("slExportFiles", array("status:=" => 1));
        foreach($feeds as $feed){
            $feed->set("status", 6);
            $feed->save();
            // чистим лишнее
            $cats = $this->modx->getCollection("slExportFilesCats", array("file_id" => $feed->get("id")));
            foreach($cats as $cat){
                $cat->remove();
            }
            $file = $feed->get("file");
            $xml = simplexml_load_file($file);
            $cats = count($xml->shop->categories->category);
            $offers = count($xml->shop->offers->offer);            
            $feed->set("products", $offers);
            $feed->save();
            $categories = array();
            foreach ($xml->shop->categories->category as $row) {
				// проверка на дубликаты
				$criteria = array(
					"export_id" => strval($row['id']),
					"file_id" => $feed->get("id"),
					"name" => strval($row)
				);
				$cat = $this->modx->getObject("slExportFilesCats", $criteria);
				if(!$cat){
					$cat = $this->modx->newObject("slExportFilesCats");
				}
                $cat->set("name", strval($row));
                $cat->set("file_id", $feed->get("id"));
                $cat->set("export_id", $row['id']);
                $cat->set("export_parent_id", $row['parentId']);
                $cat->set("createdon", time());
                $cat->save();
                $file_cat_id = strval($row['id']);
                $cat_id = $cat->get("id");
                $categories[$file_cat_id] = $cat_id;
            }
			$feed->set("categories", count($categories));
			$feed->save();
            $vendors = array();

            foreach($xml->shop->offers->offer as $offer){
                $category = strval($offer->categoryId);
                if(strval($offer->categoryId)){
                    $cat_id = $categories[$category];
                    if($cat_id){
                        if(strval($offer->vendor)){
                            if(!in_array(strval($offer->vendor), $vendors)){
                                $vendors[] = strval($offer->vendor);
                            }
                        }
                        foreach($offer->param as $param) {
                            $option = trim(strval($param["name"]));
                            $criteria = array(
                                "name" => $option,
                                "cat_id" => $cat_id
                            );
                            $cat = $this->modx->getObject("slExportFilesCatsOptions", $criteria);
                            if (!$cat){
                                $cat = $this->modx->newObject("slExportFilesCatsOptions");
                                $cat->set("name", $option);
                                $cat->set("cat_id", $cat_id);
                                $cat->set("createdon", time());
                                $cat->set("examples", strval($param));
                                // чекаем опцию с таким наименованием
                                $criteria = array(
                                    "caption" => $option
                                );
                                $synonim = $this->modx->getObject("msOption", $criteria);
                                if($synonim){
                                    $id = $synonim->get("id");
                                    $cat->set("option_id", $id);
                                }
                            }else{
                                $ex = $cat->get("examples");
                                $examples = explode("||", $cat->get("examples"));
                                $length = strlen(strval($param));
                                $sum = strlen($ex) + $length;
                                if(!in_array(strval($param), $examples) && $length <= 25 && $sum < 255){
                                    $examples[] = strval($param);
                                    $cat->set("examples", implode("||", $examples));
                                }
                            }
                            $cat->save();
                        }
                    }
                }
            }
            $feed->set("status", 2);
            $feed->set("vendors", implode("||", $vendors));
            $feed->save();
        }
        // импорт товаров
        $status = array(
            "created" => 0,
            "updated" => 0
        );
        $feeds = $this->modx->getCollection("slExportFiles", array("status:=" => 3));
        foreach($feeds as $feed){
            $file = $feed->get("file");
            $xml = simplexml_load_file($file);
            $categories = array();
            // запоминаем категории
            foreach ($xml->shop->categories->category as $row) {
                $file_cat_id = strval($row['id']);
                $criteria = array(
                    "file_id" => $feed->get("id"),
                    "export_id" => $file_cat_id
                );
                $cat = $this->modx->getObject("slExportFilesCats", $criteria);
                if($cat){
                    $cat_id = $cat->get("id");
                    $categories[$file_cat_id] = $cat_id;
                }
            }
            // импортируем товар
            $vendor = $feed->get("vendor");
            $vendor_check = $feed->get("vendor_check");
            $feed->set("status", 6);
            $feed->set("error", "");
            $feed->save();
            if($vendor){
                foreach($xml->shop->offers->offer as $row) {
                    $category = strval($row->categoryId);
                    if ($category) {
                        $cat_id = $categories[$category];
                        if ($cat_id) {
                            $cat = $this->modx->getObject("slExportFilesCats", $cat_id);
                            if($cat){
                                $parent = $cat->get("cat_id");
                                $article = strval($row->article);
                                $varticle = strval($row->vendorCode);
                                if($parent && ($article || $varticle)){
                                    $data = array();
                                    $data['pagetitle'] = strval($row->name);
                                    $data['source_url'] = strval($row->url);
                                    $data['article'] = strval($row->article);
                                    $data['vendor_article'] = strval($row->article);
                                    if(strval($row->vendorCode)){
                                        $data['article'] = strval($row->vendorCode);
                                        $data['vendor_article'] = strval($row->vendorCode);
                                    }
                                    if(strval($row->barcode)){
                                        $data['barcode'] = strval($row->barcode);
                                    }
                                    if($vendor_check){
                                        if(strval($row->vendor)){
                                            $v = $this->findVendor(strval($row->vendor));
                                            if($v){
                                                $data['vendor'] = $v;
                                            }else{
                                                $data['vendor'] = $vendor;
                                            }
                                        }else{
                                            $data['vendor'] = $vendor;
                                        }
                                    }else{
                                        $data['vendor'] = $vendor;
                                    }
                                    $data['price'] = floatval(str_replace(",", ".", strval($row->price)));
                                    $data['price_rrc'] = floatval(str_replace(",", ".", strval($row->price)));
                                    $data['length'] = floatval(str_replace(",", ".", strval($row->length)));
                                    $data['width'] = floatval(str_replace(",", ".", strval($row->width)));
                                    $data['height'] = floatval(str_replace(",", ".", strval($row->height)));
                                    $data['weight_brutto'] = floatval(str_replace(",", ".", strval($row->weight)));
                                    $data['weight_netto'] = 0;
                                    $data['introtext'] = strval($row->description);
                                    $data['content'] = strval($row->descriptionFull);
                                    $data['parent'] = $parent;
                                    $data['fixprice'] = 0;
                                    $data['places'] = 1;
                                    $data['volume'] = 0;
                                    $data['b24id'] = '';
                                    foreach($row->pictures->picture as $picture){
                                        $data['image'][] = strval($picture);
                                    }
                                    foreach($row->picture as $picture){
                                        $data['image'][] = strval($picture);
                                    }

                                    foreach($row->param as $option){
                                        $value = trim(strval($option));
                                        $opt = trim(strval($option["name"]));
                                        $query = $this->modx->newQuery("slExportFilesCatsOptions");
                                        $query->leftJoin("msOption", "msOption", "msOption.id = slExportFilesCatsOptions.option_id");
                                        $query->where(array(
                                            "cat_id" => $cat_id,
                                            "name" => $opt
                                        ));
                                        $query->select(array("slExportFilesCatsOptions.*, msOption.id as option_id, msOption.key as option_key"));
                                        $query->prepare();
                                        if($query->prepare() && $query->stmt->execute()){
                                            $conf_option = $query->stmt->fetch(PDO::FETCH_ASSOC);
                                            if($conf_option){
                                                // если не игнорим
                                                if(!$conf_option['ignore']){
                                                    if($conf_option["option_id"] || $conf_option["to_field"]){
                                                        if($conf_option["option_id"]){
                                                            // устанавливаем существующую
                                                            $data["options-".$conf_option["option_key"]] = $value;
                                                            $this->sl->api->cat_option_check($conf_option["option_id"], $data['parent']);
                                                        }
                                                        if($conf_option["to_field"]){
                                                            $v = $value;
                                                            if($conf_option["filters"]){
                                                                $filters = $this->filter_parse($conf_option["filters"]);
                                                                foreach($filters as $key => $val) {
                                                                    $v = $this->filter($key, $val, $v);
                                                                }
                                                            }
                                                            $data[$conf_option["to_field"]] = $v;
                                                        }
                                                    }else{
                                                        // создаем опцию
                                                        if($opt){
                                                            $id = 0;
                                                            // возможно, есть опция с подобным наименованием
                                                            $criteria = array(
                                                                "caption" => $opt
                                                            );
                                                            $synonim = $this->modx->getObject("msOption", $criteria);
                                                            if($synonim){
                                                                $id = $synonim->get("id");
                                                                $key = $synonim->get("key");
                                                            }else{
                                                                // TODO: возможно, настроить фильтр опций
                                                                $p = $this->modx->newObject("modResource");
                                                                $op = array(
                                                                    "key" => str_replace(array(".", ","), array(" ", " "), $p->cleanAlias($opt)),
                                                                    "caption" => $opt,
                                                                    "category" => 20,
                                                                    "type" => "combo-options",
                                                                );
                                                                $key = $op["key"];
                                                                $id = $this->sl->api->option_check($op);
                                                            }
                                                            if($id){
                                                                $this->sl->api->cat_option_check($id, $data['parent']);
                                                                $data["options-".$key] = $value;
                                                                $cat_option = $this->modx->getObject("slExportFilesCatsOptions", $conf_option['id']);
                                                                if($cat_option){
                                                                    $cat_option->set("option_id", $id);
                                                                    $cat_option->save();
                                                                }
                                                            }
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                    }
                                    // $this->modx->log(1, print_r($data, 1));
                                    $prod = $this->sl->api->new_product($data);
                                    if($prod['resource']){
                                        $resource = $this->modx->getObject("modResource", $prod['resource']);
                                        if($resource){
                                            $resource->set("createdon", time());
                                            $resource->set("updatedon", time());
                                            $resource->set("alias", md5($resource->get("id")));
                                            $resource->set("uri",  md5($resource->get("id")));
                                            $resource->set("uri_override", 1);
                                            $resource->save();
                                        }
                                    }
                                    if($prod["mode"] == "create"){
                                        $status["created"]++;
                                    }
                                    if($prod["mode"] == "update"){
                                        $status["updated"]++;
                                    }
                                    $this->modx->error->reset();
                                }
                            }
                            // пропускаем, не указан родитель
                        }
                    }
                }
                $feed->set("status", 4);
                $feed->set("created", $status["created"]);
                $feed->set("updated", $status["updated"]);
                $feed->save();
            }else{
                $feed->set("status", 5);
                $feed->set("error", "Укажите производителя");
                $feed->save();
            }
        }
    }

    public function getObject($class, $id = 0, $criteria = array()){
        $query = $this->modx->newQuery($class);
        if($id){
            $criteria = array(
                "id:=" => $id
            );
        }
        if($criteria){
            $query->where($criteria);
        }
        $query->select(array(
            "`{$class}`.*"
        ));
        $query->limit(1);
        if ($query->prepare() && $query->stmt->execute()) {
            $object = $query->stmt->fetch(PDO::FETCH_ASSOC);
            return $object;
        }
        return false;
    }

    public function getObjects($properties){
        // $this->modx->log(1, print_r($properties, 1));
        if($properties['type'] == 'bonuses'){
            return $this->sl->store->getBonuses($properties);
        }if($properties['type'] == 'request'){
            return $this->getRequests($properties);
        }
        if($properties['type'] == 'rrcreport'){
            return $this->getRRCReport($properties);
        }
        if($properties['type'] == 'rrcreportdata'){
            return $this->getRRCReportData($properties);
        }
        if($properties['type'] == 'storedata'){
            return $this->getStoreMatrixData($properties);
        }
        if($properties['type'] == 'msproducts'){
            return $this->getMSProducts($properties);
        }
        if($properties['type'] == 'reporttypes'){
            return $this->getReportTypes($properties);
        }
        if($properties['type']== 'bonus_part'){
            return $this->getBonusesParts($properties);
        }
        if($properties['type'] == 'docs'){
            return $this->getDocs($properties);
        }
        if($properties['type'] == 'docsstatus'){
            return $this->getDocsStatus($properties);
        }
        if($properties['type'] == 'shipping_statuses'){
            return $this->getShippingStatus($properties);
        }
        if($properties['type'] == 'cardstatus'){
            return $this->getStatus("slStoresRemainsStatus", $properties);
        }
        if($properties['type'] == 'available_stores'){
            return $this->sl->store->getAvailableStores($properties, 1);
        }
        if($properties['type'] == 'bonus'){
            return $this->sl->store->getBonuses($properties);
        }
        if($properties['type'] == 'plan'){
            return $this->getPlan($properties);
        }
        if($properties['type'] == 'bonus_plans'){
            return $this->getPlans($properties);
        }
        if($properties['type'] == 'akbpunkts'){
            return $this->getAkbPunkts($properties);
        }
        if($properties['type'] == 'akbsettlements'){
            return $this->getAkbSettlements($properties);
        }
        if($properties['type'] == 'akbdotsplan'){
            return $this->getAkbDotsPlan($properties);
        }
        if($properties['type'] == 'akbdata'){
            return $this->getAkbData($properties);
        }
        if($properties['type'] == 'bonus_stores'){
            return $this->getBonusAvailableStores($properties);
        }
        if($properties['type'] == 'feeds'){
            return $this->getFeeds($properties);
        }
        if($properties['type'] == 'programfiles'){
            return $this->getProgramFiles($properties);
        }
        if($properties['type'] == 'shipdata'){
            return $this->getShipData($properties);
        }
        if($properties['type'] == 'code/check'){
            return $this->checkCode($properties);
        }
        if($properties['type'] == 'balance'){
            return $this->getBalance($properties);
        }
        if($properties['type'] == 'balance_requests'){
            return $this->getBalanceRequest($properties);
        }
        if($properties['type'] == 'opts'){
            return $this->getOpts($properties);
        }
        if($properties['type'] == 'report_copo'){
            return $this->getReportCopo($properties);
        }
        if($properties['type'] == 'report_copo_details'){
            return $this->getReportCopoDetails($properties);
        }
        if($properties['type'] == 'report_copo_all'){
            return $this->getReportCopoAll($properties);
        }
        if($properties['type'] == 'report_copo_all_details'){
            return $this->getReportCopoDetailsAll($properties);
        }
        return array(
            "total" => 0,
            "items" => array()
        );
    }

    /**
     *
     * Берем товары из каталога
     *
     * @param $properties
     * @return array
     */
    public function getMSProducts($properties){
        if(isset($properties['id'])){
            $q = $this->modx->newQuery("modResource");
            $q->leftJoin('msProductData', 'msProduct', 'msProduct.id = modResource.id');
            $q->leftJoin('msVendor', 'msVendor', 'msVendor.id = msProduct.vendor');
            $q->leftJoin('modResource', 'Parent', 'Parent.id = modResource.parent');
            $q->where(array(
                "modResource.class_key:=" => "msProduct"
            ));
            if($properties['filter']){
                $words = explode(" ", $properties['filter']);
                foreach($words as $word){
                    $criteria = array();
                    $criteria['modResource.pagetitle:LIKE'] = '%'.trim($word).'%';
                    $criteria['OR:msProduct.vendor_article:LIKE'] = '%'.trim($word).'%';
                    $criteria['OR:msVendor.name:LIKE'] = '%'.trim($word).'%';
                    $q->where($criteria);
                }
            }
            if($properties['filtersdata']){
                if(isset($properties['filtersdata']['vendor'])){
                    $q->where(array(
                        "msProduct.vendor:=" => $properties['filtersdata']['vendor']
                    ));
                }
                if(isset($properties['filtersdata']['parent'])){
                    $q->where(array(
                        "modResource.parent:=" => $properties['filtersdata']['parent']
                    ));
                }
            }

            $q->select(array(
                'modResource.*',
                'msProduct.*',
                'msVendor.name as vendor_name',
                'Parent.pagetitle as parent_name'
            ));

            // Подсчитываем общее число записей
            $result['total'] = $this->modx->getCount('modResource', $q);

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
            $q->prepare();
            $this->modx->log(1, $q->toSQL());
            if($q->prepare() && $q->stmt->execute()){
                $result['items'] = $q->stmt->fetchAll(PDO::FETCH_ASSOC);
            }
            return $result;
        }
        return array(
            "items" => array(),
            "total" => 0
        );
    }


    public function getReportCopoDetailsAll($properties){
        if(isset($properties['vendor_id'])){
            if($properties['vendor_id']){
                $result['vendor'] = $this->sl->getObject($properties['vendor_id'], "msVendor");
            }else{
                $result['vendor'] = array("name" => "Не найдено");
            }

            $prefix = $this->modx->getOption('table_prefix');
            $criteria = array(
                "brand_id:=" => $properties['vendor_id']
            );
            $q = $this->modx->newQuery("slStoresRemains");
            $q->leftJoin("slStores", "slStores", "slStores.id = slStoresRemains.store_id");
            $q->leftJoin("dartLocationCity", "dartLocationCity", "dartLocationCity.id = slStores.city");
            $q->leftJoin("dartLocationRegion", "dartLocationRegion", "dartLocationRegion.id = dartLocationCity.region");
            $q->leftJoin('slStoresRemainsStatus', 'slStoresRemainsStatus', 'slStoresRemainsStatus.id = slStoresRemains.status');
            $q->leftJoin('msProductData', 'msProduct', 'msProduct.id = slStoresRemains.product_id');
            $q->leftJoin('modResource', 'modResource', 'modResource.id = msProduct.id');
            $q->where($criteria);
            if($properties['filter']){
                $words = explode(" ", $properties['filter']);
                foreach($words as $word){
                    $criteria = array();
                    $criteria['name:LIKE'] = '%'.trim($word).'%';
                    $criteria['OR:article:LIKE'] = '%'.trim($word).'%';
                    $criteria['OR:catalog:LIKE'] = '%'.trim($word).'%';
                    $q->where($criteria);
                }
            }
            if($properties['filtersdata']){
                if(isset($properties['filtersdata']['status'])){
                    $q->where(array(
                        "slStoresRemains.status:=" => $properties['filtersdata']['status']
                    ));
                }
                if(isset($properties['filtersdata']['instock'][0])){
                    $q->where(array(
                        "slStoresRemains.remains:>" => 0
                    ));
                }
                if(isset($properties['filtersdata']['active'][0])){
                    $q->where(array("slStores.active:=" => 1));
                }
            }

            $today = date_create();
            $month_ago = date_create("-1 MONTH");
            date_time_set($month_ago, 00, 00);

            $date_from = date_format($month_ago, 'Y-m-d H:i:s');
            $date_to = date_format($today, 'Y-m-d H:i:s');

            $q->select(array(
                'slStoresRemains.*',
                'msProduct.image',
                'slStoresRemainsStatus.name as status_name',
                'slStoresRemainsStatus.color as status_color',
                'COALESCE(msProduct.price_rrc, 0) as price_rrc',
                'COALESCE(slStoresRemains.remains * slStoresRemains.price, 0) as summ',
                'IF(price_rrc > 0, (slStoresRemains.price - price_rrc), 0) as price_rrc_delta',
                "COALESCE((SELECT SUM(count) AS sales FROM `{$prefix}sl_stores_docs_products` LEFT JOIN `{$prefix}sl_stores_docs` ON `{$prefix}sl_stores_docs_products`.doc_id = `{$prefix}sl_stores_docs`.id WHERE `type` = 1 AND `remain_id` = `slStoresRemains`.`id` AND `{$prefix}sl_stores_docs`.date >= '{$date_from}' AND `{$prefix}sl_stores_docs`.date <= '{$date_to}' GROUP BY `remain_id`), 0) AS `sales_30`",
                "COALESCE((SELECT SUM(count) AS sales FROM `{$prefix}sl_stores_docs_products` WHERE `type` = 1 AND `remain_id` = `slStoresRemains`.`id` GROUP BY `remain_id`), 0) AS `sales`,
							   FLOOR((slStoresRemains.remains - slStoresRemains.purchase_speed)) as forecast,FLOOR((slStoresRemains.remains - (7 * slStoresRemains.purchase_speed))) as forecast_7, CONCAT(FLOOR((slStoresRemains.remains - slStoresRemains.purchase_speed)), ' / ', FLOOR((slStoresRemains.remains - (7 * slStoresRemains.purchase_speed)))) as forecast_all"
            ));

            // Подсчитываем общее число записей
            $result['total'] = $this->modx->getCount('slStoresRemains', $q);

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
            $q->prepare();
            $this->modx->log(1, $q->toSQL());
            if($q->prepare() && $q->stmt->execute()){
                $result['items'] = $q->stmt->fetchAll(PDO::FETCH_ASSOC);
                foreach($result['items'] as $key => $item){
                    if($item['summ']){
                        $result['items'][$key]["summ"] = number_format($item["summ"], 0, ',', ' ');
                    }
                }
            }
            return $result;
        }

        return array(
            "items" => array(),
            "total" => 0
        );
    }

    public function getReportCopoAll($properties){
        // all data
        $query_all = $this->modx->newQuery("slStoresRemains");
        $query_all->leftJoin("modResource", "modResource", "modResource.id = slStoresRemains.product_id");
        $query_all->leftJoin("msProductData", "msProductData", "msProductData.id = slStoresRemains.product_id");
        $query_all->leftJoin("msVendor", "msVendor", "msVendor.id = msProductData.vendor");
        $query_all->leftJoin("slStores", "slStores", "slStores.id = slStoresRemains.store_id");
        $query_all->leftJoin("dartLocationCity", "dartLocationCity", "dartLocationCity.id = slStores.city");
        $query_all->leftJoin("dartLocationRegion", "dartLocationRegion", "dartLocationRegion.id = dartLocationCity.region");
        // data
        $query = $this->modx->newQuery("slStoresRemainsVendorReports");
        $query->leftJoin("msVendor", "msVendor", "msVendor.id = slStoresRemainsVendorReports.vendor_id");
        $query->leftJoin("slStoresRemainsReports", "slStoresRemainsReports", "slStoresRemainsReports.id = slStoresRemainsVendorReports.report_id");
        $query->leftJoin("slStores", "slStores", "slStores.id = slStoresRemainsReports.store_id");
        $query->leftJoin("dartLocationCity", "dartLocationCity", "dartLocationCity.id = slStores.city");
        $query->leftJoin("dartLocationRegion", "dartLocationRegion", "dartLocationRegion.id = dartLocationCity.region");
        if($properties['filter']){
            $words = explode(" ", $properties['filter']);
            foreach($words as $word){
                $criteria = array();
                $criteria['msVendor.name:LIKE'] = '%'.trim($word).'%';
                $query->where($criteria);
                $query_all->where($criteria);
            }
        }

        if($properties['filtersdata']){
            if(isset($properties['filtersdata']['instock'][0])){
                $query->where(array(
                    "slStoresRemainsVendorReports.find_in_stock:>" => 0
                ));
                $query_all->where(array(
                    "slStoresRemains.remains:>" => 0
                ));
                $query->select(array("
                        slStoresRemainsVendorReports.vendor_id as id,
                        slStoresRemainsVendorReports.vendor_id,
                        ROUND(AVG(slStoresRemainsVendorReports.cards), 0) as cards,
                        SUM(slStoresRemainsVendorReports.find_in_stock) as find,
                        SUM(slStoresRemainsVendorReports.identified_in_stock) as identified,
						(SUM(slStoresRemainsVendorReports.find) - SUM(slStoresRemainsVendorReports.identified)) as no_identified,
						SUM(slStoresRemainsVendorReports.summ) as vendor_price,
                        ROUND((SUM(slStoresRemainsVendorReports.identified) / SUM(slStoresRemainsVendorReports.find) * 100), 2) as percent_identified,
						ROUND((SUM(slStoresRemainsVendorReports.summ_copo) / SUM(slStoresRemainsVendorReports.summ) * 100), 2) as percent_summ_identified,
                        msVendor.name as name,
                        msVendor.export_file as export_file"));
                // И сортируем по ID в обратном порядке
                if($properties['sort']){
                    $keys = array_keys($properties['sort']);
                    $query->sortby("SUM(slStoresRemainsVendorReports.{$keys[0]}_in_stock)", $properties['sort'][$keys[0]]['dir']);
                }else{
                    $query->sortby('SUM(slStoresRemainsVendorReports.find_in_stock)', 'desc');
                }
            }else{
                $query->select(array("
                    slStoresRemainsVendorReports.vendor_id as id,
                    slStoresRemainsVendorReports.vendor_id,
                    ROUND(AVG(slStoresRemainsVendorReports.cards), 0) as cards,
                    SUM(slStoresRemainsVendorReports.find) as find,
                    SUM(slStoresRemainsVendorReports.identified) as identified,
					(SUM(slStoresRemainsVendorReports.find) - SUM(slStoresRemainsVendorReports.identified)) as no_identified,
					SUM(slStoresRemainsVendorReports.summ) as vendor_price,
                    ROUND((SUM(slStoresRemainsVendorReports.identified) / SUM(slStoresRemainsVendorReports.find) * 100), 2) as percent_identified,
					ROUND((SUM(slStoresRemainsVendorReports.summ_copo) / SUM(slStoresRemainsVendorReports.summ) * 100), 2) as percent_summ_identified,
                    msVendor.name as name,
                    msVendor.export_file as export_file"));
                // И сортируем по ID в обратном порядке
                if($properties['sort']){
                    $keys = array_keys($properties['sort']);
                    $query->sortby($keys[0], $properties['sort'][$keys[0]]['dir']);
                }else{
                    $query->sortby('find', 'desc');
                }
            }
            if(isset($properties['filtersdata']['active'][0])){
                $query->where(array("slStores.active:=" => 1));
                $query_all->where(array(
                    "slStores.active:=" => 1
                ));
            }
        }else{
            $query->select(array("slStoresRemainsVendorReports.vendor_id as id,
                    slStoresRemainsVendorReports.vendor_id,
                    ROUND(AVG(slStoresRemainsVendorReports.cards), 0) as cards,
                    SUM(slStoresRemainsVendorReports.find) as find,
                    SUM(slStoresRemainsVendorReports.identified) as identified,
					(SUM(slStoresRemainsVendorReports.find) - SUM(slStoresRemainsVendorReports.identified)) as no_identified,
					SUM(slStoresRemainsVendorReports.summ) as vendor_price,
                    ROUND((SUM(slStoresRemainsVendorReports.identified) / SUM(slStoresRemainsVendorReports.find) * 100), 2) as percent_identified,
					ROUND((SUM(slStoresRemainsVendorReports.summ_copo) / SUM(slStoresRemainsVendorReports.summ) * 100), 2) as percent_summ_identified,
                    msVendor.name as name,
                    msVendor.export_file as export_file"));
            // И сортируем по ID в обратном порядке
            if($properties['sort']){
                $keys = array_keys($properties['sort']);
                $query->sortby($keys[0], $properties['sort'][$keys[0]]['dir']);
            }else{
                $query->sortby('find', 'desc');
            }
        }
        $query->groupby('slStoresRemainsVendorReports.vendor_id');
        $query->select(array("COUNT(*) as count"));
        // Устанавливаем лимит 1/10 от общего количества записей
        // со сдвигом 1/20 (offset)

        $query->prepare();
        $total_sql = "SELECT COUNT(*) as count FROM ( {$query->toSQL()} ) AS SQ";
        $this->modx->log(1, print_r($total_sql, 1));
        $statement = $this->modx->query($total_sql);
        if($statement){
            $ress = $statement->fetch(PDO::FETCH_ASSOC);
            $result['total'] = intval($ress["count"]);
        }
        $xlsx_query = $query;
        if($properties['page'] && $properties['perpage']){
            $limit = $properties['perpage'];
            $offset = ($properties['page'] - 1) * $properties['perpage'];
            $query->limit($limit, $offset);
        }
        // $this->modx->log(1, print_r($total_sql, 1));
        // $this->modx->log(1, print_r($result, 1));
        // $this->modx->log(1, $query->toSQL());
        // all data
        $query_copo = $query_all;
        $query_all->select(array("COUNT(*) as count, SUM(slStoresRemains.price * slStoresRemains.remains) as price, SUM(slStoresRemains.remains) as remains"));
        $query_all->prepare();
        // $this->modx->log(1, $query_all->toSQL());
        if($query_all->prepare() && $query_all->stmt->execute()){
            $all_data = $query_all->stmt->fetch(PDO::FETCH_ASSOC);
            $result['all_data']['numbers']["all_summ"] = $all_data["price"];
            $result['all_data']['numbers']["all_count"] = $all_data["count"];
            $result['all_data']['numbers']["all_remains"] = $all_data["remains"];
            $result['all_data']["all_summ"] = number_format($all_data["price"], 2, ',', ' ');
            $result['all_data']["all_count"] = number_format($all_data["count"], 0, ',', ' ');
            $result['all_data']["all_remains"] = number_format($all_data["remains"], 0, ',', ' ');
        }
        $query_copo->select(array("COUNT(*) as count, SUM(slStoresRemains.price * slStoresRemains.remains) as price, SUM(slStoresRemains.remains) as remains"));
        $query_copo->where(array("slStoresRemains.product_id:>" => 0));
        // $this->modx->log(1, $query_copo->toSQL());
        if($query_copo->prepare() && $query_copo->stmt->execute()){
            $all_data = $query_copo->stmt->fetch(PDO::FETCH_ASSOC);
            $result['all_data']['numbers']["copo_summ"] = $all_data["price"];
            $result['all_data']['numbers']["copo_count"] = $all_data["count"];
            $result['all_data']['numbers']["copo_remains"] = $all_data["remains"];
            $result['all_data']["copo_summ"] = number_format($all_data["price"], 2, ',', ' ');
            $result['all_data']["copo_count"] = number_format($all_data["count"], 0, ',', ' ');
            $result['all_data']["copo_remains"] = number_format($all_data["remains"], 0, ',', ' ');
        }
        if($result['all_data']['numbers']["all_count"] && $result['all_data']['numbers']["copo_count"]){
            $percent = ($result['all_data']['numbers']["copo_count"] / $result['all_data']['numbers']["all_count"]) * 100;
            $result['all_data']["copo_percent"] = round($percent, 2);
            $result['all_data']["no_copo_percent"] = 100 - $result['all_data']["copo_percent"];
        }else{
            $result['all_data']["copo_percent"] = 0;
            $result['all_data']["no_copo_percent"] = 0;
        }
        if($result['all_data']['numbers']["all_summ"] && $result['all_data']['numbers']["copo_summ"]){
            $percent = ($result['all_data']['numbers']["copo_summ"] / $result['all_data']['numbers']["all_summ"]) * 100;
            $result['all_data']["money_copo_percent"] = round($percent, 2);
            $result['all_data']["no_money_copo_percent"] = 100 - $result['all_data']["money_copo_percent"];
        }else{
            $result['all_data']["money_copo_percent"] = 0;
            $result['all_data']["no_money_copo_percent"] = 0;
        }
        if ($query->prepare() && $query->stmt->execute()) {
            $result['items'] = $query->stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach($result['items'] as $key => $item){
                if(!$item['vendor_id']){
                    $result['items'][$key]["name"] = "Не найдено";
                }
                if($item['vendor_price']){
                    $result['items'][$key]["vendor_price"] = number_format($item["vendor_price"], 0, ',', ' ');
                }
            }
            return $result;
        }


        return array(
            "items" => array(),
            "total" => 0
        );
    }

    public function getReportCopoDetails($properties){
        if($properties['brand_id']){
            $obj = $this->sl->getObject($properties['brand_id'], "slStoresRemainsVendorReports");
            if($obj['vendor_id']){
                $result['vendor'] = $this->sl->getObject($obj['vendor_id'], "msVendor");
            }else{
                $result['vendor'] = array("name" => "Не найдено");
            }
            $prefix = $this->modx->getOption('table_prefix');
            $criteria = array(
                "brand_id:=" => $obj['vendor_id'],
                "AND:store_id:=" => $properties['id']
            );
            $q = $this->modx->newQuery("slStoresRemains");
            $q->leftJoin('slStoresRemainsStatus', 'slStoresRemainsStatus', 'slStoresRemainsStatus.id = slStoresRemains.status');
            $q->leftJoin('msProductData', 'msProduct', 'msProduct.id = slStoresRemains.product_id');
            $q->leftJoin('modResource', 'modResource', 'modResource.id = msProduct.id');
            $q->where($criteria);
            if($properties['filter']){
                $words = explode(" ", $properties['filter']);
                foreach($words as $word){
                    $criteria = array();
                    $criteria['name:LIKE'] = '%'.trim($word).'%';
                    $criteria['OR:article:LIKE'] = '%'.trim($word).'%';
                    $q->where($criteria);
                }
            }
            if($properties['filtersdata']){
                if(isset($properties['filtersdata']['status'])){
                    $q->where(array(
                        "slStoresRemains.status:=" => $properties['filtersdata']['status']
                    ));
                }
                if(isset($properties['filtersdata']['instock'][0])){
                    $q->where(array(
                        "slStoresRemains.remains:>" => 0
                    ));
                }
            }

            $today = date_create();
            $month_ago = date_create("-1 MONTH");
            date_time_set($month_ago, 00, 00);

            $date_from = date_format($month_ago, 'Y-m-d H:i:s');
            $date_to = date_format($today, 'Y-m-d H:i:s');

            $q->select(array(
                'slStoresRemains.*',
                'msProduct.image',
                'slStoresRemainsStatus.name as status_name',
                'slStoresRemainsStatus.color as status_color',
                'COALESCE(slStoresRemains.remains * slStoresRemains.price, 0) as summ',
                'COALESCE(msProduct.price_rrc, 0) as price_rrc',
                'IF(price_rrc > 0, (slStoresRemains.price - price_rrc), 0) as price_rrc_delta',
                "COALESCE((SELECT SUM(count) AS sales FROM `{$prefix}sl_stores_docs_products` LEFT JOIN `{$prefix}sl_stores_docs` ON `{$prefix}sl_stores_docs_products`.doc_id = `{$prefix}sl_stores_docs`.id WHERE `type` = 1 AND `remain_id` = `slStoresRemains`.`id` AND `{$prefix}sl_stores_docs`.date >= '{$date_from}' AND `{$prefix}sl_stores_docs`.date <= '{$date_to}' GROUP BY `remain_id`), 0) AS `sales_30`",
                "COALESCE((SELECT SUM(count) AS sales FROM `{$prefix}sl_stores_docs_products` WHERE `type` = 1 AND `remain_id` = `slStoresRemains`.`id` GROUP BY `remain_id`), 0) AS `sales`,
							   FLOOR((slStoresRemains.remains - slStoresRemains.purchase_speed)) as forecast,FLOOR((slStoresRemains.remains - (7 * slStoresRemains.purchase_speed))) as forecast_7, CONCAT(FLOOR((slStoresRemains.remains - slStoresRemains.purchase_speed)), ' / ', FLOOR((slStoresRemains.remains - (7 * slStoresRemains.purchase_speed)))) as forecast_all"
            ));

            // Подсчитываем общее число записей
            $total_query = $q;
            $total_query->prepare();
            $total_sql = "SELECT COUNT(*) as count FROM ( {$total_query->toSQL()} ) AS SQ";
            $this->modx->log(1, print_r($total_sql, 1));
            $statement = $this->modx->query($total_sql);
            if($statement){
                $ress = $statement->fetch(PDO::FETCH_ASSOC);
                $result['total'] = intval($ress["count"]);
            }
            // $result['total'] = $this->modx->getCount('slStoresRemains', $q);
            $xlsx_query = $q;
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
            $q->prepare();
            $this->modx->log(1, print_r($q->toSQL(), 1));
            if($q->prepare() && $q->stmt->execute()){
                $result['items'] = $q->stmt->fetchAll(PDO::FETCH_ASSOC);
                foreach($result['items'] as $key => $item){
                    if($item['price']){
                        $result['items'][$key]["price"] = number_format($item['price'], 2, '.', ' ');
                    }
                    if($item['summ']){
                        $result['items'][$key]["summ"] = number_format($item['summ'], 2, '.', ' ');
                    }
                }

                // $xlsx = $this->sl->xslx->generateXLSXFile($properties["tabledata"], $result['items'], "copo_file_details_".$properties["id"]."_".$properties['brand_id']);

                $i = $limit;
                $limit = 1000;
                for($i = 0; $i <= $result['total']; $i += $limit){
                    $query = $xlsx_query;
                    $query->limit($limit, $i);
                    if($query->prepare() && $query->stmt->execute()){
                        $data = $query->stmt->fetchAll(PDO::FETCH_ASSOC);
                        $xlsx = $this->sl->xslx->generateXLSXFile($properties["tabledata"], $data, "copo_file_details_".$properties["id"]."_".$properties['brand_id'], 0, $xlsx["config"]);
                    }
                }
                $result["file"] = $xlsx["filename"];

            }
            return $result;
        }

        return array(
            "items" => array(),
            "total" => 0
        );
    }

    public function getReportCopo($properties){
        $report = $this->sl->product->generateCopoReport($properties['id']);
        if($report){
            $query = $this->modx->newQuery("slStoresRemainsVendorReports");
            $query->where(array("report_id:=" => $report['id']));
            $query->leftJoin("msVendor", "msVendor", "msVendor.id = slStoresRemainsVendorReports.vendor_id");
            if($properties['filter']){
                $words = explode(" ", $properties['filter']);
                foreach($words as $word){
                    $criteria = array();
                    $criteria['msVendor.name:LIKE'] = '%'.trim($word).'%';
                    $criteria['OR:msVendor.address:LIKE'] = '%'.trim($word).'%';
                    $query->where($criteria);
                }
            }

            if($properties['filtersdata']){
                if(isset($properties['filtersdata']['instock'][0])){
                    $query->where(array(
                        "slStoresRemainsVendorReports.find_in_stock:>" => 0
                    ));
                    $query->select(array("
                        slStoresRemainsVendorReports.id,
                        slStoresRemainsVendorReports.report_id,
                        slStoresRemainsVendorReports.vendor_id,
                        slStoresRemainsVendorReports.cards,
                        slStoresRemainsVendorReports.find_in_stock as find,
                        slStoresRemainsVendorReports.identified_in_stock as identified,
                        (slStoresRemainsVendorReports.find_in_stock - slStoresRemainsVendorReports.identified_in_stock) as no_identified,
                        slStoresRemainsVendorReports.summ as vendor_price,
                        slStoresRemainsVendorReports.percent_identified_in_stock as percent_identified,
                        ROUND((slStoresRemainsVendorReports.summ_copo / slStoresRemainsVendorReports.summ * 100), 2) as percent_summ_identified,
                        msVendor.name as name,
                        msVendor.export_file as export_file"));
                    // И сортируем по ID в обратном порядке
                    if($properties['sort']){
                        $keys = array_keys($properties['sort']);
                        $query->sortby($keys[0].'_in_stock', $properties['sort'][$keys[0]]['dir']);
                    }else{
                        $query->sortby('slStoresRemainsVendorReports.find_in_stock', 'desc');
                    }
                }else{
                    $query->select(array("slStoresRemainsVendorReports.*,
                    (slStoresRemainsVendorReports.find - slStoresRemainsVendorReports.identified) as no_identified,
                    slStoresRemainsVendorReports.summ as vendor_price,
                    ROUND((slStoresRemainsVendorReports.summ_copo / slStoresRemainsVendorReports.summ * 100), 2) as percent_summ_identified,
                    msVendor.name as name,msVendor.export_file as export_file"));
                    // И сортируем по ID в обратном порядке
                    if($properties['sort']){
                        $keys = array_keys($properties['sort']);
                        $query->sortby($keys[0], $properties['sort'][$keys[0]]['dir']);
                    }else{
                        $query->sortby('slStoresRemainsVendorReports.find', 'desc');
                    }
                }
            }else{
                $query->select(array("slStoresRemainsVendorReports.*, 
                (slStoresRemainsVendorReports.find - slStoresRemainsVendorReports.identified) as no_identified,
                slStoresRemainsVendorReports.summ as vendor_price,
                ROUND((slStoresRemainsVendorReports.summ_copo / slStoresRemainsVendorReports.summ * 100), 2) as percent_summ_identified,
                msVendor.name as name,msVendor.export_file as export_file"));
                // И сортируем по ID в обратном порядке
                if($properties['sort']){
                    $keys = array_keys($properties['sort']);
                    $query->sortby($keys[0], $properties['sort'][$keys[0]]['dir']);
                }else{
                    $query->sortby('slStoresRemainsVendorReports.find', 'desc');
                }
            }
            $query->prepare();
            $total_sql = "SELECT COUNT(*) as count FROM ( {$query->toSQL()} ) AS SQ";
            $this->modx->log(1, print_r($total_sql, 1));
            $statement = $this->modx->query($total_sql);
            if($statement){
                $ress = $statement->fetch(PDO::FETCH_ASSOC);
                $result['total'] = intval($ress["count"]);
            }
            $xlsx_query = $query;

            // Устанавливаем лимит 1/10 от общего количества записей
            // со сдвигом 1/20 (offset)
            if($properties['page'] && $properties['perpage']){
                $limit = $properties['perpage'];
                $offset = ($properties['page'] - 1) * $properties['perpage'];
                $query->limit($limit, $offset);
            }
            // $this->modx->log(1, $query->toSQL());
            if ($query->prepare() && $query->stmt->execute()) {
                // $result['total'] = $query->stmt->rowCount();;
                $result['items'] = $query->stmt->fetchAll(PDO::FETCH_ASSOC);
                foreach($result['items'] as $key => $item){
                    if(!$item['vendor_id']){
                        $result['items'][$key]["name"] = "Не найдено";
                    }
                    if($item['vendor_price']){
                        $result['items'][$key]["vendor_price"] = number_format($item['vendor_price'], 2, '.', ' ');
                    }
                }
                // $xlsx = $this->sl->xslx->generateXLSXFile($properties["tabledata"], $result['items'], "copo_file_".$properties["id"]."_".$properties['brand_id']);

                $limit = 1000;
                for($i = 0; $i <= $result['total']; $i += $limit){
                    $query = $xlsx_query;
                    $query->limit($limit, $i);
                    if($query->prepare() && $query->stmt->execute()){
                        $data = $query->stmt->fetchAll(PDO::FETCH_ASSOC);
                        foreach($data as $key => $item){
                            if(!$item['vendor_id']){
                                $data[$key]["name"] = "Не найдено";
                            }
                            if($item['vendor_price']){
                                $data[$key]["vendor_price"] = number_format($item['vendor_price'], 2, '.', ' ');
                            }
                        }
                        $xlsx = $this->sl->xslx->generateXLSXFile($properties["tabledata"], $data, "copo_file_details_".$properties["id"], 0, $xlsx["config"]);
                    }
                }
                $result["file"] = $xlsx["filename"];

                return $result;
            }
        }

        return array(
            "items" => array(),
            "total" => 0
        );
    }

    public function getOpts($properties){
        $query = $this->modx->newQuery("slStores");
        $query->where(array("slStores.warehouse:=" => 1));
        $query->leftJoin("slWarehouseStores", "slWarehouseStores", "slWarehouseStores.store_id = {$properties["id"]} AND slWarehouseStores.warehouse_id = slStores.id");
        $query->leftJoin("dartLocationCity", "dartLocationCity", "dartLocationCity.id = slStores.city");
        $query->leftJoin("dartLocationRegion", "dartLocationRegion", "dartLocationRegion.id = dartLocationCity.region");

        $this->modx->log(1, print_r($properties, 1));
        if($properties['filtersdata']['region']){
            $geo_data = $this->parseRegions($properties['filtersdata']['region']);
            $criteria = array();
            if ($geo_data["cities"]) {
                $criteria["dartLocationCity.id:IN"] = $geo_data["cities"];
            }
            if ($geo_data["regions"]) {
                $criteria["dartLocationRegion.id:IN"] = $geo_data["regions"];
            }
            if($criteria){
                $query->where($criteria);
            }
        }

        if ($properties['filtersdata']['our']) {
            $query->where(array("slWarehouseStores.store_id:=" => $properties["id"]));
        }

        if ($properties['filter']) {
            $words = explode(" ", $properties['filter']);
            foreach ($words as $word) {
                $criteria = array();
                $criteria['slStores.name:LIKE'] = '%' . trim($word) . '%';
                $criteria['OR:slStores.address:LIKE'] = '%' . trim($word) . '%';
                $query->where($criteria);
            }
        }

        $query->select(array("slStores.*,IF(slWarehouseStores.warehouse_id = slStores.id, 1, 0) as connection, slWarehouseStores.date as connection_date"));
        $result['total'] = $this->modx->getCount('slStores', $query);

        // Устанавливаем лимит 1/10 от общего количества записей
        // со сдвигом 1/20 (offset)
        if($properties['page'] && $properties['perpage']){
            $limit = $properties['perpage'];
            $offset = ($properties['page'] - 1) * $properties['perpage'];
            $query->limit($limit, $offset);
        }

        // И сортируем по ID в обратном порядке
        if($properties['sort']){
            $keys = array_keys($properties['sort']);
            $query->sortby($keys[0], $properties['sort'][$keys[0]]['dir']);
        }else{
            $query->sortby('connection', 'desc');
        }

        if ($query->prepare() && $query->stmt->execute()) {
            $this->modx->log(1, $query->toSQL());
            $result['items'] = $query->stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach($result['items'] as $key => $val){
                if($val["image"]){
                    $result['items'][$key]['image'] = $this->modx->getOption("site_url")."assets/content/".$val["image"];
                }else{
                    $result['items'][$key]['image'] = $this->modx->getOption("site_url").$this->modx->getOption("shoplogistic_blank_image");
                }

                $result['items'][$key]['connection_date'] = date('d.m.Y H:i', strtotime($val['connection_date']));
            }
            return $result;
        }
    }

    public function getBalance($properties){
        $query = $this->modx->newQuery("slStoreBalance");
        $query->where(array("store_id:=" => $properties["id"]));
        // $query->leftJoin("slExportFileStatus", "slExportFileStatus", "slExportFileStatus.id = slExportFiles.status");
        $query->select(array("slStoreBalance.*"));
        $result['total'] = $this->modx->getCount('slStoreBalance', $query);
        // Устанавливаем лимит 1/10 от общего количества записей
        // со сдвигом 1/20 (offset)
        if($properties['page'] && $properties['perpage']){
            $limit = $properties['perpage'];
            $offset = ($properties['page'] - 1) * $properties['perpage'];
            $query->limit($limit, $offset);
        }

        // И сортируем по ID в обратном порядке
        if($properties['sort']){
            $keys = array_keys($properties['sort']);
            $query->sortby($keys[0], $properties['sort'][$keys[0]]['dir']);
        }else{
            $query->sortby('id', 'desc');
        }

        if ($query->prepare() && $query->stmt->execute()) {
            $result['items'] = $query->stmt->fetchAll(PDO::FETCH_ASSOC);
            // add TYPE FILE
            foreach($result['items'] as $key => $val){
                $result['items'][$key]['date'] = date('d.m.Y H:i', strtotime($val['createdon']));
                if($val['type'] == 1){
                    $result['items'][$key]['type'] = "Начисление";
                }
                if($val['type'] == 2){
                    $result['items'][$key]['type'] = "Списание";
                }
                if($val['type'] == 3){
                    $result['items'][$key]['type'] = "Информационное";
                }
                $result['items'][$key]['value'] = number_format($val['value'], 2, '.', ' ');
            }
            return $result;
        }
    }

    public function getBalanceRequest($properties){
        $query = $this->modx->newQuery("slStoreBalancePayRequest");
        $query->where(array("store_id:=" => $properties["id"]));
        $query->leftJoin("slStoreBalancePayRequestStatus", "slStoreBalancePayRequestStatus", "slStoreBalancePayRequestStatus.id = slStoreBalancePayRequest.status");
        $query->select(array("slStoreBalancePayRequest.*, slStoreBalancePayRequestStatus.name as status_name, slStoreBalancePayRequestStatus.color as status_color"));
        $result['total'] = $this->modx->getCount('slStoreBalancePayRequest', $query);
        // Устанавливаем лимит 1/10 от общего количества записей
        // со сдвигом 1/20 (offset)
        if($properties['page'] && $properties['perpage']){
            $limit = $properties['perpage'];
            $offset = ($properties['page'] - 1) * $properties['perpage'];
            $query->limit($limit, $offset);
        }

        // И сортируем по ID в обратном порядке
        if($properties['sort']){
            $keys = array_keys($properties['sort']);
            $query->sortby($keys[0], $properties['sort'][$keys[0]]['dir']);
        }else{
            $query->sortby('id', 'desc');
        }

        if ($query->prepare() && $query->stmt->execute()) {
            $result['items'] = $query->stmt->fetchAll(PDO::FETCH_ASSOC);
            // add TYPE FILE
            foreach($result['items'] as $key => $val){
                $result['items'][$key]['date'] = date('d.m.Y H:i', strtotime($val['date']));
                $result['items'][$key]['value'] = number_format($val['value'], 2, '.', ' ');
            }
            return $result;
        }
    }

    public function checkCode($properties){
        if($properties["code"]){
            $order = $this->modx->getObject("slOrder", $properties['order_id']);
            if($order){
                $code_order = $order->get("code");
                if($properties["code"] == $code_order){
                    return array(
                        "success" => true,
                        "message" => "Код верный, заказ можно выдать"
                    );
                }else{
                    return array(
                        "success" => false,
                        "message" => "Код не верный, попробуйте другой"
                    );
                }
            }
        }
    }

    public function getShipData($properties){
        $query = $this->modx->newQuery("slOrderProduct");
        $query->leftJoin("msProductData", "msProductData", "msProductData.id = slOrderProduct.product_id");
        $query->leftJoin("modResource", "modResource", "modResource.id = slOrderProduct.product_id");
        $query->leftJoin("slOrder", "slOrder", "slOrder.id = slOrderProduct.order_id");
        $query->select(array(
            "modResource.pagetitle as name, msProductData.image as image, msProductData.vendor_article as article, slOrderProduct.count as count, slOrderProduct.price as price"
        ));
        $query->where(array(
            "slOrder.ship_id:=" => $properties['ship_id']
        ));
        $result['total'] = $this->modx->getCount('slOrderProduct', $query);
        // со сдвигом 1/20 (offset)
        if($properties['page'] && $properties['perpage']){
            $limit = $properties['perpage'];
            $offset = ($properties['page'] - 1) * $properties['perpage'];
            $query->limit($limit, $offset);
        }
        if($query->prepare() && $query->stmt->execute()) {
            $result["items"] = $query->stmt->fetchAll(PDO::FETCH_ASSOC);
            return $result;
        }
        return array(
            "total" => 0,
            "items" => array()
        );
    }

    public function getRequests($properties): array
    {
        $result = array();
        $query = $this->modx->newQuery("slCardRequest");
        $query->where(array("remain_id:=" => $properties['product_id']));
        $query->leftJoin("slCardRequestStatus", "slCardRequestStatus", "slCardRequestStatus.id = slCardRequest.status");
        $query->select(array("slCardRequest.*, slCardRequestStatus.name as status_name"));

        $result['total'] = $this->modx->getCount('slCardRequest', $query);
        // Устанавливаем лимит 1/10 от общего количества записей
        // со сдвигом 1/20 (offset)
        if($properties['page'] && $properties['perpage']){
            $limit = $properties['perpage'];
            $offset = ($properties['page'] - 1) * $properties['perpage'];
            $query->limit($limit, $offset);
        }

        // И сортируем по ID в обратном порядке
        if($properties['sort']){
            $keys = array_keys($properties['sort']);
            $query->sortby($keys[0], $properties['sort'][$keys[0]]['dir']);
        }else{
            $query->sortby('slCardRequest.id', 'desc');
        }

        if ($query->prepare() && $query->stmt->execute()) {
            $result['items'] = $query->stmt->fetchAll(PDO::FETCH_ASSOC);
            // add TYPE FILE
            foreach($result['items'] as $key => $val){
                $result['items'][$key]['date'] = date('d.m.Y', strtotime($val['date']));
            }
        }
        return $result;
    }

    public function getRRCReport($properties): array
    {
        $output = array();
        $plan = false;
        $this->modx->log(1, print_r($properties, 1));
        $plan = $this->modx->getObject('slBonusesPlans', $properties['plan_id']);
        if($plan){
            $plan_data = $plan->toArray();
            $store = $this->modx->getObject("slStores", $properties['store_id']);
            $output = $plan_data;
            if($store){
                $output['store_name'] = $store->get("name");
            }
            $output['date_from'] = date('d.m.Y', strtotime($output['date_from']));
            $output['date_to'] = date('d.m.Y', strtotime($output['date_to']));
            $output['period_date'] = $output['date_from'] . ' - ' . $output['date_to'];
            $field = $this->modx->getObject("slReportsTypeFields", $plan_data["report_type_field_id"]);
            $output['summary']['plan'] = $plan_data["report_type_field_value"];
            $output['summary']['fact'] = 0;
            if ($field) {
                $output['summary']['plan_field'] = $field->get("name");
            }
            $products = array();
            if($plan_data['properties']['matrix']){
                $q = $this->modx->newQuery("slStoresMatrixProducts");
                $q->where(array("matrix_id" => $plan_data['properties']['matrix']));
                $q->select(array("slStoresMatrixProducts.product_id"));
                if($q->prepare() && $q->stmt->execute()) {
                    $ps = $q->stmt->fetchAll(PDO::FETCH_ASSOC);
                    foreach($ps as $p) {
                        $products[] = $p['product_id'];
                    }
                }
            }
            $query = $this->modx->newQuery("slStoresRemains");
            $query->select(array("slStoresRemains.*, msProductData.price_rrc, msProductData.image as product_image, msProductData.vendor_article as product_vendor_article, modResource.pagetitle as product_name"));
            $query->leftJoin("msProductData", "msProductData", "msProductData.id = slStoresRemains.product_id");
            $query->leftJoin("modResource", "modResource", "modResource.id = slStoresRemains.product_id");
            $query->where(array(
                "msProductData.id:IN" => $products,
                "msProductData.price_rrc:>" => 0,
                "AND:slStoresRemains.store_id:=" => $properties['store_id']
            ));
            if($properties['filter']){
                $words = explode(" ", $properties['filter']);
                foreach($words as $word){
                    $criteria = array();
                    $criteria['modResource.pagetitle:LIKE'] = '%'.trim($word).'%';
                    $criteria['msProductData.vendor_article:LIKE'] = '%'.trim($word).'%';
                    $query->where($criteria);
                }
            }
            $query->prepare();
            $this->modx->log(1, $query->toSQL());
            if($query->prepare() && $query->stmt->execute()) {
                // echo $query->toSQL()."<br/>";
                $remains = $query->stmt->fetchAll(PDO::FETCH_ASSOC);
                // получили все товары
                foreach ($remains as $remain) {
                    $remain_info = array(
                        "product_image" => $remain['product_image'],
                        "product_vendor_article" => $remain['product_vendor_article'],
                        "product_name" => $remain['product_name'],
                        "remain_id" => $remain['id'],
                        "price_now" => $remain['price'],
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
                        "AND:slStoresRemainsHistory.date:>=" => $plan->get('date_from'),
                        "AND:slStoresRemainsHistory.date:<=" => $plan->get('date_to'),
                    ));
                    if ($query->prepare() && $query->stmt->execute()) {
                        $history = $query->stmt->fetchAll(PDO::FETCH_ASSOC);
                        foreach ($history as $h) {
                            if ($h['price'] != $remain_info['price_rrc']) {
                                $remain_info['violation'] = 1;
                                $output['summary']['fact']++;
                            }
                            $remain_info['prices'][] = array(
                                "date" => date('d.m.Y', strtotime($h['date'])),
                                "price" => $h['price'],
                                "remains" => $h['remains']
                            );
                            if (isset($remain_info['middle_weight_prices'][$h['price']])) {
                                $remain_info['middle_weight_prices'][$h['price']]++;
                            } else {
                                $remain_info['middle_weight_prices'][$h['price']] = 1;
                            }
                            $remain_info['summ_price'] += $h['price'];
                            if ($h['price'] != $remain['price_rrc']) {
                                $remain_info['non_rrc']++;
                            }
                            if ($h['price'] > $remain_info['price_max']) {
                                $remain_info['price_max'] = $h["price"];
                            }
                            if ($h['price'] < $remain_info['price_min']) {
                                $remain_info['price_min'] = $h["price"];
                            }
                        }
                        if (count($remain_info['middle_weight_prices'])) {
                            $summ_price = 0;
                            $summ_days = 0;
                            foreach ($remain_info['middle_weight_prices'] as $key => $val) {
                                $summ_price += $key * $val;
                                $summ_days += $val;
                            }
                            $remain_info['avg_weighted_price'] = round($summ_price / $summ_days, 2);
                        } else {
                            $remain_info['avg_weighted_price'] = $remain_info["current_price"];
                            $remain_info["avg_weighted_price_variation_money"] = abs($remain_info["price_rrc"] - $remain_info['avg_weighted_price']);
                            $remain_info["avg_weighted_price_variation_percent"] = round(abs(($remain_info["avg_weighted_price"] / $remain_info["price_rrc"]) * 100), 2);
                        }
                        if (count($remain_info['prices'])) {
                            $remain_info['avg_price'] = round($remain_info['summ_price'] / count($remain_info['prices']), 2);
                            $remain_info["avg_variation_money"] = abs($remain_info["price_rrc"] - $remain_info['avg_price']);
                            $remain_info["avg_variation_percent"] = round(abs(($remain_info["avg_variation_money"] / $remain_info["price_rrc"]) * 100), 2);
                        } else {
                            $remain_info['price_max'] = $remain_info["current_price"];
                            $remain_info['price_min'] = $remain_info["current_price"];
                        }
                    }
                    $output['items'][] = $remain_info;
                }
            }
        }
        if($output['summary']["fact"] > 0){
            $output["field_value"]["no_percent"] = 0;
            $output["field_value"]["no_percent"] = 0;
            $output["field_value"]["percent"] = 100;
        }else{
            $output["field_value"]["no_percent"] = 0;
            $output["field_value"]["percent"] = 0;
        }
        $output['total'] = count($output['items']);
        return $output;
    }

    public function getStoreMatrixData ($properties): array {
        $result = array(
            "items" => array(),
            "total" => 0
        );
        $plan = $this->modx->getObject('slReports', $properties['report_id']);
        // $plan = $this->modx->getObject('slBonusesPlans', $properties['plan_id']);
        if($plan) {
            $plan_data = $plan->toArray();
            $this->modx->log(1, print_r($plan_data, 1));
            if($plan_data["properties"]["matrix"]) {
                $query = $this->modx->newQuery("slStoresMatrixProducts");
                $query->leftJoin("msProductData", "msProductData", "msProductData.id = slStoresMatrixProducts.product_id");
                $query->leftJoin("modResource", "modResource", "modResource.id = slStoresMatrixProducts.product_id");
                $query->select(array("slStoresMatrixProducts.*, modResource.pagetitle as name, msProductData.vendor_article as product_article, msProductData.image as product_image"));
                $query->where(array("matrix_id" => $plan_data["properties"]["matrix"]));
                if ($query->prepare() && $query->stmt->execute()) {
                    $result['items'] = $query->stmt->fetchAll(PDO::FETCH_ASSOC);
                    $result['total'] = count($result['items']);
                    foreach ($result['items'] as $key => $item) {
                        $result['items'][$key]["plan"] = $item["count"];
                        $query = $this->modx->newQuery('slStoresRemainsHistory');
                        $query->leftJoin("slStoresRemains", "slStoresRemains", "slStoresRemains.id = slStoresRemainsHistory.remain_id");
                        $query->where(array(
                            "slStoresRemains.product_id" => $result['items'][$key]["product_id"],
                            "slStoresRemains.store_id" => $properties["id"],
                            "AND:slStoresRemainsHistory.date:>=" => $plan_data["date_from"],
                            "AND:slStoresRemainsHistory.date:<=" => $plan_data["date_to"]
                        ));
                        $query->select(array("COALESCE(AVG(slStoresRemainsHistory.remains), 0) as fact"));
                        if ($query->prepare() && $query->stmt->execute()) {
                            $it = $query->stmt->fetch(PDO::FETCH_ASSOC);
                            $result['items'][$key]["fact"] = round($it['fact']);
                            $result['items'][$key]['percent'] = round(abs(($result['items'][$key]["fact"] / $result['items'][$key]["plan"]) * 100), 2);
                        }
                    }
                }
            }
        }
        return $result;
    }

    public function getRRCReportData($properties): array{
        $result = array(
            "items" => array(),
            "total" => 0
        );
        $plan = false;
        if($properties['source'] && $properties['elem_id']){
            if($properties['source'] == 'plan'){
                $plan = $this->modx->getObject('slBonusesPlans', $properties['elem_id']);
            }
            if($properties['source'] == 'report'){
                $plan = $this->modx->getObject('slReports', $properties['elem_id']);
            }
        }
        // $plan = $this->modx->getObject('slBonusesPlans', $properties['plan_id']);
        if($plan) {
            $plan_data = $plan->toArray();
            $query = $this->modx->newQuery("msProductData");
            $query->leftJoin("slStoresRemains", "slStoresRemains", "slStoresRemains.product_id = msProductData.id");
            $query->where(array("slStoresRemains.id:=" => $properties['remain_id']));
            $query->select(array("msProductData.price_rrc"));
            $query->prepare();
            $this->modx->log(1, $query->toSQL());
            if ($query->prepare() && $query->stmt->execute()) {
                $product = $query->stmt->fetch(PDO::FETCH_ASSOC);
            }
            $query = $this->modx->newQuery("slStoresRemainsHistory");
            $query->select(array("slStoresRemainsHistory.*"));
            $query->where(array(
                "slStoresRemainsHistory.remain_id:=" => $properties['remain_id'],
                "AND:slStoresRemainsHistory.remains:>" => 0,
                "AND:slStoresRemainsHistory.date:>=" => $plan->get('date_from'),
                "AND:slStoresRemainsHistory.date:<=" => $plan->get('date_to'),
            ));
            $result['total'] = $this->modx->getCount('slStoresRemainsHistory', $query);
            // Устанавливаем лимит 1/10 от общего количества записей
            // со сдвигом 1/20 (offset)
            if($properties['page'] && $properties['perpage']){
                $limit = $properties['perpage'];
                $offset = ($properties['page'] - 1) * $properties['perpage'];
                $query->limit($limit, $offset);
            }

            // И сортируем по ID в обратном порядке
            if($properties['sort']){
                $keys = array_keys($properties['sort']);
                $query->sortby($keys[0], $properties['sort'][$keys[0]]['dir']);
            }else{
                $query->sortby('slStoresRemainsHistory.id', 'ASC');
            }
            if ($query->prepare() && $query->stmt->execute()) {
                $result['items'] = $query->stmt->fetchAll(PDO::FETCH_ASSOC);
                foreach($result['items'] as $key => $item){
                    $result['items'][$key]['date'] = date('d.m.Y', strtotime($item['date']));
                    $result['items'][$key]['variation_money'] = $product['price_rrc'] - $item['price'];
                    $result['items'][$key]['variation_percent'] = round(abs(($result['items'][$key]['variation_money'] / $product['price_rrc']) * 100), 2);
                }
            }
        }
        return $result;
    }

    public function changeObject($properties){
        if($properties['type'] == 'docs'){
            return $this->changeDocs($properties);
        }
        if($properties['type'] == 'report'){
            return $this->changeReport($properties);
        }
        if($properties['type'] == 'bonus_participants'){
            return $this->changeBonusPart($properties);
        }
        return array(
            "total" => 0,
            "items" => array()
        );
    }

    public function changeBonusPart($properties){
        $object = $this->modx->getObject("slBonusesConnection", array("bonus_id" => $properties["bonus_id"], "store_id" => $properties["store"]["store_id"]));
        if($object){
            if($properties['action'] == 'approve'){
                $object->set("status", 2);
            }
            if($properties['action'] == 'disapprove'){
                $object->set("status", 3);
            }
            $object->save();
        }
        return array(
            "total" => 0,
            "items" => array()
        );
    }

    public function changeReport($properties){
        $report = $this->modx->getObject("slReports", $properties['report_id']['id']);
        if($report){
            $id = $report->get('id');
            if(!$this->sl->reports->checkFileBlock($id)){
                $this->sl->reports->createFileBlock($id);
            }
            // теперь запускаем формирование отчетов
            if($report->get("type") == 1){
                $report->set("status", 2);
                $report->save();
                if($this->sl->reports->generateTopSales($id)){
                    $report->set("updatedon", time());
                    $report->set("status", 3);
                    $report->save();
                    $this->sl->reports->deleteFileBlock($id);
                }
            }
            if($report->get("type") == 2){
                $report->set("status", 2);
                $report->save();
                if($this->sl->reports->generatePresent($id)) {
                    $report->set("updatedon", time());
                    $report->set("status", 3);
                    $report->save();
                    $this->sl->reports->deleteFileBlock($id);
                }
            }
            if($report->get("type") == 3){
                $this->sl->reports->set("status", 2);
                $report->save();
                if($this->sl->reports->generateRRC($id)){
                    $report->set("updatedon", time());
                    $report->set("status", 3);
                    $report->save();
                    $this->sl->reports->deleteFileBlock($id);
                }
            }
            if($report->get("type") == 4){
                $report->set("status", 2);
                $report->save();
                if($this->sl->reports->generateWeekSales($id)){
                    $report->set("updatedon", time());
                    $report->set("status", 3);
                    $report->save();
                    $this->sl->reports->deleteFileBlock($id);
                }
            }
        }
        return true;
    }

    public function changeDocs($properties){
        if($properties['doc_id'] == 'all'){
            $docs = $this->modx->getCollection("slDocs", array("status" => 3, "store_id" => $properties['id']));
            foreach($docs as $doc){
                if($doc){
                    $doc->set("status", 4);
                    $doc->save();
                }else{
                    return false;
                }
            }
        }else{
            $doc = $this->modx->getObject("slDocs", array("id" => $properties['doc_id'], "store_id" => $properties['id']));
            if($doc){
                $doc->set("status", 4);
                $doc->save();
            }else{
                return false;
            }
        }
    }

    public function getReportTypes($properties){
        $query = $this->modx->newQuery("slReportsType");
        $query->select(array("slReportsType.*"));
        if($properties['toplan']){
            $query->where(array("slReportsType.toplan:=" => 1));
        }
        // $result['total'] = $this->modx->getCount('slReportsType', $query);
        if ($query->prepare() && $query->stmt->execute()) {
            $results = $query->stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach($results as $key => $result){
                $query = $this->modx->newQuery("slReportsTypeFields");
                $query->where(array("slReportsTypeFields.type:=" => $result['id'], "AND:slReportsTypeFields.field_type:=" => 1));
                $query->select(array("slReportsTypeFields.*"));
                if ($query->prepare() && $query->stmt->execute()) {
                    $results[$key]['fields'] = $query->stmt->fetchAll(PDO::FETCH_ASSOC);
                }
            }
            foreach($results as $key => $result){
                $query = $this->modx->newQuery("slReportsTypeFields");
                $query->where(array("slReportsTypeFields.type:=" => $result['id'], "AND:slReportsTypeFields.field_type:=" => 2));
                $query->select(array("slReportsTypeFields.*"));
                if ($query->prepare() && $query->stmt->execute()) {
                    $results[$key]['params'] = $query->stmt->fetchAll(PDO::FETCH_ASSOC);
                }
            }
            return $results;
        }
    }

    public function getPlan ($properties){
        if($properties['plan_id']) {
            $query = $this->modx->newQuery("slBonusesPlans");
            $query->where(array("id:=" => $properties['plan_id'], "AND:fornew:!=" => 1));
            $query->leftJoin("slReportsType", "slReportsType", "slReportsType.id = slBonusesPlans.report_type_id");
            $query->leftJoin("slStores", "slStores", "slStores.id = slBonusesPlans.store_id");
            $query->leftJoin("slReportsTypeFields", "slReportsTypeFields", "slReportsTypeFields.id = slBonusesPlans.report_type_field_id");
            $query->select(array("slStores.name as store_name, slBonusesPlans.*,slReportsType.name as report_name,slReportsType.id as report_id,slReportsTypeFields.name as report_field_name,slReportsTypeFields.id as report_field_id"));
            $query->prepare();

            if ($query->prepare() && $query->stmt->execute()) {

                $result = $query->stmt->fetch(PDO::FETCH_ASSOC);
                $field = $this->modx->getObject("slReportsTypeFields", $result["report_type_field_id"]);
                $result['summary']['plan'] = $result["report_type_field_value"];
                if ($field) {
                    $result['summary']['plan_field'] = $field->get("name");
                }
                if($result["report_type_id"] == 2){
                    $criteria = array(
                        "slStoresConnection.vendor_id:IN" => array($result["store_id"]),
                        "AND:slStoresConnection.date:>=" => $result['date_from'],
                        "AND:slStoresConnection.date:<=" => $result['date_to'],
                        "AND:slStoresConnection.active:=" => 1,
                        "AND:slStores.active:=" => 1
                    );
                    if(isset($result["properties"]['region'])) {
                        $geo_data = $this->parseRegions($result["properties"]['region']);
                        if ($geo_data["cities"]) {
                            $criteria["AND:dartLocationCity.id:IN"] = explode(",", $geo_data["cities"]);
                        }
                        if ($geo_data["regions"]) {
                            $criteria["AND:dartLocationRegion.id:IN"] = explode(",", $geo_data["regions"]);
                        }
                    }
                    $q = $this->modx->newQuery('slStores');
                    $q->leftJoin("slStoresConnection", "slStoresConnection", "slStoresConnection.store_id = slStores.id");
                    $q->leftJoin("dartLocationCity", "dartLocationCity", "dartLocationCity.id = slStores.city");
                    $q->leftJoin("dartLocationRegion", "dartLocationRegion", "dartLocationRegion.id = dartLocationCity.region");
                    $q->where($criteria);
                    $q->select(array("COUNT(*) as count"));
                    $q->prepare();
                    if($q->prepare() && $q->stmt->execute()){
                        $res = $q->stmt->fetch(PDO::FETCH_ASSOC);
                        if($res["count"]){
                            $result['summary']['fact'] = $res["count"];
                        }else{
                            $result['summary']['fact'] = 0;
                        }
                        $result["field_value"]["no_percent"] = (($result['summary']['plan'] - $result['summary']["fact"]) * 100) / $result['summary']['plan'];
                        $result["field_value"]["no_percent"] = round($result["field_value"]["no_percent"], 2);
                        $result["field_value"]["percent"] = 100 - $result["field_value"]["no_percent"];
                    }
                }

                if($result["report_type_id"] == 3){
                    $query = $this->modx->newQuery("slStoresConnection");
                    $query->leftJoin("slStores", "slStores", "slStores.id = slStoresConnection.store_id");
                    $query->leftJoin("dartLocationCity", "dartLocationCity", "dartLocationCity.id = slStores.city");
                    $query->where(array("vendor_id" => $result["store_id"]));
                    $query->groupby("slStores.city");
                    $query->select(array("dartLocationCity.*"));
                    if($query->prepare() && $query->stmt->execute()) {
                        $res = $query->stmt->fetch(PDO::FETCH_ASSOC);
                        if($res["count"]){
                            $result['summary']['fact'] = $res["count"];
                        }else{
                            $result['summary']['fact'] = 0;
                        }
                        $result["field_value"]["no_percent"] = (($result['summary']['plan'] - $result['summary']["fact"]) * 100) / $result['summary']['plan'];
                        $result["field_value"]["no_percent"] = round($result["field_value"]["no_percent"], 2);
                        $result["field_value"]["percent"] = 100 - $result["field_value"]["no_percent"];
                    }
                }

                if($result["report_type_id"] == 4){
                    $properties['store_id'] = $result["store_id"];
                    $res = $this->getRRCReport($properties);
                    $this->modx->log(1, print_r($res, 1));
                    if($res["field_value"]["no_percent"]){
                        $result["field_value"]["no_percent"] = $res["field_value"]["no_percent"];
                    }else{
                        $result["field_value"]["no_percent"] = 0;
                    }
                    if($res["field_value"]["percent"]){
                        $result["field_value"]["percent"] = $res["field_value"]["percent"];
                    }else{
                        $result["field_value"]["percent"] = 100;
                    }
                    $result["summary"] = $res["summary"];
                }

                $result['date_from'] = date('d.m.Y', strtotime($result['date_from']));
                $result['date_to'] = date('d.m.Y', strtotime($result['date_to']));
                $result['period_date'] = $result['date_from'] . ' - ' . $result['date_to'];
                if($result['period'] == "dayly"){
                    $result['period'] = "Ежедневно";
                }
                if($result['period'] == "weekly"){
                    $result['period'] = "Еженедельно";
                }
                if($result['period'] == "monthly"){
                    $result['period'] = "Ежемесячно";
                }
                return $result;
            }
        }
    }

    public function getPlans($properties) {
        $query = $this->modx->newQuery("slBonusesPlans");
        $query->where(array("bonus_id:=" => $properties['bonus_id']));
        $query->leftJoin("slReportsType", "slReportsType", "slReportsType.id = slBonusesPlans.report_type_id");
        $query->leftJoin("slStores", "slStores", "slStores.id = slBonusesPlans.store_id");
        $query->leftJoin("slReportsTypeFields", "slReportsTypeFields", "slReportsTypeFields.id = slBonusesPlans.report_type_field_id");
        $query->select(array("slStores.name as store_name, slBonusesPlans.*,slReportsType.name as report_name,slReportsType.id as report_id,slReportsTypeFields.name as report_field_name,slReportsTypeFields.id as report_field_id"));
        if($properties['new']){
            $query->where(array("`slBonusesPlans`.`fornew`:=" => 1));
        }else{
            $query->where(array("`slBonusesPlans`.`fornew`:!=" => 1));
        }
        $result['total'] = $this->modx->getCount('slBonusesPlans', $query);
        // Устанавливаем лимит 1/10 от общего количества записей
        // со сдвигом 1/20 (offset)
        if($properties['page'] && $properties['perpage']){
            $limit = $properties['perpage'];
            $offset = ($properties['page'] - 1) * $properties['perpage'];
            $query->limit($limit, $offset);
        }

        // И сортируем по ID в обратном порядке
        if($properties['sort']){
            $keys = array_keys($properties['sort']);
            $query->sortby($keys[0], $properties['sort'][$keys[0]]['dir']);
        }else{
            $query->sortby('slBonusesPlans.id', 'desc');
        }

        if($properties['filter']){
            $words = explode(" ", $properties['filter']);
            foreach($words as $word){
                $criteria = array();
                $criteria['slBonusesPlans.name:LIKE'] = '%'.trim($word).'%';
                $query->where($criteria);
            }
        }

        if($properties['filtersdata']){
            if($properties['filtersdata']['date']){
                if($properties['filtersdata']['date'][0] && $properties['filtersdata']['date'][1]){
                    $from = date('Y-m-d H:i:s', strtotime($properties['filtersdata']['date'][0]));
                    $to = date('Y-m-d H:i:s', strtotime($properties['filtersdata']['date'][1]));
                    $query->where(array("`slBonusesPlans`.`date`:>" => $from, "`slBonusesConnection`.`date`:<" => $to));
                }
                if($properties['filtersdata']['date'][0] && !$properties['filtersdata']['date'][1]){
                    $from = date('Y-m-d H:i:s', strtotime($properties['filtersdata']['date'][0]));
                    $query->where(array("`slBonusesPlans`.`date`:>" => $from));
                }
            }
        }

        $query->prepare();
        $this->modx->log(1, $query->toSQL());
        if ($query->prepare() && $query->stmt->execute()) {
            $result['items'] = $query->stmt->fetchAll(PDO::FETCH_ASSOC);
            // add TYPE FILE
            foreach($result['items'] as $key => $val){
                $result['items'][$key]['date_from'] = date('d.m.Y', strtotime($val['date_from']));
                $result['items'][$key]['date_to'] = date('d.m.Y', strtotime($val['date_to']));
                $result['items'][$key]['period_date'] = $result['items'][$key]['date_from'] . ' - ' . $result['items'][$key]['date_to'];
                if($val['period'] == "dayly"){
                    $result['items'][$key]['period'] = "Ежедневно";
                }
                if($val['period'] == "weekly"){
                    $result['items'][$key]['period'] = "Еженедельно";
                }
                if($val['period'] == "monthly"){
                    $result['items'][$key]['period'] = "Ежемесячно";
                }
            }
            return $result;
        }
        return array(
            "items" => array(),
            "total" => 0
        );
    }

    // TODO: optimize function
    public function getDocsStatus($properties){
        $query = $this->modx->newQuery("slDocsStatus");
        $query->select(array("slDocsStatus.*"));
        $result['total'] = $this->modx->getCount('slDocsStatus', $query);
        if ($query->prepare() && $query->stmt->execute()) {
            $result['items'] = $query->stmt->fetchAll(PDO::FETCH_ASSOC);
            return $result;
        }
    }

    public function getStatus($object, $properties){
        $query = $this->modx->newQuery($object);
        $query->select(array("{$object}.*"));
        $result['total'] = $this->modx->getCount($object, $query);
        if ($query->prepare() && $query->stmt->execute()) {
            $result['items'] = $query->stmt->fetchAll(PDO::FETCH_ASSOC);
            return $result;
        }
    }

    public function getShippingStatus($properties){
        $query = $this->modx->newQuery("slWarehouseShipmentStatus");
        $query->select(array("slWarehouseShipmentStatus.*"));
        $result['total'] = $this->modx->getCount('slWarehouseShipmentStatus', $query);
        if ($query->prepare() && $query->stmt->execute()) {
            $result['items'] = $query->stmt->fetchAll(PDO::FETCH_ASSOC);
            return $result;
        }
    }

    public function getBonusesParts($properties) {
        $query = $this->modx->newQuery("slBonusesConnection");
        $query->where(array("bonus_id:=" => $properties['bonus_id']));
        $query->leftJoin("slStores", "slStores", "slStores.id = slBonusesConnection.store_id");
        $query->leftJoin("slBonusesConnectionStatus", "slBonusesConnectionStatus", "slBonusesConnectionStatus.id = slBonusesConnection.status");
        $query->select(array("slBonusesConnection.status,slBonusesConnection.store_id,slBonusesConnection.date,slStores.name,slStores.address,slBonusesConnectionStatus.name as status_name, slBonusesConnectionStatus.color as status_color"));
        $result['total'] = $this->modx->getCount('slBonusesConnection', $query);
        // Устанавливаем лимит 1/10 от общего количества записей
        // со сдвигом 1/20 (offset)
        if($properties['page'] && $properties['perpage']){
            $limit = $properties['perpage'];
            $offset = ($properties['page'] - 1) * $properties['perpage'];
            $query->limit($limit, $offset);
        }

        // И сортируем по ID в обратном порядке
        if($properties['sort']){
            $keys = array_keys($properties['sort']);
            $query->sortby($keys[0], $properties['sort'][$keys[0]]['dir']);
        }else{
            $query->sortby('slBonusesConnection.id', 'desc');
        }

        if($properties['filter']){
            $words = explode(" ", $properties['filter']);
            foreach($words as $word){
                $criteria = array();
                $criteria['slStores.name:LIKE'] = '%'.trim($word).'%';
                $criteria['OR:slStores.address:LIKE'] = '%'.trim($word).'%';
                $query->where($criteria);
            }
        }

        if($properties['filtersdata']){
            if($properties['filtersdata']['date']){
                if($properties['filtersdata']['date'][0] && $properties['filtersdata']['date'][1]){
                    $from = date('Y-m-d H:i:s', strtotime($properties['filtersdata']['date'][0]));
                    $to = date('Y-m-d H:i:s', strtotime($properties['filtersdata']['date'][1]));
                    $query->where(array("`slBonusesConnection`.`date`:>" => $from, "`slBonusesConnection`.`date`:<" => $to));
                }
                if($properties['filtersdata']['date'][0] && !$properties['filtersdata']['date'][1]){
                    $from = date('Y-m-d H:i:s', strtotime($properties['filtersdata']['date'][0]));
                    $query->where(array("`slBonusesConnection`.`date`:>" => $from));
                }
            }
        }
        $query->prepare();
        $this->modx->log(1, $query->toSQL());
        if ($query->prepare() && $query->stmt->execute()) {
            $result['items'] = $query->stmt->fetchAll(PDO::FETCH_ASSOC);
            // add TYPE FILE
            foreach($result['items'] as $key => $val){
                $result['items'][$key]['date'] = date('d.m.Y', strtotime($val['date']));
            }
            return $result;
        }
        return array(
            "items" => array(),
            "total" => 0
        );
    }

    public function getDocs($properties) {
        $query = $this->modx->newQuery("slDocs");
        $query->where(array("FIND_IN_SET({$properties["id"]}, store_id) > 0", "OR:global:=" => 1));
        $query->leftJoin("slDocsStatus", "slDocsStatus", "slDocsStatus.id = slDocs.status");
        $query->select(array("slDocs.*,slDocsStatus.name as status_name,slDocsStatus.has_action as has_action,slDocsStatus.has_submit as has_submit"));
        $result['total'] = $this->modx->getCount('slDocs', $query);
        $result['submitted'] = 0;
        // Устанавливаем лимит 1/10 от общего количества записей
        // со сдвигом 1/20 (offset)
        if($properties['page'] && $properties['perpage']){
            $limit = $properties['perpage'];
            $offset = ($properties['page'] - 1) * $properties['perpage'];
            $query->limit($limit, $offset);
        }

        // И сортируем по ID в обратном порядке
        if($properties['sort']){
            $keys = array_keys($properties['sort']);
            $query->sortby($keys[0], $properties['sort'][$keys[0]]['dir']);
        }else{
            $query->sortby('id', 'desc');
        }

        if($properties['filter']){
            $words = explode(" ", $properties['filter']);
            foreach($words as $word){
                $criteria = array();
                $criteria['slDocs.name:LIKE'] = '%'.trim($word).'%';
                $criteria['OR:slDocs.description:LIKE'] = '%'.trim($word).'%';
                $query->where($criteria);
            }
        }

        if($properties['filtersdata']){
            if($properties['filtersdata']['status']){
                $query->where(array(
                    "slDocs.status:=" => $properties['filtersdata']['status']
                ));
            }
        }
        $query->prepare();
        $this->modx->log(1, $query->toSQL());
        if ($query->prepare() && $query->stmt->execute()) {

            $result['items'] = $query->stmt->fetchAll(PDO::FETCH_ASSOC);
            // add TYPE FILE
            foreach($result['items'] as $key => $val){
                $result['items'][$key]['site_url'] = $this->modx->getOption("site_url");
                $result['items'][$key]['file'] = $this->modx->getOption("site_url").'assets/files/'.$val['file'];
                $result['items'][$key]['file_type'] = $this->getFileType('assets/files/'.$val['file']);
                $result['items'][$key]['date'] = date('d.m.Y', strtotime($val['date']));
                $result['items'][$key]['has_action'] = intval($val['has_action']);
                $result['items'][$key]['has_submit'] = intval($val['has_submit']);
                if($result['items'][$key]['has_submit']){
                    $result['submitted']++;
                }
            }
            return $result;
        }
    }

    public function getFeeds($properties) {
        $query = $this->modx->newQuery("slExportFiles");
        $query->where(array("store_id:=" => $properties["id"]));
        $query->leftJoin("slExportFileStatus", "slExportFileStatus", "slExportFileStatus.id = slExportFiles.status");
        $query->select(array("slExportFiles.*,slExportFileStatus.name as status_name,slExportFileStatus.has_action as has_action,slExportFileStatus.has_submit as has_submit"));
        $result['total'] = $this->modx->getCount('slExportFiles', $query);
        $result['submitted'] = 0;
        // Устанавливаем лимит 1/10 от общего количества записей
        // со сдвигом 1/20 (offset)
        if($properties['page'] && $properties['perpage']){
            $limit = $properties['perpage'];
            $offset = ($properties['page'] - 1) * $properties['perpage'];
            $query->limit($limit, $offset);
        }

        // И сортируем по ID в обратном порядке
        if($properties['sort']){
            $keys = array_keys($properties['sort']);
            $query->sortby($keys[0], $properties['sort'][$keys[0]]['dir']);
        }else{
            $query->sortby('id', 'desc');
        }

        if($properties['filter']){
            $words = explode(" ", $properties['filter']);
            foreach($words as $word){
                $criteria = array();
                $criteria['slExportFiles.name:LIKE'] = '%'.trim($word).'%';
                $criteria['OR:slExportFiles.description:LIKE'] = '%'.trim($word).'%';
                $query->where($criteria);
            }
        }

        if($properties['filtersdata']){
            if($properties['filtersdata']['status']){
                $query->where(array(
                    "slExportFiles.status:=" => $properties['filtersdata']['status']
                ));
            }
        }
        $query->prepare();
        $this->modx->log(1, $query->toSQL());
        if ($query->prepare() && $query->stmt->execute()) {
            $result['items'] = $query->stmt->fetchAll(PDO::FETCH_ASSOC);
            // add TYPE FILE
            foreach($result['items'] as $key => $val){
                $result['items'][$key]['date'] = date('d.m.Y', strtotime($val['date']));
                $result['items'][$key]['has_action'] = intval($val['has_action']);
                $result['items'][$key]['has_submit'] = intval($val['has_submit']);
            }
            return $result;
        }
    }

    public function getProgramFiles($properties) {
        $query = $this->modx->newQuery("slBonusDocs");
        $query->where(array("bonus_id:=" => $properties["bonus_id"]));
        $query->select(array("slBonusDocs.*"));
        $result['total'] = $this->modx->getCount('slBonusDocs', $query);
        // Устанавливаем лимит 1/10 от общего количества записей
        // со сдвигом 1/20 (offset)
        if($properties['page'] && $properties['perpage']){
            $limit = $properties['perpage'];
            $offset = ($properties['page'] - 1) * $properties['perpage'];
            $query->limit($limit, $offset);
        }

        // И сортируем по ID в обратном порядке
        if($properties['sort']){
            $keys = array_keys($properties['sort']);
            $query->sortby($keys[0], $properties['sort'][$keys[0]]['dir']);
        }else{
            $query->sortby('id', 'desc');
        }

        if($properties['filter']){
            $words = explode(" ", $properties['filter']);
            foreach($words as $word){
                $criteria = array();
                $criteria['slBonusDocs.name:LIKE'] = '%'.trim($word).'%';
                $criteria['OR:slBonusDocs.description:LIKE'] = '%'.trim($word).'%';
                $query->where($criteria);
            }
        }
        $query->prepare();
        $this->modx->log(1, $query->toSQL());
        if ($query->prepare() && $query->stmt->execute()) {
            $result['items'] = $query->stmt->fetchAll(PDO::FETCH_ASSOC);
            // add FILES CHECK
            foreach($result['items'] as $key => $val){
                $result['items'][$key]['file'] = $this->modx->getOption("site_url").$result['items'][$key]['file'];
            }
            return $result;
        }
    }

    public function getFileType($file){
        $data = mime_content_type($this->modx->getOption("base_path") . $file);
        if($data == "application/pdf"){
            return "pdf";
        }
        if($data == "image/png"){
            return "png";
        }
        if($data == "image/jpeg"){
            return "jpg";
        }
        if($data == "application/msword" || $data == "application/vnd.ms-word.document.macroenabled.12" || $data == "application/vnd.openxmlformats-officedocument.wordprocessingml.document"){
            return "doc";
        }
        if($data == "application/vnd.ms-excel" || $data == "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"){
            return "xls";
        }
    }

    public function getAkbDotsPlan($properties) {
        $query = $this->modx->newQuery("slStoresAkbDotsPlan");
        $query->where(array("store_id" => $properties["id"]));
        $query->select(array("slStoresAkbDotsPlan.*"));
        $result['total'] = $this->modx->getCount('slStoresAkbDotsPlan', $query);

        // Устанавливаем лимит 1/10 от общего количества записей
        // со сдвигом 1/20 (offset)
        if($properties['page'] && $properties['perpage']){
            $limit = $properties['perpage'];
            $offset = ($properties['page'] - 1) * $properties['perpage'];
            $query->limit($limit, $offset);
        }

        // И сортируем по ID в обратном порядке
        if($properties['sort']){
            $keys = array_keys($properties['sort']);
            $query->sortby($keys[0], $properties['sort'][$keys[0]]['dir']);
        }else{
            $query->sortby('id', 'desc');
        }
        if ($query->prepare() && $query->stmt->execute()) {
            $result['items'] = $query->stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach($result['items'] as $key => $value){
                $name = array();
                $regions = explode(",", $value["region"]);
                $cities = explode(",", $value["city"]);
                foreach($regions as $region){
                    $r = $this->modx->getObject("dartLocationRegion", $region);
                    if($r){
                        $name[] = $r->get("name");
                    }
                }
                foreach($cities as $city){
                    $c = $this->modx->getObject("dartLocationCity", $city);
                    if($c){
                        $name[] = $c->get("city");
                    }
                }
                $dt = new DateTime();
                $dt->setTimestamp(strtotime($value['date']));
                $from = $dt->modify('first day of this month')->setTime(00,00)->format('Y/m/d H:i:s');
                $to = $dt->modify('last day of this month')->setTime(23,59)->format('Y/m/d H:i:s');
                // get plan
                $criteria = array(
                    "slStoresConnection.vendor_id:IN" => array($properties['id']),
                    "AND:slStoresConnection.date:>=" => $from,
                    "AND:slStoresConnection.date:<=" => $to,
                    "AND:slStoresConnection.active:=" => 1,
                    "AND:slStores.active:=" => 1
                );
                if($value["city"]){
                    $criteria["AND:dartLocationCity.id:IN"] = explode(",", $value["city"]);
                }
                if($value["region"]){
                    $criteria["AND:dartLocationRegion.id:IN"] = explode(",", $value["region"]);
                }
                $q = $this->modx->newQuery('slStores');
                $q->leftJoin("slStoresConnection", "slStoresConnection", "slStoresConnection.store_id = slStores.id");
                $q->leftJoin("dartLocationCity", "dartLocationCity", "dartLocationCity.id = slStores.city");
                $q->leftJoin("dartLocationRegion", "dartLocationRegion", "dartLocationRegion.id = dartLocationCity.region");
                $q->where($criteria);
                $q->select(array("COUNT(*) as count"));
                $q->prepare();
                $this->modx->log(1, $q->toSQL());
                if($q->prepare() && $q->stmt->execute()){
                    $res = $q->stmt->fetch(PDO::FETCH_ASSOC);
                    if($res["count"]){
                        $result['items'][$key]['fact_akb'] = $res["count"];
                    }else{
                        $result['items'][$key]['fact_akb'] = 0;
                    }
                }
                $result['items'][$key]['date'] = date('m.Y', strtotime($value['date']));
                $result['items'][$key]["name"] = implode(", ", $name);
            }
            return $result;
        }
    }

    public function getAkbData ($properties) {
        // надо взять общее кол-во точек
        // надо взять общее кол-во населенных пунктов
        $output = array();
        if($properties["id"]){
            $query = $this->modx->newQuery("slStoresConnection");
            $query->where(array("vendor_id" => $properties["id"]));
            $query->select(array("COUNT(*) as dots"));
            if($query->prepare() && $query->stmt->execute()){
                $res = $query->stmt->fetch(PDO::FETCH_ASSOC);
                $output['dots'] = $res['dots'];
            }
            $query = $this->modx->newQuery("slStoresConnection");
            $query->leftJoin("slStores", "slStores", "slStores.id = slStoresConnection.store_id");
            $query->leftJoin("dartLocationCity", "dartLocationCity", "dartLocationCity.id = slStores.city");
            $query->where(array("vendor_id" => $properties["id"]));
            $query->select(array("COUNT(*) as cities"));
            $query->groupby(array("slStores.city"));
            if($query->prepare() && $query->stmt->execute()){
                $res = $query->stmt->fetch(PDO::FETCH_ASSOC);
                $output['cities'] = $res['cities'];
            }
        }
        return $output;
    }

    public function getLastMonth () {
        // get 12 months
        $arr = [
            'январь',
            'февраль',
            'март',
            'апрель',
            'май',
            'июнь',
            'июль',
            'август',
            'сентябрь',
            'октябрь',
            'ноябрь',
            'декабрь'
        ];
        $dateTime = new DateTime();
        $months = array();
        for($i = 1; $i < 13; $i++){
            $dt = new DateTime();
            $dt->setTimestamp($dateTime->getTimestamp());
            $month = $dateTime->format('n')-1;
            $months[$arr[$month].$dateTime->format(', Y')] = array(
                $dt->modify('first day of this month')->setTime(00,00)->format('Y/m/d H:i:s'),
                $dt->modify('last day of this month')->setTime(23,59)->format('Y/m/d H:i:s')
            );
            $dateTime->modify("-1 month");
        }
        $months = array_reverse($months);
        return $months;
    }

    public function getAkbSettlements($properties) {
        $output = array(
            "items" => array(),
            "total" => 0
        );
        $query = $this->modx->newQuery("slStoresConnection");
        $query->leftJoin("slStores", "slStores", "slStores.id = slStoresConnection.store_id");
        $query->leftJoin("dartLocationCity", "dartLocationCity", "dartLocationCity.id = slStores.city");
        $query->where(array("vendor_id" => $properties["id"]));
        $query->groupby("slStores.city");
        $query->select(array("dartLocationCity.*"));
        if($properties['page'] && $properties['perpage']){
            $limit = $properties['perpage'];
            $offset = ($properties['page'] - 1) * $properties['perpage'];
            $query->limit($limit, $offset);
        }
        if($properties['sort']){
            $keys = array_keys($properties['sort']);
            $query->sortby($keys[0], $properties['sort'][$keys[0]]['dir']);
        }else{
            $query->sortby('dartLocationCity.city', 'ASC');
        }
        if($query->prepare() && $query->stmt->execute()){
            $res = $query->stmt->fetchAll(PDO::FETCH_ASSOC);
            $output['items'] = $res;
            // TODO: change fix
            $output['total'] = count($output['items']);
            $months = $this->getLastMonth();
            foreach($output['items'] as $key => $val){

                foreach($months as $k => $v) {
                    $timestamp = strtotime($v[0]);
                    $mon = date("m", $timestamp);
                    // collect connection count
                    $criteria = array(
                        "slStoresConnection.vendor_id:IN" => array($properties['id']),
                        "AND:slStoresConnection.date:>=" => $v[0],
                        "AND:slStoresConnection.date:<=" => $v[1],
                        "AND:slStoresConnection.active:=" => 1,
                        "AND:slStores.active:=" => 1,
                        "AND:slStores.city:=" => $val['id']
                    );
                    $query = $this->modx->newQuery("slStores");
                    $query->leftJoin("slStoresConnection", "slStoresConnection", "slStoresConnection.store_id = slStores.id");
                    $query->where($criteria);
                    $query->select(array("COUNT(*) as count"));
                    $query->prepare();
                    $this->modx->log(1, $query->toSQL());
                    if ($query->prepare() && $query->stmt->execute()) {
                        $stores = $query->stmt->fetch(PDO::FETCH_ASSOC);
                        if($stores['count']){
                            $output['items'][$key]["month_".$mon] = $stores["count"];
                        }else{
                            $output['items'][$key]["month_".$mon] = 0;
                        }
                    }
                }
            }
        }
        return $output;
    }

    public function getAkbPunkts($properties) {
        $output = array(
            "items" => array(),
            "total" => 0
        );
        $months = $this->getLastMonth();
        foreach($months as $key => $val){
            // collect connection count stores
            $criteria = array(
                "slStoresConnection.vendor_id:IN" => array($properties['id']),
                "AND:slStoresConnection.date:>=" => $val[0],
                "AND:slStoresConnection.date:<=" => $val[1],
                "AND:slStoresConnection.active:=" => 1,
                "AND:slStores.active:=" => 1
            );
            $query = $this->modx->newQuery("slStores");
            $query->leftJoin("slStoresConnection", "slStoresConnection", "slStoresConnection.store_id = slStores.id");
            $query->leftJoin("dartLocationCity", "dartLocationCity", "dartLocationCity.id = slStores.city");
            $query->where($criteria);
            $query->select(array("slStores.*, dartLocationCity.city as city"));
            if($query->prepare() && $query->stmt->execute()){
                $stores = $query->stmt->fetchAll();
                $months[$key]["stores"] = array();
                foreach($stores as $store){
                    $months[$key]["stores"][] = $store['name'];
                    $months[$key]["cities"][] = $store['city'];
                }
            }
            $cities = array_unique($months[$key]["cities"]);
            $output['total'] += count($months[$key]["stores"]);
            $output['city_total'] += count($cities);

            $output["items"][] = array(
              "month" => $key,
              "stores" => implode("; ", $months[$key]["stores"]),
              "cities" => implode("; ", $cities)
            );
        }
        // collect all connections
        $criteria = array(
            "slStoresConnection.vendor_id:IN" => array($properties['id']),
            "AND:slStoresConnection.active:=" => 1,
            "AND:slStores.active:=" => 1
        );
        $query = $this->modx->newQuery("slStores");
        $query->leftJoin("slStoresConnection", "slStoresConnection", "slStoresConnection.store_id = slStores.id");
        $query->leftJoin("dartLocationCity", "dartLocationCity", "dartLocationCity.id = slStores.city");
        $query->where($criteria);
        $query->select(array("slStores.*, dartLocationCity.city as city_name, dartLocationCity.properties as city_properties"));
        if($query->prepare() && $query->stmt->execute()){
            $stores = $query->stmt->fetchAll(PDO::FETCH_ASSOC);
            $output["all_stores"] = array();
            $output["all_cities"] = array();
            foreach($stores as $store){
                $tmp = $store;
                $tmp['coords'] = array(
                    $tmp['lat'],
                    $tmp['lng']
                );
                $tmp['city_properties'] = json_decode($store['city_properties'], 1);
                $this->modx->log(1, print_r($tmp, 1));
                $output["all_stores"][] = $tmp;
                $output["all_cities"][$store['city']] = array(
                    "name" => $store['city_name'],
                    "coords" => array(
                        $tmp['city_properties']["geo_lat"],
                        $tmp['city_properties']["geo_lon"]
                    )
                );
            }
        }
        // $output['total'] = count($output["items"]);
        return $output;
    }

    public function loadData($properties){
        $output = array();
        if($this->config['loaddata'] == 'demo'){
            $stores[] = 5;
            $stores[] = 6;
        }
        if($properties['stores']){
            $stores = $properties['store'];
        }
        if($stores){
            // берем магазины
            $q = $this->modx->newQuery("slStores");
            $q->leftJoin("dartLocationCity", "dartLocationCity", "dartLocationCity.id = slStores.city");
            $q->leftJoin("dartLocationRegion", "dartLocationRegion", "dartLocationRegion.id = dartLocationCity.region");
            $q->select(array("slStores.id as id, slStores.name as name, slStores.address as address, slStores.coordinats as coordinats, dartLocationCity.city as city, dartLocationRegion.name as region"));
            $q->where(array(
                "id:IN" => $stores
            ));
            $q->prepare();
            $this->modx->log(1, $q->toSQL());
            if($q->prepare() && $q->stmt->execute()){
                $stores = $q->stmt->fetchAll(PDO::FETCH_ASSOC);
                foreach($stores as $key => $store) {
                    $output['stores'][] = $store;
                }
                // продажи
                foreach ($output['stores'] as $key => $str) {
                    $query = $this->modx->newQuery("slStoreDocsProducts");
                    $query->leftJoin("slStoreDocs", "slStoreDocs", "slStoreDocs.id = slStoreDocsProducts.doc_id");
                    $query->leftJoin("slStoresRemains", "slStoresRemains", "slStoresRemains.id = slStoreDocsProducts.remain_id");
                    $query->leftJoin("msProductData", "msProductData", "msProductData.id = slStoresRemains.product_id");
                    $query->leftJoin("msVendor", "msVendor", "msVendor.id = msProductData.vendor");
                    $query->leftJoin("modResource", "modResource", "modResource.id = slStoresRemains.product_id");
                    $query->select(array("msProductData.vendor_article as article, modResource.pagetitle as name, msVendor.name as vendor_name, slStoreDocs.guid as doc_guid, slStoreDocs.date as date, slStoreDocsProducts.count as count, slStoreDocsProducts.price as price"));
                    $query->where(array(
                        "slStoreDocs.store_id:=" => $str['id'],
                        "AND:slStoresRemains.product_id:!=" => 0,
                        "AND:msProductData.vendor_article:!=" => ""
                    ));
                    if ($query->prepare() && $query->stmt->execute()) {
                        $sales = $query->stmt->fetchAll(PDO::FETCH_ASSOC);
                        foreach ($sales as $sale) {
                            $sale = array_merge(array('store' => $str['name']), $sale);
                            $output['sales'][] = $sale;
                        }
                    }
                }
                // остатки
                foreach ($output['stores'] as $key => $str) {
                    $query = $this->modx->newQuery("slStoresRemains");
                    $query->leftJoin("msProductData", "msProductData", "msProductData.id = slStoresRemains.product_id");
                    $query->leftJoin("msVendor", "msVendor", "msVendor.id = msProductData.vendor");
                    $query->leftJoin("modResource", "modResource", "modResource.id = slStoresRemains.product_id");
                    $query->select(array("msProductData.vendor_article as article, modResource.pagetitle as name, msVendor.name as vendor_name, slStoresRemains.remains, slStoresRemains.reserved, slStoresRemains.available, slStoresRemains.price"));
                    $query->where(array(
                        "slStoresRemains.store_id:=" => $str['id'],
                        "AND:slStoresRemains.product_id:!=" => 0,
                        "AND:msProductData.vendor_article:!=" => ""
                    ));
                    if ($query->prepare() && $query->stmt->execute()) {
                        $remains = $query->stmt->fetchAll(PDO::FETCH_ASSOC);
                        foreach ($remains as $remain) {
                            $sale = array_merge(array('store' => $str['name']), $remain);
                            $output['remains'][] = $sale;
                        }
                    }
                }
                foreach($output['stores'] as $key => $store) {
                    unset($output['stores'][$key]['id']);
                }
            }
        }
        return $output;
    }

    public function fileUpload($properties){
        // $this->modx->log(1, print_r($properties, 1));
        // $this->modx->log(1, print_r($_FILES, 1));
        $output = array();
        if(count($_FILES)){
            // сначала грузим во временное хранилище
            if($properties['store_id']){
                $tmp_path = $this->modx->getOption('base_path').'assets/components/shoplogistic/tmp/'.$properties['store_id'].'/';
                $tmp_url = 'assets/components/shoplogistic/tmp/'.$properties['store_id'].'/';
                if(!file_exists($tmp_path)){
                    mkdir($tmp_path, 0777, true);
                }else{
                    // удаляем все временное
                    $files = glob($tmp_path.'*');
                    foreach($files as $file){
                        if(is_file($file)) {
                            unlink($file);
                        }
                    }
                }
                foreach($_FILES as $key => $file){
                    foreach($file['name'] as $k => $v){
                        $target = $tmp_path . basename($file['name'][$k]);
                        if (move_uploaded_file($file['tmp_name'][$k], $target)) {
                            $small_file = $this->modx->runSnippet("phpThumbOn", array(
                                "input" => $tmp_path . basename($file['name'][$k]),
                                "options" => "w=300&zc=1"
                            ));
                            $output['files'][] = array(
                                "name" => basename($file['name'][$k]),
                                "original" => $tmp_url . basename($file['name'][$k]),
                                "original_href" => str_replace("//assets", "/assets", $this->modx->getOption('site_url') . $tmp_url . basename($file['name'][$k])),
                                "thumb" => str_replace("//assets", "/assets", $this->modx->getOption('site_url') . $small_file),
                                "path" => $properties['path']
                            );
                        }
                    }
                }
            }else{
                // уведомление, что не указана организация
            }
        }else{
            // уведомление, что файлов нет
            return false;
        }
        return $output;
    }

    public function setObjects($properties){
        $response = array();
        if($properties['type'] == 'feed' && $properties['action'] == 'set'){
            $response = $this->setFeed($properties);
        }
        if($properties['type'] == 'programfile' && $properties['action'] == 'set'){
            $response = $this->setProgramFile($properties);
        }
        if($properties['type'] == 'request' && $properties['action'] == 'set'){
            $response = $this->setRequest($properties);
        }
        if($properties['type'] == 'plan' && $properties['action'] == 'set'){
            $response = $this->setPlan($properties);
        }
        if($properties['type'] == 'bonus' && $properties['action'] == 'set'){
            $response = $this->sl->program->set($properties);
        }
        if($properties['type'] == 'organization' && $properties['action'] == 'set'){
            $response = $this->setOrganization($properties);
        }
        if($properties['type'] == 'bonus_connection' && $properties['action'] == 'set') {
            $response = $this->setBonusConnection($properties);
        }
        if($properties['type'] == 'akbdotsplan' && $properties['action'] == 'set') {
            $response = $this->setAkbDotsPlan($properties);
        }
        if($properties['type'] == 'akbsettlementplan' && $properties['action'] == 'set') {
            $response = $this->setAkbSettlementPlan($properties);
        }
        if($properties['type'] == 'balance_request' && $properties['action'] == 'set') {
            $response = $this->setBalanceRequest($properties);
        }
        if($properties['type'] == 'toggleOpts') {
            $response = $this->toggleOpts($properties);
        }
        if($properties['type'] == 'work_week' && $properties['action'] == 'set'){
            $response = $this->sl->store->setWork($properties);
        }
        if($properties['type'] == 'work_week_date' && $properties['action'] == 'set'){
            $response = $this->sl->store->setWorkDate($properties);
        }
        return $response;
    }

    public function toggleOpts($properties)
    {
        // $this->modx->log(1, print_r($properties, 1));
        if($properties['action']){
            // установить
            $object = $this->modx->newObject("slWarehouseStores");
            $object->set("store_id", $properties["store"]);
            $object->set("warehouse_id", $properties["id"]);
            $object->set("description", "Установлено через ЛК магазина");
            $object->set("sync", 0);
            $object->set("date", time());
            $object->save();
            return $this->sl->success("Объект создан", $object->toArray());
        }else{
            // удалить
            $object = $this->modx->getObject("slWarehouseStores", array("store_id" => $properties["store"], "warehouse_id" => $properties["id"]));
            if($object){
                $data = $object->toArray();
                $object->remove();
            }
            return $this->sl->success("Объект удален", $data);
        }

    }

    public function setBalanceRequest($properties){
        if($properties['id']){
            $store = $this->sl->getObject($properties['id']);
            $user_id = $_SESSION['analytics_user']['profile']['id'];
            $object = $this->modx->newObject("slStoreBalancePayRequest");
            $object->set("name", $properties["form"]["name"]);
            $object->set("phone", $properties["form"]["phone"]);
            $object->set("description", $properties["form"]["description"]);
            $object->set("store_id", $properties["id"]);
            $object->set("status", 1);
            $object->set("value", $store['balance']);
            $object->set("date", time());
            $object->set("createdon", time());
            $object->set("createdby", $user_id);
            $object->save();
            return $object->toArray();
        }
    }

    public function setRequest($properties){
        $user_id = $_SESSION['analytics_user']['profile']['id'];
        $object = $this->modx->newObject("slCardRequest");
        $object->set("name", $properties["data"]["name"]);
        $object->set("url", $properties["data"]["url"]);
        $object->set("description", $properties["data"]["description"]);
        $object->set("store_id", $properties["id"]);
        $object->set("remain_id", $properties["product_id"]);
        $object->set("status", 1);
        $object->set("date", time());
        $object->set("createdon", time());
        $object->set("createdby", $user_id);
        $object->save();
        return array();
    }

    public function setFeed($properties) {
        $obj = 0;
        $user_id = $_SESSION['analytics_user']['profile']['id'];
        if($properties['id']){
            if($properties['data']['id']){
                $obj = $this->modx->getObject("slExportFiles", $properties['data']['id']);
                if($obj){
                    $obj->set("updatedon", time());
                    $obj->set("updatedby", $user_id);
                }
            }
            if(!$obj){
                $obj = $this->modx->newObject("slExportFiles");
                if($obj){
                    $obj->set("date", time());
                    $obj->set("createdon", time());
                    $obj->set("createdby", $user_id);
                    $obj->set("store_id", $properties['id']);
                }
            }
            $obj->set("name", $properties['data']['name']);
            $obj->set("status", 1);
            $obj->set("file", $properties['data']['file']);
            $obj->set("description", $properties['data']['description']);
            $obj->save();
            return $obj->toArray();
        }
    }

    public function setProgramFile($properties) {
        $obj = 0;
        $this->modx->log(1, print_r($properties, 1));
        $user_id = $_SESSION['analytics_user']['profile']['id'];
        if($properties['id']){
            if($properties['data']['id']){
                $obj = $this->modx->getObject("slBonusDocs", $properties['data']['id']);
                if($obj){
                    $obj->set("updatedon", time());
                    $obj->set("updatedby", $user_id);
                }
            }
            if(!$obj){
                $obj = $this->modx->newObject("slBonusDocs");
                if($obj){
                    $obj->set("createdon", time());
                    $obj->set("createdby", $user_id);
                }
            }
            $obj->set("name", $properties['data']['name']);
            $obj->set("bonus_id", $properties['bonus_id']);
            $obj->set("description", $properties['data']['description']);
            $obj->save();
            if($properties['data']['files']){
                if($file = $obj->get("file")){
                    $full_path = $this->modx->getOption("base_path").$file;
                    if(is_file($full_path)) {
                        unlink($full_path);
                    }
                }
                $source = $this->modx->getOption("base_path").$properties['data']['files'][0]["original"];
                // грузим новый
                if($properties['data']['files'][0]['path']){
                    $target_path = $this->modx->getOption("base_path")."assets/files/organizations/{$properties['id']}/{$properties['data']['files'][0]['path']}/";
                    $target_file = $target_path.$this->pcgbasename($source);
                    $url = "assets/files/organizations/{$properties['id']}/{$properties['data']['files'][0]['path']}/".$this->pcgbasename($source);
                }else{
                    $target_path = $this->modx->getOption("base_path")."assets/files/organizations/{$properties['id']}/";
                    $target_file = $target_path.$this->pcgbasename($source);
                    $url = "assets/files/organizations/{$properties['id']}/".$this->pcgbasename($source);
                }
                if(!file_exists($target_path)){
                    mkdir($target_path, 0777, true);
                }
                if (copy($source, $target_file)) {
                    if(is_file($source)) {
                        unlink($source);
                    }
                    $obj->set("file", $url);
                    $obj->save();
                }
            }
            return $obj->toArray();
        }
    }

    public function setPlan($properties) {
        $this->modx->log(1, print_r($properties, 1));
        $start = new DateTime($properties['data']['dates'][0]);
        $start->setTime(00,00);
        $end = new DateTime($properties['data']['dates'][1]);
        $end->setTime(23,59);
        if($properties["new"]){
            $criteria = array(
                "bonus_id" => $properties['bonus_id'],
                "fornew" => 1,
            );
            $plan = $this->modx->getObject("slBonusesPlans", $criteria);
            if($plan){
                // уже есть объект
                return false;
            }
        }
        // запустить цикл
        $stores = array(
            array(
                "id" => 0
            )
        );
        if($properties['data']['fstores']){
            // ставим на все магазины
            $q = $this->modx->newQuery("slBonusesConnection");
            $q->leftJoin("slStores", "slStores", "slStores.id = slBonusesConnection.store_id");
            $q->where(array(
                "slStores.store" => 1,
                "slBonusesConnection.bonus_id:=" => $properties["bonus_id"]
            ));
            $q->select(array(
                'slStores.id',
                'slStores.name',
                'slStores.address'
            ));
            $q->where(array(
                "slStores.active:=" => 1
            ));
            if($q->prepare() && $q->stmt->execute()) {
                $out = $q->stmt->fetchAll(PDO::FETCH_ASSOC);
                $stores = $out;
            }
        }
        if($properties['data']['fwarehouses']){
            // ставим на все склады
            $q = $this->modx->newQuery("slBonusesConnection");
            $q->leftJoin("slStores", "slStores", "slStores.id = slBonusesConnection.store_id");
            $q->where(array(
                "slStores.warehouse:=" => 1,
                "slBonusesConnection.bonus_id:=" => $properties["bonus_id"]
            ));
            $q->select(array(
                'slStores.id',
                'slStores.name',
                'slStores.address'
            ));
            $q->where(array(
                "slStores.active:=" => 1
            ));
            if($q->prepare() && $q->stmt->execute()) {
                $out = $q->stmt->fetchAll(PDO::FETCH_ASSOC);
                $stores = $out;
            }
        }
        if($properties['data']['selected'] && !$properties['data']['fwarehouses'] && !$properties['data']['fstores']){
            // ставим на все выбранные
            $stores = $properties['data']['selected'];
        }
        $output = array();
        foreach($stores as $store){
            $plan = $this->modx->newObject("slBonusesPlans");
            $plan->set("bonus_id", $properties['bonus_id']);
            $plan->set("store_id", $store['id']);
            $plan->set("report_type_id", $properties['data']['report']);
            $plan->set("report_type_field_id", $properties['data']['report_field']);
            $plan->set("report_type_field_value", $properties['data']['report_field_value']);
            $plan->set("period", $properties['data']['regular']);
            $plan->set("name", $properties['data']['name']);
            $plan->set("reward", $properties['data']['reward']);
            $plan->set("description", $properties['data']['description']);
            $plan->set("date_from", $start->format('Y-m-d H:i:s'));
            $plan->set("date_to", $end->format('Y-m-d H:i:s'));
            $plan->set("createdon", time());
            $plan->set("active", 1);
            if($properties["new"]){
                $plan->set("fornew", 1);
            }else{
                $plan->set("fornew", 0);
            }
            if($properties['data']["params"]){
                $plan->set("properties", json_encode($properties['data']["params"], JSON_UNESCAPED_UNICODE));
            }
            if($plan->save()){
                $output[] = $plan->toArray();
            }else{
                // TODO: ADD TO TELEGRAM ALERT
                return false;
            }
        }
        return $output;
    }

    public function parseRegions($data){
        $output = array(
            "regions" => array(),
            "cities" => array()
        );
        foreach($data as $key => $val){
            if($val['checked'] && !$val["partialChecked"]){
                $k_r = explode("_", $key);
                if($k_r[0] == 'region'){
                    $output['regions'][] = $k_r[1];
                }
                if($k_r[0] == 'city') {
                    $output['cities'][] = $k_r[1];
                }
            }
            // Убираем лишние города
            foreach($output['cities'] as $key => $val){
                $city = $this->modx->getObject("dartLocationCity", $val);
                $region = $city->get("region");
                if(in_array($region, $output['regions'])){
                    unset($output['cities'][$key]);
                }
            }
        }
        return $output;
    }

    public function setAkbDotsPlan ($properties) {
        if($properties['action'] == 'set' && $properties['type'] == 'akbdotsplan'){
            $store_id = $properties['id'];
            $date = new DateTime($properties['data']['month']);
            $one_day = new DateInterval('P1D');
            $date->add($one_day);
            $date->setTime(00,00);
            $geo_data = $this->parseRegions($properties['data']['city']);
            $akbdotsplan = $this->modx->newObject('slStoresAkbDotsPlan');
            $akbdotsplan->set('store_id', $store_id);
            if(count($geo_data['regions'])){
                $akbdotsplan->set('region', implode(",", $geo_data['regions']));
            }
            if(count($geo_data['cities'])){
                $akbdotsplan->set('city', implode(",", $geo_data['cities']));
            }
            $akbdotsplan->set('date', $date->format('Y-m-d H:i:s'));
            $akbdotsplan->set("createdon", time());
            $akbdotsplan->set('count', $properties['data']['dots']);
            $akbdotsplan->set('properties', json_encode($properties['data'], JSON_UNESCAPED_UNICODE));
            if($akbdotsplan->save()){
                return $akbdotsplan->toArray();
            }else{
                // TODO: ADD TO TELEGRAM ALERT
                return false;
            }
        }
        return false;
    }

    public function setAkbSettlementPlan ($properties) {
        if($properties['action'] == 'set' && $properties['type'] == 'akbsettlementplan'){
            $store_id = $properties['id'];
            $date = new DateTime($properties['data']['month']);
            $one_day = new DateInterval('P1D');
            $date->add($one_day);
            $date->setTime(00,00);
            $akbsettlementplan = $this->modx->newObject('slStoresAkbSettlementPlan');
            $akbsettlementplan->set('store_id', $store_id);
            $akbsettlementplan->set('date', $date->format('Y-m-d H:i:s'));
            $akbsettlementplan->set("createdon", time());
            $akbsettlementplan->set('count', $properties['data']['dots']);
            $akbsettlementplan->set('properties', json_encode($properties['data'], JSON_UNESCAPED_UNICODE));
            if($akbsettlementplan->save()){
                return $akbsettlementplan->toArray();
            }else{
                // TODO: ADD TO TELEGRAM ALERT
                return false;
            }
        }
        return false;
    }

    public function setBonusConnection($properties){
        $output = array();
        $criteria = array(
            "bonus_id" => $properties['bonus_id'],
            "store_id" => $properties['id'],
        );
        $bonus = $this->modx->getObject("slBonuses", $properties['bonus_id']);
        if($bonus){
            $connection = $this->modx->getObject("slBonusesConnection", $criteria);
            if($connection){
                // уже есть объект
            }else{
                $connection = $this->modx->newObject("slBonusesConnection");
                $connection->set("bonus_id", $properties['bonus_id']);
                if($bonus->get("auto_accept")){
                    $connection->set("status", 2);
                }else{
                    $connection->set("status", 1);
                }
                $connection->set("store_id", $properties['id']);
                $connection->set("date", time());
                $connection->set("active", 1);
                $connection->save();
                $output = $connection->toArray();
                // check plan for newby
                $new_plan = $this->modx->getObject("slBonusesPlans", array("bonus_id" => $properties['bonus_id'], "fornew" => 1));
                if($new_plan){
                    // создаем план для организации и создаем отчет
                    $date_from = time();
                    $period = $new_plan->get("period");
                    $dt = new DateTime();
                    $dt->setTimestamp($date_from);
                    if($period == 'dayly'){
                        // ставим конец дня
                        $dt->setTime(23,59)->format('Y/m/d H:i:s');
                    }
                    if($period == 'weekly'){
                        // ставим конец недели
                        $dt->modify('sunday this week')->setTime(23,59)->format('Y/m/d H:i:s');
                    }
                    if($period == 'monthly'){
                        // ставим конец месяца
                        $dt->modify('last day of this month')->setTime(23,59)->format('Y/m/d H:i:s');
                    }

                    $report_type = $new_plan->get("report_type_id");
                    if($report_type == 2){
                        // создаем планы АКБ по торговым точкам
                        // $akbplan = $this->setAkbDotsPlan
                    }
                    if($report_type == 3){
                        // создаем планы АКБ по населенным пунктам

                    }
                }
            }
            // first connection TODO: edit to slWarehouseStores
            $bonus = $this->modx->getObject("slBonuses", $properties['bonus_id']);
            if($bonus){
                $vendor = $bonus->get("store_id");
                $criteria = array(
                    "vendor_id" => $vendor,
                    "store_id" => $properties['id'],
                );
                $connection = $this->modx->getObject("slStoresConnection", $criteria);
                if($connection){
                    // уже есть объект
                }else{
                    $connection = $this->modx->newObject("slStoresConnection");
                    $connection->set("vendor_id", $vendor);
                    $connection->set("store_id", $properties['id']);
                    $connection->set("date", time());
                    $connection->set("active", 1);
                    $connection->save();
                }
            }
        }
        return $output;
    }

    public function setOrganization($properties){
        if($properties['action'] == 'set'){
            $store_id = $properties['id'];
            $store = $this->modx->getObject('slStores', $store_id);
            $store->set("contact", $properties['contact']);
            $store->set("phone", $properties['phone']);
            $store->set("email", $properties['email']);
            $store->set("updatedon", time());
            if($properties['files']){
                if($file = $store->get("image")){
                    $full_path = $this->modx->getOption("base_path").$file;
                    if(is_file($full_path)) {
                        unlink($full_path);
                    }
                }
                $source = $this->modx->getOption("base_path").$properties['files'][0]["original"];
                // грузим новый
                if($properties['files'][0]['path']){
                    $target_path = $this->modx->getOption("base_path")."assets/files/organizations/{$store_id}/{$properties['files'][0]['path']}/";
                    $target_file = $target_path.basename($source);
                    $url = "assets/files/organizations/{$store_id}/{$properties['files'][0]['path']}/".basename($source);
                }else{
                    $target_path = $this->modx->getOption("base_path")."assets/files/organizations/{$store_id}/";
                    $target_file = $target_path.basename($source);
                    $url = "assets/files/organizations/{$store_id}/".basename($source);
                }
                if(!file_exists($target_path)){
                    mkdir($target_path, 0777, true);
                }
                if (copy($source, $target_file)) {
                    if(is_file($source)) {
                        unlink($source);
                    }
                    $store->set("image", $url);
                }
            }
            $store->save();
            return $store->toArray();
        }
    }

    function pcgbasename($param, $suffix=null) {
        if ( $suffix ) {
            $tmpstr = ltrim(substr($param, strrpos($param, DIRECTORY_SEPARATOR) ), DIRECTORY_SEPARATOR);
            if ( (strpos($param, $suffix)+strlen($suffix) )  ==  strlen($param) ) {
                return str_ireplace( $suffix, '', $tmpstr);
            } else {
                return ltrim(substr($param, strrpos($param, DIRECTORY_SEPARATOR) ), DIRECTORY_SEPARATOR);
            }
        } else {
            return ltrim(substr($param, strrpos($param, DIRECTORY_SEPARATOR) ), DIRECTORY_SEPARATOR);
        }
    }

    public function getBonusAvailableStores ($properties, $include = 1) {
        $results = array();
        $q = $this->modx->newQuery("slBonusesConnection");
        $q->leftJoin("slStores", "slStores", "slStores.id = slBonusesConnection.store_id");
        $q->leftJoin("slBonusesConnectionStatus", "slBonusesConnectionStatus", "slBonusesConnectionStatus.id = slBonusesConnection.status");
        $q->where(array(
            "slStores.type:IN" => array(1,2),
            "slBonusesConnection.bonus_id:=" => $properties["bonus_id"]
        ));
        $q->select(array(
            'slStores.id',
            'slStores.name',
            'slStores.address',
            'slBonusesConnection.status',
            'slBonusesConnectionStatus.name as status_name',
            'slBonusesConnectionStatus.color as status_color'
        ));
        if($properties['sel_arr']){
            if($include){
                $q->where(array(
                    "slStores.id:IN" => $properties['sel_arr']
                ));
            }else{
                $q->where(array(
                    "slStores.id:NOT IN" => $properties['sel_arr']
                ));
            }
        }
        if($properties['filter']){
            $q->where(array(
                "slStores.name:LIKE" => "%{$properties['filter']}%",
                "OR:slStores.address:LIKE" => "%{$properties['filter']}%"
            ));
        }
        $q->where(array(
            "slStores.active:=" => 1
        ));
        if($q->prepare() && $q->stmt->execute()){
            $out = $q->stmt->fetchAll(PDO::FETCH_ASSOC);
            if($properties['sel_arr']){
                $results = $out;
            }else{
                if($properties['selected']){
                    $results['items'][] = $properties['selected'];
                }else{
                    $results['items'][] = array();
                }
            }
            return $results;
        }
        return array();
    }

    public function getAvailableProducts($store_id, $properties = array(), $include = 1){
        $results = array();

        $q = $this->modx->newQuery("modResource");
        $q->leftJoin('msProductData', 'msProduct', 'msProduct.id = modResource.id');
        $q->leftJoin('slStoresRemains', 'slStoresRemains', 'slStoresRemains.product_id = modResource.id');
        $q->where(array(
            "modResource.class_key:=" => "msProduct",
            "slStoresRemains.store_id:=" => $properties['id']
        ));

        $q->select(array(
            'modResource.id',
            'slStoresRemains.price as price',
            'modResource.pagetitle as name',
            'COALESCE(msProduct.image, "/assets/files/img/nopic.png") as image',
            'msProduct.vendor_article as article'
        ));

        $idsProducts = array();
        //Если нет выбранных, выдаём весь список
        if($properties['selected']){

            for($i = 0; $i < count($properties['selected']); $i++){
                $idsProducts[$i] = $properties['selected'][$i]['id'];
            }

            $q->where(array(
                "modResource.id:NOT IN" => $idsProducts
            ));
        }

        $idsProductsCategory = array();

        if($properties['filter']){
            if($properties['filter']['name']) {
                $q->where(array(
                    "modResource.pagetitle:LIKE" => "%{$properties['filter']['name']}%",
                    "OR:msProduct.vendor_article:LIKE" => "%{$properties['filter']['name']}%"
                ));
            }


            if($properties['filter']['category']){
                foreach ($properties['filter']['category'] as $key => $value) {
                    if($value['checked']){
                        array_push($idsProductsCategory, $key);
                    }
                }

                $q->where(array(
                    "modResource.parent:IN" => $idsProductsCategory
                ));
            }
        }

        if($properties['page'] && $properties['perpage']){
            $limit = $properties['perpage'];
            $offset = ($properties['page'] - 1) * $properties['perpage'];
            $q->limit($limit, $offset);
        }else{
            $limit = 50;
            $offset = 0;
            $q->limit($limit, $offset);
        }

        // Подсчитываем общее число записей
        // $result['total'] = $this->modx->getCount('slStoresRemains', $q);
        $q->prepare();
        if($q->prepare() && $q->stmt->execute()){
            $out = $q->stmt->fetchAll(PDO::FETCH_ASSOC);
            if(!$properties['selected']){
                $results['products'] = $out;

            }else{
                $results['products'] = $out;
                $results['selected'] = $properties['selected'];
            }
            return $results;
        }

        //TODO
//        $criteria = array(
//            "store_id" => $store_id
//        );
//        $vs = array();
//        $vendors = $this->modx->getCollection("slStoresBrands", $criteria);
//        foreach($vendors as $v) {
//            $vs[] = $v->get("brand_id");
//        }





//        if($properties['sel_arr']){
//            if($include){
//                $q = $this->modx->newQuery("slStoresMatrixProducts");
//                $q->leftJoin("modResource", "modResource", "modResource.id = slStoresMatrixProducts.product_id");
//                $q->leftJoin('msProductData', 'msProduct', 'msProduct.id = slStoresMatrixProducts.product_id');
//                $q->where(array(
//                    "slStoresMatrixProducts.matrix_id:=" => $properties['matrix_id'],
//                    "modResource.class_key:=" => "msProduct",
//                    //"msProduct.vendor:IN" => $vs
//                ));
//                $q->where(array(
//                    "modResource.id:IN" => $properties['sel_arr']
//                ));
//                $q->select(array(
//                    'modResource.id',
//                    'modResource.pagetitle as name',
//                    'COALESCE(msProduct.image, "/assets/files/img/nopic.png") as image',
//                    'msProduct.vendor_article as article',
//                    'slStoresMatrixProducts.count',
//                    'slStoresMatrixProducts.days'
//                ));
//            }else{

//            }
//        }


        return array();
    }

    public function setMatrix($properties){
        if($properties['action'] == 'set'){
            $store_id = $properties['id'];
            $start = new DateTime($properties['dates'][0]);
            $start->setTime(00,00);
            $end = new DateTime($properties['dates'][1]);
            $end->setTime(23,59);

            if($properties['matrix_id']){
                $matrix = $this->modx->getObject('slStoresMatrix', $properties['matrix_id']);
            }else{
                $matrix = $this->modx->newObject('slStoresMatrix');
            }
            if($matrix){
                $matrix->set("store_id", $store_id);
                $matrix->set("name", $properties['name']);
                $matrix->set("percent", $properties['percent']);
                $matrix->set("date_from", $start->format('Y-m-d H:i:s'));
                $matrix->set("date_to", $end->format('Y-m-d H:i:s'));
                $matrix->set("createdon", time());
                $matrix->set("active", 1);
                $matrix->save();
                if($matrix->get('id')){
                    if($properties['matrix_id']){
                        $crit = array(
                            "matrix_id" => $properties['matrix_id']
                        );
                        $this->modx->removeCollection("slStoresMatrixProducts", $crit);
                    }
                    foreach($properties['products'] as $product){
                        $matrix_p = $this->modx->newObject("slStoresMatrixProducts");
                        $matrix_p->set("matrix_id", $matrix->get('id'));
                        $matrix_p->set("product_id", $product['id']);
                        if($product['count']){
                            $matrix_p->set("count", $product['count']);
                        }else{
                            $matrix_p->set("count", 1);
                        }
                        if($product['days']){
                            $matrix_p->set("days", $product['days']);
                        }else{
                            $matrix_p->set("days", 1);
                        }
                        $matrix_p->save();
                    }
                    return $matrix->toArray();
                }
            }
        }
        return false;
    }

    public function getMatrix($properties){
        // $this->modx->log(1, print_r($properties, 1));
        if($properties['matrix_id']){
            $matrix = $this->modx->getObject("slStoresMatrix", $properties['matrix_id']);
            if($matrix){
                $data = $matrix->toArray();
                $data['date_from'] = date('Y/m/d H:i:s', strtotime($data['date_from']));
                $data['date_to'] = date('Y/m/d H:i:s', strtotime($data['date_to']));
                $products = $this->modx->getCollection("slStoresMatrixProducts", array("matrix_id" => $data['id']));
                $properties["sel_arr"] = array();
                foreach($products as $product){
                    $properties["sel_arr"][] = $product->get("product_id");
                }
                $data['products'][] = $this->getAvailableProducts($data['store_id'], $properties, 0);
                $data['products'][] = $this->getAvailableProducts($data['store_id'], $properties, 1);
                return $data;
            }
        }else{
            $q = $this->modx->newQuery("slStoresMatrix");
            $q->select(array(
                'slStoresMatrix.*'
            ));
            if($properties['filtersdata']){
                if(isset($properties['filtersdata']['range'])){
                    if($properties['filtersdata']['range'][0] && $properties['filtersdata']['range'][1]){
                        $from = date('Y-m-d H:i:s', strtotime($properties['filtersdata']['range'][0]));
                        $to = date('Y-m-d H:i:s', strtotime($properties['filtersdata']['range'][1]));
                        $q->where(array("`slStoresMatrix`.`date_from`:<=" => $from, "`slStoresMatrix`.`date_to`:>=" => $to));
                    }
                    if($properties['filtersdata']['range'][0] && !$properties['filtersdata']['range'][1]){
                        $from = date('Y-m-d H:i:s', strtotime($properties['filtersdata']['range'][0]));
                        $q->where(array("`slStoresMatrix`.`date_from`:<=" => $from));
                    }
                }
                if($properties['filter']){
                    $words = explode(" ", $properties['filter']);
                    foreach($words as $word){
                        $criteria = array();
                        $criteria['slStoresMatrix.name:LIKE'] = '%'.trim($word).'%';
                        $q->where($criteria);
                    }
                }
            }
            $result = array();
            // Подсчитываем общее число записей
            $result['total'] = $this->modx->getCount("slStoresMatrix", $q);

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
            $q->prepare();
            $this->modx->log(1, $q->toSQL());
            if ($q->prepare() && $q->stmt->execute()) {
                $output = array();
                $result['items'] = $q->stmt->fetchAll(PDO::FETCH_ASSOC);
                foreach($result['items'] as $key => $val){
                    $date_from = strtotime($val['date_from']);
                    $result['items'][$key]['date_from'] = date("d.m.Y H:i", $date_from);
                    $date_to = strtotime($val['date_to']);
                    $result['items'][$key]['date_to'] = date("d.m.Y H:i", $date_to);
                }
                $this->modx->log(1, print_r($output, 1));
                return $result;
            }
        }
    }

    public function deleteObject($properties){
        $this->modx->log(1, print_r($properties, 1));
        // проверка доступа должна быть в процессоре
        if($properties['type'] == 'request'){
            if(isset($properties['request_id'])){
                $request = $this->modx->getObject("slCardRequest", $properties['request_id']['id']);
                if($request){
                    if ($request->remove() !== false) {
                        return true;
                    }else{
                        $this->modx->log(1, "Проверьте удаление заявки ". $properties['request_id']['id']);
                        return false;
                    }
                }
            }
        }
        if($properties['type'] == 'programfile'){
            if(isset($properties['file_id'])){
                $request = $this->modx->getObject("slBonusDocs", $properties['file_id']['id']);
                if($request){
                    if ($request->remove() !== false) {
                        return true;
                    }else{
                        $this->modx->log(1, "Проверьте удаление файла ". $properties['file_id']['id']);
                        return false;
                    }
                }
            }
        }
        if($properties['type'] == 'bonus'){
            if(isset($properties['bonus_id'])){
                $bonus = $this->modx->getObject("slBonuses", $properties['bonus_id']['id']);
                if($bonus){
                    if ($bonus->remove() !== false) {
                        return true;
                    }else{
                        $this->modx->log(1, "Проверьте удаление Ретро-бонуса ". $properties['bonus_id']['id']);
                        return false;
                    }
                }
            }
        }
        if($properties['type'] == 'akbdotsplan'){
            if(isset($properties['plan_id'])){
                $plan = $this->modx->getObject("slStoresAkbDotsPlan", $properties['plan_id']['id']);
                if($plan){
                    if ($plan->remove() !== false) {
                        return true;
                    }else{
                        $this->modx->log(1, "Проверьте удаление плана ". $properties['plan_id']['id']);
                        return false;
                    }
                }
            }
        }
        if($properties['type'] == 'plan'){
            if(isset($properties['plan_id'])){
                $plan = $this->modx->getObject("slBonusesPlans", $properties['plan_id']['id']);
                if($plan){
                    if ($plan->remove() !== false) {
                        return true;
                    }else{
                        $this->modx->log(1, "Проверьте удаление плана ". $properties['plan_id']['id']);
                        return false;
                    }
                }
            }
        }
        if($properties['type'] == 'bonus_participants'){
            if(isset($properties['bonus_id'])){
                $connection = $this->modx->getObject("slBonusesConnection", array("store_id" => $properties['store']['store_id'], "bonus_id" => $properties['bonus_id']));
                if($connection){
                    if ($connection->remove() !== false) {
                        return true;
                    }else{
                        $this->modx->log(1, "Проверьте удаление плана ". $properties['store']['store_id']);
                        return false;
                    }
                }
            }
        }
        if($properties['type'] == 'work_week_date'){
            if(isset($properties['object_id'])){
                $work_day = $this->modx->getObject("slStoresWeekWork", array("store_id" => $properties['id'], "id" => $properties['object_id']));
                if($work_day){
                    if ($work_day->remove() !== false) {
                        return true;
                    }else{
                        $this->modx->log(1, "Проверьте удаление времени работы ". $properties['id']);
                        return false;
                    }
                }
            }
        }
        if($properties['type'] == 'report'){
            if(isset($properties['report_id'])){
                $report = $this->modx->getObject("slReports", $properties['report_id']['id']);
                if($report){
                    if($report->get("type") == 1){
                        if($this->removeTopsReport($report->get("id"))){
                            if ($report->remove() !== false) {
                                return true;
                            }else{
                                $this->modx->log(1, "Проверьте удаление отчета ". $properties['report_id']['id']);
                                return false;
                            }
                        }else{
                            return false;
                        }
                    }
                    if($report->get("type") == 2){
                        if($this->removePresentReport($report->get("id"))){
                            if ($report->remove() !== false) {
                                return true;
                            }else{
                                $this->modx->log(1, "Проверьте удаление отчета ". $properties['report_id']['id']);
                                return false;
                            }
                        }else{
                            return false;
                        }
                    }
                    if($report->get("type") == 3){
                        if($this->removeRRCReport($report->get("id"))){
                            if ($report->remove() !== false) {
                                return true;
                            }else{
                                $this->modx->log(1, "Проверьте удаление отчета ". $properties['report_id']['id']);
                                return false;
                            }
                        }else{
                            return false;
                        }
                    }
                    if($report->get("type") == 4){
                        if($this->removeWeekSalesReport($report->get("id"))){
                            if ($report->remove() !== false) {
                                return true;
                            }else{
                                $this->modx->log(1, "Проверьте удаление отчета ". $properties['report_id']['id']);
                                return false;
                            }
                        }else{
                            return false;
                        }
                    }
                }
            }
        }
    }

    public function removeTopsReport($id){
        $result = false;
        // удаляем все магазины
        $criteria = array(
            "report_id" => $id
        );
        $stores = $this->modx->getCollection("slReportsTopSales", $criteria);
        if(count($stores)){
            foreach($stores as $store){
                if ($store->remove() !== false) {
                    $result = true;
                }
            }
        }else{
            $result = true;
        }
        if(!$result){
            $this->modx->log(1, "Проверьте удаление отчета Топов продаж ". $id);
        }
        return $result;
    }

    public function removePresentReport($id){
        $result = false;
        // удаляем все магазины
        $criteria = array(
            "report_id" => $id
        );
        $stores = $this->modx->getCollection("slReportsPresent", $criteria);
        if(count($stores)){
            foreach($stores as $store){
                if ($store->remove() !== false) {
                    $result = true;
                }
            }
        }else{
            $result = true;
        }
        if(!$result){
            $this->modx->log(1, "Проверьте удаление отчета Первичной представленности ". $id);
        }
        return $result;
    }

    public function removeRRCReport($id){
        $result = false;
        // удаляем все магазины
        $criteria = array(
            "report_id" => $id
        );
        $stores = $this->modx->getCollection("slReportsRRCStores", $criteria);
        if(count($stores)){
            foreach($stores as $store){
                if ($store->remove() !== false) {
                    $result = true;
                }
            }
        }else{
            $result = true;
        }
        if(!$result){
            $this->modx->log(1, "Проверьте удаление отчета РРЦ ". $id);
        }
        return $result;
    }

    public function removeWeekSalesReport($id){
        $result = false;
        // удаляем все магазины
        $criteria = array(
            "report_id" => $id
        );
        $stores = $this->modx->getCollection("slReportsWeeks", $criteria);
        if(count($stores)){
            foreach($stores as $store){
                if ($store->remove() !== false) {
                    $result = true;
                }
            }
        }else{
            $result = true;
        }
        if(!$result){
            $this->modx->log(1, "Проверьте удаление отчета Недельных продаж ". $id);
        }
        return $result;
    }
}