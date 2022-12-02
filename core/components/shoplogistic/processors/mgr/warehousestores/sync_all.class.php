<?php

class syncAllProcessor extends modProcessor {
	public $objectType = 'slWarehouseStores';
	public $classKey = 'slWarehouseStores';
	public $languageTopics = array('shoplogistic');

	public function initialize() {
		$warehouse = trim($this->getProperty('warehouse_id'));
		if (empty($warehouse)) {
			$this->modx->error->addField('warehouse_id', $this->modx->lexicon('shoplogistic_warehousestores_err_warehouse_id'));
		}
		return true;
	}

	/** {@inheritDoc} */
	public function process() {
		$warehouse = trim($this->getProperty('warehouse_id'));
		// берем все магазины с отмеченой синхронизацией
		$remains = $this->modx->getCollection("slWarehouseRemains", array("warehouse_id" => $warehouse));
		$stores = $this->modx->getCollection("slWarehouseStores", array("sync" => 1));
		foreach($stores as $store){
			$this->modx->log(xPDO::LOG_LEVEL_ERROR, "Обновляем остатки: ".print_r($store->toArray(), 1));
			// для начала удаляем все остатки, которые были ранее
			$this->modx->removeCollection("slStoresRemains", array("store_id" => $store->get("store_id")));
			// выставляем новые значения
			foreach($remains as $remain){
				$r = $remain->toArray();
				unset($r['warehouse_id']);
				$r["store_id"] = $store->get("store_id");
				$settings = array(
					// Здесь указываем где лежат наши процессоры (по умолчанию стандартный каталог)
					'processors_path' => $this->modx->getOption('core_path') . 'components/shoplogistic/processors/'
				);
				$response = $this->modx->runProcessor('mgr/storeremains/create', $r, $settings);
				if ($response->isError()) {
					$this->modx->log(xPDO::LOG_LEVEL_ERROR, "Ошибка синхронизации остатков: ".$response->getMessage());
				}
			}
		}
		$this->logManagerAction();
		//$this->cleanup();
		return $this->success('');
	}


	/** {@inheritDoc} */
	public function logManagerAction() {
		$this->modx->logManagerAction($this->objectType.'_sync',$this->classKey, 0);
	}


}

return 'syncAllProcessor';