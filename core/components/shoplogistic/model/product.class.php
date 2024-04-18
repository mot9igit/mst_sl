<?php

class productHandler
{
    public $modx;
    public $sl;
    public $config;

    public function __construct(shopLogistic &$sl, modX &$modx)
    {
        $this->sl =& $sl;
        $this->modx =& $modx;

        $this->config = array(
            "product_price_percent" => 20
        );

        $this->modx->lexicon->load('shoplogistic:default');
    }

    /**
     * Берем данные по стоимости товара
     *
     * @param $store_id
     * @param $product_id
     * @return array|void
     */
    public function getRemainAndPriceForStore($store_id, $product_id){
        $remain = $this->getStoreRemain($store_id, $product_id);
        if(!$remain){
            $remain = $this->getMinStoreRemain($product_id);
            $remain['remains'] = 0;
        }else{
            $remain = array(
                "price" => 0,
                "remains" => 0
            );
        }
        return $remain;
    }

    /**
     * Берем остаток по ID товара и магазину
     *
     * @param $store_id
     * @param $product_id
     * @return array
     */
    public function getMinStoreRemain($product_id){
        $query = $this->modx->newQuery("slStoresRemains");
        $query->leftJoin("slStores", "slStores", "slStores.id = slStoresRemains.store_id");
        $query->where(array(
            "slStores.active:=" => 1,
            "slStoresRemains.remains:>" => 0,
            "slStoresRemains.price:>" => 0,
            "slStoresRemains.product_id:=" => $product_id,
        ));
        $query->select(array("slStoresRemains.*"));
        $query->sortby("price", "ASC");
        $query->limit(1);
        if($query->prepare() && $query->stmt->execute()){
            $response = $query->stmt->fetch(PDO::FETCH_ASSOC);
            return $response;
        }
        return array();
    }

    /**
     * Берем остаток по ID товара и магазину
     *
     * @param $store_id
     * @param $product_id
     * @return array
     */
    public function getStoreRemain($store_id, $product_id){
        $query = $this->modx->newQuery("slStoresRemains");
        $query->leftJoin("slStores", "slStores", "slStores.id = slStoresRemains.store_id");
        $query->where(array(
            "slStores.active:=" => 1,
            "slStores.id:=" => $store_id,
            "slStoresRemains.remains:>" => 0,
            "slStoresRemains.price:>" => 0,
            "slStoresRemains.product_id:=" => $product_id,
        ));
        $query->select(array("slStoresRemains.*"));
        if($query->prepare() && $query->stmt->execute()){
            $response = $query->stmt->fetch(PDO::FETCH_ASSOC);
            return $response;
        }
        return array();
    }

    /**
     *
     * Берем параметры товара, габариты в см, вес в кг, объем в куб.м.
     *
     * @param $product_id
     * @param $count
     * @return array|false
     */
    public function getProductParams($product_id = 0, $count = 1){
        $tmp = array();
        $output = array();
        if($product_id){
            $query = $this->modx->newQuery("modResource");
            $query->leftJoin("msProductData", "Data");
            $query->where(array(
                "`modResource`.`id`:=" => $product_id
            ));
            $query->select(array(
                "`modResource`.*",
                "`Data`.*"
            ));
            $query->limit(1);
            if ($query->prepare() && $query->stmt->execute()) {
                $product = $query->stmt->fetch(PDO::FETCH_ASSOC);
                if(count($product)){
                    $params = array();
                    $tmp["id"] = $product['id'];
                    $tmp["article"] = $product['article'];
                    $tmp["name"] = $product['pagetitle'];
                    $tmp['weight'] = (float)$product['weight']?:(float)$product['weight_netto'];
                    $tmp['weight_netto'] = (float)$product['weight_netto'];
                    $tmp['volume'] = (float)$product['volume'];
                    $tmp['price'] = (float)$product['price'];
                    $tmp['count'] = $count;
                    $params['dimensions'][0] = (int)$product['length'];
                    $params['dimensions'][1] = (int)$product['width'];
                    $params['dimensions'][2] = (int)$product['height'];
                    $tmp['length'] = $params['dimensions'][0];
                    $tmp['width'] = $params['dimensions'][1];
                    $tmp['height'] = $params['dimensions'][2];
                    $tmp['dimensions'] = implode('*', $params['dimensions']);
                    $tmp['product'] = $product;
                    $output[] = $tmp;
                    return $output;
                }
            }
        }
        return false;
    }

    /**
     * Импорт остатка
     *
     * @param $data
     * @return array|false
     */
    public function importRemain($data){
        if($data['key']) {
            $store = $this->getStore($data['key'], "date_remains_update");
            if ($store['id']) {
                $this->tolog('store_' . $store['id'], $data);
                $response = array();
                $response['success_info'] = array();
                $response['failed_info'] = array();
                if($data['isFull']){
                    // обнуляем остатки
                    $table = $this->modx->getTableName("slStoresRemains");
                    if($table){
                        // TODO: проверить полную выгрузку
                        /*
                        $sql = "UPDATE {$table} SET `price` = 0, `remains` = 0, `reserved` = 0, `available` = 0 WHERE `store_id` = {$store['id']};";
                        $stmt = $this->modx->prepare($sql);
                        if(!$stmt){
                            $this->modx->log(1, print_r($stmt->errorInfo, true) . ' SQL: ' . $sql);
                        }
                        if (!$stmt->execute($data)) {
                            $this->modx->log(1, print_r($stmt->errorInfo, true) . ' SQL: ' . $sql);
                        }
                        */
                    }
                    // $this->sl->api->update("slStoresRemains", );
                }
                foreach ($data['products'] as $key => $product) {
                    $error = false;
                    $message = '';
                    if (!isset($product['article'])) {
                        if ($message) {
                            $message = $message . ' || WARN! Не указан артикул товара';
                        } else {
                            $message = 'WARN! Не указан артикул товара';
                        }
                    }
                    if (!isset($product['count_current'])) {
                        if ($message) {
                            $message = $message . ' || Не указан текущий остаток товара';
                        } else {
                            $message = 'Не указан текущий остаток товара';
                        }
                        $error = true;
                    }
                    if (!isset($product['count_free'])) {
                        if ($message) {
                            $message = $message . ' || Не указан доступный для продажи остаток товара используем count_current';
                        } else {
                            $message = 'Не указан доступный для продажи остаток товара используем count_current';
                        }
                        $product['count_free'] = $product['count_current'];
                        $error = true;
                    }
                    if (!isset($product['price'])) {
                        if ($message) {
                            $message = $message . ' || Не указана цена товара';
                        } else {
                            $message = 'Не указана цена товара';
                        }
                        $error = true;
                    }
                    if (!isset($product['catalog'])) {
                        if ($message) {
                            $message = $message . ' || WARN! Не указана категория товара';
                        } else {
                            $message = 'WARN! Не указана категория товара';
                        }
                    }
                    // чекаем GUID
                    if(isset($product['GUID'])){
                        $guid = $product['GUID'];
                    }else{
                        $guid = $key;
                    }
                    if ($error) {
                        $response['failed_info'][] = array(
                            'guid' => $guid,
                            'message' => $message
                        );
                    } else {
                        $resp = $this->importRemainSingle($store['id'], $product);
                        if($response){
                            $response['success_info'][] = $resp;
                        }else{
                            $response['failed_info'][] = $resp;
                        }
                    }
                }
                $response['success'] = count($response['success_info']);
                $response['failed'] = count($response['failed_info']);
                return $response;
            }
        }
        return false;
    }

