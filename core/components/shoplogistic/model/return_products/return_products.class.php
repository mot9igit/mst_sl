<?php
class returnProductsHandler
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
            case 'post/products':
                $response = $this->postReturnProducts($properties);
                break;
            case 'get/products':
                $response = $this->getReturnProducts($properties);
                break;
			case 'getshop/products':
                $response = $this->getShopReturnProducts($properties);
                break;
        }
		
        return $response;
    }

    public function postReturnProducts($properties){

		$data = $properties['data'];
        $info = $properties['info'];

        foreach ($data as $key => $value) {
            $q = $this->modx->newObject("slReturn");
            $q->set("order_id", $info['order_id']);
            $q->set("product_id", $value['id_product']);
            $q->set("num", $this->renerateNum($info['order_id']));
            $q->set("date", date('Y-m-d H:i:s')); //TODO
            $q->set("comments_seller", "—");
            $q->set("comments_mp", "—");
            $q->set("comments_buyer", $value['comment']);
            $q->set("full_name", $info['full_name']);
            $q->set("bank_name", $info['bank_name']);
            $q->set("bank_bik", $info['bank_bik']);
            $q->set("corr_account", $info['corr_account']);
            $q->set("pay_account", $info['pay_account']);
			$q->set("status", 1);
			$q->set("decision", "—");
            $q->set("reason", $value['reason']);
            $q->set("requirement", $value['requirement']);
            $q->save();

            $return_id = $q->get("id");

            foreach ($value['files'] as $k => $v) {
                $uniqid = uniqid();
                $new_name = $uniqid . "." . pathinfo($v, PATHINFO_EXTENSION);

                $qf = $this->modx->newObject("slReturnFile");
                $qf->set("return_id", $return_id);
                $qf->set("file", $new_name);
                $qf->save();

                copy($v, $this->modx->getOption('base_path') . "assets/products_return/" . $new_name);
            }


        }

        return json_encode(
            [
                "success" => true,
            ]
        );
    }

    public function renerateNum ($order_id) {
        return rand(100, 1000) . "/" . $order_id;
    }

    public function getReturnProducts($id = 0){

        if ($id == 0){
            $query = $this->modx->newQuery("slReturn");
            $query->rightJoin("slOrder", "slOrder", "slReturn.order_id = slOrder.order_id");
            $query->leftJoin("msOrder", "msOrder", "slOrder.order_id = msOrder.id");
            $query->leftJoin("slOrderProduct", "slOrderProduct", "slOrderProduct.order_id = slOrder.id");
            $query->leftJoin("msProduct", "msProduct", "slOrderProduct.product_id = msProduct.id");
            $query->leftJoin("msProductData", "msProductData", "msProductData.id = slReturn.product_id");

            $query->where(array(
                "msOrder.user_id:=" => $this->modx->user->id
            ));

            $query->select(array(
                "`slReturn`.*",
                "`msProduct`.pagetitle",
                "`msProductData`.image",
                "`msProductData`.article",
                "`slOrder`.cost as cost",
            ));

            if ($query->prepare() && $query->stmt->execute()) {
                $orders = $query->stmt->fetchAll(PDO::FETCH_ASSOC);

                foreach ($orders as $key => $value) {
                    //Достаём статус заказа
                    $q_status = $this->modx->newQuery("slReturnStatus");
                    $q_status->where(array(
                        "slReturnStatus.id:=" => $value['status']
                    ));

                    $q_status->select(array(
                        "`slReturnStatus`.*",
                    ));

                    if ($q_status->prepare() && $q_status->stmt->execute()) {
                        $orders[$key]['status'] = $q_status->stmt->fetchAll(PDO::FETCH_ASSOC);
                    }

                }

                return $orders;
            }
        }else{
            $query = $this->modx->newQuery("slReturn");
            $query->rightJoin("slOrder", "slOrder", "slReturn.order_id = slOrder.order_id");
            $query->leftJoin("msOrder", "msOrder", "slOrder.order_id = msOrder.id");
            $query->leftJoin("slOrderProduct", "slOrderProduct", "slOrderProduct.order_id = slOrder.id");
            $query->leftJoin("msProduct", "msProduct", "slOrderProduct.product_id = msProduct.id");
            $query->leftJoin("msProductData", "msProductData", "msProductData.id = slReturn.product_id");

            $query->where(array(
                "msOrder.user_id:=" => $this->modx->user->id,
                "slReturn.id:=" => $id,
            ));

            $query->select(array(
                "`slReturn`.*",
                "`msProduct`.pagetitle",
                "`msProductData`.image",
                "`msProductData`.article",
                "`slOrder`.cost as cost",
            ));

            if ($query->prepare() && $query->stmt->execute()) {
                $order = $query->stmt->fetchAll(PDO::FETCH_ASSOC);

                if(!$order){
                    $order['error'] = array(
                        "message" => "У вас нет доступа на просмотр этого заказа либо такого заказа не существует!"
                    );
                }else{
                    //Достаём статус заказа
                    $q_status = $this->modx->newQuery("slReturnStatus");
                    $q_status->where(array(
                        "slReturnStatus.id:=" => $order[0]['status']
                    ));

                    $q_status->select(array(
                        "`slReturnStatus`.*",
                    ));

                    if ($q_status->prepare() && $q_status->stmt->execute()) {
                        $order[0]['status'] = $q_status->stmt->fetchAll(PDO::FETCH_ASSOC);
                    }

                    //Достаём файлы
                    $q_files = $this->modx->newQuery("slReturnFile");

                    $q_files->where(array(
                        "slReturnFile.return_id:=" => $order[0]['id']
                    ));

                    $q_files->select(array(
                        "`slReturnFile`.*"
                    ));

                    if ($q_files->prepare() && $q_files->stmt->execute()) {
                        $files = $q_files->stmt->fetchAll(PDO::FETCH_ASSOC);
                        $order[0]['files'] = $files;
                    }
                }
                return $order;
            }


            if ($query->prepare() && $query->stmt->execute()) {
                $order = $query->stmt->fetchAll(PDO::FETCH_ASSOC);

                if(!$order){
                    $order['error'] = array(
                        "message" => "У вас нет доступа на просмотр этого заказа либо такого заказа не существует!"
                    );
                }
            }
        }

    }
	
	
	public function getShopReturnProducts($properties){
		
		$query = $this->modx->newQuery("slReturn");
		$query->leftJoin("slOrder", "slOrder", "slReturn.order_id = slOrder.order_id");
		$query->leftJoin("msOrder", "msOrder", "slOrder.order_id = msOrder.id");
		$query->leftJoin("slOrderProduct", "slOrderProduct", "slOrderProduct.order_id = slOrder.id");
		$query->leftJoin("msProduct", "msProduct", "slOrderProduct.product_id = msProduct.id");
		$query->leftJoin("msProductData", "msProductData", "msProductData.id = slReturn.product_id");
        
        if($properties['id_return']){
            $query->where(array(
                "slOrder.store_id:=" => $properties['id'],
                "slReturn.id:=" => $properties['id_return'],
                "slOrder.createdon:IS NOT" => NULL
            ));
        }else{
            $query->where(array(
                "slOrder.store_id:=" => $properties['id'],
                "slOrder.createdon:IS NOT" => NULL
            ));
        }

		$query->select(array(
			"`slReturn`.*",
			"`msProduct`.pagetitle",
			"`msOrder`.num as number_order",
			"`msProductData`.image",
			"`msProductData`.article",
			"`slOrder`.cost as cost",
			"`slOrder`.store_id",
			"`slOrder`.createdon as date_order",
			"`slOrder`.createdon",
            "`slOrderProduct`.count",
		));
		
		$query->prepare();
		$this->modx->log(1, $query->toSQL());

		if ($query->prepare() && $query->stmt->execute()) {
			$orders = $query->stmt->fetchAll(PDO::FETCH_ASSOC);

			foreach ($orders as $key => $value) {

                //Меняем путь до файлов

                $urlMain = $this->modx->getOption("site_url");

                //$orders[$key]['image'] = $urlMain . $value['image'];

				//Достаём статус заказа
				$q_status = $this->modx->newQuery("slReturnStatus");
				$q_status->where(array(
					"slReturnStatus.id:=" => $value['status']
				));

				$q_status->select(array(
					"`slReturnStatus`.*",
				));

				if ($q_status->prepare() && $q_status->stmt->execute()) {
					$orders[$key]['status'] = $q_status->stmt->fetchAll(PDO::FETCH_ASSOC);
					$orders[$key]['status_name'] = $orders[$key]['status'][0]['name'];
				}

                //Достаём файлы
                $q_files = $this->modx->newQuery("slReturnFile");

                $q_files->where(array(
                    "slReturnFile.return_id:=" => $orders[0]['id']
                ));

                $q_files->select(array(
                    "`slReturnFile`.*"
                ));

                if ($q_files->prepare() && $q_files->stmt->execute()) {
                    $files = $q_files->stmt->fetchAll(PDO::FETCH_ASSOC);

                    foreach ($files as $k => $v) {
                        $files[$k]['file'] = $urlMain . "assets/products_return/" . $v['file'];
                    }

                    $orders[0]['files'] = $files;
                }

                $month_list = array(
                    1  => 'января',
                    2  => 'февраля',
                    3  => 'марта',
                    4  => 'апреля',
                    5  => 'мая',
                    6  => 'июня',
                    7  => 'июля',
                    8  => 'августа',
                    9  => 'сентября',
                    10 => 'октября',
                    11 => 'ноября',
                    12 => 'декабря'
                );

                $end = new DateTime($orders[0]['date']);
                $timeEnd = $end->getTimestamp();

                $date_order= new DateTime($orders[0]['date_order']);
                $time_date_order = $date_order->getTimestamp();


                $orders[0]['date_order'] = date('d', $time_date_order) . ' ' . $month_list[date('n', $time_date_order)] . ' ' . date('Y', $time_date_order) . ' ' . date('H', $time_date_order) . ':' . date('i', $time_date_order); // 25 апреля 2024

                $orders[0]['date'] = date('d', $timeEnd) . ' ' . $month_list[date('n', $timeEnd)] . ' ' . date('Y', $timeEnd) . ' ' . date('H', $timeEnd) . ':' . date('i', $timeEnd); // 25 апреля 2024



            }
		}
		
		return $orders;
	}
}