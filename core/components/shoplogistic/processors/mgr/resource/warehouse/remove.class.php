<?php

class slWarehouseRemainsRemoveProcessor extends modObjectProcessor
{
	public $objectType = 'slWarehouseRemains';
	public $classKey = 'slWarehouseRemains';
	public $languageTopics = ['shoplogistic'];
	//public $permission = 'remove';


	/**
	 * @return array|string
	 */
	public function process()
	{
		if (!$this->checkPermissions()) {
			return $this->failure($this->modx->lexicon('access_denied'));
		}

		$ids = $this->modx->fromJSON($this->getProperty('ids'));
		if (!$ids) {
			return $this->failure($this->modx->lexicon('error'));
		}

		foreach ($ids as $id) {
			if (!$object = $this->modx->getObject($this->classKey, $id)) {
				return $this->failure($this->modx->lexicon('error'));
			}

			$object->remove();
		}

		return $this->success();
	}

}

return 'slWarehouseRemainsRemoveProcessor';