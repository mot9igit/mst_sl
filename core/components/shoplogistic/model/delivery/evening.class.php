<?php
class evening{

    function __construct(shopLogistic &$sl, modX &$modx, array $config = array())    {
        $this->sl =& $sl;
        $this->modx =& $modx;
        $this->modx->lexicon->load('shoplogistic:default');

        $corePath = $this->modx->getOption('shoplogistic_core_path', $config, $this->modx->getOption('core_path') . 'components/shoplogistic/');
        $assetsUrl = $this->modx->getOption('shoplogistic_assets_url', $config, $this->modx->getOption('assets_url') . 'components/shoplogistic/');
        $assetsPath = $this->modx->getOption('shoplogistic_assets_path', $config, $this->modx->getOption('base_path') . 'assets/components/shoplogistic/');


        $this->config = array_merge([
            'corePath' => $corePath,
            'modelPath' => $corePath . 'model/',
            'processorsPath' => $corePath . 'processors/',

            'connectorUrl' => $assetsUrl . 'connector.php',
            'assetsUrl' => $assetsUrl,
            'assetsPath' => $assetsPath,
            'cssUrl' => $assetsUrl . 'css/',
            'jsUrl' => $assetsUrl . 'js/',
            'cities' => array(10,18,17,22)
        ], $config);

    }

    /**
     * Расчет цены
     *
     * @param $from
     * @param $to
     * @param $products
     * @return int
     */
    public function getPrice($from, $to, $products){
        return array(
            "price" => 150,
            "term" => 1
        );
    }

    public function log($data, $file = 'delivery_evening'){
        $this->modx->log(xPDO::LOG_LEVEL_ERROR, print_r($data, 1), array(
            'target' => 'FILE',
            'options' => array(
                'filename' => $file.'.log'
            )
        ));
    }
}