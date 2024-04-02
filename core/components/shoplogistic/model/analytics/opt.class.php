<?php
class optAnalyticsHandler
{
    public $modx;
    public $sl;
    public $config;

    public function __construct(shopLogistic &$sl, modX &$modx)
    {
        $this->sl =& $sl;
        $this->modx =& $modx;
        $this->modx->lexicon->load('shoplogistic:default');
        // link ms2
        if (is_dir($this->modx->getOption('core_path') . 'components/minishop2/model/minishop2/')) {
            $ctx = 'web';
            $this->ms2 = $this->modx->getService('miniShop2');
            if ($this->ms2 instanceof miniShop2) {
                $this->ms2->initialize($ctx);
                return true;
            }
        }
    }

    /**
     * @param $action
     * @param $properties
     * @return mixed
     */
    public function handlePages($action, $properties){
        switch ($action) {
            case 'get/mainpage':
                $response = $this->getMainPage();
                break;
            case 'get/catalog':
                $response = $this->getCatalog();
                break;
            case 'get/cart':
                $response = $this->getCart();
                break;
        }
        return $response;
    }

    /**
     * @return array
     */
    public function getMainPage(){
        $object = $this->modx->getObject("modResource", $this->modx->getOption("analytics_start_page"));
        if($object){
            $data = $object->toArray();
            $data["new_slider"] = $object->getTVValue("new_slider");
        }
        return $data;
    }

    public function getCatalog(){

    }

    public function getCart(){

    }
}