    /**
     * Импорт остатка из 1С
     *
     * @param $store_id
     * @param $data
     * @return int
     */
    public function importRemainSingle($store_id, $data){
        $message = "";
        // проверяем товар на дублирование по GUID
        $criteria = array(
            'guid' => $data["GUID"],
            'store_id' => $store_id
        );
        $o = $this->modx->getObject('slStoresRemains', $criteria);
        if (!$o) {
            $o = $this->modx->newObject('slStoresRemains');
        }
        $o->set("guid", $data["GUID"]);
        if($data['base_GUID']){
            $o->set("base_guid", $data['base_GUID']);
        }
        if($data['barcode']){
            $o->set("barcode", implode(",", $data['barcode']));
        }
        $o->set("article", $data['article']);
        $o->set("remains", $data['count_current']);
        $o->set("catalog", $data['catalog']);
        $reserved = $o->get('reserved');
        if ($reserved > 0) {
            $available = $data['count_free'] - $reserved;
        } else {
            $available = $data['count_free'];
        }
        if ($available < 0) {
            $o->set("available", 0);
        } else {
            $o->set("available", $available);
        }
        $o->set("price", $data['price']);
        $o->set('store_id', $store_id);
        if ($data['name']) {
            $o->set("name", $data['name']);
        }
        if ($o->save()) {
            // линкуем товар
            // if (!$o->get('product_id') && $o->get('autolink')) {
            if (!$o->get('product_id')) {
                $product_id = $this->linkProduct($o->get('id'));
            }
            $product_id = $o->get('product_id');
            if($product_id){
                $store = $this->sl->getObject($store_id);
                if($o->get("remains") && $store['active']){
                    $status = 1;
                }else{
                    $status = 99;
                }
                $this->setProductStatus($product_id, $status);
            }
            return $o->get("id");
        }
        return 0;
    }

    /**
     * Берем магазин и записываем последние пинги API
     *
     * @param $key
     * @param $type
     * @return void
     */
    public function getStore($key, $type = "date_api_ping") {
        $store = $this->modx->getObject("slStores", array('apikey' => $key));
        if($store){
            // set request dates
            $store->set($type, time());
            $store->set("date_api_ping", time());
            $store->save();
            $resp = $store->toArray();
            return $resp;
        }
    }

    /**
     *
     * Автоматическая прилинковка товара на основании бренда, артикула и цены
     *
     * @param $remain_id
     * @param $type
     * @return void
     */

    public function linkProduct($remain_id, $type = 'slStores'){
        $update_data = array();
        $remain = $this->sl->getObject($remain_id, "slStoresRemains");
        if($remain){
            $vendor = $this->searchVendor($remain['name']);
            if(!$vendor){
                $vendor = $this->searchVendor($remain['catalog']);
            }
            if($vendor){
                if(!$remain['article']){
                    $update_data = array(
                        "brand_id" => $vendor['id'],
                        "status" => 2
                    );
                }else{
                    if(!$remain['price']){
                        $update_data = array(
                            "brand_id" => $vendor['id'],
                            "status" => 6
                        );
                    }else{
                        // ищем карточку товара
                        $query = $this->modx->newQuery("modResource");
                        $query->leftJoin("msProductData", "Data");
                        $query->where(array(
                            "`Data`.`vendor_article`:=" => trim($remain['article']),
                            "`Data`.`vendor`:=" => $vendor['id']
                        ));
                        $query->select(array(
                            "`modResource`.*",
                            "`Data`.*"
                        ));
                        $query->limit(1);
                        if ($query->prepare() && $query->stmt->execute()) {
                            // нашли товар
                            $product = $query->stmt->fetch(PDO::FETCH_ASSOC);
                            if ($product) {
                                $update_data = array(
                                    "status" => 3,
                                    "brand_id" => $vendor['id'],
                                    "product_id" => $product['id']
                                );
                            }else{
                                $update_data = array(
                                    "status" => 4,
                                    "brand_id" => $vendor['id'],
                                    "product_id" => 0
                                );
                            }
                        }else{
                            $update_data = array(
                                "status" => 4,
                                "brand_id" => $vendor['id'],
                                "product_id" => 0
                            );
                        }
                    }
                }
            }else{
                // если не найден бренд выставляем статус
                $update_data = array(
                    "status" => 1,
                    "brand_id" => 0,
                    "product_id" => 0
                );
            }
            if(count($update_data)){
                $this->sl->api->update("slStoresRemains", $update_data, $remain['id']);
            }
            return $update_data;
        }
        return 0;
    }

