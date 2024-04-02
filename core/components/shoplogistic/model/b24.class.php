<?php
require_once (__DIR__.'/libs/crest/crest.php');

class b24Handler
{
	public $modx;
	public $sl;
	public $ms2;

	public $config;

	public function __construct(shopLogistic &$sl, modX &$modx)
	{
		$this->sl =& $sl;
		$this->modx =& $modx;
		$this->modx->lexicon->load('shoplogistic:default');
        $webhook = $this->modx->getOption("shoplogistic_crm_webhook");
        if($webhook) {
            define('C_REST_WEB_HOOK_URL', $webhook);
            $this->crest = new CRest;
        }
	}

    public function initialize(){
        // define B24
        $webhook = $this->modx->getOption("shoplogistic_crm_webhook");
        if($webhook){
            define('C_REST_WEB_HOOK_URL', $webhook);
            $this->crest = new CRest;
            // link ms2
            if (is_dir($this->modx->getOption('core_path') . 'components/minishop2/model/minishop2/')) {
                $ctx = 'web';
                $this->ms2 = $this->modx->getService('miniShop2');
                if ($this->ms2 instanceof miniShop2) {
                    $this->ms2->initialize($ctx);
                }
            }
            // $response = $this->updateFields();
            return true;
        }else{
            $this->modx->log(MODX_LOG_LEVEL_ERROR, 'shopLogistic :: CRM - Не заполнен Webhook в системных настройках');
            return false;
        }
    }

	/**
	 * Функция проверки полей (сделка и контакт) и обновления
	 */
	public function updateFields(){
		// сначала товар
		$types = array(3,1);
		foreach($types as $type){
			if($type == 3){
				$fields = $this->getProductFields();
			}
			if($type == 1){
				$fields = $this->getDealFields();
			}
			$existFields = array();
			foreach($fields['result'] as $key => $value){
				// $this->modx->log(1, $key.' '.print_r($value, 1));
				if(!isset($value['formLabel'])){
					$value['formLabel'] = false;
				}
				$criteria = array(
					"crm_id" => $key,
					"type" => $type
				);
				$obj = $this->modx->getObject("slCRMFields", $criteria);
				if(!isset($value['code'])){
					$value['code'] = '';
				}
				if(!$obj){
					// если поле не найдено - создаем
					$obj = $this->modx->newObject("slCRMFields");
					$obj->set("crm_id", $key);
					$obj->set("name", $value['formLabel']?:$value['title']);
					$obj->set("type", $type);
					$obj->set("code", $value['code']);
					$obj->set("active", 1);
					if(isset($value['enums'])){
						$obj->set("enums", json_encode($value['enums'], JSON_UNESCAPED_UNICODE));
					}
					if(isset($value['items'])){
						$obj->set("enums", json_encode($value['items'], JSON_UNESCAPED_UNICODE));
					}
				}else{
					// иначе обновляем поля
					$obj->set("name", $value['formLabel']?:$value['title']);
					$obj->set("code", $value['code']);
					if(isset($value['enums'])){
						$obj->set("enums", json_encode($value['enums'], JSON_UNESCAPED_UNICODE));
					}else{
						$obj->set("enums", "");
					}
					if(isset($value['items'])){
						$obj->set("enums", json_encode($value['items'], JSON_UNESCAPED_UNICODE));
					}
				}
				$obj->save();
				$existFields[] = $obj->get('id');
			}
			// удаляем поля, которых нет в CRM
            if(count($existFields)){
                $query = $this->modx->newQuery("slCRMFields");
                $query->where(array(
                    "type:=" => $type,
                    "AND:id:NOT IN" => $existFields
                ));
                $query->prepare();
                $objects = $this->modx->getCollection("slCRMFields", $query);
                foreach($objects as $object){
                    $object->remove();
                }
            }
		}
		// добавляем категории
		$response = $this->request('crm.category.list', array("entityTypeId" => 2));
		if($response['result']['categories']){
			$existCats = array();
			$existStages = array();
			foreach($response['result']['categories'] as $category){
				$criteria = array(
					"crm_id" => $category['id']
				);
				$obj = $this->modx->getObject("slCRMCategory", $criteria);
				if(!$obj){
					// если поле не найдено - создаем
					$obj = $this->modx->newObject("slCRMCategory");
					$obj->set("crm_id", $category['id']);
					$obj->set("name", $category['name']);
					$obj->set("entity_type", $category['entityTypeId']);
					$obj->set("sort", $category['sort']);
					$obj->set("active", 1);
				}else{
					// иначе обновляем поля
					$obj->set("name", $category['name']);
					$obj->set("sort", $category['sort']);
					$obj->set("entity_type", $category['entityTypeId']);
				}
				$obj->save();
				$existCats[] = $obj->get('id');
				// привязываем стадии
				$resp = $this->request('crm.dealcategory.stage.list', array("id" => $category['id']));
				if($resp['result']){
					foreach($resp['result'] as $stagen){
						$criteria = array(
							"crm_id" => $stagen['STATUS_ID'],
							"category_id" => $obj->get('id')
						);
						$stage = $this->modx->getObject("slCRMStage", $criteria);
						if(!$stage){
							// если поле не найдено - создаем
							$stage = $this->modx->newObject("slCRMStage");
							$stage->set("crm_id", $stagen['STATUS_ID']);
							$stage->set("name", $stagen['NAME']);
							$stage->set("category_id", $obj->get('id'));
							$stage->set("sort", $stagen['SORT']);
							$stage->set("active", 1);
						}else{
							// иначе обновляем поля
							$stage->set("name", $stagen['NAME']);
							$stage->set("sort", $stagen['SORT']);
							$stage->set("category_id", $obj->get('id'));
						}
						$stage->save();
						$existStages[] = $stage->get('id');
					}
				}
			}
		}
        // TODO: проверить очистку. Чистит лишнего много
		// чистим категории
        if(count($existCats)) {
            $query = $this->modx->newQuery("slCRMCategory");
            $query->where(array(
                "id:NOT IN" => $existCats
            ));
            $query->prepare();
            $objects = $this->modx->getCollection("slCRMCategory", $query);
            foreach ($objects as $object) {
                // $object->remove();
            }
        }
		// чистим стадии
        if(count($existStages)) {
            $query = $this->modx->newQuery("slCRMStage");
            $query->where(array(
                "id:NOT IN" => $existStages
            ));
            $query->prepare();
            $objects = $this->modx->getCollection("slCRMStage", $query);
            foreach ($objects as $object) {
                // $object->remove();
            }
        }
		return true;
	}

