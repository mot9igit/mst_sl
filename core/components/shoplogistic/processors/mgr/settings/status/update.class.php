<?php

class slOrderStatusUpdateProcessor extends modObjectUpdateProcessor
{
	/** @var msOrderStatus $object */
	public $object;
	public $classKey = 'slOrderStatus';
	public $languageTopics = array('shoplogistic');
	//public $permission = 'mssetting_save';


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
	 * @return bool
	 */
	public function beforeSet()
	{
		$required = array('name');
		foreach ($required as $field) {
			if (!$tmp = trim($this->getProperty($field))) {
				$this->addFieldError($field, $this->modx->lexicon('shoplogistic_field_required'));
			} else {
				$this->setProperty($field, $tmp);
			}
		}
		$name = $this->getProperty('name');
		if ($this->modx->getCount($this->classKey, array('name' => $name, 'id:!=' => $this->object->id))) {
			$this->modx->error->addField('name', $this->modx->lexicon('shoplogistic_err_ae'));
		}

		return !$this->hasErrors();
	}
}

return 'slOrderStatusUpdateProcessor';