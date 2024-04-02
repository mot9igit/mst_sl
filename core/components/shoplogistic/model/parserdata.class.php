<?php

class parserdata
{
    public $modx;
    public $sl;
    public $config;

    public function __construct(shopLogistic &$sl, modX &$modx, array $config = array())
    {
        $this->sl =& $sl;
        $this->modx =& $modx;
        $this->modx->lexicon->load('shoplogistic:default');

        $corePath = $this->modx->getOption('shoplogistic_core_path', $config, $this->modx->getOption('core_path') . 'components/shoplogistic/');
        $assetsUrl = $this->modx->getOption('shoplogistic_assets_url', $config, $this->modx->getOption('assets_url') . 'components/shoplogistic/');
        $assetsPath = $this->modx->getOption('shoplogistic_assets_path', $config, $this->modx->getOption('base_path') . 'assets/components/shoplogistic/');


        $this->config = array_merge([
            'corePath' => $corePath,
            'modelPath' => $corePath . 'model/',
            'processorsPath' => $corePath . 'processors/',

            'connectorUrl' => $assetsUrl . 'connector.php',
            'assetsUrl' => $assetsUrl,
            'assetsPath' => $assetsPath,
            'cssUrl' => $assetsUrl . 'css/',
            'jsUrl' => $assetsUrl . 'js/',
        ], $config);

        $this->config['token'] = $this->modx->getOption("shoplogistic_parserdata_token");
        $this->config['url'] = $this->modx->getOption("shoplogistic_parserdata_url");
    }