	public function getCustomValues($order_data, $type){
		$criteria = array(
			"type" => $type
		);
		$contact_fields = $this->modx->getCollection("slCRMFields", $criteria);
		$custom_values = array();
		foreach($contact_fields as $f){
			$field = $f->toArray();
			if($field['field']){
				$tmp = array();
				$farr = explode(".", $field['field']);
				if(count($farr) > 1){
					$key = $farr[0];
					$ff = $farr[1];
				}else{
					$key = 'order';
					$ff = $farr[0];
				}
                if(isset($order_data[$key])){
                    if($order_data[$key][$ff]) {
                        if ($field['properties']) {
                            $props = json_decode($field['properties'], 1);
                            // $this->modx->log(1, print_r($props, 1));
                            foreach($props as $prop){
                                if($prop['filter']){
                                    switch ($prop['filter']){
                                        case 'striptags':
                                            $order_data[$key][$ff] = strip_tags($order_data[$key][$ff]);
                                            break;
                                        case 'image':
                                            $order_data[$key][$ff] = array(
                                                basename($order_data[$key][$ff]),
                                                $this->base64Encode($order_data[$key][$ff])
                                            );;
                                            break;
                                    }
                                }
                                // ENUMS
                                if($prop['enums']){
                                    // $this->modx->log(1, print_r($prop['enums'], 1));
                                    foreach($prop['enums'] as $k => $enum){
                                        if($k == $order_data[$key][$ff]){
                                            $order_data[$key][$ff] = $enum;
                                        }
                                    }
                                }
                            }
                        }
                        $custom_values[$field['crm_id']] = $order_data[$key][$ff];
                    }
                }
			}
		}
		return $custom_values;
	}

	public function addProduct($id) {
		$method = 'crm.product.add';
		$product = $this->modx->getObject('msProductData', $id);
		if($product){
			$double = $this->checkProduct($id);
			$data = array();
			$resource = $this->modx->getObject("modResource", $id);
			$data['resource'] = $resource->toArray();
			$data['product'] = $product->toArray();
			$fields = $this->getCustomValues($data, 3);
			if($product->get('image')){
				$fields["PREVIEW_PICTURE"]["fileData"] = array(
					basename($product->get('image')),
					$this->base64Encode($product->get('image'))
				);
			}
			if($double['total'] == 0){
				$response = $this->request($method, array("fields" => $fields));
				if($response['result']){
					$product->set("b24id", $response['result']);
					$product->save();
				}
				return $response['result'];
			}else{
				// товар уже есть в системе - обновляем
				if($double['result'][0]["ID"]){
					$product->set("b24id", $double['result'][0]["ID"]);
					$product->save();
					$response = $this->updateProduct($double['result'][0]["ID"], $fields);
					return $double['result'][0]["ID"];
				}
			}
		}
	}

