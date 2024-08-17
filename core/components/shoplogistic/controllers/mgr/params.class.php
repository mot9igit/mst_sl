<?php

if (!class_exists('slManagerController')) {
    require_once dirname(__FILE__, 2) . '/manager.class.php';
}

class ShoplogisticMgrParamsManagerController extends slManagerController
{
    /**
     * @return string
     */
    public function getPageTitle()
    {
        return $this->modx->lexicon('shoplogistic_params') . ' | shoplogistic';
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
        $this->addJavascript($this->shopLogistic->config['jsUrl'] . 'mgr/params/settings/grid.js?v='.$this->shopLogistic->config['version']);
        $this->addJavascript($this->shopLogistic->config['jsUrl'] . 'mgr/params/settings/windows.js?v='.$this->shopLogistic->config['version']);

        $this->addJavascript($this->shopLogistic->config['jsUrl'] . 'mgr/params/groups/grid.js?v='.$this->shopLogistic->config['version']);
        $this->addJavascript($this->shopLogistic->config['jsUrl'] . 'mgr/params/groups/windows.js?v='.$this->shopLogistic->config['version']);

        $this->addJavascript($this->shopLogistic->config['jsUrl'] . 'mgr/params/params.panel.js?v='.$this->shopLogistic->config['version']);
        $this->addJavascript($this->shopLogistic->config['jsUrl'] . 'mgr/params/params.js?v='.$this->shopLogistic->config['version']);

        $this->addHtml('<script type="text/javascript">
			shopLogistic.config = ' . json_encode($this->shopLogistic->config) . ';
			shopLogistic.config.connector_url = "' . $this->shopLogistic->config['connectorUrl'] . '";
			Ext.onReady(function() {MODx.load({ xtype: "shoplogistic-page-params"});});
        </script>');

        $this->shopLogistic->loadServices();

        $this->modx->invokeEvent('slOnManagerCustomCssJs', array(
            'controller' => $this,
            'page' => 'params',
        ));
    }
}