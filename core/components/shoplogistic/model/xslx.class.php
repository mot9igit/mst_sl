<?php

require_once dirname(__FILE__) . '/../libs/vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Reader\IReader;
use PhpOffice\PhpSpreadsheet\Writer\IWriter;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\IReadFilter;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class slXSLX{
	public function __construct(shopLogistic &$sl, modX &$modx)
	{
		$this->sl =& $sl;
		$this->modx =& $modx;
		$this->modx->lexicon->load('shoplogistic:default');

        $dir = dirname(__FILE__);
        $file = $dir.'/libs/PHPExcel/Classes/PHPExcel.php';
        if (file_exists($file)) {
            include_once $file;
        }else{
            return $this->error("Ошибка загрузки файла Excel: ".$file);
        }
	}

    /**
     * Парсим файл РРЦ
     *
     * @param $store_id
     * @param $file
     * @return void
     */
    public function parseRRCFile ($store_id, $file) {
        $out = array(
            "total" => 0,
            "success" => array(
                "total" => 0,
                "data" => array()
            ),
            "errors" => array(
                "total" => 0,
                "data" => array()
            )
        );
        $file_in = $this->modx->getOption("base_path").$file;
        if(file_exists($file_in)){
            try {
                $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file_in);
            } catch (\PhpOffice\PhpSpreadsheet\Reader\Exception $e) {
                $output = $this->sl->tools->error("Некорректный файл для загрузки!");
            }
            $sheetData = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);
            $out["total"] = count($sheetData);
            foreach($sheetData as $key => $value) {
                // пропускаем шапку
                if ($key != 1) {
                    $criteria = array(
                        "store_id:=" => $store_id,
                        "article:=" => $value["A"]
                    );
                    $query = $this->modx->newQuery("slStoresRemains");
                    $query->where($criteria);
                    $query->select(array(
                        "slStoresRemains.*"
                    ));
                    $count = $this->modx->getCount("slStoresRemains", $query);
                    if(!$count){
                        $out['errors'][] = $this->sl->tools->error("Остаток не найден!", $value);
                    }else{
                        if($count == 1){
                            if($query->prepare() && $query->stmt->execute()) {
                                $remain = $query->stmt->fetch(PDO::FETCH_ASSOC);
                                if($remain){
                                    $object = $this->modx->getObject("slStoresRemains", $remain["id"]);
                                    if($object){
                                        $object->set("price", $value["D"]);
                                        if($object->save()){
                                            $out['success']['data'][] = $this->sl->tools->error("Объект остатка обновлен!", $value);
                                        }else{
                                            $out['errors']['data'][] = $this->sl->tools->error("Объект остатка не обновлен, ошибка!", $value);
                                        }
                                    }else{
                                        $out['errors']['data'][] = $this->sl->tools->error("Объект остатка не найден!", $value);
                                    }
                                }else{
                                    $out['errors']['data'][] = $this->sl->tools->error("Остаток не найден!", $value);
                                }
                            }
                        }else{
                            $out['errors']['data'][] = $this->sl->tools->error("Найдено больше 1 товара с аналогичным артикулом!", $value);
                        }
                    }
                }
            }
            $out['success']['total'] = count($out['success']['data']);
            $out['errors']['total'] = count($out['errors']['data']);
        }
        $output = $this->sl->tools->error("Файл обработан!", $out);
        return $output;
    }

    public function setIndividualActions($owner_id, $file, $store_id = 53){
        $output = array();
        $owner = $this->modx->getObject("slOrg", $owner_id);
        if($owner) {
            $owner_data = $owner->toArray();
            $org_id = $owner->get("id");
            $out = array(
                "total" => 0,
                "success" => array(
                    "total" => 0,
                    "data" => array()
                ),
                "errors" => array(
                    "total" => 0,
                    "data" => array()
                )
            );
            $file_in = $this->modx->getOption("base_path") . $file;
            if (file_exists($file_in)) {
                try {
                    $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file_in);
                } catch (\PhpOffice\PhpSpreadsheet\Reader\Exception $e) {
                    $output = $this->sl->tools->error("Некорректный файл для загрузки!");
                }
                $sheetData = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);
                $out["total"] = count($sheetData);
                foreach ($sheetData as $key => $value) {
                    // пропускаем шапку
                    if ($key != 1) {
                        // тестовый запуск
                        // if($key == 2) {
                            // Нам важны контакты, поэтому проверяем есть ли они
                            if ($value['F'] || $value['G']) {
                                $inn = $value['C'];
                                $query = $this->modx->newQuery("slOrgRequisites");
                                $query->leftJoin("slOrg", "slOrg", "slOrg.id = slOrgRequisites.org_id");
                                $query->where(array(
                                    'slOrgRequisites.inn:=' => $inn
                                ));
                                $count = $this->modx->getCount('slOrgRequisites', $query);
                                if ($count > 0) {
                                    $query->select(array("slOrg.*"));
                                    if ($query->prepare() && $query->stmt->execute()) {
                                        $userdata["organization"] = $query->stmt->fetch(PDO::FETCH_ASSOC);
                                        if (count($userdata["organization"])) {
                                            if ($userdata["organization"]["id"]) {
                                                $org = $this->modx->getObject("slOrg", $userdata["organization"]["id"]);
                                                if ($org) {
                                                    $type_koeff = 0.65;
                                                    $client_data = $org->toArray();
                                                    $save_data = array(
                                                        "name" => "Индивидуальная акция {$owner_data['name']} для {$client_data['name']}",
                                                        "resource" => 0,
                                                        "global" => 1,
                                                        "active" => 1,
                                                        "createdon" => time(),
                                                        "store_id" => $store_id,
                                                        "compatibility_discount" => 0,
                                                        "compatibility_postponement" => 0,
                                                        "type" => 3,
                                                        "shipment_type" => 0,
                                                        "payer" => 0,
                                                        "delivery_payment_terms" => 0,
                                                        "delivery_payment_value" => 0,
                                                        "org_id" => $owner_data['id'],
                                                        "client_id" => $client_data['id']
                                                    );
                                                    if ($value["H"]) {
                                                        $save_data["delay"] = $value["H"];
                                                    }
                                                    // Добавляем акцию
                                                    $criteria = array(
                                                        "org_id:=" => $owner_data['id'],
                                                        "client_id:=" => $client_data['id']
                                                    );
                                                    $act = $this->modx->getObject("slActions", $criteria);
                                                    if(!$act){
                                                        $act = $this->modx->newObject("slActions");
                                                    }
                                                    foreach($save_data as $k => $v){
                                                        $act->set($k, $v);
                                                    }
                                                    $act->save();
                                                    $client_data["action"] = $act->toArray();
                                                    // выставляем отсрочку
                                                    $criteria = array(
                                                        "action_id:=" => $client_data["action"]["id"]
                                                    );
                                                    $delay = $this->modx->getObject("slActionsDelay",$criteria);
                                                    if(!$delay){
                                                        $delay = $this->modx->newObject("slActionsDelay");
                                                    }
                                                    $delay->set("percent", 100);
                                                    $delay->set("action_id", $client_data["action"]["id"]);
                                                    if ($value["H"]) {
                                                        $delay->set("day", $value["H"]);
                                                    }else{
                                                        $delay->set("day", 0);
                                                    }
                                                    $delay->save();
                                                    $client_data["action"]['delay'] = $delay->toArray();
                                                    if($value["I"]){
                                                        // Основная продукция Интерскол
                                                        $sale_koeff = 1 - floatval($value["I"]);
                                                        $group_id = 2;
                                                        $query = $this->modx->newQuery("slStoresRemains");
                                                        $query->where(array(
                                                            "FIND_IN_SET({$group_id}, groups) > 0",
                                                            "slStoresRemains.store_id:=" => $store_id
                                                        ));
                                                        $query->select(array("slStoresRemains.*"));
                                                        if($query->prepare() && $query->stmt->execute()) {
                                                            $remains = $query->stmt->fetchAll(PDO::FETCH_ASSOC);
                                                            foreach ($remains as $remain) {
                                                                $remain["new_price"] = $remain["price"] * $sale_koeff;
                                                                $remain["old_price"] = $remain["price"];
                                                                $remain["sale_koeff"] = $sale_koeff;

                                                                $criteria = array(
                                                                    "action_id:=" => $client_data["action"]["id"],
                                                                    "remain_id:=" => $remain["id"]
                                                                );
                                                                $product = $this->modx->getObject("slActionsProducts",$criteria);
                                                                if(!$product){
                                                                    $product = $this->modx->newObject("slActionsProducts");
                                                                }
                                                                $product->set("action_id", $client_data["action"]["id"]);
                                                                $product->set("old_price", $remain["old_price"]);
                                                                $product->set("new_price", $remain["new_price"]);
                                                                $product->set("active", 1);
                                                                $product->set("createdon", time());
                                                                $product->set("multiplicity", 1);
                                                                $product->set("remain_id", $remain["id"]);
                                                                $product->set("min_count", 1);
                                                                $product->save();
                                                                // $client_data["action"]["products"][] = $product->toArray();
                                                            }
                                                        }
                                                    }
                                                    if($value["J"]){
                                                        // Оснастка Интерскол
                                                        $sale_koeff = 1 - floatval($value["J"]);
                                                        $type_koeff = $sale_koeff;
                                                        $group_id = 1;
                                                        $query = $this->modx->newQuery("slStoresRemains");
                                                        $query->where(array(
                                                            "FIND_IN_SET({$group_id}, groups) > 0",
                                                            "slStoresRemains.store_id:=" => $store_id
                                                        ));
                                                        $query->select(array("slStoresRemains.*"));
                                                        if($query->prepare() && $query->stmt->execute()) {
                                                            $remains = $query->stmt->fetchAll(PDO::FETCH_ASSOC);
                                                            foreach ($remains as $remain) {
                                                                $remain["new_price"] = $remain["price"] * $sale_koeff;
                                                                $remain["old_price"] = $remain["price"];
                                                                $remain["sale_koeff"] = $sale_koeff;
                                                                $criteria = array(
                                                                    "action_id:=" => $client_data["action"]["id"],
                                                                    "remain_id:=" => $remain["id"]
                                                                );
                                                                $product = $this->modx->getObject("slActionsProducts",$criteria);
                                                                if(!$product){
                                                                    $product = $this->modx->newObject("slActionsProducts");
                                                                }
                                                                $product->set("action_id", $client_data["action"]["id"]);
                                                                $product->set("old_price", $remain["old_price"]);
                                                                $product->set("new_price", $remain["new_price"]);
                                                                $product->set("active", 1);
                                                                $product->set("createdon", time());
                                                                $product->set("multiplicity", 1);
                                                                $product->set("remain_id", $remain["id"]);
                                                                $product->set("min_count", 1);
                                                                $product->save();
                                                                // $client_data["action"]["products"][] = $product->toArray();
                                                            }
                                                        }
                                                    }
                                                    // Фиксированные цены Интерскол
                                                    if($type_koeff == 0.65){
                                                        $file = $this->modx->getOption("base_path") . 'assets/files/tmp/xlsx/53_fixes_r.xlsx';
                                                    }
                                                    if($type_koeff == 0.54){
                                                        $file = $this->modx->getOption("base_path") . 'assets/files/tmp/xlsx/53_fixes_opt.xlsx';
                                                    }
                                                    if (file_exists($file)) {
                                                        try {
                                                            $salesheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file);
                                                        } catch (\PhpOffice\PhpSpreadsheet\Reader\Exception $e) {
                                                            $output = $this->sl->tools->error("Некорректный файл для загрузки!");
                                                        }
                                                        $saleData = $salesheet->getActiveSheet()->toArray(null, true, true, true);
                                                        $group_id = 3;
                                                        $query = $this->modx->newQuery("slStoresRemains");
                                                        $query->where(array(
                                                            "FIND_IN_SET({$group_id}, groups) > 0",
                                                            "slStoresRemains.store_id:=" => $store_id
                                                        ));
                                                        $query->select(array("slStoresRemains.*"));
                                                        if ($query->prepare() && $query->stmt->execute()) {
                                                            $remains = $query->stmt->fetchAll(PDO::FETCH_ASSOC);
                                                            foreach ($remains as $remain) {
                                                                foreach ($saleData as $key => $value) {
                                                                    if($remain['article'] == $value["A"]){
                                                                        $remain["new_price"] = $value["B"];
                                                                        $remain["old_price"] = $remain["price"];
                                                                        $criteria = array(
                                                                            "action_id:=" => $client_data["action"]["id"],
                                                                            "remain_id:=" => $remain["id"]
                                                                        );
                                                                        $product = $this->modx->getObject("slActionsProducts",$criteria);
                                                                        if(!$product){
                                                                            $product = $this->modx->newObject("slActionsProducts");
                                                                        }
                                                                        $product->set("action_id", $client_data["action"]["id"]);
                                                                        $product->set("old_price", $remain["old_price"]);
                                                                        $product->set("new_price", $remain["new_price"]);
                                                                        $product->set("active", 1);
                                                                        $product->set("createdon", time());
                                                                        $product->set("multiplicity", 1);
                                                                        $product->set("remain_id", $remain["id"]);
                                                                        $product->set("min_count", 1);
                                                                        $product->save();
                                                                        // $client_data["action"]["products"][] = $product->toArray();
                                                                    }
                                                                }
                                                            }
                                                        }
                                                    }
                                                    $out['errors']['data'][] = $client_data;
                                                } else {
                                                    $out['errors']['data'][] = $value;
                                                }
                                            } else {
                                                $out['errors']['data'][] = $value;
                                            }
                                        } else {
                                            $out['errors']['data'][] = $value;
                                        }
                                    } else {
                                        $out['errors']['data'][] = $value;
                                    }
                                } else {
                                    $out['errors']['data'][] = $value;
                                }
                            }
                        //}
                    }
                }
                $out['success']['total'] = count($out['success']['data']);
                $out['errors']['total'] = count($out['errors']['data']);
                $output = $this->sl->tools->success("Файл обработан", $out);
            }else{
                $output = $this->sl->tools->error("Файл не найден", $file_in);
            }
        }else {
            $output = $this->sl->tools->error("Организация не найдена");
        }
        return $output;
    }

    /**
     * Закидываем пакетно организации
     *
     * @param $owner_id
     * @param $file
     * @return mixed
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public function parseOrganizations($owner_id, $file){
        $output = array();
        $owner = $this->modx->getObject("slOrg", $owner_id);
        if($owner) {
            $org_id = $owner->get("id");
            $out = array(
                "total" => 0,
                "success" => array(
                    "total" => 0,
                    "data" => array()
                ),
                "errors" => array(
                    "total" => 0,
                    "data" => array()
                )
            );
            $file_in = $this->modx->getOption("base_path").$file;
            if(file_exists($file_in)) {
                try {
                    $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file_in);
                } catch (\PhpOffice\PhpSpreadsheet\Reader\Exception $e) {
                    $output = $this->sl->tools->error("Некорректный файл для загрузки!");
                }
                $sheetData = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);
                $out["total"] = count($sheetData);
                foreach($sheetData as $key => $value) {
                    // пропускаем шапку
                    if ($key != 1) {
                        // Нам важны контакты, поэтому проверяем есть ли они
                        if($value['F'] || $value['G']){
                            // Собираем данные по организации
                            $properties = array(
                                "id" => $owner_id,
                                "client_id" => 0
                            );
                            $properties["name"] = trim($value['A']);
                            $properties['data']['org']['name']["value"] = trim($value['A']);
                            $properties['data']['org']['inn'] = trim($value['C']);
                            $properties['data']['upload_image'] = false;
                            $properties['data']['contact'] = trim($value['E']);
                            if($value['F']){
                                $properties['data']['phone'] = $this->sl->tools->phoneFormat(trim($value['F']));
                            }
                            $properties['data']['email'] = trim($value['G']);
                            $warehouses = explode("||", trim($value['D']));
                            foreach($warehouses as $warehouse){
                                $properties['data']['org']["warehouses"][] = array(
                                    "address" => array(
                                        "id" => 0,
                                        "value" => trim($warehouse)
                                    )
                                );
                            }
                            $out['success']['data'][] = $properties;
                            // Сохраняем
                            $data = $this->sl->orgHandler->setOrgVirtualProfile($properties, 1);
                            $org = false;
                            if($data["data"]["organization"]["id"]){
                                $org = $this->modx->getObject("slOrg", $data["data"]["organization"]["id"]);
                            }
                            // Закидываем в B24
                            $card_id = 0;
                            $organization_id = 0;
                            $res = $this->sl->b24->checkRequizites($properties['data']['org']['inn']);
                            if($res["result"]){
                                $organization_id = $res["result"][0]["ENTITY_ID"];
                                if($organization_id){
                                    $criteria = array(
                                        "entityTypeId" => 1034,                 // Это наш бизнес процесс
                                        "filter" => array(
                                            "companyId" => $organization_id
                                        )
                                    );
                                    $res = $this->sl->b24->checkCard($criteria);
                                    if($res["total"] > 0){
                                        $card_id = $res["result"]["items"][0]["id"];
                                    }
                                }
                            }
                            if($card_id){
                                // 1. Меняем стадию
                                $res = $this->sl->b24->updateCard(1034, $card_id, array("stageId" => "DT1034_89:NEW"));
                            }else{
                                // 2. Создаем заново объекты и связываем
                                $organization_data = array();
                                $name_data = array();
                                // Клининг параметров
                                if (!class_exists('Dadata')) {
                                    require_once dirname(__FILE__) . '/dadata.class.php';
                                }

                                $token = $this->modx->getOption('shoplogistic_api_key_dadata');
                                $secret = $this->modx->getOption('shoplogistic_secret_key_dadata');
                                $dadata = new Dadata($token, $secret);
                                $dadata->init();
                                // клиним имя
                                $result = $dadata->clean("name", $properties['data']['contact']);
                                if($result){
                                    $name_data = $result[0];
                                }
                                $companyData["TITLE"] = $properties['data']['org']['name']["value"];
                                if($name_data){
                                    $legalData = array(
                                        "NAME" => $name_data["name"],
                                        "SECOND_NAME" => $name_data["patronymic"],
                                        "LAST_NAME" => $name_data["surname"],
                                        "phone" => $properties['data']['phone'],
                                        "email" => $properties['data']['email'],
                                        "ASSIGNED_BY_ID" => 55
                                    );
                                }else{
                                    $legalData = array(
                                        "NAME" => $properties['data']['contact'],
                                        "phone" => $properties['data']['phone'],
                                        "email" => $properties['data']['email'],
                                        "ASSIGNED_BY_ID" => 55
                                    );
                                }
                                // Если организация не найдена
                                // Собираем данные
                                $companyData = array(
                                    "CONTACT" => array(),
                                    "COMPANY_TYPE" => "OTHER",
                                    "TITLE" => $properties['data']['org']['name']["value"]
                                );
                                if(!$organization_id){
                                    $result = $dadata->getOrganization($properties['data']['org']['inn']);
                                    if($result["suggestions"]) {
                                        $organization_data = $result["suggestions"][0];
                                    }
                                    if($organization_data){
                                        $companyData["TITLE"] = $organization_data["value"];
                                    }else{
                                        $companyData["TITLE"] = $properties['data']['org']['name']["value"];
                                    }
                                    $lpr = $this->sl->b24->addContact($legalData);
                                    $companyData["CONTACT"][] = $lpr;
                                    $organization_id = $this->sl->b24->addCompany($companyData);
                                }else{
                                    $lpr = $this->sl->b24->addContact($legalData);
                                    $companyData["CONTACT"][] = $lpr;
                                }
                                if($organization_id){
                                    if($org){
                                        $org->set("bitrix_id", $organization_id);
                                        $org->save();
                                    }
                                }
                                if($organization_data){
                                    // Цепляем реквизиты
                                    $requiziteData = array(
                                        "NAME" => $companyData["TITLE"],
                                        "RQ_INN" => $organization_data["data"]["inn"],
                                        "RQ_KPP" => $organization_data["data"]["kpp"],
                                        "RQ_EMAIL" => $properties['data']['email'],
                                        "RQ_PHONE" => $properties['data']['phone'],
                                        'ENTITY_TYPE_ID' => 4,
                                        "ENTITY_ID" => $organization_id,
                                        "PRESET_ID" => 1,
                                        'ACTIVE' => 'Y',
                                    );
                                    if(strlen(trim($organization_data["data"]["ogrn"])) == 13){
                                        $requiziteData["RQ_OGRN"] = $organization_data["data"]["ogrn"];
                                        $requiziteData["UF_CRM_1718187136"] = $properties['data']['email'];
                                        $requiziteData["UF_CRM_1718187151"] = $properties['data']['phone'];
                                        $requiziteData["PRESET_ID"] = 1;
                                    }
                                    if(strlen(trim($organization_data["data"]["ogrn"])) == 15){
                                        $requiziteData["RQ_OGRNIP"] = $organization_data["data"]["ogrn"];
                                        $requiziteData["UF_CRM_1718187196"] = $properties['data']['email'];
                                        $requiziteData["UF_CRM_1718187208"] = $properties['data']['phone'];
                                        $requiziteData["PRESET_ID"] = 3;
                                    }
                                    $req = $this->sl->b24->addRequizite($requiziteData);
                                    if($properties['delivery_addresses'][0]["value"]){
                                        $addressData = array(
                                            'fields'=>array(
                                                'TYPE_ID' => 1,
                                                'ENTITY_TYPE_ID' => 8,
                                                'ENTITY_ID' => $req,
                                                'ADDRESS_1' => $properties['data']['org']["warehouses"][0]['address']["value"]
                                            ),
                                        );
                                        $address_actual = $this->sl->b24->request('crm.address.add', $addressData);
                                    }
                                }
                                // СПО ЕКБ - 2079, МСТ - 1285
                                $cardData = array(
                                    "entityTypeId" => 1034,
                                    "fields" => array(
                                        "title" => $companyData["TITLE"],
                                        "categoryId" => 89,
                                        "stageId" => "DT1034_89:NEW",
                                        "assignedById" => 55,
                                        "companyId" => $organization_id,
                                        "contactIds" => $companyData["CONTACT"],
                                        "ufCrm29_1718046509" => $lpr,
                                        "ufCrm29_1723628130779" => $companyData["TITLE"],
                                        "ufCrm11_1690930757286" => 1959,
                                        "ufCrm29_1723628107335" => $properties['data']['org']["warehouses"][0]['address']["value"],
                                        "ufCrm29_1723628206207" => $properties['data']['contact'],
                                        "ufCrm29_1723627823491" => $properties['data']['phone'],
                                        "ufCrm29_1723627869858" => $properties['data']['email'],
                                        "ufCrm29_1723628150363" => $properties['data']['org']['inn'],
                                        'ufCrm11_1686253198139' => 1285
                                    )
                                );
                                if($properties['data']['org']["warehouses"][0]['address']["value"]) {
                                    if (!class_exists('Dadata')) {
                                        require_once dirname(__FILE__) . '/dadata.class.php';
                                    }
                                    $token = $this->modx->getOption('shoplogistic_api_key_dadata');
                                    $secret = $this->modx->getOption('shoplogistic_secret_key_dadata');
                                    $dadata = new Dadata($token, $secret);
                                    $dadata->init();
                                    $res = $dadata->clean('address', $properties['data']['org']["warehouses"][0]['address']["value"]);
                                    if(isset($res[0])){
                                        $cardData['fields']['ufCrm29_1723627886050'] = $res[0]["city"] ? $res[0]["city"] : $res[0]["settlement"];
                                    }

                                }
                                $card = $this->sl->b24->addCard($cardData);
                            }
                        }else{
                            $out['errors']['data'][] = $properties;
                        }
                    }
                }
                $out['success']['total'] = count($out['success']['data']);
                $out['errors']['total'] = count($out['success']['data']);
                $output = $this->sl->tools->success("Файл обработан", $out);
            }else{
                $output = $this->sl->tools->error("Файл не найден", $file_in);
            }
        }else {
            $output = $this->sl->tools->error("Организация не найдена");
        }
        return $output;
    }

    /**
     * Парсим файл групп товаров
     *
     * @param $group_id
     * @param $file
     * @return void
     */
    public function parseProductGroupsFile ($group_id, $file) {
        $output = array();
        $group = $this->modx->getObject("slStoresRemainsGroups", $group_id);
        if($group){
            $store_id = $group->get("store_id");
            $out = array(
                "total" => 0,
                "success" => array(
                    "total" => 0,
                    "data" => array()
                ),
                "errors" => array(
                    "total" => 0,
                    "data" => array()
                )
            );
            $file_in = $this->modx->getOption("base_path").$file;
            if(file_exists($file_in)) {
                try {
                    $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file_in);
                } catch (\PhpOffice\PhpSpreadsheet\Reader\Exception $e) {
                    $output = $this->sl->tools->error("Некорректный файл для загрузки!");
                }
                // Обнуление категорий товаров
                $query = $this->modx->newQuery("slStoresRemains");
                $query->where(array(
                    "FIND_IN_SET({$group_id}, groups) > 0",
                    "slStoresRemains.store_id:=" => $store_id
                ));
                $query->select(array("slStoresRemains.id"));
                if($query->prepare() && $query->stmt->execute()){
                    $remains = $query->stmt->fetchAll(PDO::FETCH_ASSOC);
                    foreach($remains as $remain){
                        $object = $this->modx->getObject("slStoresRemains", $remain["id"]);
                        if($object){
                            $groups = $object->get("groups");
                            $groups = explode(",", $groups);
                            foreach($groups as $k => $g){
                                if($g == $group_id){
                                    unset($groups[$k]);
                                }
                            }
                            $object->set("groups", implode(",", $groups));
                            $object->save();
                        }
                    }
                }
                $sheetData = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);
                $out["total"] = count($sheetData);
                foreach($sheetData as $key => $value) {
                    // пропускаем шапку
                    // if ($key != 1) {
                        $criteria = array(
                            "store_id:=" => $store_id,
                            "article:=" => trim($value["A"])
                        );
                        $query = $this->modx->newQuery("slStoresRemains");
                        $query->where($criteria);
                        $query->select(array(
                            "slStoresRemains.*"
                        ));
                        $count = $this->modx->getCount("slStoresRemains", $query);
                        if(!$count){
                            $out['errors'][] = $this->sl->tools->error("Остаток не найден!", $value);
                        }else{
                            if($count == 1){
                                if($query->prepare() && $query->stmt->execute()) {
                                    $remain = $query->stmt->fetch(PDO::FETCH_ASSOC);
                                    if($remain){
                                        $object = $this->modx->getObject("slStoresRemains", $remain["id"]);
                                        if($object){
                                            $groups = $object->get("groups");
                                            $groups = explode(",", $groups);
                                            $groups[] = $group_id;
                                            $object->set("groups", implode(",", $groups));
                                            if($object->save()){
                                                $out['success']['data'][] = $this->sl->tools->error("Объект остатка обновлен!", $value);
                                            }else{
                                                $out['errors']['data'][] = $this->sl->tools->error("Объект остатка не обновлен, ошибка!", $value);
                                            }
                                        }else{
                                            $out['errors']['data'][] = $this->sl->tools->error("Объект остатка не найден!", $value);
                                        }
                                    }else{
                                        $out['errors']['data'][] = $this->sl->tools->error("Остаток не найден!", $value);
                                    }
                                }
                            }else{
                                $out['errors']['data'][] = $this->sl->tools->error("Найдено больше 1 товара с аналогичным артикулом!", $value);
                            }
                        }
                    // }
                }
                $out['success']['total'] = count($out['success']['data']);
                $out['errors']['total'] = count($out['errors']['data']);
                $output = $this->sl->tools->success("Файл обработан", $out);
            }else{
                $output = $this->sl->tools->error("Файл не найден", $file_in);
            }
        }else{
            $output = $this->sl->tools->error("Группа не найдена");
        }
        return $output;
    }

    /**
     * Читаем файл товаров для акций
     *
     * @param $file
     * @return Worksheet
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
     */
    public function processActionFile ($store_id, $file, $type) {
        $file_in = $this->modx->getOption("base_path").$file;
        if(file_exists($file_in)){
            try {
                $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file_in);
            } catch (\PhpOffice\PhpSpreadsheet\Reader\Exception $e) {
                $output = $this->sl->tools->error("Некорректный файл для загрузки!");
            }
            $sheetData = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);
            // Структура по столбцам

            //b2b
            // А - артикул, B - бренд, C - Наименование, D - Цена, E - Старая цена, F - Кратность, G -  GUID

            //b2c
            // А - артикул, B - бренд, C - Наименование, D - Цена, E - Старая цена, F - GUID


            // Также по умолчанию игнорируем первую строку
            foreach($sheetData as $key => $value){
                if($key != 1){
                    // сначала чекаем GIUD
                    if($type == 'b2b'){
                        if(isset($value["G"])){
                            $criteria = array(
                                "store_id:IN" => $store_id,
                                "giud:=" => $value["G"]
                            );
                        }else{
                            $criteria = array(
                                "store_id:IN" => $store_id,
                                "article:=" => $value["A"]
                            );
                        }
                    } else if($type == 'b2c') {
                        if(isset($value["F"])){
                            $criteria = array(
                                "store_id:IN" => $store_id,
                                "giud:=" => $value["F"]
                            );
                        }else{
                            $criteria = array(
                                "store_id:IN" => $store_id,
                                "article:=" => $value["A"]
                            );
                        }
                    }

                    $query = $this->modx->newQuery("slStoresRemains");
                    $query->leftJoin("msProductData", "msProductData", "msProductData.id = slStoresRemains.product_id");
                    $query->where($criteria);
                    $query->select(array(
                        "slStoresRemains.*",
                        'COALESCE(msProductData.image, "/assets/files/img/nopic.png") as image'
                    ));
                    if($query->prepare() && $query->stmt->execute()){
                        $remain = $query->stmt->fetch(PDO::FETCH_ASSOC);

                        if($remain){

                            $urlMain = $this->modx->getOption("site_url");
                            $remain['image'] = $urlMain . $remain['image'];

                            $sheetData[$key]["remain"] = $remain;
                            if(!isset($value["E"])){
                                if($remain['price'] > 0){
                                    $sheetData[$key]["E"] = $remain['price'];
                                }
                            }
                            if($type == 'b2b') {
                                if(!isset($value["F"])){
                                    $sheetData[$key]["F"] = 1;
                                }
                            }
                        }else{
                            $sheetData[$key]["error"] = array(
                                "message" => "Остаток не найден"
                            );
                        }
                    }
                }
            }
            $output = $this->sl->tools->success("Операция обработана.", $sheetData);
        }else{
            $output = $this->sl->tools->error("Файла не существует!");
        }
        return $output;
    }


	public function generateTest(){
		$spreadsheet = new Spreadsheet();
		$sheet = $spreadsheet->getActiveSheet();


		// тестовые данные: номер ячейки - строка данных
		$data =  [
			'B1' => 'Hello, PhpSpredsheet!',
			'B2' => 'Hello, Myrusakov!',
			'B3' => 'Open please, this message'
		];


		foreach($data as $cell => $value)
		{
			// заполняем ячейки листа значениями
			$sheet->setCellValue($cell, $value);
		}


		// пишем файл в формат Excel
		$writer = new Xlsx($spreadsheet);
		$path = 'assets/files/stores/reports/test/';
		$file = $path.'report.xlsx';
		$file_path = $this->modx->getOption('base_path').$file;
		if (!file_exists($this->modx->getOption('base_path').$path)) {
			mkdir($this->modx->getOption('base_path').$path, 0777, true);
		}
		$writer->save($file_path);
		return $file;
	}

    public function generateOptOrder($data, $properties){

        if($properties['store_id'] && $properties['warehouse_id']){
            $basket = $data['basket'][$properties['warehouse_id']];

            $spreadsheet = new Spreadsheet();
            $myWorkSheet = new Worksheet($spreadsheet, mb_strimwidth($basket['stores'][$properties['store_id']]['name_short'], 0, 29));
            $spreadsheet->addSheet($myWorkSheet);
            $spreadsheet->removeSheetByIndex(0);
            $spreadsheet->setActiveSheetIndex(0);
            $sheet = $spreadsheet->getActiveSheet();

            $sheet->getColumnDimension('A')->setWidth(22);
            $sheet->getColumnDimension('B')->setWidth(24);
            $sheet->getColumnDimension('C')->setWidth(20);
            $sheet->getColumnDimension('D')->setWidth(20);
            $sheet->getColumnDimension('E')->setWidth(20);
            $sheet->getColumnDimension('F')->setWidth(11);
            $sheet->getColumnDimension('G')->setWidth(18);
            $sheet->getColumnDimension('H')->setWidth(15);
            $sheet->getColumnDimension('I')->setWidth(15);
            $sheet->getColumnDimension('J')->setWidth(15);
            $sheet->getColumnDimension('K')->setWidth(15);
            $sheet->getColumnDimension('L')->setWidth(50);
            $sheet->getColumnDimension('M')->setWidth(50);
            $sheet->getStyle('A1:G999')->getAlignment()->setVertical('center');

            $sheet->getCell('A1')->setValue('Заказы от ');

            $sheet->mergeCells("B1:C1");
            $sheet->getStyle('A1:C1')->getFont()->setSize(24);
            $sheet->getStyle('A1:C1')->getFont()->setBold(true);
            $sheet->getCell('B1')->setValue(date('d.m.Y'));

            $sheet->getCell('A2')->setValue('Поставщик:');
            $sheet->getCell('A3')->setValue('Склад поставщика:');
            $sheet->getCell('A4')->setValue('Оплата доставки:');
            $sheet->getCell('A5')->setValue('Срок доставки:');
            $sheet->getCell('A6')->setValue('Адрес доставки:');

//            $sheet->getCell('E2')->setValue('Отсрочка платежа:');
//            $sheet->getCell('E3')->setValue('График платежей:');

            $sheet->getStyle('E2:E3')->getFont()->setSize(10);
            $sheet->getStyle('E2:E3')->getFont()->setBold(true);
            $sheet->getStyle('E2:E3')->getAlignment()->setHorizontal('right');

            $sheet->getStyle('A2:A6')->getFont()->setSize(10);
            $sheet->getStyle('A2:A6')->getFont()->setBold(true);
            $sheet->getStyle('A2:A6')->getAlignment()->setHorizontal('right');

            for($i = 2; $i <= 6; $i++){
                $sheet->getRowDimension($i)->setRowHeight(27);
            }

            //Отсрочка


            //СБОР ДАННЫХ
            //TODO: Не учитывается если несколько складов привязано к одной организации
            $q = $this->modx->newQuery("slOrgStores");
            $q->leftJoin("slOrg", "slOrg", "slOrg.id = slOrgStores.org_id");
            $q->where(array(
                "`slOrgStores`.`store_id`:=" => $basket['stores'][$properties['store_id']]['id'],
            ));
            $q->select(array(
                "`slOrg`.*"
            ));

            if($q->prepare() && $q->stmt->execute()){
                $org = $q->stmt->fetch(PDO::FETCH_ASSOC);
                $query = $this->modx->newQuery("slOrgRequisites");
                $query->where(array("slOrgRequisites.org_id:=" => $org["id"]));
                $query->select(array("slOrgRequisites.*"));
                if($query->prepare() && $query->stmt->execute()){
                    $org['req'] = $query->stmt->fetch(PDO::FETCH_ASSOC);
                }
                if($org['req']){
                    $org['inn'] = $org['req']['inn'];
                }
                $sheet->getCell('B2')->setValue($org['name'] . ", ИНН: " . $org['inn']);
            }

            //Склад
            $sheet->getCell('B3')->setValue("«".$basket['stores'][$properties['store_id']]['name_short'] . "», " . $basket['stores'][$properties['store_id']]['address_short']);

            //Оплата доставки
            $payer = 0;
            foreach($basket['stores'][$properties['store_id']]['products'] as $product){
                if($product['payer'] == 1){
                    $payer = 1;
                    break;
                }
            }
            $sheet->getCell('B4')->setValue($payer == 1 ? "Поставщик" : "Покупатель");

            //Срок доставки
            if($basket['stores'][$properties['store_id']]['products']){
                $product_first = array_key_first($basket['stores'][$properties['store_id']]['products']);
                $delivery = $this->sl->cart->getNearShipment($basket['stores'][$properties['store_id']]['products'][$product_first]["store_id"], $properties['store_id']);

                $date_delivery = date("d.m.Y", time()+60*60*24* $delivery);

                $sheet->getCell('B5')->setValue($date_delivery);
                $sheet->getCell('C5')->setValue($delivery . " дней");
                $sheet->getStyle('C5')->getFont()->setItalic(true);
            } else if($basket['stores'][$properties['store_id']]['complects']) {
                $this->modx->log(1, print_r($basket['stores'][$properties['store_id']]['complects'], 1));
                $this->modx->log(1, "KENOST xslx");
                //TODO КОПЛЕКТЫ
                $product_first = array_key_first($basket['stores'][$properties['store_id']]['complects']);
//
                $delivery = $this->sl->cart->getNearShipment($basket['stores'][$properties['store_id']]['complects'][$product_first]['products'][0]['remain_id'], $basket['stores'][$properties['store_id']]['products'][$product_first]['products'][0]["store_id"]);
//
                $date_delivery = date("d.m.Y", time()+60*60*24* $delivery);
//
                $sheet->getCell('B5')->setValue($date_delivery);
                $sheet->getCell('C5')->setValue($delivery . " дней");
                $sheet->getStyle('C5')->getFont()->setItalic(true);
            }

            //Получаем склад доставки
            $warehouse = array();
            foreach ($data['warehouses'] as $warehous){
                if($warehous['id'] == $basket['stores'][$properties['store_id']]['id_warehouse']) {
                    $warehouse = $warehous;
                    break;
                }
            }
            $sheet->getCell('B6')->setValue($warehouse['name']);

            $sheet->getCell('A8')->setValue('№');
            $sheet->getCell('B8')->setValue('Артикул');
            $sheet->getCell('C8')->setValue('Наименование');
            $sheet->getCell('H8')->setValue('Количество');
            $sheet->getCell('I8')->setValue('Цена');
            $sheet->getCell('J8')->setValue('Сумма');
            $sheet->getCell('K8')->setValue('Отсрочка (дн.)');
            $sheet->getCell('L8')->setValue('Условия акции');
            $sheet->getCell('M8')->setValue('Название акции');

            $sheet->getCell('L7')->setValue('Акции');
            $sheet->getStyle('L7')->getFont()->setSize(24);
            $sheet->getStyle('L7')->getFont()->setBold(true);
            $sheet->getStyle('L7')->getFont()->getColor(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->setARGB('808080');
            $sheet->getStyle('L7')->getAlignment()->setHorizontal('center');

            $sheet->getStyle('L8:M8')->getFont()->getColor(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->setARGB('808080');
            $sheet->mergeCells("C8:G8");
            $sheet->getStyle('A8:M8')->getFont()->setSize(12);
            $sheet->getStyle('A8:M8')->getFont()->setBold(true);
            $sheet->getStyle('A8:M8')->getAlignment()->setHorizontal('center');

            $sheet->getRowDimension(7)->setRowHeight(32);

            $num = 1;
            $start_position = 9;
            foreach($basket['stores'][$properties['store_id']]['products'] as $key => $items){
//                $this->modx->log(1, print_r($items, 1));
//                $this->modx->log(1, "KENOST xslx");
                foreach($items['basket'] as $k => $product){
                    $sheet->insertNewRowBefore($start_position);
                    $sheet->setCellValueExplicit('A'.$start_position, $num, PHPExcel_Cell_DataType::TYPE_STRING);
                    $sheet->setCellValueExplicit('B'.$start_position, $items['article'], PHPExcel_Cell_DataType::TYPE_STRING);
                    $sheet->setCellValueExplicit('C'.$start_position, $items['name'], PHPExcel_Cell_DataType::TYPE_STRING);
                    $sheet->mergeCells('C'.$start_position.":".'G'.$start_position);
                    $sheet->getStyle('C'.$start_position.":".'G'.$start_position)->getAlignment()->setHorizontal('left');
                    $sheet->getCell('H'.$start_position)->setValue($product['count']);
                    $sheet->getCell('I'.$start_position)->setValue($product['price']);
                    $sheet->getCell('J'.$start_position)->setValue($product['cost']);
                    $delay = "Предоплата";
                    if($product['delay']){
                        $delay = $product['delay'];
                    }
                    $sheet->getCell('K'.$start_position)->setValue($delay);

                    $sheet->getStyle( 'A'.$start_position.":".'K'.$start_position )->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
                    $sheet->getRowDimension($start_position)->setRowHeight(25);
                    $sheet->getStyle('A'.$start_position.":".'K'.$start_position)->getFont()->setBold(false);

                    //Получаем акции

                    if($product['tags']){
                        $tagsText = "";
                        foreach($product['tags'] as $action) {
                            foreach ($action as $tag) {
                                if($tag['type'] == 'sale'){
                                    $tagsText = $tagsText . "Скидка " . $tag['value'] . "%";
                                    if($tag['min_count'] > 1){
                                        $tagsText = $tagsText . " при покупке от " . $tag['min_count'] . 'шт';
                                    }
                                    $tagsText = $tagsText . ", ";

                                }

                                if($tag['type'] == 'min_sum'){
                                    $tagsText = $tagsText . "Минимальна сумма покупки " . $tag['value'] . "₽";
                                    $tagsText = $tagsText . ", ";
                                }

                                if($tag['type'] == 'min_sum'){
                                    $tagsText = $tagsText . "Минимальна сумма покупки " . $tag['value'] . "₽";
                                    $tagsText = $tagsText . ", ";
                                }

                                if($tag['type'] == 'free_delivery'){
                                    $tagsText = $tagsText . "Бесплатная доставка";
                                    if($tag['condition'] == '2'){
                                        $tagsText = $tagsText . " при покупке от " . $tag['value'] . '₽';
                                    }
                                    if($tag['condition'] == '3'){
                                        $tagsText = $tagsText . " при покупке от " . $tag['value'] . 'шт';
                                    }
                                    $tagsText = $tagsText . ", ";
                                }

                                if($tag['type'] == 'gift'){
                                    $tagsText = $tagsText . " Подарок";
                                    $tagsText = $tagsText . ", ";
                                }

                                if($tag['type'] == 'multiplicity'){
                                    $tagsText = $tagsText . "Кратность упаковки" . $tag['value'] . 'шт';
                                    $tagsText = $tagsText . ", ";
                                }
                            }
                            $tagsText = substr($tagsText,0,-2);
                            $tagsText = $tagsText . " \n";
                        }
                        $sheet->getCell('L'.$start_position)->setValue($tagsText);
                        $sheet->getStyle('L'.$start_position)->getAlignment()->setVertical('center');
                        $sheet->getStyle('L'.$start_position)->getAlignment()->setHorizontal('left');
                        $sheet->getStyle('L'.$start_position)->getFont()->setBold(false);
                        $sheet->getStyle('L'.$start_position)->getFont()->setSize(10);
                        $sheet->getStyle('L'.$start_position)->getFont()->getColor(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->setARGB('808080');
                    }

                    $tagsTextName = "";
                    if($product['actions_ids']){
                        foreach($product['actions_ids'] as $action_id){
                            $q = $this->modx->newQuery("slActions");
                            $q->where(array(
                                "`slActions`.`id`:=" => $action_id,
                            ));
                            $q->select(array(
                                "`slActions`.name"
                            ));

                            if($q->prepare() && $q->stmt->execute()){
                                $action = $q->stmt->fetch(PDO::FETCH_ASSOC);
                                if($tagsTextName == ""){
                                    $tagsTextName = $action['name'];
                                } else {
                                    $tagsTextName = $tagsTextName . " \n" . $action['name'];
                                }
                            }
                        }
                        $sheet->getCell('M'.$start_position)->setValue($tagsTextName);
                        $sheet->getStyle('M'.$start_position)->getAlignment()->setVertical('center');
                        $sheet->getStyle('M'.$start_position)->getAlignment()->setHorizontal('left');
                        $sheet->getStyle('M'.$start_position)->getFont()->setBold(false);
                        $sheet->getStyle('M'.$start_position)->getFont()->setSize(10);
                        $sheet->getStyle('M'.$start_position)->getFont()->getColor(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->setARGB('808080');
                    }

                    $start_position++;
                    $num++;
                }
            }
            foreach($basket['stores'][$properties['store_id']]['complects'] as $key => $items){
//                $this->modx->log(1, print_r($items, 1));
//                $this->modx->log(1, "KENOST xslx");
                foreach($items['products'] as $k => $product){
                    $sheet->insertNewRowBefore($start_position);
                    $sheet->setCellValueExplicit('A'.$start_position, $num, PHPExcel_Cell_DataType::TYPE_STRING);
                    $sheet->setCellValueExplicit('B'.$start_position, $product['article'], PHPExcel_Cell_DataType::TYPE_STRING);
                    $sheet->setCellValueExplicit('C'.$start_position, $product['name'], PHPExcel_Cell_DataType::TYPE_STRING);
                    $sheet->mergeCells('C'.$start_position.":".'G'.$start_position);
                    $sheet->getStyle('C'.$start_position.":".'G'.$start_position)->getAlignment()->setHorizontal('left');
                    $sheet->getCell('H'.$start_position)->setValue($product['info']['count']);
                    $sheet->getCell('I'.$start_position)->setValue($product['info']['price']);
                    $sheet->getCell('J'.$start_position)->setValue($product['info']['count'] * $product['info']['price']);

                    $delay = "Предоплата";
                    if($product['delay']){
                        $delay = $product['delay'];
                    }
                    $sheet->getCell('K'.$start_position)->setValue($delay);

                    $sheet->getStyle( 'A'.$start_position.":".'K'.$start_position )->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
                    $sheet->getRowDimension($start_position)->setRowHeight(25);
                    $sheet->getStyle('A'.$start_position.":".'K'.$start_position)->getFont()->setBold(false);

                    $start_position++;
                    $num++;
                }
            }
            $sheet->mergeCells('A'.$start_position.":".'I'.$start_position);
            $sheet->getStyle('A'.$start_position.":".'I'.$start_position)->getAlignment()->setHorizontal('right');
            $sheet->getCell('A'.$start_position)->setValue("Итого:");
            $sheet->getCell('J'.$start_position)->setValue($basket['stores'][$properties['store_id']]['cost']);
            $sheet->getRowDimension($start_position)->setRowHeight(25);
            $sheet->getStyle('A'.$start_position.":".'K'.$start_position)->getFont()->setBold(true);
            $sheet->getStyle('A'.$start_position.":".'K'.$start_position)->getFont()->setSize(14);

        } else {
            $this->modx->log(1, print_r($data, 1));
            $this->modx->log(1, "KENOST DaTa");
            $spreadsheet = new Spreadsheet();
            $spreadsheet->removeSheetByIndex(0);

            $index_active = 0;
            foreach ($data['basket'] as $k => $warehouse){
                foreach ($warehouse['stores'] as $key => $store) {
                    $myWorkSheet = new Worksheet($spreadsheet, mb_strimwidth($store['name_short'], 0, 29));
                    $spreadsheet->addSheet($myWorkSheet);
                    $spreadsheet->setActiveSheetIndex($index_active);
                    $sheet = $spreadsheet->getActiveSheet();

                    $sheet->getColumnDimension('A')->setWidth(22);
                    $sheet->getColumnDimension('B')->setWidth(24);
                    $sheet->getColumnDimension('C')->setWidth(20);
                    $sheet->getColumnDimension('D')->setWidth(20);
                    $sheet->getColumnDimension('E')->setWidth(20);
                    $sheet->getColumnDimension('F')->setWidth(11);
                    $sheet->getColumnDimension('G')->setWidth(18);
                    $sheet->getColumnDimension('H')->setWidth(15);
                    $sheet->getColumnDimension('I')->setWidth(15);
                    $sheet->getColumnDimension('J')->setWidth(15);
                    $sheet->getColumnDimension('K')->setWidth(15);
                    $sheet->getColumnDimension('L')->setWidth(50);
                    $sheet->getColumnDimension('M')->setWidth(50);
                    $sheet->getStyle('A1:G999')->getAlignment()->setVertical('center');

                    $sheet->getCell('A1')->setValue('Заказы от ');

                    $sheet->mergeCells("B1:C1");
                    $sheet->getStyle('A1:C1')->getFont()->setSize(24);
                    $sheet->getStyle('A1:C1')->getFont()->setBold(true);
                    $sheet->getCell('B1')->setValue(date('d.m.Y'));

                    $sheet->getCell('A2')->setValue('Поставщик:');
                    $sheet->getCell('A3')->setValue('Склад поставщика:');
                    $sheet->getCell('A4')->setValue('Оплата доставки:');
                    $sheet->getCell('A5')->setValue('Срок доставки:');
                    $sheet->getCell('A6')->setValue('Адрес доставки:');


                    $sheet->getStyle('E2:E3')->getFont()->setSize(10);
                    $sheet->getStyle('E2:E3')->getFont()->setBold(true);
                    $sheet->getStyle('E2:E3')->getAlignment()->setHorizontal('right');

                    $sheet->getStyle('A2:A6')->getFont()->setSize(10);
                    $sheet->getStyle('A2:A6')->getFont()->setBold(true);
                    $sheet->getStyle('A2:A6')->getAlignment()->setHorizontal('right');

                    for($i = 2; $i <= 6; $i++){
                        $sheet->getRowDimension($i)->setRowHeight(27);
                    }

                    //Отсрочка


                    //СБОР ДАННЫХ
                    //TODO: Не учитывается если несколько складов привязано к одной организации
                    $q = $this->modx->newQuery("slOrgStores");
                    $q->leftJoin("slOrg", "slOrg", "slOrg.id = slOrgStores.org_id");
                    $q->where(array(
                        "`slOrgStores`.`store_id`:=" => $store['id'],
                    ));
                    $q->select(array(
                        "`slOrg`.*"
                    ));

                    if($q->prepare() && $q->stmt->execute()){
                        $org = $q->stmt->fetch(PDO::FETCH_ASSOC);
                        $query = $this->modx->newQuery("slOrgRequisites");
                        $query->where(array("slOrgRequisites.org_id:=" => $org["id"]));
                        $query->select(array("slOrgRequisites.*"));
                        if($query->prepare() && $query->stmt->execute()){
                            $org['req'] = $query->stmt->fetch(PDO::FETCH_ASSOC);
                        }
                        if($org['req']){
                            $org['inn'] = $org['req']['inn'];
                        }
                        $sheet->getCell('B2')->setValue($org['name'] . ", ИНН: " . $org['inn']);
                    }

                    //Склад
                    $sheet->getCell('B3')->setValue("«".$store['name_short'] . "», " . $store['address_short']);

                    //Оплата доставки
                    $payer = 0;
                    foreach($store['products'] as $product){
                        if($product['payer'] == 1){
                            $payer = 1;
                            break;
                        }
                    }
                    $sheet->getCell('B4')->setValue($payer == 1 ? "Поставщик" : "Покупатель");

                    //Срок доставки
                    if($store['products']){
                        $product_first = array_key_first($store['products']);
                        $delivery = $this->sl->cart->getNearShipment($store['products'][$product_first]["store_id"], $store['id']);

                        $date_delivery = date("d.m.Y", time()+60*60*24* $delivery);

                        $sheet->getCell('B5')->setValue($date_delivery);
                        $sheet->getCell('C5')->setValue($delivery . " дней");
                        $sheet->getStyle('C5')->getFont()->setItalic(true);
                    } else if($store['complects']) {
                        $this->modx->log(1, print_r($store['complects'], 1));
                        $this->modx->log(1, "KENOST xslx");
                        //TODO КОПЛЕКТЫ
                        $product_first = array_key_first($store['complects']);
//
                        $delivery = $this->sl->cart->getNearShipment($store['products'][$product_first]['products'][0]["store_id"], $store['id']);
//
                        $date_delivery = date("d.m.Y", time()+60*60*24* $delivery);
//
                        $sheet->getCell('B5')->setValue($date_delivery);
                        $sheet->getCell('C5')->setValue($delivery . " дней");
                        $sheet->getStyle('C5')->getFont()->setItalic(true);
                    }

                    //Получаем склад доставки
                    $warehouse = array();
                    foreach ($data['warehouses'] as $warehous){
                        if($warehous['id'] == $store['id_warehouse']) {
                            $warehouse = $warehous;
                            break;
                        }
                    }
                    $sheet->getCell('B6')->setValue($warehouse['name']);

                    $sheet->getCell('A8')->setValue('№');
                    $sheet->getCell('B8')->setValue('Артикул');
                    $sheet->getCell('C8')->setValue('Наименование');
                    $sheet->getCell('H8')->setValue('Количество');
                    $sheet->getCell('I8')->setValue('Цена');
                    $sheet->getCell('J8')->setValue('Сумма');
                    $sheet->getCell('K8')->setValue('Отсрочка (дн.)');
                    $sheet->getCell('L8')->setValue('Условия акции');
                    $sheet->getCell('M8')->setValue('Название акции');

                    $sheet->getCell('L7')->setValue('Акции');
                    $sheet->getStyle('L7')->getFont()->setSize(24);
                    $sheet->getStyle('L7')->getFont()->setBold(true);
                    $sheet->getStyle('L7')->getFont()->getColor(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->setARGB('808080');
                    $sheet->getStyle('L7')->getAlignment()->setHorizontal('center');

                    $sheet->getStyle('L8:M8')->getFont()->getColor(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->setARGB('808080');
                    $sheet->mergeCells("C8:G8");
                    $sheet->getStyle('A8:M8')->getFont()->setSize(12);
                    $sheet->getStyle('A8:M8')->getFont()->setBold(true);
                    $sheet->getStyle('A8:M8')->getAlignment()->setHorizontal('center');

                    $sheet->getRowDimension(7)->setRowHeight(32);

                    $num = 1;
                    $start_position = 9;
                    foreach($store['products'] as $key => $items){
                        foreach($items['basket'] as $k => $product){
                            $sheet->insertNewRowBefore($start_position);
                            $sheet->setCellValueExplicit('A'.$start_position, $num, PHPExcel_Cell_DataType::TYPE_STRING);
                            $sheet->setCellValueExplicit('B'.$start_position, $items['article'], PHPExcel_Cell_DataType::TYPE_STRING);
                            $sheet->setCellValueExplicit('C'.$start_position, $items['name'], PHPExcel_Cell_DataType::TYPE_STRING);
                            $sheet->mergeCells('C'.$start_position.":".'G'.$start_position);
                            $sheet->getStyle('C'.$start_position.":".'G'.$start_position)->getAlignment()->setHorizontal('left');
                            $sheet->getCell('H'.$start_position)->setValue($product['count']);
                            $sheet->getCell('I'.$start_position)->setValue($product['price']);
                            $sheet->getCell('J'.$start_position)->setValue($product['cost']);
                            $delay = "Предоплата";
                            if($product['delay']){
                                $delay = $product['delay'];
                            }
                            $sheet->getCell('K'.$start_position)->setValue($delay);

                            $sheet->getStyle( 'A'.$start_position.":".'K'.$start_position )->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
                            $sheet->getRowDimension($start_position)->setRowHeight(25);
                            $sheet->getStyle('A'.$start_position.":".'K'.$start_position)->getFont()->setBold(false);

                            //Получаем акции

                            if($product['tags']){
                                $tagsText = "";
                                foreach($product['tags'] as $action) {
                                    foreach ($action as $tag) {
                                        if($tag['type'] == 'sale'){
                                            $tagsText = $tagsText . "Скидка " . $tag['value'] . "%";
                                            if($tag['min_count'] > 1){
                                                $tagsText = $tagsText . " при покупке от " . $tag['min_count'] . 'шт';
                                            }
                                            $tagsText = $tagsText . ", ";
                                        }

                                        if($tag['type'] == 'min_sum'){
                                            $tagsText = $tagsText . "Минимальна сумма покупки " . $tag['value'] . "₽";
                                            $tagsText = $tagsText . ", ";
                                        }

                                        if($tag['type'] == 'min_sum'){
                                            $tagsText = $tagsText . "Минимальна сумма покупки " . $tag['value'] . "₽";
                                            $tagsText = $tagsText . ", ";
                                        }

                                        if($tag['type'] == 'free_delivery'){
                                            $tagsText = $tagsText . "Бесплатная доставка";
                                            if($tag['condition'] == '2'){
                                                $tagsText = $tagsText . " при покупке от " . $tag['value'] . '₽';
                                            }
                                            if($tag['condition'] == '3'){
                                                $tagsText = $tagsText . " при покупке от " . $tag['value'] . 'шт';
                                            }
                                            $tagsText = $tagsText . ", ";
                                        }

                                        if($tag['type'] == 'gift'){
                                            $tagsText = $tagsText . " Подарок";
                                            $tagsText = $tagsText . ", ";
                                        }

                                        if($tag['type'] == 'multiplicity'){
                                            $tagsText = $tagsText . "Кратность упаковки" . $tag['value'] . 'шт';
                                            $tagsText = $tagsText . ", ";
                                        }
                                    }
                                    $tagsText = substr($tagsText,0,-2);
                                    $tagsText = $tagsText . " \n";
                                }
                                $sheet->getCell('L'.$start_position)->setValue($tagsText);
                                $sheet->getStyle('L'.$start_position)->getAlignment()->setVertical('center');
                                $sheet->getStyle('L'.$start_position)->getAlignment()->setHorizontal('left');
                                $sheet->getStyle('L'.$start_position)->getFont()->setBold(false);
                                $sheet->getStyle('L'.$start_position)->getFont()->setSize(10);
                                $sheet->getStyle('L'.$start_position)->getFont()->getColor(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->setARGB('808080');
                            }

                            $tagsTextName = "";
                            if($product['actions_ids']){
                                foreach($product['actions_ids'] as $action_id){
                                    $q = $this->modx->newQuery("slActions");
                                    $q->where(array(
                                        "`slActions`.`id`:=" => $action_id,
                                    ));
                                    $q->select(array(
                                        "`slActions`.name"
                                    ));

                                    if($q->prepare() && $q->stmt->execute()){
                                        $action = $q->stmt->fetch(PDO::FETCH_ASSOC);
                                        if($tagsTextName == ""){
                                            $tagsTextName = $action['name'];
                                        } else {
                                            $tagsTextName = $tagsTextName . " \n" . $action['name'];
                                        }
                                    }
                                }
                                $sheet->getCell('M'.$start_position)->setValue($tagsTextName);
                                $sheet->getStyle('M'.$start_position)->getAlignment()->setVertical('center');
                                $sheet->getStyle('M'.$start_position)->getAlignment()->setHorizontal('left');
                                $sheet->getStyle('M'.$start_position)->getFont()->setBold(false);
                                $sheet->getStyle('M'.$start_position)->getFont()->setSize(10);
                                $sheet->getStyle('M'.$start_position)->getFont()->getColor(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->setARGB('808080');
                            }

                            $start_position++;
                            $num++;
                        }
                    }
                    foreach($store['complects'] as $key => $items){
                        foreach($items['products'] as $k => $product){
                            $sheet->insertNewRowBefore($start_position);
                            $sheet->setCellValueExplicit('A'.$start_position, $num, PHPExcel_Cell_DataType::TYPE_STRING);
                            $sheet->setCellValueExplicit('B'.$start_position, $product['article'], PHPExcel_Cell_DataType::TYPE_STRING);
                            $sheet->setCellValueExplicit('C'.$start_position, $product['name'], PHPExcel_Cell_DataType::TYPE_STRING);
                            $sheet->mergeCells('C'.$start_position.":".'G'.$start_position);
                            $sheet->getStyle('C'.$start_position.":".'G'.$start_position)->getAlignment()->setHorizontal('left');
                            $sheet->getCell('H'.$start_position)->setValue($product['info']['count']);
                            $sheet->getCell('I'.$start_position)->setValue($product['info']['price']);
                            $sheet->getCell('J'.$start_position)->setValue($product['info']['count'] * $product['info']['price']);

                            $delay = "Предоплата";
                            if($product['delay']){
                                $delay = $product['delay'];
                            }
                            $sheet->getCell('K'.$start_position)->setValue($delay);

                            $sheet->getStyle( 'A'.$start_position.":".'K'.$start_position )->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
                            $sheet->getRowDimension($start_position)->setRowHeight(25);
                            $sheet->getStyle('A'.$start_position.":".'K'.$start_position)->getFont()->setBold(false);

                            $start_position++;
                            $num++;
                        }
                    }
                    $sheet->mergeCells('A'.$start_position.":".'I'.$start_position);
                    $sheet->getStyle('A'.$start_position.":".'I'.$start_position)->getAlignment()->setHorizontal('right');
                    $sheet->getCell('A'.$start_position)->setValue("Итого:");
                    $sheet->getCell('J'.$start_position)->setValue($store['cost']);
                    $sheet->getRowDimension($start_position)->setRowHeight(25);
                    $sheet->getStyle('A'.$start_position.":".'K'.$start_position)->getFont()->setBold(true);
                    $sheet->getStyle('A'.$start_position.":".'K'.$start_position)->getFont()->setSize(14);

                    $index_active++;
                }
            }

        }




        // пишем файл в формат Excel
        $writer = new Xlsx($spreadsheet);
        $path = 'assets/files/stores/reports/test/';
        $file = $path.'order.xlsx';
        $file_path = $this->modx->getOption('base_path').$file;
        if (!file_exists($this->modx->getOption('base_path').$path)) {
            mkdir($this->modx->getOption('base_path').$path, 0777, true);
        }
        $writer->save($file_path);
        return $file;
    }

	public function getNum(){
		// вычисляем номер регистра
		$num = 0;
		$c = $this->modx->newQuery('slStoreRegistry');
		$c->select('num');
		$c->sortby('id', 'DESC');
		$c->limit(1);
		if ($c->prepare() && $c->stmt->execute()) {
			$num = $c->stmt->fetchColumn();
		}
		$number = str_pad(intval($num) + 1, 8, '0', STR_PAD_LEFT);
		return $number;
	}

	public function generateRegistryFile($store_id, $from = '', $to = '', $number = 0){
		/*
			F2 - номер отчета по реализации: "Отчет реализации № 2049429"
			F3 - период реализации: "Реализация товаров за период с 01.08.2022 по 31.08.2022"
			F4 - номер договора: "по Договору оферты для Продавцов на Платформе Shopfermer24"

			B7 - юр. лицо плательщика					M7 - юр. лицо получателя
			C8 - ИНН плательщика						N8 - ИНН получателя
			C9 - КПП плательщика (если есть)			N9 - КПП получателя (если есть)

			Товарные позиции (начиная с первой):

			B15 - номер п/п
			C15 - наименование позиции
			D15 - Код товара продавца
			F15 - Код товара Shopfermer24

			Справочно:
			G15 - Цена продавца
			H15 - Комиссия за продажу

			Реализовано:
			I15 - Цена реализации
			J15 - Кол-во
			K15 - Реализовано на сумму
			L15 - Итого комиссия
			O15 - Итого к начислению

				Реализовано итого:
				K16 (строки + 1) - Реализовано на сумму
				K16 (строки + 1) - Итого комиссия
				K16 (строки + 1) - Итого к начислению

			Возвращено:
			O15 - Цена реализации
			P15 - Кол-во
			Q15 - Возвращено на сумму
			R15 - Итого комиссия
			S15 - Итого возвращено

				Возвращено итого:
				Q16 (строки + 1) - Реализовано на сумму
				R16 (строки + 1) - Итого комиссия
				S16 (строки + 1) - Итого возвращено

			D19 (строки + 3) - Итого к начислению (К16 - Т16)
			D20 (строки + 4) - В том числе НДС

			С22 (строки + 6) - юр. лицо плательщика
			M22 (строки + 6) - юр. лицо получателя
		*/
		// вычисляем дату последнего реестра
		/*
		$criteria = array(
			"store_id" => $store_id
		);
		$query = $this->modx->newQuery("slStoreRegistry", $criteria);
		$query->sortby('id','DESC');
		$registry = $this->modx->getObject("slStoreRegistry", $query);
		if($registry){
			$date_from = $registry->get("createdon");
		}else{
			$criteria = array(
				"store_id" => $store_id
			);
			$query = $this->modx->newQuery("slStoreBalance", $criteria);
			$query->sortby('id','ASC');
			$balance = $this->modx->getObject("slStoreBalance", $query);
			if($balance){
				$date_from = $balance->get("createdon");
			}
		}
		*/
		$date_from = date("d.m.Y", strtotime($from));
		$date_to = date("d.m.Y", strtotime($to));

		// берем юр. лица
		$seller_base['name'] = $this->modx->getOption("shoplogistic_ur_name");
		$seller_base['inn'] = $this->modx->getOption("shoplogistic_inn");
		if($this->modx->getOption("shoplogistic_kpp")){
			$seller_base['kpp'] = $this->modx->getOption("shoplogistic_kpp");
		}else{
			$seller_base['kpp'] = "-";
		}

		$store = $this->modx->getObject("slStores", $store_id);
		if($store){
			$seller['name'] = $store->get("ur_name").', '.$store->get("company_type");
			$seller['inn'] = $store->get("inn");
			if($store->get("kpp")){
				$seller['kpp'] = $store->get("kpp");
			}else{
				$seller['kpp'] = "-";
			}
		}

		$file_in = $this->modx->getOption("base_path").'assets/files/examples/xlsx/registry_template.xlsx';

		$spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file_in);
		$sheet = $spreadsheet->getActiveSheet();
		$sheet->getCell('F2')->setValue('Отчет реализации № '.$number);
		$sheet->getCell('F3')->setValue('Реализация товаров за период с '.$date_from.' по '.$date_to);
		$sheet->getCell('F4')->setValue('по Договору оферты для Продавцов на Платформе '.$this->modx->getOption("site_name"));

		$sheet->getCell('B7')->setValue($seller_base['name']);
		$sheet->getCell('C8')->setValueExplicit($seller_base['inn'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
		$sheet->getCell('C9')->setValue($seller_base['kpp']);

		$sheet->getCell('M7')->setValue($seller['name']);
		$sheet->getCell('N8')->setValueExplicit($seller['inn'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
		$sheet->getCell('N9')->setValue($seller['kpp']);

		// пробегаемся по файлам
		$registry_products = array();
		$criteria = array(
			"store_id" => $store_id,
			"type" => 1
		);
		$query = $this->modx->newQuery("slStoreBalance", $criteria);
		$query->sortby('id','ASC');
		$balance = $this->modx->getCollection("slStoreBalance", $query);
		foreach($balance as $bal){
			$order = $bal->getOne("Order");
			$o = $order->toArray();
			/*
			if($o['delivery_cost'] > 0){
				$tmp = array(
					"name" => "Доставка до клиента",
					"count" => 1,
					"price" => $o['delivery_cost'],
					"article" => "7USM-DELIVERY"
				);
				$registry_products[0] = $tmp;
			}
			*/
			if($order){
				$products = $order->getMany('Products');
				foreach ($products as $product){
					$key = md5($product->get('product_id').$product->get('price'));
					if(isset($registry_products[$key])){
						$registry_products[$key]['count'] = $registry_products[$key]['count'] + $product->get('count');
					}else{
						$registry_products[$key] = $product->toArray();
					}
					$p = $product->getOne("Product");
					if($p){
						$registry_products[$key]['article'] = $p->get('article');
					}
				}
			}
		}

		//$sheet->insertNewRowBefore(1);
		$all_cash = 0;
		$all_price = 0;
		$all_tax = 0;
		$start_position = 15;
		$start_number = 1;

		foreach($registry_products as $key => $product){
			$sheet->insertNewRowBefore($start_position);
			/*
			 * B15 - номер п/п
			 * C15 - наименование позиции
			 * D15 - Код товара продавца
			 * F15 - Код товара Shopfermer24
			 * G15 - Цена продавца
			 * H15 - Комиссия за продажу
			 * I15 - Цена реализации
			 * J15 - Кол-во
			 * K15 - Реализовано на сумму
			 * L15 - Итого комиссия
			 * N15 - Итого к начислению
			 *
			*/

			$sheet->getCell('B'.$start_position)->setValue($start_number);
			$sheet->getCell('C'.$start_position)->setValue($product['name']);
			$sheet->getCell('D'.$start_position)->setValue($product['article']);
			$sheet->getCell('F'.$start_position)->setValue($product['article']);

			$sheet->getCell('G'.$start_position)->setValue($product['price']);
			$sheet->getCell('H'.$start_position)->setValue($this->modx->getOption("shoplogistic_tax_percent").'%');

			$sheet->getCell('I'.$start_position)->setValue($product['price']);
			$sheet->getCell('J'.$start_position)->setValue($product['count']);
			$price = $product['price']*$product['count'];
			$sheet->getCell('K'.$start_position)->setValue($price);
			$tax = $price*($this->modx->getOption("shoplogistic_tax_percent")/100);
			$sheet->getCell('L'.$start_position)->setValue($tax);
			$sheet->mergeCells('L'.$start_position.":M".$start_position);
			$cash = $price - $tax;
			$sheet->getCell('N'.$start_position)->setValue($cash);

			$all_price = $all_price + $price;
			$all_cash = $all_cash + $cash;
			$all_tax = $all_tax + $tax;

			$start_position++;
			$start_number++;

		}

		// Общие значения

		/*
		 * K16 (строки + 1) - Реализовано на сумму
		 * L16 (строки + 1) - Итого комиссия
		 * O16 (строки + 1) - Итого к начислению
		 */
		$sheet->getCell('K'.$start_position)->setValue($all_price);
		$sheet->getCell('L'.$start_position)->setValue($all_tax);
		$sheet->getCell('N'.$start_position)->setValue($all_cash);

		$sheet->getStyle('A15:S'.$start_position)->getFont()->setBold(false);

		$all_c = $start_position+2;
		$all_cp = $start_position+5;
		$sheet->getCell('D'.$all_c)->setValue($all_cash);
		$sheet->getCell('C'.$all_cp)->setValue($seller_base['name']);
		$sheet->getCell('M'.$all_cp)->setValue($seller['name']);

		$writer = new Xlsx($spreadsheet);
		$path = 'assets/files/stores/reports/'.$store_id.'/';
		if (!file_exists($path)) {
			mkdir($path, 0777, true);
		}
		$file = $path.'registry_'.$number.'_'.$store_id.'.xlsx';
		$file_path = $this->modx->getOption('base_path').$file;
		if (!file_exists($this->modx->getOption('base_path').$path)) {
			mkdir($this->modx->getOption('base_path').$path, 0777, true);
		}
		$writer->save($file_path);
		return $file;
	}

    public function generateReportFile($report_id){
        $report = $this->modx->getObject('slReports', $report_id);
        if($report){
            if($report->get("type") == 4){
                if($file = $this->generateWeekSalesFile($report_id)){
                    $report->set("file", $file);
                    $report->save();
                }
            }
        }
    }

    public function generateXLSXFile($table, $data, $name = "") {
        // $this->modx->log(1, print_r($table, 1));
        // $this->modx->log(1, print_r($data, 1));
        // $this->modx->log(1, print_r($name, 1));
        $config = array(
            "blank_coloumn" => 1,
            "blank_row" => 1,
            "coloumns" => array()
        );
        if($data){
            $spreadsheet = new Spreadsheet();
            $activeWorksheet = $spreadsheet->getActiveSheet();
            // set header
            foreach($table as $key => $coloumn){
                $activeWorksheet->setCellValueByColumnAndRow($config["blank_coloumn"], $config["blank_row"], $coloumn["label"]);
                $config["coloumns"][$key] = $config["blank_coloumn"];
                $config["blank_coloumn"]++;
            }
            $config["blank_row"]++;
            // set data
            foreach($data as $item){
                foreach($config["coloumns"] as $key => $col){
                    $activeWorksheet->setCellValueByColumnAndRow($col, $config["blank_row"], $item[$key]);
                }
                $config["blank_row"]++;
            }
            $writer = new Xlsx($spreadsheet);
            $path = "assets/content/tmp/reports/";
            if (!file_exists($this->modx->getOption('base_path') . $path)) {
                mkdir($this->modx->getOption('base_path') . $path, 0777, true);
            }
            $writer->save($this->modx->getOption('base_path') . $path . $name . ".xlsx");
            return array("filename" => $this->modx->getOption('site_url') . $path . $name . ".xlsx");
        }
        return false;
    }

    public function generateWeekSalesFile($report_id){
        $report = $this->modx->getObject("slReports", $report_id);
        $styleArray = [
            'font' => [
                'bold' => true,
            ]
        ];
        if($report) {
            $report_data = $report->toArray();
            $data = $this->sl->reports->getWeekSales(array("report_id" => $report_id));
            if ($data) {
                // return $data["columns"];
                // generate xlsx header
                $spreadsheet = new Spreadsheet();
                $activeWorksheet = $spreadsheet->getActiveSheet();
                $activeWorksheet->setCellValue('A1', "Период");
                $activeWorksheet->setCellValue('A2', "Номер недели");
                $activeWorksheet->mergeCells('B1:E1');
                $activeWorksheet->mergeCells('B2:E2');
                $activeWorksheet->setCellValue('B1', "Сводная информация за период");
                $activeWorksheet->setCellValue('B2', count($data['weeks'])." нед.");
                $i = 6;
                foreach($data["weeks"] as $index => $week){
                    $date_from = date("d.m.Y", strtotime($week['date_from']));
                    $date_to = date("d.m.Y", strtotime($week['date_to']));
                    $num = $index + 1;
                    $last_c = $i + 3;
                    $activeWorksheet->mergeCellsByColumnAndRow($i, 1, $last_c, 1);
                    $activeWorksheet->mergeCellsByColumnAndRow($i, 2, $last_c, 2);
                    $activeWorksheet->setCellValueByColumnAndRow($i, 1, $num.' нед.');
                    $activeWorksheet->setCellValueByColumnAndRow($i, 2, $date_from.' - '.$date_to);
                    /*
                    for ($j = $i + 1; $j <= $last_c; $j++) {
                        $activeWorksheet->getColumnDimensionByColumn($j)->setOutlineLevel(1)->setVisible(false)->setCollapsed(true);
                    }
                    */
                    $i = $last_c + 1;
                }
                $row = 3;
                $i = 1;
                foreach ($data["columns"] as $column) {
                    $activeWorksheet->setCellValueByColumnAndRow($i, $row, $column['label']);
                    $i++;
                }
                $row++;
                foreach ($data["items"] as $item) {
                    if ($item["data"]) {
                        $i = 1;
                        foreach ($data["columns"] as $column) {
                            $activeWorksheet->setCellValueByColumnAndRow($i, $row,$item["data"][$column["field"]]);
                            $activeWorksheet->getStyleByColumnAndRow($i, $row)->applyFromArray($styleArray);
                            $i++;
                        }
                    }
                    // запоминаем родителя
                    $row++;
                    $collapse_row = $row;
                    if ($item["children"]) {
                        foreach ($item["children"] as $sub_row) {
                            if ($sub_row["data"]) {
                                $i = 1;
                                foreach ($data["columns"] as $column) {
                                    $activeWorksheet->setCellValueByColumnAndRow($i, $row, $sub_row["data"][$column["field"]]);
                                    if(isset($sub_row["children"])){
                                        $activeWorksheet->getStyleByColumnAndRow($i, $row)->applyFromArray($styleArray);
                                    }
                                    $i++;
                                }
                                // запоминаем суб родителя
                                $row++;
                                $collapse_row_2 = $row;
                                if (isset($sub_row["children"])) {
                                    foreach ($sub_row["children"] as $s_row) {
                                        if ($s_row["data"]) {
                                            $i = 1;
                                            foreach ($data["columns"] as $column) {
                                                $activeWorksheet->setCellValueByColumnAndRow($i, $row, $s_row["data"][$column["field"]]);
                                                $i++;
                                            }
                                            $row++;
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
                $writer = new Xlsx($spreadsheet);
                $path = "assets/files/organization/{$report_data['store_id']}/reports/sales/";
                if (!file_exists($this->modx->getOption('base_path') . $path)) {
                    mkdir($this->modx->getOption('base_path') . $path, 0777, true);
                }
                $writer->save($this->modx->getOption('base_path') . $path . "weeksales_{$report_data['id']}.xlsx");
                return $path . "weeksales_{$report_data['id']}.xlsx";
            }
        }
    }

    public function generateWeekSalesFileOLD($report_id){
        // нужно взять данные
        $output = array();
        $report = $this->modx->getObject("slReports", $report_id);
        if($report) {
            $report_data = $report->toArray();
            if (isset($report_data['properties']['matrix'])) {
                $subq = $this->modx->newQuery("slStoresMatrixProducts");
                $subq->leftJoin('slStoresMatrix', 'slStoresMatrix', 'slStoresMatrix.id = slStoresMatrixProducts.matrix_id');
                $subq->leftJoin('msProductData', 'msProductData', 'msProductData.id = slStoresMatrixProducts.product_id');
                $subq->leftJoin('modResource', 'modResource', 'modResource.id = slStoresMatrixProducts.product_id');
                $subq->where(array("matrix_id:=" => $report_data['properties']['matrix']));
                $subq->select(array("modResource.pagetitle as name, msProductData.*"));
                if ($subq->prepare() && $subq->stmt->execute()) {
                    $prods = $subq->stmt->fetchAll(PDO::FETCH_ASSOC);
                    foreach($prods as $prod){
                        $output['products'][$prod['id']] = $prod;
                    }
                }
                $output['products']['all'] = array(
                    "name" => "Итого",
                    "product_id" => "all",
                    "vendor_article" => "",
                    "id" => "all",
                );
            }
            $query = $this->modx->newQuery("slReportsWeeks");
            $query->where(array("report_id" => $report_id));
            $query->select(array("slReportsWeeks.*"));
            if ($query->prepare() && $query->stmt->execute()) {
                $weeks = $query->stmt->fetchAll(PDO::FETCH_ASSOC);
                foreach ($weeks as $key => $week) {
                    $query = $this->modx->newQuery("slReportsWeekSales");
                    $query->where(array("week_id:=" => $week['id']));
                    $query->select(array("slReportsWeekSales.*"));
                    if ($query->prepare() && $query->stmt->execute()) {
                        $sales = $query->stmt->fetchAll(PDO::FETCH_ASSOC);
                        $stores = array();
                        $general = array(
                            'all' => 0
                        );
                        // суммарно по номенклатурно
                        foreach($output['products'] as $product){
                            $general['sales'][] = array(
                                "product_id" => $product['id'],
                                "sales" => 0
                            );
                        }
                        foreach ($sales as $sale) {
                            $stores[] = $sale['store_id'];
                            foreach($general['sales'] as $kk => $val){
                                if($val['product_id'] == $sale['product_id']){
                                    $general['sales'][$kk]['sales'] += $sale['sales'];
                                }
                            }
                            $general['all'] += $sale['sales'];
                        }
                        $weeks[$key]['general'] = $general;
                        $stores = array_unique($stores);
                        $query = $this->modx->newQuery("slStores");
                        $query->leftJoin("dartLocationCity", "dartLocationCity", "dartLocationCity.id = slStores.city");
                        $query->leftJoin("dartLocationRegion", "dartLocationRegion", "dartLocationRegion.id = dartLocationCity.region");
                        $query->select(array("slStores.id,slStores.name,slStores.address,dartLocationCity.city as city_name,dartLocationRegion.name as region_name"));
                        $query->where(array("slStores.id:IN" => $stores));
                        if ($query->prepare() && $query->stmt->execute()) {
                            $stores = $query->stmt->fetchAll(PDO::FETCH_ASSOC);
                            foreach ($stores as $k => $store) {
                                $str = array(
                                    "product_id" => "all",
                                    "sales" => 0
                                );
                                foreach ($sales as $sale) {
                                    if ($store['id'] == $sale['store_id']) {
                                        $stores[$k]['sales'][] = $sale;
                                        $str['sales'] += $sale['sales'];
                                    }
                                }
                                $stores[$k]['sales'][] = $str;
                            }
                        }
                        $weeks[$key]['sales'] = $stores;
                    }
                    $output['weeks'] = $weeks;
                }
                // генерируем документ
                $spreadsheet = new Spreadsheet();
                $activeWorksheet = $spreadsheet->getActiveSheet();
                // заполняем товары
                $product_coords = array();
                $line = 3;
                $activeWorksheet->mergeCells('A1:B1');
                $activeWorksheet->setCellValue('A1', "Номенклатура");
                $activeWorksheet->setCellValue('A2', "Артикул");
                $activeWorksheet->setCellValue('B2', "Наименование");
                foreach($output['products'] as $product){
                    $activeWorksheet->setCellValue('A'.$line, $product['vendor_article']);
                    $activeWorksheet->setCellValue('B'.$line, $product['name']);
                    $product_coords[$product['id']] = $line;
                    $line++;
                }
                // mergeCellsByColumnAndRow($pColumn1 = 0, $pRow1 = 1, $pColumn2 = 0, $pRow2 = 1)
                $coloumn = 3;
                $row = 1;
                foreach($output['weeks'] as $week){
                    $date_from = date("d.m.Y", strtotime($week['date_from']));
                    $date_to = date("d.m.Y", strtotime($week['date_to']));
                    $coloumns = count($week['sales']);
                    $last_c = $coloumn + $coloumns - 1;
                    $last_c++;
                    $activeWorksheet->mergeCellsByColumnAndRow($coloumn, $row, $last_c, $row);
                    $activeWorksheet->setCellValueByColumnAndRow($coloumn, $row, $date_from.' - '.$date_to);
                    for ($i = $coloumn + 1; $i <= $last_c; $i++) {
                        $activeWorksheet->getColumnDimensionByColumn($i)->setOutlineLevel(1)->setVisible(false)->setCollapsed(true);
                    }
                    $r = $row + 1;
                    $c = $coloumn;
                    $activeWorksheet->setCellValueByColumnAndRow($c, $r, "Итого");
                    foreach($week['general']['sales'] as $gs){
                        $product_line = $product_coords[$gs['product_id']];
                        $activeWorksheet->setCellValueByColumnAndRow($c, $product_line, $gs['sales']);
                    }
                    $product_line = $product_coords['all'];
                    $activeWorksheet->setCellValueByColumnAndRow($c, $product_line, $week['general']['all']);
                    $c++;
                    foreach($week['sales'] as $sale){
                        $activeWorksheet->setCellValueByColumnAndRow($c, $r, $sale['name']);
                        foreach($sale['sales'] as $s){
                            $product_line = $product_coords[$s['product_id']];
                            $activeWorksheet->setCellValueByColumnAndRow($c, $product_line, $s['sales']);
                        }
                        $c++;
                    }
                    $coloumn += $coloumns + 1;
                }
                $writer = new Xlsx($spreadsheet);
                $path = "assets/files/organization/{$report_data['store_id']}/reports/sales/";
                if (!file_exists( $this->modx->getOption('base_path').$path)) {
                    mkdir($this->modx->getOption('base_path').$path, 0777, true);
                }
                $writer->save($this->modx->getOption('base_path').$path."weeksales_{$report_data['id']}.xlsx");
                return $path."weeksales_{$report_data['id']}.xlsx";
            }
        }
    }

    public function etm(){
        $output = array();
        $file = 'cron/files/etm/report.xlsx';
        // открываем файл отчета
        $file_in = $this->modx->getOption("base_path").$file;
        if(file_exists($file_in)) {
            try {
                $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file_in);
            } catch (\PhpOffice\PhpSpreadsheet\Reader\Exception $e) {
                $output = $this->sl->tools->error("Некорректный файл для загрузки!");
            }
            $sheetData = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);
            $output["total_report"] = count($sheetData);
            foreach($sheetData as $key => $value) {
                if ($key != 1) {
                    $tmp = array(
                        "code" => $value["O"],
                        "name" => $value["P"],
                        "vendor_code" => $value["R"],
                        "article" => $value["U"],
                        "needle" => array()
                    );
                    if($value["A"] == "Нет"){
                        $tmp["needle"][] = "photo_main";
                    }
                    if($value["B"] == "Нет"){
                        $tmp["needle"][] = "photo_other";
                    }
                    if($value["C"] == "Нет"){
                        $tmp["needle"][] = "tech_info";
                    }
                    if($value["H"] == "Нет"){
                        $tmp["needle"][] = "description";
                    }
                    if(count($tmp["needle"])){
                        $output["products"][] = $tmp;
                    }
                }
            }
            $yml_file = "https://dev.mst.tools/assets/files/parser/97.xml";
            $xml = simplexml_load_file($yml_file);
            // Генерируем документ Фото
            $photosheet = new Spreadsheet();
            $activePhotosheet = $photosheet->getActiveSheet();
            // заполняем шапку
            $activePhotosheet->setCellValue('A1', "Статус");
            $activePhotosheet->setCellValue('B1', "Код производителя");
            $activePhotosheet->setCellValue('C1', "Расширенный артикул");
            $activePhotosheet->setCellValue('D1', "Бренд");
            $activePhotosheet->setCellValue('E1', "Основное Изображение");
            $activePhotosheet->setCellValue('F1', "Дополнительные Изображения");
            $activePhotosheet->setCellValue('G1', "Ссылка на скачивание файла");
            // Генерируем документ Описания
            $descsheet = new Spreadsheet();
            $activeDescsheet = $descsheet->getActiveSheet();
            // заполняем шапку
            $activeDescsheet->setCellValue('A1', "Статус");
            $activeDescsheet->setCellValue('B1', "Код производителя");
            $activeDescsheet->setCellValue('C1', "Расширенный артикул");
            $activeDescsheet->setCellValue('D1', "Бренд");
            $activeDescsheet->setCellValue('E1', "Описание");
            $activeDescsheet->setCellValue('F1', "Преимущества");
            // Генерируем документ Доп материалов
            $docssheet = new Spreadsheet();
            $activeDocssheet = $docssheet->getActiveSheet();
            // заполняем шапку
            $activeDocssheet->setCellValue('A1', "Статус");
            $activeDocssheet->setCellValue('B1', "Код производителя");
            $activeDocssheet->setCellValue('C1', "Расширенный артикул");
            $activeDocssheet->setCellValue('D1', "Бренд");
            $activeDocssheet->setCellValue('E1', "Название дополнительной технической информации");
            $activeDocssheet->setCellValue('F1', "Наименование файла");
            $activeDocssheet->setCellValue('G1', "Ссылка на скачивание файла");
            $indexes = array(
                "photo" => 2,
                "desc" => 2,
                "docs" => 2
            );
            foreach($output["products"] as $k => $product){
                if(count($product["needle"])){
                    foreach ($xml->shop->offers->offer as $row) {
                        $articles = explode(",", strval($row->article));
                        foreach($articles as $ka => $article){
                            $articles[$ka] = trim($article);
                        }
                        if(in_array($product["article"], $articles)){
                            foreach($product["needle"] as $need){
                                if($need == "photo_main"){
                                    $photo = strval($row->pictures->picture[0]);
                                    if($photo) {
                                        $output["products"][$k]["needle_values"][$need] = strval($row->pictures->picture[0]);
                                    }
                                }
                                if($need == "photo_other"){
                                    foreach($row->pictures->picture as $kp => $picture){
                                        $output["products"][$k]["needle_values"][$need][] = strval($picture);
                                    }
                                    unset($output["products"][$k]["needle_values"][$need][0]);
                                }
                                if($need == "description"){
                                    $desc = trim(str_replace("Купить", "", strip_tags(strval($row->descriptionFull))));
                                    $description = explode("Рекомендованная розничная цена", $desc);
                                    $output["products"][$k]["needle_values"][$need] = $description[0];
                                }
                                if($need == "tech_info"){
                                    foreach($row->param as $param){
                                        $pval = strval($param);
                                        $pos = strpos($pval, "/storage/docs/");
                                        if ($pos !== false) {
                                            $output["products"][$k]["needle_values"][$need][strval($param["name"])] = "https://www.interskol.ru".$pval;
                                        }
                                    }
                                }
                            }
                        }
                    }
                    // Если какая-то инфа есть
                    if(count($output["products"][$k]["needle_values"])){
                        // 1. Собираем картинки Главная
                        if($output["products"][$k]["needle_values"]["photo_main"]){
                            $url = $output["products"][$k]["needle_values"]["photo_main"];
                            $urlers = explode("/", $output["products"][$k]["needle_values"]["photo_main"]);
                            $urlers = array_reverse($urlers);
                            $activePhotosheet->setCellValue('A'.$indexes["photo"], "Добавление материалов");
                            $activePhotosheet->setCellValue('B'.$indexes["photo"], "2623");
                            $activePhotosheet->setCellValue('C'.$indexes["photo"], $output["products"][$k]['article']);
                            $activePhotosheet->setCellValue('D'.$indexes["photo"], "Интерскол");
                            $activePhotosheet->setCellValue('E'.$indexes["photo"], $urlers[0]);
                            $activePhotosheet->setCellValue('G'.$indexes["photo"], $url);
                            $indexes["photo"]++;
                        }
                        // 2. Собираем картинки Допы
                        if(count($output["products"][$k]["needle_values"]["photo_other"])){
                            foreach($output["products"][$k]["needle_values"]["photo_other"] as $photo){
                                $url = $photo;
                                $urlers = explode("/", $photo);
                                $urlers = array_reverse($urlers);
                                $activePhotosheet->setCellValue('A'.$indexes["photo"], "Добавление материалов");
                                $activePhotosheet->setCellValue('B'.$indexes["photo"], "2623");
                                $activePhotosheet->setCellValue('C'.$indexes["photo"], $output["products"][$k]['article']);
                                $activePhotosheet->setCellValue('D'.$indexes["photo"], "Интерскол");
                                $activePhotosheet->setCellValue('F'.$indexes["photo"], $urlers[0]);
                                $activePhotosheet->setCellValue('G'.$indexes["photo"], $url);
                                $indexes["photo"]++;
                            }

                        }
                        // 3. Собираем описания
                        if($output["products"][$k]["needle_values"]["description"]){
                            if(trim($output["products"][$k]["needle_values"]["description"]) != "Совместимость:"){
                                $activeDescsheet->setCellValue('A'.$indexes["desc"], "Добавление материалов");
                                $activeDescsheet->setCellValue('B'.$indexes["desc"], "2623");
                                $activeDescsheet->setCellValue('C'.$indexes["desc"], $output["products"][$k]['article']);
                                $activeDescsheet->setCellValue('D'.$indexes["desc"], "Интерскол");
                                $activeDescsheet->setCellValue('E'.$indexes["desc"], trim($output["products"][$k]["needle_values"]["description"]));
                                $indexes["desc"]++;
                            }
                        }
                        // 4. Собираем доп материалы
                        if($output["products"][$k]["needle_values"]["tech_info"]){
                            foreach($output["products"][$k]["needle_values"]["tech_info"] as $kf => $file){
                                $url = $file;
                                $urlers = explode("/", $file);
                                $urlers = array_reverse($urlers);
                                $activeDocssheet->setCellValue('A'.$indexes["docs"], "Добавление материалов");
                                $activeDocssheet->setCellValue('B'.$indexes["docs"], "2623");
                                $activeDocssheet->setCellValue('C'.$indexes["docs"], $output["products"][$k]['article']);
                                $activeDocssheet->setCellValue('D'.$indexes["docs"], "Интерскол");
                                $activeDocssheet->setCellValue('E'.$indexes["docs"], $kf);
                                $activeDocssheet->setCellValue('F'.$indexes["docs"], $urlers[0]);
                                $activeDocssheet->setCellValue('G'.$indexes["docs"], $url);
                                $indexes["docs"]++;
                            }
                        }
                    }
                }
            }
            // Фотки
            $pwriter = new Xlsx($photosheet);
            $path = "cron/files/etm/output/";
            if (!file_exists( $this->modx->getOption('base_path').$path)) {
                mkdir($this->modx->getOption('base_path').$path, 0777, true);
            }
            $pwriter->save($this->modx->getOption('base_path').$path."images.xlsx");
            // Описания
            $descwriter = new Xlsx($descsheet);
            $descwriter->save($this->modx->getOption('base_path').$path."desc.xlsx");
            // Доп материалы
            $docswriter = new Xlsx($docssheet);
            $docswriter->save($this->modx->getOption('base_path').$path."docs.xlsx");
        }
        return $output;
    }
}