    /**
     *
     * Поиск производителя из набора слов
     *
     * @param $name
     * @return mixed
     */

    public function searchVendor($name){
        $output = array();
        $search_name = trim(mb_strtolower(str_replace('.', '', $name)));
        $name = trim(preg_replace('/\s+/', ' ', preg_replace('/[^ a-zа-яё\d]/ui', ' ', $search_name)));
        $words = explode(" ", $name);
        $crit_name = array();
        $crit_assoc = array();
        foreach($words as $key => $word){
            $w = preg_replace('/[^ a-zа-яё\d]/ui', '', trim($word));
            $words[$key] = $w;
            $crit_name[]["name:LIKE"] = "%{$w}%";
            $crit_assoc[]["association:LIKE"] = "%{$w}%";
        }
        // сначала пробегаем по ассоциациям
        $query = $this->modx->newQuery("slBrandAssociation");
        $query->select(array("slBrandAssociation.*, LENGTH(association) as lenght_name"));
        $query->where($crit_assoc, xPDOQuery::SQL_OR);
        $query->sortby('LENGTH(association)', 'DESC');
        $query->prepare();
        if ($query->prepare() && $query->stmt->execute()) {
            $associations = $query->stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach($associations as $association){
                $pos = strpos($search_name, mb_strtolower($association["association"]));
                if($pos !== false){
                    $output = $this->sl->objects->getObject("msVendor", $association['brand_id']);
                }
            }
        }
        if(!$output) {
            // берем бренды
            $query = $this->modx->newQuery("msVendor");
            $query->select(array("msVendor.*, LENGTH(name) as lenght_name"));
            $query->where($crit_name, xPDOQuery::SQL_OR);
            $query->sortby('LENGTH(name)', 'DESC');
            $query->prepare();
            if ($query->prepare() && $query->stmt->execute()) {
                $vendors = $query->stmt->fetchAll(PDO::FETCH_ASSOC);
                foreach ($vendors as $vendor) {
                    $pos = strpos($search_name, mb_strtolower($vendor["name"]));
                    if ($pos !== false) {
                        $output = $vendor;
                    }
                }
            }
        }
        return $output;
    }

    public function getCategories () {
        $query = $this->modx->newQuery("modResource");
        $query->select(array(
            "`modResource`.*"
        ));
        $query->where(array(
            "`modResource`.`class_key`:=" => 'msCategory',
            "`modResource`.`deleted`:=" => 0,
            "`modResource`.`published`:=" => 1
        ));
        if ($query->prepare() && $query->stmt->execute()) {
            $categories = $query->stmt->fetchAll(PDO::FETCH_ASSOC);
            return $categories;
        }
    }

    public function buildCategoriesTree (array $categories, $parentId = 0, $idKey = 'id') {
        $branch = array();
        foreach ($categories as $element) {
            if ($element['parent'] == $parentId) {
                $children = $this->buildCategoriesTree($categories, $element[$idKey]);
                if ($children) {
                    $element['children'] = $children;
                }
                // $elem_id = $element['id'];
                unset($element['id']);
                unset($element['parent']);
                $branch[] = $element;
                unset($element);
            }
        }
        return $branch;
    }

    public function getVendors ($search = '') {
        $query = $this->modx->newQuery("msVendor");
        $query->select(array(
            "`msVendor`.*"
        ));
        if($search){
            $query->where(array(
                "`msVendor`.`name`:LIKE" => "%{$search}%"
            ));
        }
        if ($query->prepare() && $query->stmt->execute()) {
            $vendors = $query->stmt->fetchAll(PDO::FETCH_ASSOC);
            return $vendors;
        }
    }

    public function getMatrix ($id, $search = '') {
        $query = $this->modx->newQuery("slStoresMatrix");
        $query->select(array(
            "`slStoresMatrix`.*"
        ));
        if($search){
            $query->where(array(
                "`slStoresMatrix`.`name`:LIKE" => "%{$search}%"
            ));
        }
        if($id){
            $query->where(array(
                "`slStoresMatrix`.`store_id`:=" => $id
            ));
        }
        $query->prepare();
        $this->modx->log(1, $query->toSQL());
        if ($query->prepare() && $query->stmt->execute()) {
            $matrix = $query->stmt->fetchAll(PDO::FETCH_ASSOC);
            return $matrix;
        }
    }

    public function getBrands ($id, $search = '') {
        $query = $this->modx->newQuery("slStoresBrands");
        $query->leftJoin("msVendor", "Vendor", "Vendor.id = slStoresBrands.brand_id");
        $query->select(array(
            "`Vendor`.`name` as name",
            "`slStoresBrands`.brand_id as id"
        ));
        if($search){
            $query->where(array(
                "`Vendor`.`name`:LIKE" => "%{$search}%"
            ));
        }
        if($id){
            $query->where(array(
                "`slStoresBrands`.`store_id`:=" => $id
            ));
        }
        $query->prepare();
        $this->modx->log(1, $query->toSQL());
        if ($query->prepare() && $query->stmt->execute()) {
            $brands = $query->stmt->fetchAll(PDO::FETCH_ASSOC);
            return $brands;
        }
    }

    public function getStores ($search = '') {
        $query = $this->modx->newQuery("slStores");
        $query->select(array(
            "`slStores`.*"
        ));
        if($search){
            $query->where(array(
                "`slStores`.`name`:LIKE" => "%{$search}%"
            ));
        }
        $query->where(array(
            "`slStores`.`active`:=" => 1,
            "AND:`slStores`.`type`:=" => 1
        ));
        if ($query->prepare() && $query->stmt->execute()) {
            $stores = $query->stmt->fetchAll(PDO::FETCH_ASSOC);
            return $stores;
        }
    }

