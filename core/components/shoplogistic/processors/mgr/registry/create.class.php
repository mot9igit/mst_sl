<?php

class slStoreRegistryCreateProcessor extends modObjectCreateProcessor
{
    public $objectType = 'slStoreRegistry';
    public $classKey = 'slStoreRegistry';
    public $languageTopics = ['shoplogistic'];
    public $permission = 'create';

	protected $shopLogistic;
	/**
	 * @return bool|null|string
	 */
	public function initialize()
	{
		$corePath = $this->modx->getOption('shoplogistic_core_path', array(), $this->modx->getOption('core_path') . 'components/shoplogistic/');
		$this->shopLogistic = $this->modx->getService('shopLogistic', 'shopLogistic', $corePath . 'model/');
		$this->shopLogistic->loadServices();

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

        return parent::beforeSet();
    }

	/**
	 * @return bool|string
	 */
	public function beforeSave()
	{
		$store_id = $this->object->get('store_id');
		$date_from = $this->object->get('date_from');
		$date_to = $this->object->get('date_to');
		$num = $this->shopLogistic->xslx->getNum();
		$this->object->set('num', $num);
		$this->object->set('file', $this->shopLogistic->xslx->generateRegistryFile($store_id, $date_from, $date_to, $num));
		$this->object->set('createdon', time());

		return parent::beforeSave();
	}

}

return 'slStoreRegistryCreateProcessor';