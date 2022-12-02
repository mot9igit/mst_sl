<?php

/**
 * The home manager controller for shopLogistic.
 *
 */
class shopLogisticHomeManagerController extends modExtraManagerController
{
    /** @var shopLogistic $shopLogistic */
    public $shopLogistic;


    /**
     *
     */
    public function initialize()
    {
		$corePath = $this->modx->getOption('shoplogistic_core_path', array(), $this->modx->getOption('core_path') . 'components/shoplogistic/');
		$this->shopLogistic = $this->modx->getService('shopLogistic', 'shopLogistic', $corePath . 'model/');
        parent::initialize();
    }


    /**
     * @return array
     */
    public function getLanguageTopics()
    {
        return ['shoplogistic:default'];
    }


    /**
     * @return bool
     */
    public function checkPermissions()
    {
        return true;
    }


    /**
     * @return null|string
     */
    public function getPageTitle()
    {
        return $this->modx->lexicon('shoplogistic');
    }


    /**
     * @return void
     */
    public function loadCustomCssJs()
    {
        $this->addCss($this->shopLogistic->config['cssUrl'] . 'mgr/shoplogistic.css?v='.$this->shopLogistic->config['version']);
        $this->addJavascript($this->shopLogistic->config['jsUrl'] . 'mgr/shoplogistic.js?v='.$this->shopLogistic->config['version']);
        $this->addJavascript($this->shopLogistic->config['jsUrl'] . 'mgr/misc/utils.js?v='.$this->shopLogistic->config['version']);
        $this->addJavascript($this->shopLogistic->config['jsUrl'] . 'mgr/misc/combo.js?v='.$this->shopLogistic->config['version']);
		$this->addJavascript($this->shopLogistic->config['jsUrl'] . 'mgr/misc/default.window.js?v='.$this->shopLogistic->config['version']);
		$this->addJavascript($this->shopLogistic->config['jsUrl'] . 'mgr/misc/default.grid.js?v='.$this->shopLogistic->config['version']);
        $this->addJavascript($this->shopLogistic->config['jsUrl'] . 'mgr/widgets/stores.grid.js?v='.$this->shopLogistic->config['version']);
        $this->addJavascript($this->shopLogistic->config['jsUrl'] . 'mgr/widgets/stores.windows.js?v='.$this->shopLogistic->config['version']);
		$this->addJavascript($this->shopLogistic->config['jsUrl'] . 'mgr/widgets/storeusers.grid.js?v='.$this->shopLogistic->config['version']);
		$this->addJavascript($this->shopLogistic->config['jsUrl'] . 'mgr/widgets/storeusers.windows.js?v='.$this->shopLogistic->config['version']);
		$this->addJavascript($this->shopLogistic->config['jsUrl'] . 'mgr/widgets/storeremains.grid.js?v='.$this->shopLogistic->config['version']);
		$this->addJavascript($this->shopLogistic->config['jsUrl'] . 'mgr/widgets/storeremains.windows.js?v='.$this->shopLogistic->config['version']);
		$this->addJavascript($this->shopLogistic->config['jsUrl'] . 'mgr/widgets/registry.grid.js?v='.$this->shopLogistic->config['version']);
		$this->addJavascript($this->shopLogistic->config['jsUrl'] . 'mgr/widgets/registry.windows.js?v='.$this->shopLogistic->config['version']);
		$this->addJavascript($this->shopLogistic->config['jsUrl'] . 'mgr/widgets/storebalance.grid.js?v='.$this->shopLogistic->config['version']);
		$this->addJavascript($this->shopLogistic->config['jsUrl'] . 'mgr/widgets/storebalance.windows.js?v='.$this->shopLogistic->config['version']);
		$this->addJavascript($this->shopLogistic->config['jsUrl'] . 'mgr/widgets/warehouse.grid.js?v='.$this->shopLogistic->config['version']);
		$this->addJavascript($this->shopLogistic->config['jsUrl'] . 'mgr/widgets/warehouse.windows.js?v='.$this->shopLogistic->config['version']);
		$this->addJavascript($this->shopLogistic->config['jsUrl'] . 'mgr/widgets/warehouseremains.grid.js?v='.$this->shopLogistic->config['version']);
		$this->addJavascript($this->shopLogistic->config['jsUrl'] . 'mgr/widgets/warehouseremains.windows.js?v='.$this->shopLogistic->config['version']);
		$this->addJavascript($this->shopLogistic->config['jsUrl'] . 'mgr/widgets/warehouseusers.grid.js?v='.$this->shopLogistic->config['version']);
		$this->addJavascript($this->shopLogistic->config['jsUrl'] . 'mgr/widgets/warehouseusers.windows.js?v='.$this->shopLogistic->config['version']);
		$this->addJavascript($this->shopLogistic->config['jsUrl'] . 'mgr/widgets/warehousestores.grid.js?v='.$this->shopLogistic->config['version']);
		$this->addJavascript($this->shopLogistic->config['jsUrl'] . 'mgr/widgets/warehousestores.windows.js?v='.$this->shopLogistic->config['version']);
		$this->addJavascript($this->shopLogistic->config['jsUrl'] . 'mgr/widgets/city/city.grid.js?v='.$this->shopLogistic->config['version']);
		$this->addJavascript($this->shopLogistic->config['jsUrl'] . 'mgr/widgets/city/fields.grid.js?v='.$this->shopLogistic->config['version']);
		$this->addJavascript($this->shopLogistic->config['jsUrl'] . 'mgr/widgets/city/city.windows.js?v='.$this->shopLogistic->config['version']);
		$this->addJavascript($this->shopLogistic->config['jsUrl'] . 'mgr/widgets/city/fields.windows.js?v='.$this->shopLogistic->config['version']);
        $this->addJavascript($this->shopLogistic->config['jsUrl'] . 'mgr/widgets/home.panel.js?v='.$this->shopLogistic->config['version']);
        $this->addJavascript($this->shopLogistic->config['jsUrl'] . 'mgr/sections/home.js?v='.$this->shopLogistic->config['version']);

        $this->addHtml('<script type="text/javascript">
        shopLogistic.config = ' . json_encode($this->shopLogistic->config) . ';
        shopLogistic.config.connector_url = "' . $this->shopLogistic->config['connectorUrl'] . '";
        Ext.onReady(function() {MODx.load({ xtype: "shoplogistic-page-home"});});
        </script>');
    }


    /**
     * @return string
     */
    public function getTemplateFile()
    {
        $this->content .= '<div id="shoplogistic-panel-home-div"></div>';

        return '';
    }
}