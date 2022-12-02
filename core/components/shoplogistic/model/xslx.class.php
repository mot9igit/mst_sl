<?php

require_once dirname(__FILE__) . '/../libs/vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class slXSLX{
	public function __construct(shopLogistic &$sl, modX &$modx)
	{
		$this->sl =& $sl;
		$this->modx =& $modx;
		$this->modx->lexicon->load('shoplogistic:default');
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
}