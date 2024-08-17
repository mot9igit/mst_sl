<?php

if (!class_exists('slManagerController')) {
    require_once dirname(__FILE__, 2) . '/manager.class.php';
}

class ShoplogisticMgrQueueManagerController extends slManagerController
{
    /**
     * @return string
     */
    public function getPageTitle()
    {
        return $this->modx->lexicon('shoplogistic_queue') . ' | shoplogistic';
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

        $this->addJavascript($this->shopLogistic->config['jsUrl'] . 'mgr/queue/queue.js?v='.$this->shopLogistic->config['version']);
        $this->addJavascript($this->shopLogistic->config['jsUrl'] . 'mgr/queue/panel.js?v='.$this->shopLogistic->config['version']);
        $this->addJavascript($this->shopLogistic->config['jsUrl'] . 'mgr/queue/grid.js?v='.$this->shopLogistic->config['version']);
        $this->addJavascript($this->shopLogistic->config['jsUrl'] . 'mgr/queue/windows.js?v='.$this->shopLogistic->config['version']);
        $this->addJavascript($this->shopLogistic->config['jsUrl'] . 'mgr/queue/api_request/grid.js?v='.$this->shopLogistic->config['version']);
        $this->addJavascript($this->shopLogistic->config['jsUrl'] . 'mgr/queue/api_request/windows.js?v='.$this->shopLogistic->config['version']);

        $this->addHtml('<script type="text/javascript">
			shopLogistic.config = ' . json_encode($this->shopLogistic->config) . ';
			shopLogistic.config.connector_url = "' . $this->shopLogistic->config['connectorUrl'] . '";
			Ext.onReady(function() {MODx.load({ xtype: "shoplogistic-page-queue"});});
        </script>');

        $this->modx->invokeEvent('slOnManagerCustomCssJs', array(
            'controller' => $this,
            'page' => 'queue',
        ));
    }
}