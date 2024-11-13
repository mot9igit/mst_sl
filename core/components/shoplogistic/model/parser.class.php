<?php
class parser
{
    public $modx;
    public $sl;
    public $config;
    public $output;

    public function __construct(shopLogistic &$sl, modX &$modx)
    {

        $this->sl =& $sl;
        $this->modx =& $modx;
        $this->sl->loadServices();
        $this->modx->lexicon->load('shoplogistic:default');

        $dir = dirname(__FILE__);
        $file = $dir.'/libs/simplehtmldom/simple_html_dom.php';
        if (file_exists($file)) {
            include_once $file;
        }else{
            return $this->error("Ошибка загрузки файла Simple HTML DOM: ".$file);
        }

        $file = $dir.'/libs/PHPExcel/Classes/PHPExcel.php';
        if (file_exists($file)) {
            include_once $file;
        }else{
            return $this->error("Ошибка загрузки файла Excel: ".$file);
        }

        $this->config = array(
            "out" => "YML",
            "chunk" => "parser_yml",
            'base_path' => $this->modx->getOption("assets_path").'files/parser/',
            'test_mode' => 0,
            'exclude' => array(),
            'table_config' => array(
                "fields" => array()
            )
        );

        $this->output = array(
            "history" => array(),
            "categories" => array(),
            "offers" => array()
        );
    }

    /**
     * Организуем подключение
     *
     * @param $url
     * @return array
     */
    public function connect($url){
        if(is_array($url)){
            $url = $url[0];
        }
        $ch = curl_init($url);
        $ckfile = tempnam ("/tmp", "CURLCOOKIE");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);


