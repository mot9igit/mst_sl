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

        $this->addJavascript($this->shopLogistic->config['jsUrl'] . 'mgr/widgets/vendors/vendors.grid.js?v='.$this->shopLogistic->config['version']);
        $this->addJavascript($this->shopLogistic->config['jsUrl'] . 'mgr/widgets/vendors/brands.grid.js?v='.$this->shopLogistic->config['version']);
        $this->addJavascript($this->shopLogistic->config['jsUrl'] . 'mgr/widgets/vendors/brands.windows.js?v='.$this->shopLogistic->config['version']);
        $this->addJavascript($this->shopLogistic->config['jsUrl'] . 'mgr/widgets/vendors/matrix.grid.js?v='.$this->shopLogistic->config['version']);
        $this->addJavascript($this->shopLogistic->config['jsUrl'] . 'mgr/widgets/vendors/matrix.windows.js?v='.$this->shopLogistic->config['version']);
        $this->addJavascript($this->shopLogistic->config['jsUrl'] . 'mgr/widgets/stores/stores.grid.js?v='.$this->shopLogistic->config['version']);
        $this->addJavascript($this->shopLogistic->config['jsUrl'] . 'mgr/widgets/stores/stores.windows.js?v='.$this->shopLogistic->config['version']);
        $this->addJavascript($this->shopLogistic->config['jsUrl'] . 'mgr/widgets/stores/remains/storeremains.grid.js?v='.$this->shopLogistic->config['version']);
        $this->addJavascript($this->shopLogistic->config['jsUrl'] . 'mgr/widgets/stores/remains/storeremains.windows.js?v='.$this->shopLogistic->config['version']);
        $this->addJavascript($this->shopLogistic->config['jsUrl'] . 'mgr/widgets/stores/remains/history/grid.js?v='.$this->shopLogistic->config['version']);
        $this->addJavascript($this->shopLogistic->config['jsUrl'] . 'mgr/widgets/stores/remains/history/windows.js?v='.$this->shopLogistic->config['version']);
        $this->addJavascript($this->shopLogistic->config['jsUrl'] . 'mgr/widgets/stores/remains/cats/grid.js?v='.$this->shopLogistic->config['version']);
        $this->addJavascript($this->shopLogistic->config['jsUrl'] . 'mgr/widgets/stores/remains/cats/windows.js?v='.$this->shopLogistic->config['version']);
		$this->addJavascript($this->shopLogistic->config['jsUrl'] . 'mgr/widgets/stores/users/storeusers.grid.js?v='.$this->shopLogistic->config['version']);
		$this->addJavascript($this->shopLogistic->config['jsUrl'] . 'mgr/widgets/stores/users/storeusers.windows.js?v='.$this->shopLogistic->config['version']);
        $this->addJavascript($this->shopLogistic->config['jsUrl'] . 'mgr/widgets/stores/settings/grid.js?v='.$this->shopLogistic->config['version']);
        $this->addJavascript($this->shopLogistic->config['jsUrl'] . 'mgr/widgets/stores/settings/windows.js?v='.$this->shopLogistic->config['version']);
        $this->addJavascript($this->shopLogistic->config['jsUrl'] . 'mgr/widgets/stores/api_requests/grid.js?v='.$this->shopLogistic->config['version']);
        $this->addJavascript($this->shopLogistic->config['jsUrl'] . 'mgr/widgets/stores/api_requests/windows.js?v='.$this->shopLogistic->config['version']);
        $this->addJavascript($this->shopLogistic->config['jsUrl'] . 'mgr/widgets/docs/grid.js?v='.$this->shopLogistic->config['version']);
        $this->addJavascript($this->shopLogistic->config['jsUrl'] . 'mgr/widgets/docs/windows.js?v='.$this->shopLogistic->config['version']);
		$this->addJavascript($this->shopLogistic->config['jsUrl'] . 'mgr/widgets/stores_remains/prices.grid.js?v='.$this->shopLogistic->config['version']);
		$this->addJavascript($this->shopLogistic->config['jsUrl'] . 'mgr/widgets/stores_remains/prices.windows.js?v='.$this->shopLogistic->config['version']);
        $this->addJavascript($this->shopLogistic->config['jsUrl'] . 'mgr/widgets/stores/stores/warehousestores.grid.js?v='.$this->shopLogistic->config['version']);
        $this->addJavascript($this->shopLogistic->config['jsUrl'] . 'mgr/widgets/stores/stores/warehousestores.windows.js?v='.$this->shopLogistic->config['version']);
		$this->addJavascript($this->shopLogistic->config['jsUrl'] . 'mgr/widgets/docs/product.grid.js?v='.$this->shopLogistic->config['version']);
        $this->addJavascript($this->shopLogistic->config['jsUrl'] . 'mgr/widgets/home.panel.js?v='.$this->shopLogistic->config['version']);
        $this->addJavascript($this->shopLogistic->config['jsUrl'] . 'mgr/sections/home.js?v='.$this->shopLogistic->config['version']);
        $this->addJavascript($this->shopLogistic->config['jsUrl'] . 'mgr/widgets/org/org.grid.js?v='.$this->shopLogistic->config['version']);
        $this->addJavascript($this->shopLogistic->config['jsUrl'] . 'mgr/widgets/org/org.windows.js?v='.$this->shopLogistic->config['version']);
        $this->addJavascript($this->shopLogistic->config['jsUrl'] . 'mgr/widgets/org/balance/storebalance.grid.js?v='.$this->shopLogistic->config['version']);
        $this->addJavascript($this->shopLogistic->config['jsUrl'] . 'mgr/widgets/org/balance/storebalance.windows.js?v='.$this->shopLogistic->config['version']);
        $this->addJavascript($this->shopLogistic->config['jsUrl'] . 'mgr/widgets/org/requizites/orgrequisites.grid.js?v='.$this->shopLogistic->config['version']);
        $this->addJavascript($this->shopLogistic->config['jsUrl'] . 'mgr/widgets/org/requizites/orgrequisites.windows.js?v='.$this->shopLogistic->config['version']);
        $this->addJavascript($this->shopLogistic->config['jsUrl'] . 'mgr/widgets/org/bank_requizites/orgrequisitesbank.grid.js?v='.$this->shopLogistic->config['version']);
        $this->addJavascript($this->shopLogistic->config['jsUrl'] . 'mgr/widgets/org/bank_requizites/orgrequisitesbank.windows.js?v='.$this->shopLogistic->config['version']);
        $this->addJavascript($this->shopLogistic->config['jsUrl'] . 'mgr/widgets/org/stores/orgstores.grid.js?v='.$this->shopLogistic->config['version']);
        $this->addJavascript($this->shopLogistic->config['jsUrl'] . 'mgr/widgets/org/stores/orgstores.windows.js?v='.$this->shopLogistic->config['version']);
        $this->addJavascript($this->shopLogistic->config['jsUrl'] . 'mgr/widgets/org/users/orgusers.grid.js?v='.$this->shopLogistic->config['version']);
        $this->addJavascript($this->shopLogistic->config['jsUrl'] . 'mgr/widgets/org/users/orgusers.windows.js?v='.$this->shopLogistic->config['version']);
        $this->addJavascript($this->shopLogistic->config['jsUrl'] . 'mgr/widgets/org/registry/registry.grid.js?v='.$this->shopLogistic->config['version']);
        $this->addJavascript($this->shopLogistic->config['jsUrl'] . 'mgr/widgets/org/registry/registry.windows.js?v='.$this->shopLogistic->config['version']);

        $this->addHtml('<script type="text/javascript">
        shopLogistic.config = ' . json_encode($this->shopLogistic->config) . ';
        shopLogistic.config.connector_url = "' . $this->shopLogistic->config['connectorUrl'] . '";
        Ext.onReady(function() {MODx.load({ xtype: "shoplogistic-page-home"});});
        </script>');

        $this->addHtml('
          <script>
          Ext.onReady(function(){
            MODx.ux.Ace.replaceTextAreas(
              Ext.query(".shoplogistic-window textarea")
            );
          });
          </script>
        ');
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