<?php

class slOrderGetListProcessor extends modObjectGetListProcessor
{
	public $classKey = 'slOrder';
	public $languageTopics = array('shoplogistic');
	public $defaultSortField = 'id';
	public $defaultSortDirection = 'DESC';
	//public $permission = 'msorder_list';
	/** @var  shoplogistic $shoplogistic */
	protected $shoplogistic;
	/** @var  xPDOQuery $query */
	protected $query;


	/**
	 * @return bool|null|string
	 */
	public function initialize()
	{
		$this->shoplogistic = $this->modx->getService('shoplogistic');

		if (!$this->modx->hasPermission($this->permission)) {
			return $this->modx->lexicon('access_denied');
		}

		return parent::initialize();
	}


	/**
	 * @param xPDOQuery $c
	 *
	 * @return xPDOQuery
	 */
	public function prepareQueryBeforeCount(xPDOQuery $c)
	{
		$c->leftJoin('msOrder', 'msOrder');
		$c->leftJoin('modUser', 'User', 'msOrder.user_id = User.id');
		$c->leftJoin('modUserProfile', 'UserProfile', 'msOrder.user_id = UserProfile.id');
		$c->leftJoin('slOrderStatus', 'Status');
		$c->leftJoin('msDelivery', 'Delivery', 'msOrder.delivery = Delivery.id');
		$c->leftJoin('msPayment', 'Payment', 'msOrder.payment = Payment.id');
		$c->leftJoin('slStores', 'Store');
		$c->leftJoin('slWarehouse', 'Warehouse');

		$query = trim($this->getProperty('query'));
		if (!empty($query)) {
			if (is_numeric($query)) {
				$c->andCondition(array(
					'id' => $query,
					//'OR:User.id' => $query,
				));
			} else {
				$c->where(array(
					'num:LIKE' => "{$query}%",
					'OR:comment:LIKE' => "%{$query}%",
					'OR:User.username:LIKE' => "%{$query}%",
					'OR:UserProfile.fullname:LIKE' => "%{$query}%",
					'OR:UserProfile.email:LIKE' => "%{$query}%",
				));
			}
		}
		if ($status = $this->getProperty('status')) {
			$c->where(array(
				'status' => $status,
			));
		}
		if ($customer = $this->getProperty('customer')) {
			$c->where(array(
				'msOrder.user_id' => (int)$customer,
			));
		}
		if ($context = $this->getProperty('context')) {
			$c->where(array(
				'msOrder.context' => $context,
			));
		}
		if ($date_start = $this->getProperty('date_start')) {
			$c->andCondition(array(
				'createdon:>=' => date('Y-m-d 00:00:00', strtotime($date_start)),
			), null, 1);
		}
		if ($date_end = $this->getProperty('date_end')) {
			$c->andCondition(array(
				'createdon:<=' => date('Y-m-d 23:59:59', strtotime($date_end)),
			), null, 1);
		}

		$this->query = clone $c;

		$c->select(
			$this->modx->getSelectColumns('slOrder', 'slOrder', '', array('delivery', 'payment'), true) . ',
            msOrder.delivery as delivery_id, msOrder.payment as payment_id, msOrder.user_id as user_id,
            UserProfile.fullname as customer, User.username as customer_username,
            Status.name as status_name, Status.color as color, Delivery.name as delivery, Payment.name as payment, 
            Warehouse.name as warehouse_name, Store.name as store_name'
		);
		$c->groupby($this->classKey . '.id');
		$c->prepare();
		$this->modx->log(1, $c->toSQL());

		return $c;
	}

	/**
	 * @param array $data
	 *
	 * @return array
	 */
	public function prepareArray(array $data)
	{
		if (empty($data['customer'])) {
			$data['customer'] = $data['customer_username'];
		}
		if (isset($data['cost'])) {
			$data['cost'] = $this->shoplogistic->formatPrice($data['cost']);
		}
		if (isset($data['cart_cost'])) {
			$data['cart_cost'] = $this->shoplogistic->formatPrice($data['cart_cost']);
		}
		if (isset($data['delivery_cost'])) {
			$data['delivery_cost'] = $this->shoplogistic->formatPrice($data['delivery_cost']);
		}
		if (isset($data['weight'])) {
			$data['weight'] = $this->shoplogistic->formatWeight($data['weight']);
		}

		$data['actions'] = array(
			array(
				'cls' => '',
				'icon' => 'icon icon-edit',
				'title' => $this->modx->lexicon('shoplogistic_menu_update'),
				'action' => 'updateOrder',
				'button' => true,
				'menu' => true,
			),
			array(
				'cls' => array(
					'menu' => 'red',
					'button' => 'red',
				),
				'icon' => 'icon icon-trash-o',
				'title' => $this->modx->lexicon('shoplogistic_menu_remove'),
				'multiple' => $this->modx->lexicon('shoplogistic_menu_remove_multiple'),
				'action' => 'removeOrder',
				'button' => true,
				'menu' => true,
			),
			/*
			array(
				'cls' => '',
				'icon' => 'icon icon-cog actions-menu',
				'menu' => false,
				'button' => true,
				'action' => 'showMenu',
				'type' => 'menu',
			),
			*/
		);

		return $data;
	}
}

return 'slOrderGetListProcessor';