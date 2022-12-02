<?php

class shopLogisticOrderProcessor extends modProcessor {

	public $permission = '';

	public function process()
	{
		$data = [
			'price' => '',
			'time' => '',
			'service' => '',
			'mode' => '',
			'address' => ''
		];

		if($order_id = $this->getProperty('order_id')) {
			if($order = $this->modx->getObject('msOrder', $order_id)) {
				$properties = $order->get('properties');
				if(!empty($properties['esl'])) {
					$data = array_merge($data, $properties['esl']);
				}
				if(!empty($properties['sl'])) {
					$data = array_merge($data, $properties['sl']);
				}
			}
		}


		return $this->modx->toJSON([
			'success' => true,
			'delivery' => $data
		]);
	}

}

return 'shopLogisticOrderProcessor';