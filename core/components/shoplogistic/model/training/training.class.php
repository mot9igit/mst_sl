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
            case 'set/training':
                $response = $this->setTraining($properties);
                break;
        }
        return $response;
    }

    //Устанавливаем в юзера просмотренное видео-обучение
    public function setTraining($properties){
        $user_id = $_SESSION['analytics_user']['profile']['id'];
        $user = $this->modx->getObject("modUser", $user_id);
        if ($profile = $user->getOne('Profile')) {
            // Получаем специальное поле extended
            $extended = $profile->get('extended');

            // Добавляем новое значение
            $extended['training'][$properties['page']] = true;
            $profile->set('extended', $extended);
            $profile->save();
        }
        return true;
    }

    public function getCatalog($properties){
        $result = array();
        $data = $this->modx->runSnippet('pdoMenu', array(
            "parents" => 24816,
            "level" => 2,
            "includeTVs" => "route_link, video_iframe",
            "processTVs" => 1,
            "return" => "data",
            "context" => "web",
            "includeContent" => 1
        ));
        $i = 0;
        foreach ($data as $key => $value) {
            $data[$key]['index'] = $i;

            $user_id = $_SESSION['analytics_user']['profile']['id'];
            $user = $this->modx->getObject("modUser", $user_id);
            if ($profile = $user->getOne('Profile')) {
                // Получаем специальное поле extended
                $extended = $profile->get('extended');
                $training = $extended['training'];

                // Добавляем новое значение
//                $extended['teech'][] = 'mydata';
//                // И сохраняем обратно в профиль
//                $profile->set('extended', $extended);
//                $profile->save();
            }
            $result['training'] = $extended['training'];

            if($data[$key]['route_link'] != ""){
                $data[$key]['route_link'] = str_replace(' ', '', $data[$key]['route_link']);
                $data[$key]['route_link'] = explode(',', $data[$key]['route_link']);

                $data[$key]['route_link'] = array_filter($data[$key]['route_link'], function($value) {
                    return $value !== null && $value !== '';
                });

                foreach ($data[$key]['route_link'] as $kr => $vr){
                    if($vr == $properties['page']){
                        $result['index1'] = $i;

                        if(!$training[$properties['page']]){
                            $result['video'] = $data[$key]['video_iframe'];
                        }
                    }
                }
            }

            $j = 0;
            if($value['children']){
                foreach ($value['children'] as $k => $v) {
                    $data[$key]['children'][$k]['index1'] = $j;

                    if($data[$key]['children'][$k]['route_link'] != "") {
                        $data[$key]['children'][$k]['route_link'] = str_replace(' ', '', $data[$key]['children'][$k]['route_link']);
                        $data[$key]['children'][$k]['route_link'] = explode(',', $data[$key]['children'][$k]['route_link']);

                        $data[$key]['children'][$k]['route_link'] = array_filter($data[$key]['children'][$k]['route_link'], function($value) {
                            return $value !== null && $value !== '';
                        });

                        foreach ($data[$key]['children'][$k]['route_link'] as $keyr => $valuer) {
                            if ($valuer == $properties['page']) {
                                $result['index1'] = $i;
                                $result['index2'] = $data[$key]['children'][$k]['id'];
                                if(!$training[$properties['page']]){
                                    $result['video'] = $data[$key]['children'][$k]['video_iframe'];
                                }
                            }
                        }
                    }

                    $j++;
                }
            }
            $i++;
        }
        $result['items'] = $data;
        return $result;
    }
}