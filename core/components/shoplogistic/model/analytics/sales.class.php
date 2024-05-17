<?php
class salesAnalyticsHandler
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
            case 'set':
                $response = $this->setAction($properties);
                break;
            case 'get':
                $response = $this->getAction($properties);
                break;
            case 'delete':
                $response = $this->deleteAction($properties);
                break;
            case 'off/on':
                $response = $this->offAndOnAction($properties);
                break;
        }
        return $response;
    }

    /**
     * Создание/редактирование акции
     * @return array
     */
    public function setAction($properties){

        $store_id = $properties['id'];
        $start = new DateTime($properties['dates'][0]);
        $start->setTime(00,00);
        $end = new DateTime($properties['dates'][1]);
        $end->setTime(23,59);

        if($properties['action_id']){
            $action = $this->modx->getObject('slActions', $properties['action_id']);
        }else{
            $action = $this->modx->newObject('slActions');
        }

        if($action){
            $action->set("store_id", $store_id);
            $action->set("name", $properties['name']);
            $action->set("description", $properties['description']);

            if(!file_exists($this->modx->getOption('base_path') . "assets/content/banners/")){
                mkdir($this->modx->getOption('base_path') . "assets/content/banners/", 0777, true);
            }

            if($properties['files']['max']['name']) {
                $image = $this->modx->getOption('base_path') . "assets/content/banners/" . $properties['files']['max']['name'];

                if(rename($this->modx->getOption('base_path') . $properties['files']['max']['original'], $image)){
                    $action->set("image", "banners/" . $properties['files']['max']['name']);
                }
            }

            if($properties['files']['min']['name']){
                $image_inner = $this->modx->getOption('base_path') . "assets/content/banners/" . $properties['files']['min']['name'];

                if(rename($this->modx->getOption('base_path') . $properties['files']['min']['original'], $image_inner)){
                    $action->set("image_inner", "banners/" . $properties['files']['min']['name']);
                }
            }

            $action->set("compatibility_discount", $properties['compatibilityDiscount']);
            $action->set("compatibility_postponement", $properties['compatibilityPost']);
            $action->set("date_from", $start->format('Y-m-d H:i:s'));
            $action->set("date_to", $end->format('Y-m-d H:i:s'));
            $action->set("createdon", time());
            $action->set("active", 1);
            $action->save();
            if($action->get('id')){
                if($properties['action_id']){
                    $crit = array(
                        "action_id" => $properties['action_id']
                    );
                    $this->modx->removeCollection("slActionsProducts", $crit);
                    $this->modx->removeCollection("slActionsStores", $crit);
                }

                foreach($properties['products'] as $product){
                    $action_p = $this->modx->newObject("slActionsProducts");
                    $action_p->set("action_id", $action->get('id'));
                    $action_p->set("product_id", $product['id']);
                    $price = (float)$product['price'];
                    $action_p->set("old_price", $price);
                    $action_p->set("new_price", $product['finalPrice']);

                    $action_p->save();
                }

                foreach($properties['organizations'] as $organization){
                    $action_o = $this->modx->newObject("slActionsStores");
                    $action_o->set("action_id", $action->get('id'));
                    $action_o->set("store_id", $organization['id']);

                    $action_o->save();
                }

                return $action->toArray();
            }
        }
    }

    /**
     * Просмотр акций
     * @return array
     */
    public function getAction($properties){
        if($properties['action_id']){
            $action = $this->modx->getObject("slActions", $properties['action_id']);
            if($action){
                $data = $action->toArray();
                $data['date_from'] = date('Y/m/d H:i:s', strtotime($data['date_from']));
                $data['date_to'] = date('Y/m/d H:i:s', strtotime($data['date_to']));

                $q = $this->modx->newQuery("slActionsProducts");
                $q->leftJoin("modResource", "modResource", "modResource.id = slActionsProducts.product_id");
                $q->leftJoin("msProductData", "msProductData", "msProductData.id = slActionsProducts.product_id");
                $q->select(array(
                    'slActionsProducts.*',
                    'modResource.pagetitle as name',
                    'msProductData.image as image', 
                ));
                $q->where(array("`slActionsProducts`.`action_id`:=" => $data['id']));

                if ($q->prepare() && $q->stmt->execute()) {
                    $products = $q->stmt->fetchAll(PDO::FETCH_ASSOC);
                    $selected = new stdClass();

                    $count_products = 0;

                    foreach($products as $product){
                        $id = $product['product_id'];
                        $product['id'] = $id;
                        $product['price'] = (float)$product['old_price'];
                        $product['discountInRubles'] = (float)$product['old_price'] - $product['new_price'];
                        $product['discountInterest'] = $product['discountInRubles'] / ($product['old_price'] / 100);
                        $product['finalPrice'] = (float)$product['new_price'];
                        $selected->$id = $product;
                        $count_products++;
                    }

                    $data['products'] = $selected;
                    $data['total_products'] = $count_products;
                }

                $query = $this->modx->newQuery("slActionsStores");
                $query->leftJoin("slStores", "slStores", "slStores.id = slActionsStores.store_id");
                $query->select(array(
                    'slActionsStores.*',
                    'slStores.name',
                    'slStores.image'
                ));
                $query->where(array("`slActionsStores`.`action_id`:=" => $data['id']));

                if ($query->prepare() && $query->stmt->execute()) {
                    $organizations = $query->stmt->fetchAll(PDO::FETCH_ASSOC);
                    $organizations_selected = new stdClass();

                    $urlMain = $this->modx->getOption("site_url");

                    foreach($organizations as $organization){
                        $id = $organization['store_id'];
                        $organization['id'] = $id;
                        $organization['image'] = $urlMain . "assets/content/" . $organization['image'];
                        $organizations_selected->$id = $organization;
                    }

                    $data['organization'] = $organizations_selected;
                }

                if($data['image']){
                    $data['image'] = "assets/content/" . $data['image'];
                }

                if($data['image_inner']){
                    $data['image_inner'] = "assets/content/" . $data['image_inner'];
                }

                //$products = $this->modx->getCollection("slActionsProducts", array("action_id" => $data['id']));
                //$data['products'] = $products;
                //$properties["sel_arr"] = array();
                //foreach($products as $product){
                    //$properties["sel_arr"][] = $product->get("product_id");
                //}
                //$data['products'][] = $this->getAvailableProducts($data['store_id'], $properties, 0);
                //$data['products'][] = $this->getAvailableProducts($data['store_id'], $properties, 1);
                return $data;
            }
        }else{
            $q = $this->modx->newQuery("slActions");
            $q->select(array(
                'slActions.*'
            ));
            $q->where(array("`slActions`.`store_id`:=" => $properties['id']));
            if($properties['filtersdata']){
                if(isset($properties['filtersdata']['range'])){
                    if($properties['filtersdata']['range'][0] && $properties['filtersdata']['range'][1]){
                        $from = date('Y-m-d H:i:s', strtotime($properties['filtersdata']['range'][0]));
                        $to = date('Y-m-d H:i:s', strtotime($properties['filtersdata']['range'][1]));
                        $q->where(array("`slActions`.`date_from`:<=" => $from, "`slStoresMatrix`.`date_to`:>=" => $to));
                    }
                    if($properties['filtersdata']['range'][0] && !$properties['filtersdata']['range'][1]){
                        $from = date('Y-m-d H:i:s', strtotime($properties['filtersdata']['range'][0]));
                        $q->where(array("`slActions`.`date_from`:<=" => $from));
                    }
                }
                if($properties['filter']){
                    $words = explode(" ", $properties['filter']);
                    foreach($words as $word){
                        $criteria = array();
                        $criteria['slActions.name:LIKE'] = '%'.trim($word).'%';
                        $q->where($criteria);
                    }
                }
            }
            $result = array();
            // Подсчитываем общее число записей
            $result['total'] = $this->modx->getCount("slActions", $q);

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
                    if($result['items'][$key]['image']){
                        $result['items'][$key]['image'] = "assets/content/" . $result['items'][$key]['image'];
                    }

                    if($result['items'][$key]['image_inner']){
                        $result['items'][$key]['image_inner'] = "assets/content/" . $result['items'][$key]['image_inner'];
                    }
                }
                $this->modx->log(1, print_r($output, 1));
                return $result;
            }
        }
    }

    /**
     * Удаление акции
     * @return array
     */
    public function deleteAction($properties) {
        if($properties['store_id'] && $properties['action_id']){
            $action = $this->modx->getObject("slActions", array('id' => $properties['action_id'], 'store_id' => $properties['store_id']));
            if($action){
                $action->remove();

                $result = array(
                    "status" => true
                );

                return $result;
            }
        }

        $result = array(
            "status" => false
        );
        return $result;
    }

    /**
     * Удаление акции
     * @return array
     */
    public function offAndOnAction($properties){
        if($properties['store_id'] && $properties['action_id']) {
            $action = $this->modx->getObject("slActions", array('id' => $properties['action_id'], 'store_id' => $properties['store_id']));

            if($action) {
                if($action->active){
                    $action->set("active", 0);
                }else{
                    $action->set("active", 1);
                }
                $action->save();
                return $action->toArray();
            }
        }

        $result = array(
            "status" => false
        );
        return $result;
    }
}