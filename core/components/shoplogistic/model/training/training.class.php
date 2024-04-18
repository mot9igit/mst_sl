<?php
class trainingHandler
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
    public function handlePages($action, $properties = array()){
        switch ($action) {
            case 'get/catalog':
                $response = $this->getCatalog($properties);
                break;
        }
        return $response;
    }

    public function getCatalog($properties){
        $data = $this->modx->runSnippet('pdoMenu', array(
            "parents" => 24816,
            "level" => 2,
            "includeTVs" => "menu_image",
            "processTVs" => 1,
            "return" => "data",
            "context" => "web",
            "includeContent" => 1
        ));

        $i = 0;

        foreach ($data as $key => $value) {
            $data[$key]['index'] = $i;

            $j = 0;
            foreach ($value['children'] as $k => $v) {
                $data[$key]['children'][$k]['index'] = $j;
                $j++;
            }
            $i++;
        }

        return $data;
    }
}