    public function getWarehouses ($search = '') {
        $query = $this->modx->newQuery("slStores");
        $query->select(array(
            "`slStores`.*"
        ));
        if($search){
            $query->where(array(
                "`slStores`.`name`:LIKE" => "%{$search}%"
            ));
        }
        $query->where(array(
            "`slStores`.`active`:=" => 1,
            "AND:`slStores`.`type`:=" => 2
        ));
        if ($query->prepare() && $query->stmt->execute()) {
            $stores = $query->stmt->fetchAll(PDO::FETCH_ASSOC);
            return $stores;
        }
    }

    public function linkDocsProducts($doc_id, $products){
        $response = array();
        $criteria = array(
            "doc_id" => $doc_id
        );
        // remove old products
        $old_prods = $this->modx->getCollection('slStoreDocsProducts', $criteria);
        foreach ($old_prods as $old_prod) {
            $old_prod->remove();
        }
        foreach ($products as $k => $product) {
            $prod = $this->modx->newObject('slStoreDocsProducts');
            $prod->set("doc_id", $doc_id);
            $prod->set("guid", $product['guid']);
            $prod->set("count", abs($product['count']));
            $prod->set("price", $product['price']);
            if ($product['count'] <= 0) {
                $prod->set("type", 2);
            } else {
                $prod->set("type", 1);
            }
            $prod->set('createdon', time());
            // check exist product
            $guid = '';
            if($product['guid']){
                $guid = $product['guid'];
            }
            if($product['GUID']){
                $guid = $product['GUID'];
            }
            $remain = false;
            if($guid){
                $criteria = array(
                    "guid" => $guid
                );
                $remain = $this->modx->getObject('slStoresRemains', $criteria);
            }
            if ($remain) {
                $prod->set("remain_id", $remain->get('id'));
                $p = $remain->get("price");
                if ($p != $product['price']) {
                    $response[$product['guid']][] = "Цена товара отличается от системной.";
                }
            } else {
                // TODO: создаем или нет?
                $response[$product['guid']][] = "Товара нет в системе.";
            }
            $prod->save();
        }
        return $response;
    }

    public function importDocs($data){
        $response = array(
            'success_info' => array(),
            'failed_info' => array(),
            'products_info' => array()
        );
        $store = $this->getStore($data['key'], "date_docs_update");
        foreach ($data['docs'] as $k => $doc) {
            if($doc['GUID']){
                $key = $doc['GUID'];
            }else{
                $key = $k;
            }
            // если удаление
            if ($doc['delete']) {
                $criteria = array(
                    "guid" => $key
                );
                $criteria['store_id'] = $store['id'];
                $doc = $this->modx->getObject('slStoreDocs', $criteria);
                if ($doc) {
                    if (!$doc->remove()) {
                        $response['failed_info'][] = array(
                            'guid' => $key,
                            'message' => "Произошла ошибка при удалении документа"
                        );
                    } else {
                        $response['success_info'][] = array(
                            'guid' => $key,
                            'message' => 'Документ удален'
                        );
                    }
                } else {
                    $response['failed_info'][] = array(
                        'guid' => $key,
                        'message' => "Указанный документ не найден"
                    );
                }
            } else {
                // создание
                $required = array('number', 'date', 'products');
                $error = false;
                foreach ($required as $req) {
                    if (!$doc[$req]) {
                        $response['failed_info'][] = array(
                            'guid' => $key,
                            'message' => "Не все обязательные поля переданы. Проверьте наличие: " . implode(",", $required)
                        );
                        $error = true;
                    }
                }
                if (!$error) {
                    // create doc
                    $criteria = array(
                        "guid" => $key,
                        'store_id' => $store['id']
                    );
                    // check dublicate
                    $document = $this->modx->getObject('slStoreDocs', $criteria);
                    if ($document) {
                        $document->set('guid', $key);
                        $document->set('doc_number', $doc['number']);
                        $document->set('date', strtotime($doc['date']));
                        if($data['base_GUID']){
                            $document->set("base_guid", $data['base_GUID']);
                        }
                        $document->set('createdon', time());
                        $document->set('store_id', $store['id']);
                        if ($document->save()) {
                            $doc_id = $document->get('id');
                            $response[$key]['products_info'] = $this->linkDocsProducts($doc_id, $doc['products']);
                            $response['success_info'][] = array(
                                'guid' => $key,
                                'message' => 'Документ обновлен'
                            );
                        } else {
                            $response['failed_info'][] = array(
                                'guid' => $key,
                                'message' => "Произошла ошибка при обновлении документа. Check API!"
                            );
                        }
                    } else {
                        // create new
                        $document = $this->modx->newObject('slStoreDocs');
                        $document->set('guid', $key);
                        if($data['base_GUID']){
                            $document->set("base_guid", $data['base_GUID']);
                        }
                        $document->set('doc_number', $doc['number']);
                        $document->set('date', strtotime($doc['date']));
                        $document->set('createdon', time());
                        $document->set('store_id', $store['id']);
                        if ($document->save()) {
                            // link products
                            $doc_id = $document->get('id');
                            $response[$key]['products_info'] = $this->linkDocsProducts($doc_id, $doc['products']);
                            $response['success_info'][] = array(
                                'guid' => $key,
                                'message' => 'Документ создан'
                            );
                        } else {
                            $response['failed_info'][] = array(
                                'guid' => $key,
                                'message' => "Произошла ошибка при создании документа. Check API!"
                            );
                        }
                    }
                }
            }
        }
        return $response;
    }

    public function getRemain ($store_id, $guid) {
        $criteria = array(
            'guid' => $guid,
            'store_id' => $store_id
        );
        $o = $this->modx->getObject('slStoresRemains', $criteria);
        if($o){
            return $o->get("id");
        }else{
            return false;
        }
    }