	public function checkProduct($id){
		$product = $this->modx->getObject('msProduct', $id);
		if($product){
			$method = "crm.product.list";
			$key = $this->modx->getOption('shoplogistic_crm_product_key_field');
			if($key){
				$criteria = array(
					"field" => $key,
					"type" => 3
				);
				$obj = $this->modx->getObject("slCRMFields", $criteria);
				$ob_key = explode('.', $key);
				if($obj){
					$data = array(
						"filter" => array(
							$obj->get('crm_id') => $product->get($ob_key[1])
						),
						"select" => array('*', 'PROPERTY_*')
					);
					$response = $this->request($method, $data);
					return $response;
				}
			}
			return false;
		}
	}

	public function updateProduct($id, $data){
		$method = "crm.product.update";
		$data = array(
			"id" => $id,
			"fields" => $data
		);
		$response = $this->request($method, $data);
		return $response;
	}

	public function base64Encode ($file){
		$path = str_replace("//", "/", $this->modx->getOption('base_path').$file);
		// echo $path;
		// $type = pathinfo($path, PATHINFO_EXTENSION);
		$data = file_get_contents($path);
		$base64 = base64_encode($data);
		return $base64;
	}

	public function getProductFields() {
		$method = "crm.product.fields";
		$response = $this->request($method);
		return $response;
	}

	public function getDealFields() {
		$method = "crm.deal.fields";
		$response = $this->request($method);
		return $response;
	}

	public function addContact($data) {
		$contact = $this->checkContact($data);
		$phone = $data['phone'];
		unset($data['phone']);
		if($phone){
			$data['PHONE'] = array(
				array(
					"VALUE" => $phone,
					"VALUE_TYPE" => "WORK"
				)
			);
		}
		$email = $data['email'];
		unset($data['email']);
		if($email){
			$data['EMAIL'] = array(
				array(
					"VALUE" => $email,
					"VALUE_TYPE" => "WORK"
				)
			);
		}
		if(count($contact['result']) && $contact['total'] == 1){
			$rdata = array(
				"id" => $contact['result'][0]['ID'],
				"fields" => $data
			);
			$response = $this->updateContact($rdata);
			return $contact['result'][0]['ID'];
		}else{
			$method = "crm.contact.add";
			$response = $this->request($method, array("fields" => $data));
			if($response['result']){
				return $response['result'];
			}
		}
		return false;
	}

	public function updateContact($data) {
		$method = "crm.contact.update";
		$response = $this->request($method, $data);
		return $response;
	}

	public function checkContact($data){
		$method = "crm.contact.list";
		$filter = array();
		// check by phone default
		if($data['phone']){
			$filter["PHONE"] = $data['phone'];
		}
		/*
		if($data['email']){
			$filter["EMAIL"] = $data['email'];
		}
		*/
		if(count($filter)){
			$data = array(
				"filter" => $filter ,
				"select" => array('*', 'PHONE', 'EMAIL', 'UF_*')
			);
			$response = $this->request($method, $data);
			return $response;
		}
	}

	public function renderPhone($phone){
		$p = preg_replace("/[^0-9]/", '', $phone);
		if (substr($p,0,1) == 8) {
			$p[0] = 7;
		}
		if (substr($p,0,1) != 8 && substr($p,0,1) != 7) {
			$p = '7'.$p;
		}
		if(strlen($p) == 11){
			$p = '+'.$p;
			return $p;
		}else{
			$this->modx->log(MODX_LOG_LEVEL_ERROR, 'shopLogistic :: CRM - Не корректный формат телефона: '.$p);
			return false;
		}
	}

	public function prepareStage($id){
		$output = array();
		$stage = $this->modx->getObject('slCRMStage', $id);
		if($stage){
			$output['STAGE_ID'] = $stage->get('crm_id');
			$category = $this->modx->getObject("slCRMCategory", $stage->get('category_id'));
			if($category){
				$output['CATEGORY_ID'] = $category->get("crm_id");
			}
		}
		return $output;
	}

	public function getDeal($id){
		$method = "crm.deal.get";
		$response = $this->request($method, array('id' => $id));
		return $response;
	}

	public function updateDeal($id, $data){
		$method = "crm.deal.update";
		if(isset($data['STAGE_ID'])){
			if(is_numeric($data['STAGE_ID'])){
				$stage = $this->prepareStage($data['STAGE_ID']);
				$data = array_merge($data, $stage);
			}
		}
		$deal = $this->getDeal(intval($id));
		// $this->modx->log(1, print_r($deal, 1));
		if($deal['result']){
			$prepare_data = $this->getCustomValues($data, 1);
			$request_data = array(
				"id" => $deal['result']['ID'],
				"fields" => array_merge($data, $prepare_data)
			);
			// $this->modx->log(1, print_r($request_data, 1));
			$response = $this->request($method, $request_data);
			return $response;
		}
		return false;
	}

