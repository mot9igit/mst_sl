<?php

if (!class_exists('slManagerController')) {
	require_once dirname(__FILE__, 2) . '/manager.class.php';
}

class ShoplogisticMgrCRMManagerController extends slManagerController
{
	/**
	 * @return string
	 */
	public function getPageTitle()
	{
		return $this->modx->lexicon('ms2_settings') . ' | shoplogistic';
	}


	/**
	 * @return array
	 */
	public function getLanguageTopics()
	{
		return array('shoplogistic:default');
	}


	/**
	 *
	 */
	public function loadCustomCssJs()
	{

		$this->addCss($this->shopLogistic->config['cssUrl'] . 'mgr/shoplogistic.css?v='.$this->shopLogistic->config['version']);
		$this->addJavascript($this->shopLogistic->config['jsUrl'] . 'mgr/shoplogistic.js?v='.$this->shopLogistic->config['version']);
		$this->addJavascript($this->shopLogistic->config['jsUrl'] . 'mgr/misc/utils.js?v='.$this->shopLogistic->config['version']);
		$this->addJavascript($this->shopLogistic->config['jsUrl'] . 'mgr/misc/combo.js?v='.$this->shopLogistic->config['version']);
		$this->addJavascript($this->shopLogistic->config['jsUrl'] . 'mgr/misc/default.window.js?v='.$this->shopLogistic->config['version']);
		$this->addJavascript($this->shopLogistic->config['jsUrl'] . 'mgr/misc/default.grid.js?v='.$this->shopLogistic->config['version']);
		$this->addJavascript($this->shopLogistic->config['jsUrl'] . 'mgr/crm/category/grid.js?v='.$this->shopLogistic->config['version']);
		$this->addJavascript($this->shopLogistic->config['jsUrl'] . 'mgr/crm/category/window.js?v='.$this->shopLogistic->config['version']);
		$this->addJavascript($this->shopLogistic->config['jsUrl'] . 'mgr/crm/stage/grid.js?v='.$this->shopLogistic->config['version']);
		$this->addJavascript($this->shopLogistic->config['jsUrl'] . 'mgr/crm/stage/window.js?v='.$this->shopLogistic->config['version']);

		$this->addJavascript($this->shopLogistic->config['jsUrl'] . 'mgr/crm/products/grid.js?v='.$this->shopLogistic->config['version']);
		$this->addJavascript($this->shopLogistic->config['jsUrl'] . 'mgr/crm/products/window.js?v='.$this->shopLogistic->config['version']);
		$this->addJavascript($this->shopLogistic->config['jsUrl'] . 'mgr/crm/deal/grid.js?v='.$this->shopLogistic->config['version']);
		$this->addJavascript($this->shopLogistic->config['jsUrl'] . 'mgr/crm/deal/window.js?v='.$this->shopLogistic->config['version']);

		$this->addJavascript($this->shopLogistic->config['jsUrl'] . 'mgr/crm/crm.panel.js?v='.$this->shopLogistic->config['version']);
		$this->addJavascript($this->shopLogistic->config['jsUrl'] . 'mgr/crm/crm.js?v='.$this->shopLogistic->config['version']);

		$this->addHtml('<script type="text/javascript">
			shopLogistic.config = ' . json_encode($this->shopLogistic->config) . ';
			shopLogistic.config.connector_url = "' . $this->shopLogistic->config['connectorUrl'] . '";
			Ext.onReady(function() {MODx.load({ xtype: "shoplogistic-page-crm"});});
        </script>');

		$this->shopLogistic->loadServices();
        $this->shopLogistic->b24->initialize();

		$this->modx->invokeEvent('slOnManagerCustomCssJs', array(
			'controller' => $this,
			'page' => 'crm',
		));
	}
}