    /**
     * Смена статусов наличия при манипуляциях с магазином
     *
     * @param $store_id
     * @param $available
     * @return void
     */
    public function changeAvailableStatus ($store_id, $available) {
        $query = $this->modx->newQuery("slStoresRemains");
        $query->where(array(
            "slStoresRemains.store_id:=" => $store_id,
            "AND:slStoresRemains.product_id:>" => 0
        ));
        $query->select(array("slStoresRemains.product_id"));
        if($query->prepare() && $query->stmt->execute()){
            $products = $query->stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach($products as $product){
                // проверяем остаток в других магазинах, если нужно проставить НЕ в наличии
                if(($available == 99) || ($product['remains'] == 0 && $product['price'] == 0)){
                    if($this->getAvailableStatus($product['product_id'])){
                        $status = 1;
                    }else{
                        $status = 99;
                    }
                }else{
                    $status = 1;
                }
                $this->setProductStatus($product['product_id'], $status);
            }
        }
    }

    /**
     * Генерация отчета о сопоставлении
     *
     * @param $store_id
     * @return void
     */
    public function generateCopoReport($store_id, $generate = false){
        $store = $this->sl->getObject($store_id);
        if($store){
            $name = "Отчет по сопоставлению магазина {$store['name']}";
            $copoReport = $this->modx->getObject("slStoresRemainsReports", array("store_id" => $store_id));
            if(!$copoReport){
                $copoReport = $this->modx->newObject("slStoresRemainsReports");
                $copoReport->set("store_id", $store_id);
                $copoReport->set("createdon", time());
            }else{
                $copoReport->set("updatedon", time());
            }
            $copoReport->set("name", $name);
        }
        if($generate){
            $copoReport->save();
            $this->generateCopoVendorsReport($copoReport->get("id"));
        }
        return $copoReport->toArray();
    }

    /**
     * Генерация отчета по сопоставлению побрендово
     *
     * @param $report_id
     * @return void
     */
    public function generateCopoVendorsReport($report_id){
        $output = array();
        $copoReport = $this->sl->getObject($report_id, "slStoresRemainsReports");
        if($copoReport){
            $brands = array();
            $criteria = array(
                "report_id:=" => $report_id
            );
            $this->modx->removeCollection("slStoresRemainsVendorReports", $criteria);
            $store_id = $copoReport["store_id"];
            $query = $this->modx->newQuery("slStoresRemains");
            $query->leftJoin("msProductData", "msProductData", "msProductData.id = slStoresRemains.product_id");
            $query->leftJoin("modResource", "modResource", "modResource.id = slStoresRemains.product_id");
            $query->select(array("DISTINCT(slStoresRemains.brand_id) AS vendor"));
            $query->where(array("slStoresRemains.store_id:=" => $store_id));
            // $query->limit(30, 10);
            if($query->prepare() && $query->stmt->execute()){
                $results = $query->stmt->fetchAll(PDO::FETCH_ASSOC);
                foreach($results as $v){
                    $criteria = array(
                        "vendor_id:=" => $v["vendor"],
                        "report_id:=" => $report_id
                    );
                    $vendor = $this->modx->getObject("slStoresRemainsVendorReports", $criteria);
                    if(!$vendor){
                        $vendor = $this->modx->newObject("slStoresRemainsVendorReports");
                        $vendor->set("vendor_id", $v["vendor"]);
                        $vendor->set("report_id", $report_id);
                        $vendor->set("createdon", time());
                    }else{
                        $vendor->set("updatedon", time());
                    }

                    // кол-во найденных
                    $query = $this->modx->newQuery("slStoresRemains");
                    $query->where(array(
                        "slStoresRemains.store_id:=" => $store_id,
                        "slStoresRemains.brand_id:=" => $v["vendor"],
                    ));
                    $find_count = $this->modx->getCount("slStoresRemains", $query);
                    $query->where(array("slStoresRemains.remains:>" => 0));
                    $find_count_in_stock = $this->modx->getCount("slStoresRemains", $query);
                    $vendor->set("find", $find_count);
                    $vendor->set("find_in_stock", $find_count_in_stock);

                    // кол-во идентифицированных
                    $query = $this->modx->newQuery("slStoresRemains");
                    $query->where(array(
                        "slStoresRemains.store_id:=" => $store_id,
                        "slStoresRemains.brand_id:=" => $v["vendor"],
                        "slStoresRemains.product_id:>" => 0,
                    ));
                    $ident_count = $this->modx->getCount("slStoresRemains", $query);
                    $query->where(array("slStoresRemains.remains:>" => 0));
                    $ident_in_stock = $this->modx->getCount("slStoresRemains", $query);
                    $vendor->set("identified", $ident_count);
                    $vendor->set("identified_in_stock", $ident_in_stock);

                    // Процент найденных
                    if ($find_count) {
                        $percent = $ident_count / $find_count * 100;
                        $vendor->set("percent_identified", $percent);
                    }
                    if ($find_count_in_stock) {
                        $percent = $ident_in_stock / $find_count_in_stock * 100;
                        $vendor->set("percent_identified_in_stock", $percent);
                    }

                    // сумма товара
                    $query = $this->modx->newQuery("slStoresRemains");
                    $query->where(array(
                        "slStoresRemains.store_id:=" => $store_id,
                        "slStoresRemains.brand_id:=" => $v["vendor"],
                    ));
                    $query->select(array("SUM(slStoresRemains.remains * slStoresRemains.price) as summ"));
                    if($query->prepare() && $query->stmt->execute()){
                        $summ = $query->stmt->fetch(PDO::FETCH_ASSOC);
                        if($summ["summ"]){
                            $vendor->set("summ", $summ["summ"]);
                        }
                    }

                    // сумма товара сопоставленного
                    $query = $this->modx->newQuery("slStoresRemains");
                    $query->where(array(
                        "slStoresRemains.store_id:=" => $store_id,
                        "slStoresRemains.brand_id:=" => $v["vendor"],
                        "slStoresRemains.product_id:>" => 0
                    ));
                    $query->select(array("SUM(slStoresRemains.remains * slStoresRemains.price) as summ"));
                    if($query->prepare() && $query->stmt->execute()){
                        $summ = $query->stmt->fetch(PDO::FETCH_ASSOC);
                        if($summ["summ"]){
                            $vendor->set("summ_copo", $summ["summ"]);
                        }
                    }

                    if($v["vendor"]){
                        // кол-во карточек
                        $q = $this->modx->newQuery("msProductData");
                        $q->where(array("vendor:=" => $v["vendor"]));
                        $total = $this->modx->getCount('msProductData', $q);
                        $vendor->set("cards", $total);
                    }else{
                        $vendor->set("identified", 0);
                        $vendor->set("percent_identified", 0);
                        $vendor->set("identified_in_stock", 0);
                        $vendor->set("percent_identified_in_stock", 0);
                    }

                    $vendor->save();
                    $output[$v["vendor"]] = $vendor->toArray();
                    $brands[] = $v["vendor"];
                }
                // очистка от брендов, которые мы не нашли
                $query = $this->modx->newQuery("slStoresRemainsVendorReports");
                $query->where(array(
                    "slStoresRemainsVendorReports.vendor_id:NOT IN" => $brands,
                    "slStoresRemainsVendorReports.report_id:=" => $report_id
                ));
                $query->select(array("slStoresRemainsVendorReports.id"));
                if($query->prepare() && $query->stmt->execute()){
                    $results = $query->stmt->fetchAll(PDO::FETCH_ASSOC);
                    foreach($results as $brand) {
                        $obj = $this->modx->getObject('slStoresRemainsVendorReports', $brand["id"]);
                        if (!$obj->remove()) {
                            $this->modx->log(1, "Ошибка удаления объекта slStoresRemainsVendorReports {$brand["id"]}");
                        }
                    }
                }
            }
        }
        return $output;
    }

