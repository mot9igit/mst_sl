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

    public function importRemain($data){
        if($data['key']) {
            $store = $this->getStore($data['key']);
            if ($store['id']) {
                $this->tolog('store_' . $store['id'], $data);
                $response = array();
                $response['success_info'] = array();
                $response['failed_info'] = array();
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
                    if ($error) {
                        $response['failed_info'][] = array(
                            'guid' => $key,
                            'message' => $message
                        );
                    } else {
                        // проверяем товар на дублирование по GUID
                        $criteria = array(
                            'guid' => $key,
                            'store_id' => $store['id']
                        );
                        $o = $this->modx->getObject('slStoresRemains', $criteria);
                        if (!$o) {
                            $o = $this->modx->newObject('slStoresRemains');
                            if ($message) {
                                $message = "Product created! Info: " . $message;
                            } else {
                                $message = "Product created!";
                            }
                        } else {
                            if ($message) {
                                $message = "Product updated! Info: " . $message;
                            } else {
                                $message = "Product updated!";
                            }
                        }
                        $o->set("guid", $key);
                        if($data['base_GUID']){
                            $o->set("base_guid", $data['base_GUID']);
                        }
                        if($product['barcode']){
                            $o->set("barcode", $product['barcode']);
                        }
                        $o->set("article", $product['article']);
                        $o->set("remains", $product['count_current']);
                        $o->set("catalog", $product['catalog']);
                        $reserved = $o->get('reserved');
                        if ($reserved > 0) {
                            $available = $product['count_free'] - $reserved;
                        } else {
                            $available = $product['count_free'];
                        }
                        if ($available < 0) {
                            $o->set("available", 0);
                        } else {
                            $o->set("available", $available);
                        }
                        $o->set("price", $product['price']);
                        $o->set('store_id', $store['id']);
                        if ($product['name']) {
                            $o->set("name", $product['name']);
                        }
                        if ($o->save()) {
                            // линкуем товар
                            if(!$o->get('product_id') && $o->get('autolink')){
                                $this->linkProduct($o->get('id'), $store['type']);
                            }
                            // синхронизация, если необходима
                            $stores = $this->modx->getCollection("slWarehouseStores", array("warehouse_id" => $store['id'], "sync" => 1));
                            if (count($stores)) {
                                $remain = $o->toArray();
                                foreach ($stores as $store) {
                                    unset($remain['warehouse_id']);
                                    $remain["store_id"] = $store->get("store_id");
                                    $settings = array(
                                        // Здесь указываем где лежат наши процессоры (по умолчанию стандартный каталог)
                                        'processors_path' => $this->modx->getOption('core_path') . 'components/shoplogistic/processors/'
                                    );
                                    // проверяем существование остатка
                                    $obj = $this->modx->getObject('slStoresRemains', array('store_id' => $remain['store_id'], 'guid' => $remain['guid']));
                                    if ($obj) {
                                        $action = 'update';
                                        $remain['id'] = $obj->get('id');
                                    } else {
                                        $action = 'create';
                                    }
                                    $response = $this->modx->runProcessor('mgr/storeremains/' . $action, $remain, $settings);
                                    if ($response->isError()) {
                                        $this->modx->log(xPDO::LOG_LEVEL_ERROR, "Ошибка синхронизации остатков: " . $response->getMessage());
                                    }
                                    $this->modx->error->reset();
                                }
                            }
                            $response['success_info'][] = array(
                                'guid' => $key,
                                'message' => $message
                            );
                        } else {
                            $response['failed_info'][] = array(
                                'guid' => $key,
                                'message' => 'Product save filed, check API!'
                            );
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

    public function getStore($key) {
        $store = $this->modx->getObject("slStores", array('apikey' => $key));
        if($store){
            $resp = array(
                'id' => $store->get('id')
            );
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
        If($type == 'slStores'){
            $object = 'slStoresRemains';
        }
        if($type == 'slWarehouse'){
            $object = 'slWarehouseRemains';
        }
        $remain = $this->sl->objects->getObject($object, $remain_id);
        if($remain){
            $vendor = $this->searchVendor($remain['name']);
            if(!$vendor){
                $vendor = $this->searchVendor($remain['catalog']);
            }
            if($vendor){
                // если известен производитель ищем по артикулу
                $query = $this->modx->newQuery("modResource");
                $query->leftJoin("msProductData", "Data");
                $query->where(array(
                    "`Data`.`vendor_article`:=" => $remain['article'],
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
                    if($product){
                        // линкуем товар, если цена не отличается более чем на процент из кофигурации
                        $percent = abs((($product['price'] - $remain['price']) * 100) / $product['price']);
                        if($percent <= $this->config['product_price_percent']){
                            $this->sl->api->update($object, array("product_id" => $product['id']), $remain_id);
                        }else{
                            // TODO: уведомление + пометка о расхождении цены на 20%
                            return 3;
                        }
                        return true;
                    }else{
                        // TODO: уведомление о том, что товары бренда есть, а данного нет
                        return 2;
                    }
                }
            }else{
                // ничего не делаем, производитель не найден
                return 4;
            }
        }
        return false;
    }

    /**
     *
     * Поиск производителя из набора слов
     *
     * @param $name
     * @return mixed
     */

    public function searchVendor($name){
        $where = array();
        $name = trim(preg_replace('/\s+/', ' ', preg_replace('/[^ a-zа-яё\d]/ui', ' ', $name)));
        $words = explode(" ", $name);
        foreach($words as $key => $word){
            $words[$key] = preg_replace('/[^ a-zа-яё\d]/ui', '', trim($word));
        }
        $where["name:IN"] = $words;
        $vendor = $this->sl->objects->getObject("msVendor", 0, $where);
        return $vendor;
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

    public function buildCategoriesTree (array &$categories, $parentId = 0, $idKey = 'id') {
        $branch = array();
        foreach ($categories as &$element) {
            if ($element['parent'] == $parentId) {
                $children = $this->buildCategoriesTree($categories, $element[$idKey]);
                if ($children) {
                    $element['children'] = $children;
                }
                $elem_id = $element['id'];
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
            $criteria = array(
                "guid" => $product['guid']
            );
            $remain = $this->modx->getObject('slStoresRemains', $criteria);
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
        $store = $this->getStore($data['key']);
        foreach ($data['docs'] as $key => $doc) {
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