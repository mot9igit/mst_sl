<?php

class slOrderStatusGetProcessor extends modObjectGetProcessor
{
	/** @var slOrderStatus $object */
	public $object;
	public $classKey = 'slOrderStatus';
	public $languageTopics = array('shoplogistic');
	//public $permission = 'mssetting_view';


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
}

return 'slOrderStatusGetProcessor';