    /**
     * Установка значения наличия
     *
     * @param $product_id
     * @param $available
     * @return void
     */
    public function setProductStatus($product_id, $available){
        $data = array(
            "available" => $available
        );
        $this->sl->api->update("msProductData", $data, $product_id);
    }

    /**
     * Проверка остатка в активных магазинах
     *
     * @param $product_id
     * @return bool
     */
    public function getAvailableStatus($product_id){
        $query = $this->modx->newQuery("slStoresRemains");
        $query->leftJoin("slStores", "slStores", "slStores.id = slStoresRemains.store_id");
        $query->where(array(
            "slStoresRemains.product_id:=" => $product_id,
            "slStoresRemains.remains:>" => 0,
            "slStoresRemains.price:>" => 0,
            "slStores.active:=" => 1
        ));
        $query->select(array("slStoresRemains.*"));
        if($query->prepare() && $query->stmt->execute()){
            $remain = $query->stmt->fetch(PDO::FETCH_ASSOC);
            if($remain["remains"] > 0){
                return true;
            }
        }
        return false;
    }

    /**
     * Импорт остатков на определенный день
     *
     * @param $data
     * @return int[]
     * @throws Exception
     */
    public function importRemainsCheckpoints($data) {
        $response = array(
            'created' => 0,
            'failed' => 0,
            'updated' => 0
        );
        $store = $this->getStore($data['key'], "remains_checkpoint_update");
        if($store){
            foreach ($data['product_archive'] as $k => $archive) {
                $today = new DateTime($archive['date']);
                $dd = $today->getTimestamp();
                $today->setTime(0,0,0);
                $date_from = $today->getTimestamp();
                $today->setTime(23,59,59);
                $date_to = $today->getTimestamp();
                foreach($archive['products'] as $product){
                    $remain_id = $this->getRemain($store['id'], $product['GUID']);
                    if(!$remain_id){
                        $remain_id = $this->importRemainSingle($store['id'], $product);
                    }
                    // если дата сегодня
                    if($dd >= $date_from && $dd <= $date_to){
                        $remain = $this->modx->getObject("slStoresRemains", $remain_id);
                        $remain->set("updatedon", time());
                        $remain->set("count", $product['count_current']);
                        $remain->set("price", $product['price']);
                        $remain->set("remains", $product['count_current']);
                        $remain->set("reserved", 0);
                        $remain->set("available",$product['count_free']);
                        $remain->save();
                    }
                    if($remain_id){
                        $criteria = array(
                            "remain_id" => $remain_id,
                            "date:>=" => date('Y-m-d H:i:s', $date_from),
                            "date:<=" => date('Y-m-d H:i:s', $date_to),
                        );
                        $setter = $this->modx->getObject("slStoresRemainsHistory", $criteria);
                        if(!$setter){
                            $setter = $this->modx->newObject("slStoresRemainsHistory");
                            $setter->set("remain_id", $remain_id);
                            $setter->set("createdon", time());
                            $setter->set("date", $dd);
                            $response['created']++;
                        }else{
                            $setter->set("updatedon", time());
                            $response['updated']++;
                        }
                        $setter->set("count", $product['count_current']);
                        $setter->set("price", $product['price']);
                        $setter->set("remains", $product['count_current']);
                        $setter->set("reserved", 0);
                        $setter->set("available",$product['count_free']);
                        $setter->save();
                    }
                }
            }
        }
        return $response;
    }

