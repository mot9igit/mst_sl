<?php

if (!class_exists('slManagerController')) {
    require_once dirname(__FILE__, 2) . '/manager.class.php';
}

class ShoplogisticMgrExportFilesManagerController extends slManagerController
{
    /**
     * @return string
     */
    public function getPageTitle()
    {
        return $this->modx->lexicon('shoplogistic_export_files') . ' | shoplogistic';
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
        $this->addJavascript($this->shopLogistic->config['jsUrl'] . 'mgr/misc/strftime-min-1.3.js?v='.$this->shopLogistic->config['version']);
        $this->addJavascript($this->shopLogistic->config['jsUrl'] . 'mgr/misc/utils.js?v='.$this->shopLogistic->config['version']);
        $this->addJavascript($this->shopLogistic->config['jsUrl'] . 'mgr/misc/combo.js?v='.$this->shopLogistic->config['version']);
        $this->addJavascript($this->shopLogistic->config['jsUrl'] . 'mgr/misc/default.window.js?v='.$this->shopLogistic->config['version']);
        $this->addJavascript($this->shopLogistic->config['jsUrl'] . 'mgr/misc/default.grid.js?v='.$this->shopLogistic->config['version']);
        $this->addJavascript($this->shopLogistic->config['jsUrl'] . 'mgr/orders/orders.grid.js?v='.$this->shopLogistic->config['version']);

        $this->addJavascript($this->shopLogistic->config['jsUrl'] . 'mgr/export_files/export_files.js?v='.$this->shopLogistic->config['version']);
        $this->addJavascript($this->shopLogistic->config['jsUrl'] . 'mgr/export_files/panel.js?v='.$this->shopLogistic->config['version']);
        $this->addJavascript($this->shopLogistic->config['jsUrl'] . 'mgr/export_files/grid.js?v='.$this->shopLogistic->config['version']);
        $this->addJavascript($this->shopLogistic->config['jsUrl'] . 'mgr/export_files/windows.js?v='.$this->shopLogistic->config['version']);
        $this->addJavascript($this->shopLogistic->config['jsUrl'] . 'mgr/export_files/status.grid.js?v='.$this->shopLogistic->config['version']);
        $this->addJavascript($this->shopLogistic->config['jsUrl'] . 'mgr/export_files/status.windows.js?v='.$this->shopLogistic->config['version']);
        $this->addJavascript($this->shopLogistic->config['jsUrl'] . 'mgr/export_files/cats.grid.js?v='.$this->shopLogistic->config['version']);
        $this->addJavascript($this->shopLogistic->config['jsUrl'] . 'mgr/export_files/cats.windows.js?v='.$this->shopLogistic->config['version']);

        $this->addHtml('<script type="text/javascript">
			shopLogistic.config = ' . json_encode($this->shopLogistic->config) . ';
			shopLogistic.config.connector_url = "' . $this->shopLogistic->config['connectorUrl'] . '";
			Ext.onReady(function() {MODx.load({ xtype: "shoplogistic-page-export-files"});});
        </script>');

        $this->modx->invokeEvent('slOnManagerCustomCssJs', array(
            'controller' => $this,
            'page' => 'export_files',
        ));
    }
}