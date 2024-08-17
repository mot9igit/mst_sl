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
                                "store_id:=" => $store_id,
                                "giud:=" => $value["G"]
                            );
                        }else{
                            $criteria = array(
                                "store_id:=" => $store_id,
                                "article:=" => $value["A"]
                            );
                        }
                    } else if($type == 'b2c') {
                        if(isset($value["F"])){
                            $criteria = array(
                                "store_id:=" => $store_id,
                                "giud:=" => $value["F"]
                            );
                        }else{
                            $criteria = array(
                                "store_id:=" => $store_id,
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

    public function generateOptOrder($basket, $properties){
        if($properties['store_id']){
            $spreadsheet = new Spreadsheet();
            $myWorkSheet = new Worksheet($spreadsheet, mb_strimwidth($basket['stores'][$properties['store_id']]['name_short'], 0, 29));
            $spreadsheet->addSheet($myWorkSheet);
            $spreadsheet->removeSheetByIndex(0);
            $spreadsheet->setActiveSheetIndex(0);
            $sheet = $spreadsheet->getActiveSheet();


            $sheet->mergeCells("A1:J1");
            $sheet->getStyle('A1:J1')->getFont()->setSize(14);
            $sheet->getStyle('A1:J1')->getFont()->setBold(true);

            $sheet->getCell('A2')->setValue('Артикул');
            $sheet->getCell('B2')->setValue('Наименование');
            $sheet->getCell('C2')->setValue('Количество');
            $sheet->getCell('D2')->setValue('Цена');
            $sheet->getCell('E2')->setValue('Сумма');
//            $sheet->getCell('F2')->setValue('Отсрочка');
//            $sheet->getCell('G2')->setValue('Оплата доставки');
//            $sheet->getCell('H2')->setValue('Акции');
//            $sheet->getCell('J2')->setValue('Срок доставки');



            $sheet->getCell('A1')->setValue('Заказ у поставщика ' . $basket['stores'][$properties['store_id']]['name']);

            $start_position = 3;
            foreach($basket['stores'][$properties['store_id']]['products'] as $key => $product){
                $sheet->insertNewRowBefore($start_position);

                $sheet->setCellValueExplicit('A'.$start_position, $product['article'], PHPExcel_Cell_DataType::TYPE_STRING);
                $sheet->setCellValueExplicit('B'.$start_position, $product['name'], PHPExcel_Cell_DataType::TYPE_STRING);
                $sheet->getCell('C'.$start_position)->setValue($product['info']['count']);
                $sheet->getCell('D'.$start_position)->setValue($product['info']['price']);
                $sheet->getCell('E'.$start_position)->setValue($product['info']['price'] * $product['info']['count']);

                $start_position++;
            }
            if(count($basket['stores'][$properties['store_id']]['complects'])) {
                foreach ($basket['stores'][$properties['store_id']]['complects'] as $key => $complect) {
                    foreach ($complect['products'] as $k => $product) {
                        $sheet->insertNewRowBefore($start_position);

                        $sheet->setCellValueExplicit('A'.$start_position, $product['article'], PHPExcel_Cell_DataType::TYPE_STRING);
                        $sheet->setCellValueExplicit('B'.$start_position, $product['name'], PHPExcel_Cell_DataType::TYPE_STRING);
                        $sheet->getCell('C'.$start_position)->setValue($product['info']['count']);
                        $sheet->getCell('D'.$start_position)->setValue($product['info']['price']);
                        $sheet->getCell('E'.$start_position)->setValue($product['info']['price'] * $product['info']['count']);
                        $start_position++;
                    }
                }

            }
        } else {
            $spreadsheet = new Spreadsheet();
            $spreadsheet->removeSheetByIndex(0);

            $index_active = 0;
            foreach ($basket['stores'] as $key => $store) {
                $myWorkSheet = new Worksheet($spreadsheet, mb_strimwidth($store['name_short'], 0, 29));
                $spreadsheet->addSheet($myWorkSheet);
                $spreadsheet->setActiveSheetIndex($index_active);
                $sheet = $spreadsheet->getActiveSheet();

                $sheet->mergeCells("A1:E1");
                $sheet->getStyle('A1:E1')->getFont()->setSize(14);
                $sheet->getStyle('A1:E1')->getFont()->setBold(true);

                $sheet->getCell('A2')->setValue('Артикул');
                $sheet->getCell('B2')->setValue('Наименование');
                $sheet->getCell('C2')->setValue('Количество');
                $sheet->getCell('D2')->setValue('Цена');
                $sheet->getCell('E2')->setValue('Сумма');

                $sheet->getCell('A1')->setValue('Заказ у поставщика ' . $store['name']);

                $start_position = 3;
                foreach ($store['products'] as $k => $product) {
                    $sheet->insertNewRowBefore($start_position);

                    $sheet->setCellValueExplicit('A'.$start_position, $product['article'], PHPExcel_Cell_DataType::TYPE_STRING);
                    $sheet->setCellValueExplicit('B'.$start_position, $product['name'], PHPExcel_Cell_DataType::TYPE_STRING);
                    $sheet->getCell('C'.$start_position)->setValue($product['info']['count']);
                    $sheet->getCell('D'.$start_position)->setValue($product['info']['price']);
                    $sheet->getCell('E'.$start_position)->setValue($product['info']['price'] * $product['info']['count']);

                    $start_position++;
                }

                if(count($store['complects'])){
                    foreach($store['complects'] as $key_c => $complect){

                        foreach($complect['products'] as $k_c => $product){
                            $sheet->insertNewRowBefore($start_position);

                            $sheet->setCellValueExplicit('A'.$start_position, $product['article'], PHPExcel_Cell_DataType::TYPE_STRING);
                            $sheet->setCellValueExplicit('B'.$start_position, $product['name'], PHPExcel_Cell_DataType::TYPE_STRING);
                            $sheet->getCell('C'.$start_position)->setValue($product['info']['count']);
                            $sheet->getCell('D'.$start_position)->setValue($product['info']['price']);
                            $sheet->getCell('E'.$start_position)->setValue($product['info']['price'] * $product['info']['count']);

                            $start_position++;
                        }

                    }
                }

                $index_active++;


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
}