    /**
     * считаем скорость продаж за последний месяц
     *
     * @param $remain_id
     * @return array
     */
    public function getPurchaseSpeed($remain_id){
        $remain = $this->sl->getObject($remain_id, "slStoresRemains");
        $today = date_create();
        $month_ago = date_create("-1 MONTH");
        date_time_set($month_ago, 00, 00);
        $query = $this->modx->newQuery("slStoreDocsProducts");
        $query->leftJoin("slStoreDocs", "slStoreDocs", "slStoreDocs.id = slStoreDocsProducts.doc_id");
        $query->where(array(
            "slStoreDocsProducts.remain_id" => $remain_id,
            "slStoreDocs.date:>=" => date_format($month_ago, 'Y-m-d H:i:s'),
            "slStoreDocs.date:<=" => date_format($today, 'Y-m-d H:i:s'),
        ));
        $query->select("slStoreDocsProducts.remain_id, SUM(slStoreDocsProducts.count) as sales");
        $query->groupby("slStoreDocsProducts.remain_id");
        if($query->prepare() && $query->stmt->execute()){
            $result = $query->stmt->fetch(PDO::FETCH_ASSOC);
            if($result){
                if($result["sales"]){
                    $query = $this->modx->newQuery("slStoresRemainsHistory");
                    $query->where(array(
                        "slStoresRemainsHistory.remain_id" => $remain_id,
                        "slStoresRemainsHistory.date:>=" => date_format($month_ago, 'Y-m-d H:i:s'),
                        "slStoresRemainsHistory.date:<=" => date_format($today, 'Y-m-d H:i:s'),
                        "slStoresRemainsHistory.remains:>" => 0,
                    ));
                    $query->select(array("slStoresRemainsHistory.*"));
                    if($query->prepare() && $query->stmt->execute()){
                        $times = $query->stmt->fetch(PDO::FETCH_ASSOC);
                        $result['times'] = count($times);
                        if($times){
                            $result['speed'] = $result['sales'] / count($times);
                            $result['speed'] = round($result['speed'], 2);
                        }else{
                            $result['speed'] = 0;
                        }
                    }
                }else{
                    $result['speed'] = 0;
                }
            }else{
                $result['remain_id'] = $remain_id;
                $result['sales'] = 0;
                $result['speed'] = 0;
            }
            $result['price'] = $remain['price'];
            // дополнительно выставляем дней с out of stock, если кол-во равно нулю
            $today = date_create();
            $month_ago = date_create("-1 MONTH");
            date_time_set($month_ago, 00, 00);

            $date_from = date_format($month_ago, 'Y-m-d H:i:s');
            $date_to = date_format($today, 'Y-m-d H:i:s');
            $sql = "SELECT (SELECT count(1) FROM `gewn5fer4GqeR_sl_stores_remains_history` t2 WHERE t2.`remain_id` = {$remain_id} AND t2.`date` >= '{$date_from}' AND t2.`date` <= '{$date_to}' AND t2.remains = `gewn5fer4GqeR_sl_stores_remains_history`.remains AND not exists (SELECT 1 FROM `gewn5fer4GqeR_sl_stores_remains_history` t3 WHERE t3.`remain_id` = '{$remain_id}' AND t3.`date` >= '{$date_from}' AND t3.`date` <= '{$date_to}' AND t3.id between least(`gewn5fer4GqeR_sl_stores_remains_history`.id, t2.id) AND greatest(`gewn5fer4GqeR_sl_stores_remains_history`.id, t2.id) AND t3.remains <> t2.remains)) AS repeats FROM `gewn5fer4GqeR_sl_stores_remains_history` WHERE `remains` = 0 AND `remain_id` = {$remain_id} AND `date` >= '{$date_from}' AND `date` <= '{$date_to}' LIMIT 1";
            $statement = $this->modx->prepare($sql);
            if($statement->execute()){
                $repeats = $statement->fetch(PDO::FETCH_ASSOC);
                if($repeats['repeats'] && $remain['remains'] == 0){
                    $result['out_of_stock_days'] = $repeats['repeats'];
                    $result['no_money'] = $remain['price'] * $repeats['repeats'] * $result['speed'];
                }else{
                    $result['out_of_stock_days'] = 0;
                    $result['no_money'] = 0;
                }
            }else{
                $result['out_of_stock_days'] = 0;
                $result['no_money'] = 0;
            }

            return $result;
        }
    }

    /**
     * Берем актуальное дерево категорий (рекурсивно)
     *
     * @param $parent
     * @return array
     */
    public function getActualProductCategories($vendor){
        $categories = array();
        $query = $this->modx->newQuery("msProductData");
        $query->leftJoin("modResource", "modResource", "modResource.id = msProductData.id");
        $query->select(array("DISTINCT(modResource.parent) as id"));
        $query->where(array(
            "msProductData.vendor:=" => $vendor
        ));
        if ($query->prepare() && $query->stmt->execute()) {
            $cats = $query->stmt->fetchAll(PDO::FETCH_ASSOC);
            if($cats){
                foreach($cats as $cat){
                    $cat = $this->sl->getObject($cat["id"], "modResource");
                    if($cat){
                        $categories[] = $cat;
                    }
                }
            }
        }
        return $categories;
    }