        $output['content'] = curl_exec($ch);
        $output['httpcode'] = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        // $output['content'] = mb_convert_encoding($output['content'], "utf-8", "windows-1251");
        // $this->modx->log(1, print_r($output));
        usleep(500000);
        curl_close($ch);
        return $output;
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
        foreach($filters as $filter) {
            $f = explode($delimeter_params, $filter);
            $filter_name = $f[0];
            if ($f[1]) {
                if ($clean) {
                    $f[1] = str_replace(array("=>", "(", ")", "/"), array(":", "{", "}", ""), $f[1]);
                } else {
                    $f[1] = str_replace(array("=>", "[", "]"), array(":", "{", "}"), '[' . $f[1]);
                }
            }
            $filter_param = json_decode($f[1], 1);
            $out[$filter_name] = $filter_param;
        }
        return $out;
    }

    /**
     * Находим категории
     *
     * @param $url
     * @return array
     */
    public function get_cats($url, $parent = ''){
        $this->checkprogress($url);
        $output = array(
            "categories" => array()
        );
        if(is_array($url)){
            $url = $url[0];
        }
        $request = $this->connect($url);
        $cat = new simple_html_dom;
        $cat->load($request["content"]);
        // $this->checkprogress(print_r($this->config, 1));
        if($this->config['once_cats']) {
            if(!count($this->output['categories'])){
                $cats = $cat->find($this->config["categories"]["base"]);
            }else{
                $cats = array();
            }
        }else{
            $cats = $cat->find($this->config["categories"]["base"]);
        }
        if(count($cats)){
            $this->checkprogress("На странице ".print_r($url, 1)." найдены категории в размере ".count($cats)." идем вглубь");
            $categories = array();
            foreach($cats as $cat){
                // сохраняем то, что нужно
                foreach($this->config["categories"]["selectors"] as $key => $value){
                    $tmp_val = $this->get_field($key, $value, $cat);
                    if($tmp_val){
                        $data[$key] = $tmp_val;
                    }
                }
                if($data['href']){
                    $href = $data['href'][0];
                    $inner_data = $this->get_inner_cats($href);
                }
                if($parent){
                    $data['parent'] = md5($parent);
                }else{
                    $data['parent'] = "";
                }
                if($data["title"]){
                    $data['id'] =  md5($data["title"]);
                }

                $tmp = array_merge($data, $inner_data);
                if($tmp["title"]){
                    $output['categories'][] = $tmp;
                }
                // $this->modx->log(1, print_r($output, 1));
            }
        }else{
            $this->checkprogress("На странице ".print_r($url, 1)." НЕ найдены категории");
            $output['cat'] = $this->get_inner_cats($url);
            // check pagination
            $p = array($url);
            $pages = $cat->find($this->config["products"]["pagination"]['selector']);
            if(count($pages)){
                foreach($pages as $page){
                    if(isset($this->config["products"]["pagination"]['filters']['add_base_url'])){
                        // FIX double slashes
                        $link = str_replace($this->config["base_url"].'/', $this->config["base_url"], $this->config["base_url"] . $page->href);
                    }else{
                        $link = $page->href;
                    }
                    if(!in_array($link, $p)){
                        $p[] = $link;
                    }
                }
            }
            $output['pages'] = $p;
            $this->checkprogress("Найдено страниц - ".count($p));
        }
        $this->checkprogress("На странице ".print_r($output, 1));
        return $output;
    }

    /**
     * Внутренние страницы категорий
     *
     * @param $url
     * @return array
     */
    public function get_inner_cats($url){
        $request = $this->connect($url);
        if($request["httpcode"]==200){
            $prod = new simple_html_dom;;
            $prod->load($request["content"]);
            // $this->modx->log(1, $request["content"]);
            $inner_content = $prod->find($this->config["categories"]["inner_base"], 0);
            $data = array();
            foreach($this->config["categories"]["inner_page_selectors"] as $key => $value){
                $tmp_val = $this->get_field($key, $value, $inner_content);
                if($tmp_val){
                    $data[$key] = $tmp_val;
                }
            }
            return $data;
        }else{
            $this->checkprogress("Ответ от сервера - ".$request["httpcode"]." - страница - ".$url);
            return array();
        }
    }

    /**
     * Берем товары
     *
     * @param $url
     * @return array
     */
    public function get_products($url, $parent){
        // подключаемся по URL
        $request = $this->connect($url);
        $prod = new simple_html_dom;
        $prod->load($request["content"]);
        $products = $prod->find($this->config["products"]["base"]);
        if(count($products)){
            $this->checkprogress("На странице ".$url." найдены товары в размере ".count($products)." идем вглубь \n");
            $productes = array();
            foreach($products as $product){
                // сохраняем то, что нужно
                foreach($this->config["products"]["selectors"] as $key => $value){
                    $data[$key] = $this->get_field($key, $value, $product);
                    if(isset($value['filters']['split'])){
                        foreach($data[$key] as $k => $v){
                            $data[$k] = $v;
                        }
                    }
                }
                if($data['href']){
                    if(is_array($data['href'])){
                        $href = $data['href'][0];
                    }else{
                        $href = $data['href'];
                    }
                    $inner_data = $this->get_inner_products($href);
                }
                $this->checkprogress(print_r($inner_data, 1));
                foreach($this->config['table_config']['fields'] as $key => $value){
                    if($value['default']){
                        $data[$key] = "";
                    }
                }
                if($parent){
                    $data['parent'] = md5($parent);
                }
                $tmp = array_merge($data, $inner_data);
                if($this->config['unique']){
                    if(!in_array($tmp[$this->config['key_product_field']], $this->status["products"])){
                        $this->status["products"][] = $tmp[$this->config['key_product_field']];
                        $productes[] = $tmp;
                    }else{
                        // TODO: найден дубль, сохранить категорию
                    }
                }else{
                    $productes[] = $tmp;
                }
                $this->checkprogress(print_r($tmp, 1));
            }
        }else{
            $this->checkprogress("Товаров нет \n");
        }
        return $productes;
    }

    /**
     * Товары внутренняя страница
     *
     * @param $url
     * @return array
     */
    public function get_inner_products($url){
        $request = $this->connect($url);
        if($request["httpcode"]==200){
            $prod = new simple_html_dom;;
            $prod->load($request["content"]);
            $inner_content = $prod->find($this->config["products"]["inner_base"], 0);
            $data = array();
            foreach($this->config["products"]["inner_page_selectors"] as $key => $value){
                $data[$key] = $this->get_field($key, $value, $inner_content);
                $this->modx->log(1, print_r($key, 1));
                $this->modx->log(1, print_r($value, 1));
                if(isset($value['filters']['tofields'])) {
                    $val = $this->get_field($key, $value, $inner_content);
                    foreach($val as $k => $v){
                        if($k == "weight_brutto_alt"){
                            $data["weight_brutto"] = $v;
                        }else{
                            $data[$k] = $v;
                        }
                    }
                    if($val["metrix"]){
                        foreach($val["metrix"] as $k => $v){
                            $data[$k] = $v;
                        }
                    }
                    if($val["metrix_alt"]){
                        foreach($val["metrix_alt"] as $k => $v){
                            $data[$k] = $v;
                        }
                    }
                }
                if(isset($value['filters']['split'])){
                    foreach($data[$key] as $k => $v){
                        $data[$k] = $v;
                    }
                }
            }
            if($data["weight_brutto"]){
                $weight = explode("-", $data["weight_brutto"]);
                if(count($weight) > 1){
                    $w = str_replace(",", ".", trim($weight[1]));
                }else{
                    $w = str_replace(",", ".", trim($weight[0]));
                }
                $w = str_replace(" кг", "", trim($w));
                $data["weight_brutto"] = $w;
            }
            return $data;
        }else{
            $this->checkprogress("Ответ от серевера - ".$request["httpcode"]." - страница - ".$url);
            return array();
        }
    }

    /**
     * @param $name
     * @param $config
     * @param $value
     * @return array
     */
    public function filterField($name, $config, $value = null, $element = null){
        $field_value = '';
        switch ($name) {
            case 'tofields':
                $field_value = array();
                $field_value['params'] = $value;
                foreach($value as $k => $v){
                    foreach($config['config'] as $ck => $cv){
                        if($cv == $k){
                            if($config["filters"][$ck]){
                                $filters = $this->filter_parse($config["filters"][$ck], "~", "-:", 1);
                                foreach($filters as $key => $val){
                                    $field_value[$ck] = $this->filterField($key, $val, $v);
                                }
                            }else{
                                $field_value[$ck] = $v;
                            }
                        }
                    }
                }
                break;
            case 'koef':
                if($config["koef"]){
                    $field_value = floatval(str_replace(",", ".", $value)) * $config["koef"];
                }
                break;
            case 'plaintext':
                $field_value = html_entity_decode(trim($value));
                break;
            case 'breadcrumbs':
                $field_value = array();
                if($element){
                    $elems = $element->find($config['selector']);
                    foreach($elems as $elem){
                        $field_value[] = html_entity_decode(trim($elem->plaintext));
                    }
                }
                break;
            case 'add_base_url':
                if(is_array($value)){
                    $field_value = array();
                    // $this->modx->log(1, print_r($value, 1));
                    // $this->modx->log(1, print_r($this->config["base_url"], 1));
                    foreach($value as $key => $item){
                        if($item) {
                            $last = mb_substr($this->config["base_url"], -1);
                            $first = mb_substr($item, 0, 1);
                            if ($last == "/" && $first == "/") {
                                $sitem = substr($item, 1);
                            } else {
                                $sitem = $item;
                            }
                            $field_value[$key] = $this->config["base_url"] . $sitem;
                        }
                    }
                }else{
                    $last = mb_substr($this->config["base_url"], -1);
                    $first = mb_substr($value, 0, 1);
                    if($last == "/" && $first == "/"){
                        $sitem = substr($value, 1);
                        $field_value = $this->config["base_url"].$sitem;
                    }else{
                        $field_value = $this->config["base_url"].$value;
                    }

                }
                break;
            case 'numeric':
                if(is_array($value)){
                    $field_value = array();
                    foreach($value as $key => $item){
                        $field_value[$key] = preg_replace('/[^\d.]+/', '', $item);
                    }
                }else{
                    $field_value = preg_replace('/[^\d.]+/', '', $value);
                }
                break;
            case 'replace':
                if(is_array($value)){
                    $field_value = array();
                    foreach($value as $key => $item){
                        foreach($config as $k => $v){
                            $field_value[$key] = html_entity_decode(trim(str_replace($k, $v, $item)));
                        }
                    }
                }else{
                    foreach($config as $k => $v){
                        $field_value = html_entity_decode(trim(str_replace($k, $v, $value)));
                    }
                }
                break;
            case 'split':
                $elems = explode($config['delimeter'], $value);
                $field_value = array();
                foreach($config['elements'] as $key => $elem){
                    if($config['koef']){
                        $v = trim($elems[floatval($key)]);
                        $field_value[$elem] = floatval($v) * $config['koef'];
                    }else{
                        $field_value[$elem] = html_entity_decode(trim($elems[$key]));
                    }
                }
                break;
            case "strip_tags":
                if(is_array($value)){
                    $field_value = array();
                    foreach($value as $key => $item){
                        $field_value[$key] = html_entity_decode(strip_tags($item));
                    }
                }else{
                    $field_value = html_entity_decode(strip_tags($value));
                }
                break;
            case "last_word":
                if(is_array($value)){
                   // ничего
                }else{
                    $array = explode(" ", trim($value));
                    $array = array_reverse($array);
                    $field_value = html_entity_decode(strip_tags(trim($array[0])));
                    $vowels = array("(", ")");
                    $field_value = str_replace($vowels, "", $field_value);
                }
                break;
            case 'elements':
                $field_value = array();
                if($config['row'] && $element){
                    if($config['row'] == 'this'){
                        foreach($element as $k => $row){
                            if(isset($config['title_source'])){
                                if($config['title_source'] == 'attribute'){
                                    $lab = $row->find($config['title'], 0)->{$config['source_name']};
                                }
                                $val = $row->find($config['value'], 0)->plaintext;
                            }else{
                                if($config['mode'] == "split"){
                                    $text = $row->plaintext;
                                    $t = explode($config['separator'], $text);
                                    $lab = $t[0];
                                    $val = $t[1];
                                }else{
                                    $lab = $row->find($config['title'], 0)->plaintext;
                                    $val = $row->find($config['value'], 0)->plaintext;
                                }
                            }
                            if($lab){
                                if($config['replace_label']){
                                    $replace = explode("==", $config['replace_label']);
                                    if(count($replace) > 1){
                                        $lab = str_replace($replace[0], $replace[1], $lab);
                                    }
                                }
                                if($config['replace_value']){
                                    $replace = explode("==", $config['replace_value']);
                                    if(count($replace) > 1){
                                        $val = str_replace($replace[0], $replace[1], $val);
                                    }
                                }
                                $field_value[html_entity_decode(trim($lab))] = html_entity_decode(trim($val));
                            }
                        }
                    }else{
                        if(is_array($element)){
                            foreach($element as $k => $el) {
                                $rows = $el->find($config['row']);
                                foreach ($rows as $row) {
                                    if (isset($config['title_source'])) {
                                        if ($config['title_source'] == 'attribute') {
                                            $lab = $row->find($config['title'], 0)->{$config['source_name']};
                                        }
                                        $val = $row->find($config['value'], 0)->plaintext;
                                    } else {
                                        if($config['mode'] == "split"){
                                            $text = $row->plaintext;
                                            $t = explode($config['separator'], $text);
                                            $lab = $t[0];
                                            $val = $t[1];
                                        }else{
                                            $lab = $row->find($config['title'], 0)->plaintext;
                                            if ($config['val_source'] == 'attribute') {
                                                $val = $row->find($config['value'], 0)->{$config['val_source_name']};
                                            }else{
                                                $val = $row->find($config['value'], 0)->plaintext;
                                            }
                                        }
                                    }
                                    if ($lab) {
                                        if($config['replace_label']){
                                            $replace = explode("==", $config['replace_label']);
                                            if(count($replace) > 1){
                                                $lab = str_replace($replace[0], $replace[1], $lab);
                                            }
                                        }
                                        if($config['replace_value']){
                                            $replace = explode("==", $config['replace_value']);
                                            if(count($replace) > 1){
                                                $val = str_replace($replace[0], $replace[1], $val);
                                            }
                                        }
                                        $field_value[html_entity_decode(trim($lab))] = html_entity_decode(trim($val));
                                    }
                                }
                            }
                        }else{
                            $rows = $element->find($config['row']);
                            foreach ($rows as $row) {
                                if (isset($config['title_source'])) {
                                    if ($config['title_source'] == 'attribute') {
                                        $lab = $row->find($config['title'], 0)->{$config['source_name']};
                                    }
                                    $val = $row->find($config['value'], 0)->plaintext;
                                } else {
                                    if($config['mode'] == "split"){
                                        $text = $row->plaintext;
                                        $t = explode($config['separator'], $text);
                                        $lab = $t[0];
                                        $val = $t[1];
                                    }else{
                                        if ($config['val_source'] == 'attribute') {
                                            $val = $row->find($config['value'], 0)->{$config['val_source_name']};
                                        }else{
                                            $val = $row->find($config['value'], 0)->plaintext;
                                        }
                                        $lab = $row->find($config['title'], 0)->plaintext;

                                    }
                                }
                                if ($lab) {
                                    if($config['replace_label']){
                                        $replace = explode("==", $config['replace_label']);
                                        if(count($replace) > 1){
                                            $lab = str_replace($replace[0], $replace[1], $lab);
                                        }
                                    }
                                    if($config['replace_value']){
                                        $replace = explode("==", $config['replace_value']);
                                        if(count($replace) > 1){
                                            $val = str_replace($replace[0], $replace[1], $val);
                                        }
                                    }
                                    $field_value[html_entity_decode(trim($lab))] = html_entity_decode(trim($val));
                                }
                            }
                        }

                    }
                }else{
                    $this->modx->log(1, "Не передан элемент");
                }
                break;
            case 'table_to_fields':
                // TODO: CHECK table mode
                $field_value = array();
                if(is_array($element)) {
                    foreach ($element as $k => $el) {
                        if ($config["type"] == "row") {
                            if ($el) {
                                $rows = $el->find('tr');
                                foreach ($rows as $row) {
                                    $tds = $row->find("td");
                                    if (trim($tds[0]->plaintext) != $config["head"] && trim($tds[1]->plaintext)) {
                                        $field_value[html_entity_decode(trim($tds[0]->plaintext))] = html_entity_decode(trim($tds[1]->plaintext));
                                    }
                                }
                            }
                        }
                    }
                }else{
                    if ($config["type"] == "row") {
                        if ($element) {
                            $rows = $element->find('tr');
                            foreach ($rows as $row) {
                                $tds = $row->find("td");
                                if (trim($tds[0]->plaintext) != $config["head"] && trim($tds[1]->plaintext)) {
                                    $field_value[html_entity_decode(trim($tds[0]->plaintext))] = html_entity_decode(trim($tds[1]->plaintext));
                                }
                            }
                        }
                    }
                }
                break;
        }
        return $field_value;
    }

    /**
     * Собираем кофигурацию по таску
     *
     * @param $task_id
     * @return array
     */
    public function getConfig($task_id){
        $task = $this->modx->getObject("slParserTasks", $task_id);
        if($task){
            $config_id = $task->get("config_id");
            if($config_id){
                $query = $this->modx->newQuery("slParserConfig");
                $query->where(array("slParserConfig.id:=" => $config_id));
                $query->select(array("slParserConfig.*"));
                if($query->prepare() && $query->stmt->execute()){
                    $config = $query->stmt->fetch(PDO::FETCH_ASSOC);
                    if($config){
                        $this->config["name"] = $task->get("name") . " ({$task_id})";
                        $this->output["name"] = $task->get("name") . " ({$task_id})";
                        $this->config["log_file"] = "parser_{$task_id}";
                        $this->config["base_url"] = $config["base_url"];
                        $this->output["base_url"] = $config["base_url"];
                        $this->config["file_name"] = $task_id . ".xml";
                        $this->config["once_cats"] = $config["once_cats"];
                        $this->config["unique"] = $config["unique"];
                        $this->config["key_product_field"] = $config["key_product_field"];
                        $this->config["categories"]["base"] = $config["categories_base"];
                        $this->config["categories"]["inner_base"] = $config["categories_base_inner"];
                        $this->config["products"]["base"] = $config["products_base"];
                        $this->config["products"]["inner_base"] = $config["products_base_inner"];
                        if($config["pagination"]){
                            $this->config["products"]["pagination"]["selector"] = $config["pagination_selector"];
                            $this->config["products"]["pagination"]["filters"] = $this->filter_parse($config["pagination_filters"]);
                        }
                        $query = $this->modx->newQuery("slParserConfigFields");
                        $query->where(array("slParserConfigFields.config_id:=" => $config_id));
                        $query->select(array("slParserConfigFields.*"));
                        if($query->prepare() && $query->stmt->execute()){
                            $fields = $query->stmt->fetchAll(PDO::FETCH_ASSOC);
                            foreach($fields as $field){
                                if($field["field_object"] == 1){
                                    if($field["field_type"] == 1){
                                        if($field["selector"]){
                                            $tmp = $this->getFieldConfig($field);
                                            $this->config["categories"]['selectors'][$field["name"]] = $tmp;
                                        }
                                    }
                                    if($field["field_type"] == 2){
                                        if($field["selector"]){
                                            $tmp = $this->getFieldConfig($field);
                                            $this->config["categories"]['inner_page_selectors'][$field["name"]] = $tmp;
                                        }
                                    }
                                }
                                if($field["field_object"] == 2){
                                    if($field["field_type"] == 1){
                                        if($field["selector"]){
                                            $tmp = $this->getFieldConfig($field);
                                            $this->config["products"]['selectors'][$field["name"]] = $tmp;
                                        }
                                    }
                                    if($field["field_type"] == 2){
                                        if($field["selector"]){
                                            $tmp = $this->getFieldConfig($field);
                                            $this->config["products"]['inner_page_selectors'][$field["name"]] = $tmp;
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
        return $this->config;
    }

    /**
     * Собираем конфиг поля
     *
     * @param $field
     * @return array
     */
    public function getFieldConfig($field){
        $tmp = array();
        $tmp["selector"] = $field["selector"];
        $tmp["type"] = $field["type"];
        if($field['this']){
            $tmp["goal_type"] = $field["type"];
            $tmp["type"] = 'this';
        }
        if($field["element_name"]){
            $tmp["name"] = $field["element_name"];
        }
        if($field["index_search"]){
            $tmp["index_search"] = $field["index_search"];
            $tmp["index"] = $field["index"];
            $tmp["sub_element"] = $field["subelement"];
            $tmp["sub_index"] = $field["subindex"];
        }
        if($field["field_filters"]){
            $tmp["filters"] = $this->filter_parse($field["field_filters"]);
        }
        return $tmp;
    }

    /**
     * Работа с полями
     *
     * @param $field
     * @param $config
     * @param $cat
     * @return array|string|string[]|null
     */
    public function get_field($field, $config, $cat){
        //$this->modx->log(1, print_r($field, 1));
        switch ($config['type']) {
            case "this":
                switch ($config['goal_type']) {
                    case "field":
                        $field_value = trim($cat->innertext);
                        break;
                    case "attribute":
                        $field_value[] = $cat->{$config['name']};
                        break;
                }
                break;
            case "field":
                $elems = $cat->find($config['selector']);
                $this->modx->log(1, print_r($config, 1));
                $this->modx->log(1, count($elems));
                if($config["index_search"] && isset($elems[$config["index"]])){
                    if(isset($config["sub_element"])){
                        if(isset($config["sub_index"])){
                            $elem = $elems[$config["index"]]->find($config["sub_element"]);
                            $field_value = trim($elem[$config["sub_index"]]->innertext);
                        }else{
                            $field_value = trim($elems[$config["index"]]->find($config["sub_element"], 0)->innertext);
                        }
                    }else{
                        $field_value = trim($elems[$config["index"]]->innertext);
                    }
                }else{
                    $field_value = trim($elems[0]->innertext);
                }
                break;
            case "withhtml":
                $field_value = array();
                $data = $cat->find($config['selector']);
                foreach($data as $item){
                    $field_value[] = trim($item->outertext);
                }

                break;
            case "attribute":
                $this->modx->log(1, print_r($config,1));
                $elems = $cat->find($config["selector"]);
                foreach($elems as $key => $elem){
                    $dt[] = $elem->{$config['name']};
                }
                if($config["index_search"] && isset($dt[$config["index"]])){
                    $field_value = trim($dt[$config["index"]]);
                }else{
                    $field_value = $dt;
                }
                break;
            case "css":
                $elems = $cat->find($config["selector"]);
                foreach($elems as $key => $elem){
                    $style = $elem->style;
                    preg_match('/\(([^)]+)\)/', $style, $match);
                    $field_value[] = str_replace("'", "", $match[1]);
                }
                break;
            case "table":
                $cat = $cat->find($config['selector']);
                break;
            case "tables":
                $cat = $cat->find($config['selector']);
                break;
            default:
                // $field_value = trim($cat->find($config['selector'], 0)->innertext);
                break;

        }
        if($config['filters']){
            //$this->modx->log(1, print_r(count($cat), 1));
            foreach($config["filters"] as $key => $val){
                $field_value = $this->filterField($key, $val, $field_value, $cat);
                //$this->modx->log(1, print_r($field_value, 1));
            }
        }
        // $this->modx->log(1, print_r($field_value, 1));
        return $field_value;
    }

    /**
     * Парсим
     *
     * @param $url
     * @return void
     */
    public function parse($url, $parent = ""){
        if(!in_array($url, $this->output['history'])){
            $this->output['history'][] = $url;
            if($this->config['once_cats']) {
                if(!count($this->output['categories'])){
                    $cats = $this->get_cats($url, $parent);
                    if($this->config["test_mode"]){
                        $cats['categories'] = array($cats['categories'][0]);
                    }
                }else{
                    $cats = $this->get_cats($url, $parent);
                    $cats['categories'] = array();
                }
            }else{
                $cats = $this->get_cats($url, $parent);
            }
            if(!count($cats['categories'])){
                if(!$parent){
                    $cats['cat']['id'] = md5($cats['cat']['title']);
                    if(!count($this->output['categories'])) {
                        $this->output['categories'][] = $cats['cat'];
                    }
                }
                foreach($cats['pages'] as $page){
                    $products = $this->get_products($page, $cats['cat']['title']);
                    foreach($products as $k => $product){
                        if($cats['cat']['breadcrumbs']){
                            $products[$k]['category'] = implode("||", $cats['cat']['breadcrumbs']);
                        }
                        $products[$k]['parent'] = md5($cats['cat']['title']);
                        $this->output['offers'][] = $products[$k];
                    }
                    if($this->config["out"] == "XLSX"){
                        $this->generateXLSX($products);
                    }
                }
            }else{
                foreach($cats['categories'] as $key => $cat){
                    $cats['categories'][$key]['id'] = md5($cat['title']);
                }
                $this->output['categories'] = array_merge($this->output['categories'], $cats['categories']);
                foreach($this->output['categories'] as $k => $cat){
                    if(!$cat["id"]){
                        unset($this->output['categories'][$k]);
                    }
                }
                foreach($cats['categories'] as $c){
                    if(!in_array($c["href"][0], $this->config["exclude"])){
                        $this->parse($c["href"][0], $cats['cat']['title']);
                    }
                }
            }
            if($this->config["out"] == "YML"){
                $this->generateYML();
            }
            $this->checkprogress(print_r($this->output, 1));
        }
    }

    /**
     *
     * Обработка тасков парсера
     *
     * @return void
     *
     */
    public function handleTasks(){
        $tasks = $this->modx->getCollection("slParserTasks", array("status:=" => 1));
        foreach($tasks as $task){
            $this->output = array(
                "categories" => array(),
                "offers" => array()
            );
            $task->set("status", 4);
            $task->save();
            $this->getConfig($task->get("id"));
            $file_name = $this->config["base_path"].$this->config["file_name"];
            $file_url = '/assets/files/parser/'.$this->config["file_name"];
            $this->config["file_name"] = $file_name;
            $exclude = $task->get("exclude");
            if($exclude){
                $this->config["exclude"] = explode(",", $exclude);
            }
            $url = $task->get("url");
            $this->parse($url);
            $task->set("file", $file_url);
            $task->set("status", 2);
            $task->save();
        }
    }

    /**
     * Генерируем YML
     *
     *
     */
    public function generateYML(){
        $pdo = $this->modx->getService('pdoFetch');
        if($pdo){
            $content = $pdo->getChunk($this->config["chunk"], $this->output);
            $fd = fopen($this->config["file_name"], 'w');
            fwrite($fd, $content);
            fclose($fd);
        }
    }

    /**
     * Errors
     *
     * @param $message
     * @return void
     */
    public function error($message){
        $this->modx->log(xPDO::LOG_LEVEL_ERROR, $message);
    }

    /**
     * Лог процесса
     *
     * @param $text
     * @return void
     */
    public function checkprogress($text){
        $this->sl->tools->log($text, $this->config["log_file"]);
    }

}