<?php

class slOrderProductRemoveProcessor extends modObjectRemoveProcessor
{
	public $classKey = 'slOrderProduct';
	public $objectType = 'slOrderProduct';
	public $languageTopics = array('shoplogistic');
	public $beforeRemoveEvent = 'slOnBeforeRemoveOrderProduct';
	public $afterRemoveEvent = 'slOnRemoveOrderProduct';
	//public $permission = 'msorder_save';
	/** @var msOrder $order */
	protected $order;


	/**
	 * @return bool|null|string
	 */
	public function beforeRemove()
	{
		if (!$this->order = $this->object->getOne('Order')) {
			return $this->modx->lexicon('shoplogistic_err_order_nf');
		}

		if ($status = $this->order->getOne('Status')) {
			if ($status->get('final')) {
				return $this->modx->lexicon('shoplogistic_err_status_final');
			}
		}

		$this->setProperty('cost', $this->getProperty('price') * $this->getProperty('count'));

		return !$this->hasErrors();
	}


	/**
	 *
	 */
	public function afterRemove()
	{
		// Fix "cache"
		if ($this->order = $this->modx->getObject('slOrder', $this->order->id, false)) {
			$this->order->updateProducts();
		}
	}
}

return 'slOrderProductRemoveProcessor';