	public function addDeal($data, $send_data = array()){
		$contact_data = array(
			"NAME" => $data['address']['receiver'],
			"email" => $data['user']['email']
		);
		// fix phone format
		if($data['address']['phone']){
			$data['address']['phone'] = $this->renderPhone($data['address']['phone']);
			$contact_data['phone'] = $data['address']['phone'];
		}
		$contact_id = $this->addContact($contact_data);
        $custom_values = $this->getCustomValues($data, 1);
		$deal_data = array_merge($send_data, $custom_values);
		$deal_data["TITLE"] = "Заказ #".$data['order']['num'];
		$deal_data["CURRENCY_ID"] = "RUB";
        $deal_data["ASSIGNED_BY_ID"] = $this->modx->getOption("shoplogistic_assigned_by_id");
        $deal_data["TYPE_ID"] = $this->modx->getOption("shoplogistic_type_id");
		$deal_data["OPPORTUNITY"] = $data['order']['cart_cost'];
		$deal_data["CONTACT_ID"] = $contact_id;
		// $deal_data["STAGE_ID"] = "RUB";
		if(isset($data['order']['store_id'])){
			$deal_data["UF_CRM_1678043491"] = $data['order']['store_id'];
		}
		if(isset($data['order']['warehouse_id'])) {
			$deal_data["UF_CRM_1678043469"] = $data['order']['warehouse_id'];
		}
		if(isset($data['STAGE_ID'])){
			if(is_numeric($data['STAGE_ID'])){
				$stage = $this->prepareStage($data['STAGE_ID']);
				$deal_data = array_merge($deal_data, $stage);
			}
		}
		$method = "crm.deal.add";
        $this->modx->log(1, print_r($deal_data, 1));
		$response = $this->request($method, array("fields" => $deal_data));
        $this->modx->log(1, print_r($response, 1));
		if($response['result']){
			$deal_id = $response['result'];
			$order = $this->modx->getObject('slOrder', array("num" => $data['order']["num"]));
			if($order){
				$order->set("crm_id", $deal_id);
				$order->save();
				if(count($data['products'])){
					if($this->modx->getOption("shoplogistic_crm_link_products")){
                        // TODO: написать создание товара
						$response = $this->linkProducts($deal_id, $data['products']);
					}else{
						$response = $this->addComment($deal_id, $data['products']);
					}
				}
				return $deal_id;
			}
		}
		return $data;
	}

	public function linkProducts($deal_id, $products){
		$method = "crm.deal.productrows.set";
		$data = array(
			"id" => $deal_id
		);
		foreach($products as $product){
			$tmp = array(
				"PRODUCT_ID" => $product['b24id'],
				"PRICE" => $product['price'],
				"QUANTITY" => $product['count']
			);
			$data['rows'][] = $tmp;
		}
		$response = $this->request($method, $data);
		return $response;
	}

	public function addComment($deal_id, $products){
		$method = "crm.timeline.comment.add";
		$data = array(
			"ENTITY_ID" => intval($deal_id),
			"ENTITY_TYPE" => "deal"
		);
		$output = "[table]";
		$output .= "[tr]
			[td][b]Товар[/b][/td]
			[td][b]Кол-во[/b][/td]
			[td][b]Цена[/b][/td]
			[td][b]Стоимость[/b][/td]
		[/tr]";
		foreach($products as $product){
			$url = $this->modx->makeUrl($product['product_id'], '', '', 'full');
			$excludeOptions = array('modification','modifications','msal');
			$options = array();
			if(isset($product['options'])){
				foreach($product['options'] as $key => $option){
					if(!in_array($key, $excludeOptions)){
						$options[] = $option;
					}
				}
			}
			$output .= "[tr]
				[td][url=".$url."]".mb_strimwidth($product['name'], 0, 40, '...')."[/url] ([b]".implode(", ", $options)."[/b])[/td]
				[td]".$product['count']." шт.[/td]
				[td]".$product['price']." руб.[/td]
				[td]".$product['cost']." руб.[/td]
			[/tr]";
		}
		$output .= "[/table]";
		$data['COMMENT'] = $output;
		$response = $this->request($method, array("fields" => $data));
		return $response;
	}

	public function request($method, $data = array()){
		$response = $this->crest->call($method, $data);
		return $response;
	}

    public function tolog($data) {
        $this->modx->log(xPDO::LOG_LEVEL_ERROR, print_r($data, 1), array(
            'target' => 'FILE',
            'options' => array(
                'filename' => 'bitrix24.log'
            )
        ));
    }
}