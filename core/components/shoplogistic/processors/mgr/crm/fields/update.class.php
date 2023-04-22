<?php

class slCRMFieldsUpdateProcessor extends modObjectUpdateProcessor
{
	public $objectType = 'slCRMFields';
	public $classKey = 'slCRMFields';
	public $languageTopics = ['shoplogistic'];
	//public $permission = 'save';

	/** @var  shopLogistic $shopLogistic */
	protected $shopLogistic;

	/**
	 * @return bool|null|string
	 */
	public function initialize()
	{
		$this->shopLogistic = $this->modx->getService('shopLogistic');
		$this->shopLogistic->loadServices(); // it will be "mgr"

		return parent::initialize();
	}
	/**
	 * We doing special check of permission
	 * because of our objects is not an instances of modAccessibleObject
	 *
	 * @return bool|string
	 */
	public function beforeSave()
	{
		if (!$this->checkPermissions()) {
			return $this->modx->lexicon('access_denied');
		}

		return true;
	}


	/**
	 * @return bool
	 */
	public function beforeSet()
	{
		$id = (int)$this->getProperty('id');
		if (empty($id)) {
			return $this->modx->lexicon('shoplogistic_item_err_ns');
		}
		$rdata = $this->getProperties();
		if($rdata){
			// $response = $this->shopLogistic->b24->updateField($rdata);
		}
		return parent::beforeSet();
	}
}

return 'slCRMFieldsUpdateProcessor';