    /**
     * Обработка тасков
     *
     * @return void
     */
    public function handleTasks(){
        // Обрабатываем новые
        $query = $this->modx->newQuery("slParserDataTasks");
        $query->where(array(
            "status:=" => 1,
            "external_id:=" => ""
        ));
        $query->select(array("slParserDataTasks.*"));
        if($query->prepare() && $query->stmt->execute()){
            $tasks = $query->stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach($tasks as $task){
                $links = explode(",", $task["url"]);
                $data = array();
                foreach($links as $link){
                    $data["products"][] = array(
                        "link" => $link
                    );
                }
                $response = $this->request('specs/', $data);
                if($response["task_id"]){
                    $tk = $this->modx->getObject("slParserDataTasks", $task["id"]);
                    if($tk){
                        $tk->set("external_id", $response["task_id"]);
                        $tk->save();
                    }
                }
            }
        }
        // обрабатываем те, у которых стояли таски
        $query = $this->modx->newQuery("slParserDataTasks");
        $query->where(array(
            "status:NOT IN" => array(3,4,8,9),
            "external_id:>" => "0"
        ));
        $query->select(array("slParserDataTasks.*"));
        if($query->prepare() && $query->stmt->execute()) {
            $tasks = $query->stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($tasks as $task) {
                $response = $this->request('specs/'.$task["external_id"], array(), "GET");
                $tk = $this->modx->getObject("slParserDataTasks", $task["id"]);
                if($tk) {
                    if ($response["status"]) {
                        $status = $this->modx->getObject("slParserDataTasksStatus", array("status_key" => $response["status"]));
                        $tk->set("status", $status->get("id"));
                        if($response["status"] == "OK"){
                            // разбор категорий и опций
                            foreach($response["results"] as $k => $result){
                                if($result["status"] == "OK"){
                                    $cats = array_reverse($result["specs"]["breadcrumbs"]);
                                    $parents = $result["specs"]["breadcrumbs"];
                                    foreach($parents as $key => $parent){
                                        $parents[$key] = trim($parent);
                                    }
                                    $brand = array_pop($parents);
                                    $category = $cats[1];
                                    $service = $this->modx->getObject("slParserDataService", array("service_key" => $result["domain"]));
                                    if($service){
                                        $service_id = $service->get("id");
                                        // проверка на дубликаты
                                        $criteria = array(
                                            "service_id" => $service_id,
                                            "export_parents" => implode("||", $parents),
                                            "name" => $category
                                        );
                                        $cat = $this->modx->getObject("slParserDataCats", $criteria);
                                        if(!$cat){
                                            $cat = $this->modx->newObject("slParserDataCats");
                                            $cat->set("createdon", time());
                                        }else{
                                            $cat->set("updatedon", time());
                                        }
                                        $cat->set("name", $category);
                                        $cat->set("service_id", $service_id);
                                        $cat->set("export_parents", implode("||", $parents));
                                        $cat->save();
                                        $cat_id = $cat->get("id");
                                        $response["results"][$k]["parent"] = $cat_id;
                                        $tk->set("data", json_encode($response["results"], JSON_UNESCAPED_UNICODE));
                                        foreach($result["specs"]["specifications"] as $param) {
                                            $option = trim($param["name"]);
                                            $criteria = array(
                                                "name" => $option,
                                                "cat_id" => $cat_id
                                            );
                                            $opt = $this->modx->getObject("slParserDataCatsOptions", $criteria);
                                            if (!$opt){
                                                $opt = $this->modx->newObject("slParserDataCatsOptions");
                                                $cat->set("check", 0);
                                                $cat->save();
                                                $opt->set("name", $option);
                                                $opt->set("cat_id", $cat_id);
                                                $opt->set("createdon", time());
                                                $opt->set("examples", strval($param["value"]));
                                                // чекаем опцию с таким наименованием
                                                $criteria = array(
                                                    "caption" => $option
                                                );
                                                $synonim = $this->modx->getObject("msOption", $criteria);
                                                if($synonim){
                                                    $id = $synonim->get("id");
                                                    $opt->set("option_id", $id);
                                                }
                                                $criteria = array(
                                                    "name" => $option,
                                                    "option_id:>" => 0
                                                );
                                                $precedent = $this->modx->getObject("slParserDataCatsOptions", $criteria);
                                                if($precedent){
                                                    $id = $precedent->get("option_id");
                                                    $opt->set("option_id", $id);
                                                }
                                            }else{
                                                $ex = $opt->get("examples");
                                                $examples = explode("||", $opt->get("examples"));
                                                $length = strlen(strval($param["value"]));
                                                $sum = strlen($ex) + $length;
                                                if(!in_array(strval($param["value"]), $examples) && $length <= 25 && $sum < 255){
                                                    $examples[] = strval($param["value"]);
                                                    $opt->set("examples", implode("||", $examples));
                                                }
                                            }
                                            $opt->save();
                                        }
                                    }
                                }
                            }
                        }
                        $tk->save();
                    }
                }
            }
        }
        // обрабатываем те, которые готовы к импорту
        $query = $this->modx->newQuery("slParserDataTasks");
        $query->where(array(
            "status:=" => 8,
            "external_id:>" => "0"
        ));
        $query->select(array("slParserDataTasks.*"));
        if($query->prepare() && $query->stmt->execute()) {
            $tasks = $query->stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($tasks as $task) {
                $tk = $this->modx->getObject("slParserDataTasks", $task["id"]);
                if($tk) {
                    $tk->set("description", "");
                    $tk->save();
                }
                $response_data = json_decode($task["data"], 1);
                foreach($response_data as $item){
                    if($item["parent"]){
                        $parent = $this->modx->getObject("slParserDataCats", $item["parent"]);
                        if($parent){
                            $check = $parent->get("check");
                            $cat_id = $parent->get("cat_id");
                            // сначала проверяем, сверены ли опции и выставлена ли категория
                            if($check && $cat_id){
                                // импортируем товар
                                $parent = $parent->get("cat_id");
                                if($task["article_last_word"]){
                                    $words = explode(" ", $item["specs"]['title']);
                                    $rwords = array_reverse($words);
                                    $article = $rwords[0];
                                    $varticle = $rwords[0];
                                }else{
                                    $article = 'vseinstrumenti_'.$item["specs"]['code'];
                                    $varticle = 'vseinstrumenti_'.$item["specs"]['code'];
                                }
                                if($parent && ($article || $varticle)) {
                                    $data = array();
                                    $data['pagetitle'] = $item["specs"]['title'];
                                    $data['source_url'] = $item["link"];
                                    $data['article'] = $article;
                                    $data['vendor_article'] = $varticle;
                                    $data['price'] = 0;
                                    $data['price_rrc'] = 0;
                                    $data['length'] = 0;
                                    $data['width'] = 0;
                                    $data['height'] = 0;
                                    $data['weight_brutto'] = 0;
                                    $data['weight_netto'] = 0;
                                    $data['introtext'] = "";
                                    $data['content'] = "<p>" . $item["specs"]['description'] . "</p>";
                                    $data['parent'] = $parent;
                                    $data['fixprice'] = 0;
                                    $data['places'] = 1;
                                    $data['volume'] = 0;
                                    $data['b24id'] = '';
                                    $data['image'] = $item["specs"]['photos'];
                                    if($item["specs"]['brand']){
                                        $vendor = $this->modx->getObject("msVendor", array("name" => $item["specs"]['brand']["name"]));
                                        if($vendor){
                                            $data['vendor'] = $vendor->get("id");
                                        }
                                    }
                                    foreach($item["specs"]['package_info'] as $pack){
                                        if($pack["name"] == "Единица товара"){
                                            $data['measure'] = $pack["value"];
                                        }
                                        if($pack["name"] == "Вес, кг"){
                                            $data['weight'] = $pack["value"];
                                            $data['weight_brutto'] = $pack["value"];
                                            $data['weight_netto'] = $pack["value"];
                                        }
                                        if($pack["name"] == "Длина, мм"){
                                            $data['length'] = $pack["value"] * 0.1;
                                        }
                                        if($pack["name"] == "Ширина, мм"){
                                            $data['width'] = $pack["value"] * 0.1;
                                        }
                                        if($pack["name"] == "Высота, мм"){
                                            $data['height'] = $pack["value"] * 0.1;
                                        }
                                    }
                                    foreach($item["specs"]['specifications'] as $specification){
                                        $opt_query = $this->modx->newQuery("slParserDataCatsOptions");
                                        $opt_query->leftJoin("msOption", "msOption", "msOption.id = slParserDataCatsOptions.option_id");
                                        $opt_query->where(array(
                                            "cat_id" => $item["parent"],
                                            "name" => $specification["name"]
                                        ));
                                        $opt_query->select(array("slParserDataCatsOptions.*, msOption.id as option_id, msOption.key as option_key"));
                                        if($opt_query->prepare() && $opt_query->stmt->execute()){
                                            $conf_option = $opt_query->stmt->fetch(PDO::FETCH_ASSOC);
                                            if($conf_option){
                                                if(!$conf_option['ignore']){
                                                    if($conf_option["option_id"] || $conf_option["to_field"]){
                                                        if($conf_option["option_id"]){
                                                            // устанавливаем существующую
                                                            $data["options-".$conf_option["option_key"]] = $specification["value"];
                                                            $this->sl->api->cat_option_check($conf_option["option_id"], $item['parent']);
                                                        }
                                                        if($conf_option["to_field"]){
                                                            $v = $specification["value"];
                                                            if($conf_option["filters"]){
                                                                $filters = $this->sl->objects->filter_parse($conf_option["filters"]);
                                                                foreach($filters as $key => $val) {
                                                                    $v = $this->sl->objects->filter($key, $val, $v);
                                                                }
                                                            }
                                                            $data[$conf_option["to_field"]] = $v;
                                                        }
                                                    }else{
                                                        // создаем опцию
                                                        if($specification["name"]){
                                                            $opt_id = 0;
                                                            // возможно, есть опция с подобным наименованием
                                                            $criteria = array(
                                                                "caption" => $specification["name"]
                                                            );
                                                            $synonim = $this->modx->getObject("msOption", $criteria);
                                                            if($synonim){
                                                                $opt_id = $synonim->get("id");
                                                                $opt_key = $synonim->get("key");
                                                            }else{
                                                                // TODO: возможно, настроить фильтр опций
                                                                $p = $this->modx->newObject("modResource");
                                                                $op = array(
                                                                    "key" => str_replace(array(".", ","), array(" ", " "), $p->cleanAlias($specification["name"])),
                                                                    "caption" => $specification["name"],
                                                                    "category" => 20,
                                                                    "type" => "combo-options",
                                                                );
                                                                $opt_key = $op["key"];
                                                                $opt_id = $this->sl->api->option_check($op);
                                                            }
                                                            if($opt_id){
                                                                $this->sl->api->cat_option_check($opt_id, $item['parent']);
                                                                $data["options-".$opt_key] = $specification["value"];
                                                                $cat_option = $this->modx->getObject("slParserDataCatsOptions", $conf_option['id']);
                                                                if($cat_option){
                                                                    $cat_option->set("option_id", $opt_id);
                                                                    $cat_option->save();
                                                                }
                                                            }
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                    }
                                    $prod = $this->sl->api->new_product($data);
                                    if($prod['resource']){
                                        $resource = $this->modx->getObject("modResource", $prod['resource']);
                                        if($resource){
                                            $resource->set("createdon", time());
                                            $resource->set("updatedon", time());
                                            $resource->set("alias", md5($resource->get("id")));
                                            $resource->set("uri",  'products/'.md5($resource->get("id")));
                                            $resource->set("uri_override", 1);
                                            $resource->save();
                                        }
                                    }
                                    if($tk) {
                                        $tk->set("status", 9);
                                        $tk->save();
                                    }
                                }
                            }else{
                                if($tk) {
                                    $tk->set("status", 4);
                                    $tk->set("description", "Отмодерируйте опции категории и отметьте у них галочку 'Категория проверена'");
                                    $tk->save();
                                }
                            }
                        }
                    }else{
                        if($tk) {
                            $tk->set("status", 4);
                            $tk->set("description", "Не у всех категорий указано соответствие с категорией маркетплейса.");
                            $tk->save();
                        }
                    }
                }
            }
        }
    }

    /**
     * Запрос информации
     *
     * @param $action
     * @param $data
     * @param $type
     * @return mixed
     */
    public function request($action, $data, $type = "POST"){
        $out = array();
        $url = $this->config['url'].$action;
        if($type == "GET"){
            $url = $url.'?'.http_build_query($data, '', '&');
        }
        if( $curl = curl_init() ) {
            $headers = array('Content-Type:application/json');
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER,true);

            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($curl, CURLOPT_HEADER, false);
            if($type == "POST"){
                curl_setopt($curl, CURLOPT_POST, true);
                curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
            }
            $headers[] = "Authorization: ".$this->config['token'];
            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
            $out = curl_exec($curl);
            curl_close($curl);
        }
        $this->modx->log(1, print_r($url, 1));
        $this->modx->log(1, print_r($data, 1));
        $this->modx->log(1, print_r($type, 1));
        $this->modx->log(1, print_r($headers, 1));
        $response_data = json_decode($out, 1);
        $this->modx->log(1, print_r($response_data, 1));
        return $response_data;
    }
}