    /**
     * Генерация YML файлов магазина
     *
     * @param $store_id
     * @return array[]
     */
    public function generateStoreYML($store_id){
        $this->modx->query( '
          SET `low_priority_updates` = `ON`
        ' );
        $output = array(
            'categories' => array(),
            'vendors' => array(),
            'products' => array()
        );
        $store = $this->sl->store->getStore($store_id);
        if($store){
            // проверяем остатки магазина
            $query = $this->modx->newQuery("slStoresRemains");
            $query->select(array("slStoresRemains.*"));
            $query->where(array("store_id:=" => $store_id));
            $all_data = $this->modx->getCount("slStoresRemains", $query);
            if($all_data){
                $file_path = $this->modx->getOption("base_path").'assets/files/organization/'.$store['id'].'/ymls/';
                $filename = $file_path.'remains.yml';
                $file_url = '/assets/files/organization/'.$store['id'].'/ymls/remains.yml';
                if(!file_exists($file_path)){
                    mkdir($file_path, 0755, true);
                }
                $fd = fopen($filename, 'w');
                $base_content = $this->sl->pdoTools->getChunk("@FILE chunks/catalog_yml_base.tpl", $output);
                fwrite($fd, $base_content);
                fseek($fd, 0, SEEK_END);
                $limit = 500;
                for($i = 0; $i <= $all_data; $i += $limit) {
                    $query = $this->modx->newQuery("slStoresRemains");
                    $query->select(array("slStoresRemains.*"));
                    $query->where(array("store_id:=" => $store_id));
                    $query->limit($limit, $i);
                    if ($query->prepare() && $query->stmt->execute()) {
                        $products = $query->stmt->fetchAll(PDO::FETCH_ASSOC);
                        foreach ($products as $key => $product) {
                            // записываем в файл
                            $offer_content = "\r\n" . $this->sl->pdoTools->getChunk("@FILE chunks/catalog_yml_remain.tpl", $products[$key]) . "\r\n";
                            fwrite($fd, $offer_content);
                        }
                    }
                }
                $end_file = "</offers>\r\n</shop>\r\n</yml_catalog>";
                fwrite($fd, $end_file);
                fclose($fd);
                $st = $this->modx->getObject("slStores", $store['id']);
                if($st){
                    $st->set("yml_file", $file_url);
                    $st->save();
                }
            }
        }
        $this->modx->query( '
           SET `low_priority_updates` = `OFF`
        ' );
        return $output;
    }

    /**
     * Генерация YML файлов каталога товаров по брендам
     *
     * @return array[]
     */
    public function generateVendorsYMLs(){
        $this->modx->query( '
          SET `low_priority_updates` = `ON`
        ' );
        $output = array(
            'categories' => array(),
            'vendors' => array(),
            'products' => array()
        );
        $output['categories'] = $this->getActualProductCategories(2);
        $cats = array();
        foreach($output['categories'] as $c){
            $cats[] = $c['id'];
        }
        $query = $this->modx->newQuery("msVendor");
        $query->select(array("msVendor.id as id, msVendor.name as name"));
        $query->where(array("msVendor.id:=" => 1355));
        $all_data = $this->modx->getCount("msVendor", $query);
        // ограничение на память
        $limit = 500;
        for($i = 0; $i <= $all_data; $i += $limit) {
            $query = $this->modx->newQuery("msVendor");
            $query->select(array("msVendor.id as id, msVendor.name as name"));
            $query->where(array("msVendor.id:=" => 1355));
            $query->limit($limit, $i);
            if ($query->prepare() && $query->stmt->execute()) {
                $vendors = $query->stmt->fetchAll(PDO::FETCH_ASSOC);
                $output['vendors'] = array_merge($output['vendors'], $vendors);
                // получили производителей, создаем файл и записываем туда товары
                foreach($vendors as $vendor){
                    $products_data = 0;
                    $query = $this->modx->newQuery("msProductData");
                    $query->leftJoin("modResource", "modResource", "modResource.id = msProductData.id");
                    $query->select(array("COUNT(*) as count"));
                    $query->where(array(
                        "msProductData.vendor:=" => $vendor['id'],
                        "AND:modResource.parent:IN" => $cats
                    ));
                    if ($query->prepare() && $query->stmt->execute()) {
                        $prods = $query->stmt->fetch(PDO::FETCH_ASSOC);
                        $products_data = $prods['count'];
                    }
                    $limit = 500;
                    // проверяем есть ли товары и начинаем собирать файл
                    if($products_data){
                        // assets/files/ymls/
                        $res = $this->modx->newObject("modResource");
                        $name = $res->cleanAlias($vendor['name']);
                        $file_path = $this->modx->getOption("base_path").'assets/files/ymls/';
                        $filename = $file_path.$name.'_'.$vendor['id'].'.yml';
                        if(!file_exists($file_path)){
                            mkdir($file_path, 0755, true);
                        }
                        // if (!file_exists($filename)) {
                            // открываем файл и пишем данные
                            $fd = fopen($filename, 'w');
                            $base_content = $this->sl->pdoTools->getChunk("@FILE chunks/catalog_yml_base.tpl", $output);
                            fwrite($fd, $base_content);
                            fseek($fd, 0, SEEK_END);
                            for ($i = 0; $i <= $products_data; $i += $limit) {
                                $query = $this->modx->newQuery("msProductData");
                                $query->leftJoin("modResource", "modResource", "modResource.id = msProductData.id");
                                $query->select(array("modResource.pagetitle, modResource.introtext, modResource.content, modResource.parent, msProductData.*"));
                                $query->where(array(
                                    "msProductData.vendor:=" => $vendor['id'],
                                    "AND:modResource.parent:IN" => $cats
                                ));
                                $query->limit($limit, $i);
                                if ($query->prepare() && $query->stmt->execute()) {
                                    $products = $query->stmt->fetchAll(PDO::FETCH_ASSOC);
                                    foreach ($products as $key => $product) {
                                        $file_url = "assets/files/products/" . $product['id'] . '/';
                                        $dir = $this->modx->getOption("base_path") . $file_url;
                                        $files = array();
                                        if ($handle = opendir($dir)) {
                                            $index = 1;
                                            while (false !== ($file = readdir($handle))) {
                                                if ($file != "." && $file != "..") {
                                                    $path_parts = pathinfo($file_url . $file);
                                                    $file_data = array(
                                                        "url" => $file_url . $file,
                                                        "name" => $product['pagetitle'],
                                                        "product_id" => $product['id'],
                                                        "file_id" => $product['id'] . '_' . $index
                                                    );
                                                    $files[$path_parts['filename']] = $file_data;
                                                    $index++;
                                                }
                                            }
                                        }
                                        $products[$key]["images"] = $files;
                                        $products[$key]["vendor_name"] = $vendor['name'];
                                        // записываем в файл
                                        $offer_content = "\r\n" . $this->sl->pdoTools->getChunk("@FILE chunks/catalog_yml_product.tpl", $products[$key]) . "\r\n";
                                        fwrite($fd, $offer_content);
                                    }
                                    // $output['products'] = array_merge($output['products'], $products);
                                }
                            }
                            $end_file = "</offers>\r\n</shop>\r\n</yml_catalog>";
                            fwrite($fd, $end_file);
                            fclose($fd);
                        // }
                    }
                }
            }
        }
        $this->modx->query( '
           SET `low_priority_updates` = `OFF`
        ' );
        return $output;
    }

    public function isJson($string) {
        json_decode($string);
        return json_last_error() === JSON_ERROR_NONE;
    }

    public function tolog($id, $data) {
        $this->modx->log(xPDO::LOG_LEVEL_ERROR, print_r($data, 1), array(
            'target' => 'FILE',
            'options' => array(
                'filename' => 'import_1c_'.$id.'.log'
            )
        ));
    }
}