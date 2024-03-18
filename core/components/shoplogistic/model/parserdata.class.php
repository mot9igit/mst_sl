<?php

class parserdata
{
    public $modx;
    public $sl;
    public $config;

    public function __construct(shopLogistic &$sl, modX &$modx, array $config = array())
    {
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
        ], $config);

        $this->config['token'] = $this->modx->getOption("shoplogistic_parserdata_token");
        $this->config['url'] = $this->modx->getOption("shoplogistic_parserdata_url");

    }

    /**
     * Запрос информации
     *
     * @param $action
     * @param $data
     * @param $type
     * @return mixed
     */
    public function request($action, $data, $type = "POST"){
        $out = array();
        $url = $this->config['url'].$action;
        if($type == "GET"){
            $url = $url.'?'.http_build_query($data, '', '&');
        }
        if( $curl = curl_init() ) {
            $headers = array('Content-Type:application/json');
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER,true);

            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($curl, CURLOPT_HEADER, false);
            if($type == "POST"){
                curl_setopt($curl, CURLOPT_POST, true);
                curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
            }
            $headers[] = "Authorization: ".$this->config['token'];
            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
            $out = curl_exec($curl);
            curl_close($curl);
        }
        $response_data = json_decode($out, 1);
        return $response_data;
    }
}