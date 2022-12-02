<?php

if (!class_exists('slManagerController')) {
	require_once dirname(__FILE__, 2) . '/manager.class.php';
}

class ShoplogisticMgrSettingsManagerController extends slManagerController
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
		$this->addJavascript($this->shopLogistic->config['jsUrl'] . 'mgr/settings/status/grid.js');
		$this->addJavascript($this->shopLogistic->config['jsUrl'] . 'mgr/settings/status/window.js');

		$this->addJavascript($this->shopLogistic->config['jsUrl'] . 'mgr/settings/settings.panel.js');
		$this->addJavascript($this->shopLogistic->config['jsUrl'] . 'mgr/settings/settings.js');

		$this->addHtml('<script type="text/javascript">
			shopLogistic.config = ' . json_encode($this->shopLogistic->config) . ';
			shopLogistic.config.connector_url = "' . $this->shopLogistic->config['connectorUrl'] . '";
			Ext.onReady(function() {MODx.load({ xtype: "shoplogistic-page-settings"});});
        </script>');

		$this->modx->invokeEvent('slOnManagerCustomCssJs', array(
			'controller' => $this,
			'page' => 'settings',
		));
	}
}