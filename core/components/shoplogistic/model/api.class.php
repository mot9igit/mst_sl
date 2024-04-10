<?php

class minishop2_fast_api{
	public $modx;

	function __construct(modX &$modx, array $config = array()){
		$this->defaultConfig = array(
			'name' => "csv",
			'base_url_img' => "images/",
			'base_url_tv' => "assets/files/tvs",
			'path_iterator' => 0,
			'base_path' => dirname(__FILE__),
			'modx_base_catalog' => 2,
			'price_k' => 1,
			'price_opt' => 1,
			'show_metrics' => false,
			"categories_key_field" => "link_attributes",
			"check_brand" => 0,
			"update_products" => 0,
			'start_rows' => 1,
			'parse_rows' => 999999,
			'author_id' => 439,
			'images_mode' => 'gallery',
			'defaults_new_categories' => array(
				'template' => 21,
				'template_last' => 3,
				'published' => 1,
				'class_key' => 'msCategory',
				'description' => '',
				//'tvs' => 1
			),
			'defaults_new_products' => array(
				'template' => 4,
				'published' => 1,
				'class_key' => 'msProduct',
				'show_in_tree' => 0,
                'source' => 2
				//'tvs' => 1
			),
		);
		$this->modx = &$modx;
		$this->config = array_merge($this->defaultConfig, $config);
		$this->data = array();
		$this->dir = dirname(__FILE__);
		$this->time_start = microtime(true);
		if($this->config['show_metrics']){
			$this->checkprogress("Начинаю работу");
		}
	}

