<?php

class slOrderProductCreateProcessor extends modObjectCreateProcessor
{
	public $classKey = 'slOrderProduct';
	public $objectType = 'slOrderProduct';
	public $languageTopics = array('shoplogistic');
	public $beforeSaveEvent = 'slOnBeforeCreateOrderProduct';
	public $afterSaveEvent = 'slOnBeforeCreateOrderProduct';
	//public $permission = 'msorder_save';
	/** @var msOrder $order */
	protected $order;


	/**
	 * @return bool|null|string
	 */
	public function initialize()
	{
		if (!$this->modx->hasPermission($this->permission)) {
			return $this->modx->lexicon('access_denied');
		}

		return parent::initialize();
	}


	/**
	 * @return bool|null|string
	 */
	public function beforeSet()
	{
		$count = $this->getProperty('count');
		if ($count <= 0) {
			$this->modx->error->addField('count', $this->modx->lexicon('shoplogistic_err_ns'));
		}

		if ($options = $this->getProperty('options')) {
			$tmp = json_decode($options, true);
			if (!is_array($tmp)) {
				$this->modx->error->addField('options', $this->modx->lexicon('shoplogistic_err_json'));
			} else {
				$this->setProperty('options', $tmp);
			}
		}

		if (!$this->order = $this->modx->getObject('slOrder', array('id' => $this->getProperty('order_id')))) {
			return $this->modx->lexicon('shoplogistic_err_order_nf');
		}

		/** @var msOrderStatus $status */
		if ($status = $this->order->getOne('Status')) {
			if ($status->get('final')) {
				return $this->modx->lexicon('shoplogistic_err_status_final');
			}
		}

		$this->setProperty('cost', $this->getProperty('price') * $this->getProperty('count'));
		$this->setProperty('product_id', $this->getProperty('id'));

		return !$this->hasErrors();
	}


	/**
	 * @return bool
	 */
	public function beforeSave()
	{
		$this->object->fromArray(array(
			'rank' => $this->modx->getCount('slOrderProduct'),
		));

		return parent::beforeSave();
	}


	/**
	 *
	 */
	public function afterSave()
	{
		// Fix "cache"
		if ($this->order = $this->modx->getObject('slOrder', $this->order->id, false)) {
			$this->order->updateProducts();
		}
	}
}

return 'slOrderProductCreateProcessor';