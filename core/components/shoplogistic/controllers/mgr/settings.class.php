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
        $this->activateRTE();
		$this->addCss($this->shopLogistic->config['cssUrl'] . 'mgr/shoplogistic.css?v='.$this->shopLogistic->config['version']);
		$this->addJavascript($this->shopLogistic->config['jsUrl'] . 'mgr/shoplogistic.js?v='.$this->shopLogistic->config['version']);
		$this->addJavascript($this->shopLogistic->config['jsUrl'] . 'mgr/misc/utils.js?v='.$this->shopLogistic->config['version']);
		$this->addJavascript($this->shopLogistic->config['jsUrl'] . 'mgr/misc/combo.js?v='.$this->shopLogistic->config['version']);
		$this->addJavascript($this->shopLogistic->config['jsUrl'] . 'mgr/misc/default.window.js?v='.$this->shopLogistic->config['version']);
		$this->addJavascript($this->shopLogistic->config['jsUrl'] . 'mgr/misc/default.grid.js?v='.$this->shopLogistic->config['version']);
        $this->addJavascript($this->shopLogistic->config['jsUrl'] . 'mgr/settings/association/grid.js');
        $this->addJavascript($this->shopLogistic->config['jsUrl'] . 'mgr/settings/association/window.js');
		$this->addJavascript($this->shopLogistic->config['jsUrl'] . 'mgr/widgets/delivery/grid.js');
		$this->addJavascript($this->shopLogistic->config['jsUrl'] . 'mgr/widgets/delivery/windows.js');
        $this->addJavascript($this->shopLogistic->config['jsUrl'] . 'mgr/widgets/connection_statuses/grid.js');
        $this->addJavascript($this->shopLogistic->config['jsUrl'] . 'mgr/widgets/connection_statuses/windows.js');
        $this->addJavascript($this->shopLogistic->config['jsUrl'] . 'mgr/widgets/shipment_status/grid.js');
        $this->addJavascript($this->shopLogistic->config['jsUrl'] . 'mgr/widgets/shipment_status/windows.js');
        $this->addJavascript($this->shopLogistic->config['jsUrl'] . 'mgr/widgets/stores_remains/status.grid.js');
        $this->addJavascript($this->shopLogistic->config['jsUrl'] . 'mgr/widgets/stores_remains/status.windows.js');
        $this->addJavascript($this->shopLogistic->config['jsUrl'] . 'mgr/reporttypes/grid.js');
        $this->addJavascript($this->shopLogistic->config['jsUrl'] . 'mgr/reporttypes/windows.js');
        $this->addJavascript($this->shopLogistic->config['jsUrl'] . 'mgr/reporttypes/fields.grid.js');
        $this->addJavascript($this->shopLogistic->config['jsUrl'] . 'mgr/reporttypes/fields.windows.js');

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

    public function activateRTE(){

        $className = 'TinyMCERTE\Plugins\Events\\OnManagerPageBeforeRender';

        $corePath = $this->modx->getOption('tinymcerte.core_path', null, $this->modx->getOption('core_path') . 'components/tinymcerte/');
        /** @var TinyMCERTE $tinymcerte */
        $tinymcerte = $this->modx->getService('tinymcerte', 'TinyMCERTE', $corePath . 'model/tinymcerte/', [
            'core_path' => $corePath
        ]);

        if ($tinymcerte) {
            if (class_exists($className)) {
                $handler = new $className($this->modx, $this->shopLogistic->config);
                if (get_class($handler) == $className) {
                    $handler->run();
                } else {
                    $this->modx->log(xPDO::LOG_LEVEL_ERROR, $className. ' could not be initialized!', '', 'TinyMCE RTE Plugin');
                }
            } else {
                $this->modx->log(xPDO::LOG_LEVEL_ERROR, $className. ' was not found!', '', 'TinyMCE RTE Plugin');
            }
        }

        return;

    }

}