	// сохранение файла
	public function save_file($url, $path = "/"){
		if($this->config['show_metrics']){
			$this->checkprogress("Сохраняю файлы");
		}
		$file = basename($url);
		if($url[0] == '/' && $url[1] == '/'){
			$url = "https:".$url;
		}
		$destination_path = $this->modx->getOption("base_path").$this->config["base_url_tv"].$path;
		if (!file_exists($destination_path)) {
			mkdir($destination_path, 0755);
		}
		$destination = $destination_path.$file;
		$dest_file = fopen($destination, "w");
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_REFERER, "");
		curl_setopt($ch, CURLOPT_FILE, $dest_file);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		$result = curl_exec($ch);
		$info = curl_getinfo($ch);
		$error = curl_errno($ch);
		//$this->modx->log(1, print_r($info, 1));
		//$this->modx->log(1, print_r($result, 1));
		//$this->modx->log(1, print_r($error, 1));
		curl_close($ch);
		fclose($dest_file);
		return str_replace("assets/files/", "", $this->config["base_url_tv"]).$path.$file;
	}

	/* Сохранение картинки */
	public function save_img($img, $collision = 0){
		if($this->config['show_metrics']){
			$this->checkprogress("Сохраняю картинки");
		}
		if($img[0] == '/' && $img[1] == '/'){
			$img = "https:".$img;
		}else{
			$img = str_replace("//", "/", $this->config["base_url"].$img);
		}
		$path = $this->config["base_path"].'/'.$this->config["base_url_img"];
		$filename = $path;
		// проверяем существование каталога
		if (!file_exists($filename)) {
			mkdir($filename, 0755);
		}
		$file = basename($img);
		$imageurl = $img;
		$path = $filename.$file;
		// проверяем коллизию
		if(file_exists($path)){
			if($collision){
				// $this->checkprogress("Дублирование изображения - ввожу итератор");
				if (!file_exists($filename.$this->config['path_iterator'].'/')){
					mkdir($filename.$this->config['path_iterator'].'/', 0755);
				}
				$path = explode("?", $filename.$this->config['path_iterator'].'/'.$file);
				$this->config['path_iterator']++;
				$path = explode("?", $path);
				$pather = explode("?", str_replace("//", "/", $this->config["base_url_img"].$file));
				//$this->checkprogress("Загружаю картинку - ".$img." - в папку (".$pather[0].") - ".$path[0]);
				$ch = curl_init($img);
				$fp = fopen($path[0], 'wb');
				curl_setopt($ch, CURLOPT_FILE, $fp);
				curl_setopt($ch, CURLOPT_HEADER, 0);
				curl_exec($ch);
				curl_close($ch);
				fclose($fp);
				return "parse/".$pather[0];
			}else{
				return "parse/".$this->config["base_url_img"].$file;
			}
		}else{
			$path = explode("?", $path);
			$pather = explode("?", str_replace("//", "/", $this->config["base_url_img"].$file));
			//$this->checkprogress("Загружаю картинку - ".$img." - в папку (".$pather[0].") - ".$path[0]);
			$ch = curl_init($img);
			$fp = fopen($path[0], 'wb');
			curl_setopt($ch, CURLOPT_FILE, $fp);
			curl_setopt($ch, CURLOPT_HEADER, 0);
			curl_exec($ch);
			curl_close($ch);
			fclose($fp);
		}

		return "parse/".$pather[0];
	}

	// создаем категории из дерева
	public function categories_from_tree($data, $level = 0, $parent = 0){
		if($this->config['show_metrics']){
			$this->checkprogress("Создаю категории из дерева");
		}
		if($level == 0){
			$this->data['tempcats'] = array();
		}
		if($parent == 0){
			$parent = $this->config['modx_base_catalog'];
		}
		foreach($data as $cat){
			$save = $this->config['defaults_new_categories'];
			$save['pagetitle'] = $cat['name'];
			if($cat['uiid']){
				if($this->config["categories_key_field"]){
					$key = $this->config["categories_key_field"];
				}else{
					$key = 'link_attributes';
				}
				$save[$key] = $cat['uiid'];
			}
			$save['parent'] = $parent;
			//echo "<pre>";
			//print_r($save, 1);
			//echo "</pre>";
			$id = $this->new_category($save);
			if(isset($cat['items']) && $id){
				if(count($cat['items'])){
					$this->categories_from_tree($cat['items'], $level++, $id);
				}
			}else{
				if($id){
					if($save['template_last']){
						$update_data = array(
							"template" => $save['template_last']
						);
						$this->update("modResource", $update_data, $id);
					}
					$this->data['tempcats'][] = $id;
					return $id;
				}
			}
		}
	}

	// проверка существования категории товаров
	public function check_category($data){
		if($this->config['show_metrics']){
			$this->checkprogress("Проверяю категорию");
		}
		if($this->config["categories_key_field"]){
			$key = $this->config["categories_key_field"];
		}else{
			$key = 'link_attributes';
		}
		$sql = "SELECT * FROM {$this->modx->getTableName('modResource')} WHERE `class_key` = 'msCategory' AND `{$key}` = '{$data[$key]}' LIMIT 1";
		$q = $this->modx->prepare($sql);
		$q->execute();
		$res = $q->fetchAll(PDO::FETCH_ASSOC);
		if(count($res)){
			return $res[0]['id'];
		}else{
			return false;
		}
	}

	/* Проверка существования товара с артикулом */
	public function check_product($article){
		if($this->config['show_metrics']){
			$this->checkprogress("Проверяю товар");
		}
		if($article){
			$criteria = array(
				'article' => $article
			);
			$sql = "SELECT * FROM {$this->modx->getTableName('msProductData')} WHERE `article` = '{$article}' LIMIT 1";
			$q = $this->modx->prepare($sql);
			$q->execute();
			$res = $q->fetchAll(PDO::FETCH_ASSOC);
			if(count($res)){
				return $res[0]['id'];
			}else{
				return false;
			}
		}else{
			return false;
		}
	}

	/* опции */
	public function option_check($data){
		if($this->config['show_metrics']){
			$this->checkprogress("Создаю опцию");
		}
		$sql = "SELECT * FROM {$this->modx->getTableName('msOption')} WHERE `key` = '{$data['key']}' LIMIT 1";
		$q = $this->modx->prepare($sql);
		$q->execute();
		$res = $q->fetchAll(PDO::FETCH_ASSOC);
		if(count($res)){
			return $res[0]['id'];
		}else{
			// создаем опцию
			$id = $this->create("msOption", $data);
			return $id;
		}
	}

	/* чекаем опцию у категории */
	public function cat_option_check($option_id, $cat_id){
		if($this->config['show_metrics']){
			$this->checkprogress("Отмечаю опцию у категории");
		}
		$sql = "SELECT * FROM {$this->modx->getTableName('msCategoryOption')} WHERE `option_id` = {$option_id} AND `category_id` = {$cat_id} LIMIT 1";
		$q = $this->modx->prepare($sql);
		$q->execute();
		$res = $q->fetchAll(PDO::FETCH_ASSOC);
		if(count($res)){
			return $res[0]['id'];
		}else{
			// чекаем опцию у категории
			$op_data = array(
				'option_id' => $option_id,
				'category_id' => $cat_id,
				'active' => 1,
				'value' => ''
			);
			if($op_data["option_id"] && $op_data["category_id"]){
				$id = $this->create("msCategoryOption", $op_data);
				return $id;
			}
		}
	}

	/* производитель */
	public function vendor_check($name, $data = array()){
		if($this->config['show_metrics']){
			$this->checkprogress("Проверяю производителя");
		}
		$vendor_data = array(
			'name' => $name
		);
		if(count($data) && $name){
			foreach($data as $key => $val){
				if($key == 'image'){
					if($val){
						$image = $this->save_img($val, 0);
						if(file_exists($this->modx->getOption("base_path").$image)){
							$vendor_data["logo"] = $image;
						}
					}else{
						$vendor_data["logo"] = "";
					}
				}
			}
		}
		$sql = "SELECT * FROM {$this->modx->getTableName('msVendor')} WHERE `name` = '{$name}' LIMIT 1";
		$q = $this->modx->prepare($sql);
		$q->execute();
		$res = $q->fetchAll(PDO::FETCH_ASSOC);
		if(count($res)){
			// обновляем данные (пока только картинка)
			$this->update("msVendor", $vendor_data, $res[0]['id']);
			return $res[0]['id'];
		}else{
			// создаем производителя
			$id = $this->create("msVendor", $vendor_data);
			return $id;
		}
	}

	public function setResource($data){
		if($this->config['show_metrics']){
			$this->checkprogress("Создаю ресурс");
		}
		if(!isset($data['context_key'])){
			$data['context_key'] = 'web';
		}
		// интегрируем базовые настройки: опубликован, даты, кем создан
		if(!isset($data["alias"])){
			$p = $this->modx->newObject("modResource");
			$data['alias'] = $p->cleanAlias($data['pagetitle']);
			$data['uri'] = $p->getAliasPath($data['alias'], $data);
		}
		if(!isset($data['createdon'])){
			$date = date('Y-m-d H:i:s');
			// $data['createdon'] = $date;
			$data['createdon'] = time();
		}
		if(!isset($data['createdby'])){
			$data['createdby'] = $this->config['author_id'];
		}
		if(!isset($data['deleted'])){
			$data['deleted'] = 0;
		}
		if(!isset($data['published'])){
			$data['published'] = 1;
		}
		if(!isset($data['description'])){
			$data['description'] = '';
		}
		$id = $this->create('modResource', $data);
		return $id;
	}

	public function setTVs($tvs, $id){
		if($this->config['show_metrics']){
			$this->checkprogress("Создаю TV");
		}
		// интегрируем TV
		foreach($tvs as $key => $tv){
			$tvdata = array(
				"tmplvarid" => $key,
				"contentid" => $id,
				"value" => $tv
			);
			$tv_id = $this->create("modTemplateVarResource", $tvdata);
		}
	}

	public function setOptions($options, $id){
		if($this->config['show_metrics']){
			$this->checkprogress("Создаю опции товара");
		}
		// интегрируем Опции товара
		foreach($options as $key => $option){
			$option_data = array(
				"key" => $key,
				"product_id" => $id,
				"value" => $option
			);
			if($option_data['key'] && $option_data['product_id'] && $option_data['value']){
				$opt_id = $this->create("msProductOption", $option_data);
			}
		}
	}

	public function setGallery($images, $id){
		if($this->config['show_metrics']){
			$this->checkprogress("Создаю галерею");
		}
		if(count($images) && $id){
			foreach($images as $image){
				if($image[0] == '/' && $image[1] == '/'){
					$image = "https:".$image;
				}
				$image = explode("?", $image);
				$gallery = array(
					'id' => $id,
					'name' => '',
					'rank' => 0,
					'file' => $image[0]
				);
				$upload = $this->modx->runProcessor('gallery/upload', $gallery, array(
					'processors_path' => $this->modx->getOption("core_path").'components/minishop2/processors/mgr/'
				));
				if ($upload->isError()) {
					$this->checkprogress(print_r($upload->getResponse(),1).' / '.$id);
					$this->modx->error->reset();
				}
			}
		}
	}

	public function setFileGallery($images, $id){
		if($this->config['show_metrics']){
			$this->checkprogress("Создаю картинки в папке");
		}
		if(count($images) && $id){
			$url = '/assets/files/products/'.$id.'/';
			$filename = $this->modx->getOption("base_path").'assets/files/products/'.$id.'/';
			// $this->checkprogress($filename);
			// $this->checkprogress(print_r($images, 1));
			if (!file_exists($filename)) {
				mkdir($filename, 0755, 1);
			}else{
                $files = glob($filename.'*');
                foreach($files as $file){
                    if(is_file($file)) {
                        unlink($file);
                    }
                }
            }
			$check = 0;
            $image_thumb = '';
			foreach($images as $key => $img){
				$file = basename($img);
				$path = $filename.$file;
				$path = explode("?", $path);
				$ch = curl_init($img);
				$fp = fopen($path[0], 'wb');
				curl_setopt($ch, CURLOPT_FILE, $fp);
				curl_setopt($ch, CURLOPT_HEADER, 0);
				curl_exec($ch);
				curl_close($ch);
				fclose($fp);
				if(!$check){
					if(file_exists($path[0])){
                        if($key == 0){
                            $image_thumb = $url.$file;
                        }
					}
				}
			}
            if($image_thumb) {
                $data = array(
                    "image" => $image_thumb,
                    "thumb" => $image_thumb
                );
                $this->update("msProductData", $data, $id);
            }
		}
	}

	public function setImages($images, $id){
		if($this->config['show_metrics']){
			$this->checkprogress("Создаю картинки");
		}
		// интегрируем картинки
		if(count($images) && $id){
			if($this->config['images_mode'] == "gallery"){
				$this->setGallery($images, $id);
			}else{
				$this->setFileGallery($images, $id);
			}
		}
	}

	/* создание Категории товаров */
	public function new_category($data){
		if($this->config['show_metrics']){
			$this->checkprogress("Создаю категорию");
		}
		$cat_id = $this->check_category($data);
		if($cat_id){
			return $cat_id;
		}else{
			$data = array_merge($this->config["defaults_new_categories"], $data);
			// создание категории
			$id = $this->setResource($data);
			$this->checkprogress("Создана категория товаров {$data['pagetitle']} ({$id})");
			// TODO: проверить TVs
			return $id;
		}
	}

	/* создание товара с картинками */
	public function new_product($data){
		if($this->config['show_metrics']){
			$this->checkprogress("Создаю товар");
		}
		$data = array_merge($this->config["defaults_new_products"], $data);
		$images = $data['image'];
		unset($data['image']);
		$product_id = $this->check_product($data["article"]);
		if($product_id){
			if($this->config['update_products']){
				// обновление товара только msProductData
				$this->update("msProductData", $data, $product_id);
			}
			return array(
                "mode" => "update",
                "resource" => $product_id
            );
		}else{
			$options = array();
			$tvs = array();
			foreach($data as $key => $item){
				$pos_tv = strpos($key, 'tv');
				$pos_option = strpos($key, 'options-');
				if($pos_tv !== false && $pos_option !== false){
					// $prod->set($key, $item);
				}else{
					if($pos_tv !== false){
						$k_tv = str_replace("tv", "", $key);
						$tvs[$k_tv] = $item;
					}
					if($pos_option !== false){
						$k_opt = str_replace("options-", "", $key);
						$options[$k_opt] = $item;
					}
				}
			}
			// создание товара
			$id = $this->setResource($data);
			if($id){
				$data['id'] = $id;
                $resource = $this->modx->newObject("modResource");
                if($resource) {
                    $class_key = $data['class_key'];
                    $pagetitle = $data['pagetitle'];
                    if ($class_key == 'msCategory') {
                        $alias = $id . "_" . $resource->cleanAlias($pagetitle);
                        $up_data = array(
                            "alias" => $alias,
                            "uri" => "category/" . $alias,
                            "uri_override" => 1
                        );
                        $this->update("modResource", $up_data, $id);
                    }
                    if ($class_key == 'msProduct') {
                        $alias = $id . "_" . $resource->cleanAlias($pagetitle);
                        $up_data = array(
                            "alias" => $alias,
                            "uri" => "products/" . $alias,
                            "uri_override" => 1
                        );
                        $this->update("modResource", $up_data, $id);
                    }
                    $id = $this->create("msProductData", $data);
                    if (count($tvs)) {
                        $this->setTVs($tvs, $id);
                    }
                    if (count($options)) {
                        $this->setOptions($options, $id);
                    }
                    if ($images) {
                        $this->setImages($images, $id);
                    }
                }
			}
			$this->checkprogress("Создан товар {$data['pagetitle']} ({$id})");
            return array(
                "mode" => "create",
                "resource" => $id
            );
		}
	}

	// проверка объекта
	public function check($object, $data){
		$where = '';
		// пока только AND и один оператор
		foreach($data as $key => $dt){
			if($where == ''){
				$where = "`{$key}` == {$dt}";
			}else{
				$where .= " AND `{$key}` == {$dt}";
			}
		}

		$sql = "SELECT * FROM {$this->modx->getTableName($object)} WHERE '{$where}' LIMIT 1";
		$q = $this->modx->prepare($sql);
		$q->execute();
		$res = $q->fetch(PDO::FETCH_ASSOC);
		if(count($res)){
			return $res;
		}else{
			return false;
		}
	}

	// поиск набора объектов
	public function getCollection($where = array()){
		$q = $this->modx->newQuery('modResource');
		$q->where($where);
		$q->select(array(
			'modResource.*'
		));
		$q->prepare();
		if($q->stmt && $q->stmt->execute()) {
			return $q->stmt->fetchAll(PDO::FETCH_ASSOC);
		}else{
			return false;
		}
	}

	// создание объекта
	public function create($object, $data){
		if($this->config['show_metrics']){
			$this->checkprogress("Создаю");
		}
		$table = $this->modx->getTableName($object);
		if($table){
			$coloumns = explode(",", str_replace(array("`", " "), array("", ""),$this->modx->getSelectColumns($object)));
			// check data on coloumns
			foreach($data as $key => $val){
				if(!in_array($key, $coloumns)){
					unset($data[$key]);
				}
			}
			// print_r($data);
			$keys = [];
			$values = [];
			foreach ($data as $key => $value) {
				$keys[] = "`$key`";
                if(!is_int($value)){
                    $value = addslashes($value);
                }
				$values[] = is_int($value) || $value == 'now()' ? $value : "'$value'";
			}
			$keys = implode(', ', $keys);
			$values = implode(', ', $values);
			$sql = "INSERT INTO {$table} ({$keys}) VALUES ({$values});";
			// echo $sql."<br/>";
			$stmt = $this->modx->prepare($sql);
			if(!$stmt){
				$this->modx->log(1, print_r($stmt->errorInfo, true) . ' SQL: ' . $sql);
			}
			if (!$stmt->execute($data)) {
				$this->modx->log(1, print_r($stmt->errorInfo, true) . ' SQL: ' . $sql);
			}else{
				return $this->modx->lastInsertId();
			}
		}else{
			$this->checkprogress("Не могу инициализировать таблицу: ".$table);
			return false;
		}
	}

	// обновление данных
	public function update($object, $data, $id = 0){
		if($this->config['show_metrics']){
			$this->checkprogress("Обновляю");
		}
		$table = $this->modx->getTableName($object);
		if($table){
			$coloumns = explode(",", str_replace(array("`", " "), array("", ""),$this->modx->getSelectColumns($object)));
			// check data on coloumns
			foreach($data as $key => $val){
				if(!in_array($key, $coloumns)){
					unset($data[$key]);
				}
			}
			$update_data = [];
			foreach ($data as $key => $value) {
                if(!is_int($value)){
                    $value = addslashes($value);
                }
				$v = is_int($value) ? $value : "'{$value}'";
				$update_data[] = "`{$key}` = {$v}";
			}
			// print_r($update_data);
			$values = implode(', ', $update_data);
			$sql = "UPDATE {$table} SET {$values} WHERE `id` = {$id};";
			// echo $sql."<br/>";
			$stmt = $this->modx->prepare($sql);
			if(!$stmt){
				$this->modx->log(1, print_r($stmt->errorInfo, true) . ' SQL: ' . $sql);
			}
			if (!$stmt->execute($data)) {
				$this->modx->log(1, print_r($stmt->errorInfo, true) . ' SQL: ' . $sql);
			}
			return $id;
		}else{
			$this->checkprogress("Не могу инициализировать таблицу: ".$table);
			return false;
		}
	}

	// пишем результаты работы в файл
	public function checkprogress($text, $file = 'import_log'){
		$time_check_point = microtime(true) - $this->time_start;
		$text = round($time_check_point, 2).'s: '.$text;
		$this->modx->log(xPDO::LOG_LEVEL_ERROR, print_r($text, 1), array(
			'target' => 'FILE',
			'options' => array(
				'filename' => $file.'.log'
			)
		));
	}
}