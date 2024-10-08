<?php

if (!class_exists('slManagerController')) {
    require_once dirname(__FILE__, 2) . '/manager.class.php';
}

class ShoplogisticMgrBonusManagerController extends slManagerController
{
    /**
     * @return string
     */
    public function getPageTitle()
    {
        return $this->modx->lexicon('shoplogistic_bonus') . ' | shoplogistic';
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

        $this->addJavascript($this->shopLogistic->config['jsUrl'] . 'mgr/bonus/bonus.panel.js?v='.$this->shopLogistic->config['version']);
        $this->addJavascript($this->shopLogistic->config['jsUrl'] . 'mgr/bonus/bonus.js?v='.$this->shopLogistic->config['version']);

        $this->addJavascript($this->shopLogistic->config['jsUrl'] . 'mgr/bonus/gift/grid.js?v='.$this->shopLogistic->config['version']);
        $this->addJavascript($this->shopLogistic->config['jsUrl'] . 'mgr/bonus/gift/windows.js?v='.$this->shopLogistic->config['version']);

        $this->addJavascript($this->shopLogistic->config['jsUrl'] . 'mgr/bonus/motivation/grid.js?v='.$this->shopLogistic->config['version']);
        $this->addJavascript($this->shopLogistic->config['jsUrl'] . 'mgr/bonus/motivation/windows.js?v='.$this->shopLogistic->config['version']);

        $this->addHtml('<script type="text/javascript">
			shopLogistic.config = ' . json_encode($this->shopLogistic->config) . ';
			shopLogistic.config.connector_url = "' . $this->shopLogistic->config['connectorUrl'] . '";
			Ext.onReady(function() {MODx.load({ xtype: "shoplogistic-gift-bonus"});});
        </script>');

        $this->shopLogistic->loadServices();

        $this->modx->invokeEvent('slOnManagerCustomCssJs', array(
            'controller' => $this,
            'page' => 'bonus',
        ));
    }

    public function activateRTE(){

        $plugin = $this->modx->getObject('modPlugin',array('name'=>'TinyMCE'));
        $tinyPath = $this->modx->getOption('core_path').'components/tinymce/';
        $tinyProperties = $plugin->getProperties();
        require_once $tinyPath.'tinymce.class.php';
        $tiny = new TinyMCE($this->modx, $tinyProperties);

        $tinyProperties['language'] = $this->modx->getOption('cultureKey', null, $this->modx->getOption('manager_language',null,'ru'));
        $tinyProperties['cleanup'] = true;
        $tinyProperties['width'] = '100%';
        $tinyProperties['height'] = 100;

        $tinyProperties['tiny.custom_buttons1'] = 'undo,redo,separator,pastetext,search,replace,separator,cleanup,removeformat,tablecontrols,separator,modxlink,unlink,anchor,separator,image,media,separator,code';
        $tinyProperties['tiny.custom_buttons2'] = 'formatselect,separator,forecolor,backcolor,separator,bold,italic,underline,separator,strikethrough,sub,sup,separator,justifyleft,justifycenter,justifyright,justifyfull';
        $tinyProperties['tiny.custom_buttons3'] = '';

        $tiny->setProperties($tinyProperties);
        $tiny->initialize();

    }
}