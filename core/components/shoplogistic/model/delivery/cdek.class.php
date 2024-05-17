<?php
    class cdek{

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
            ], $config);

            if($this->modx->getOption("shoplogistic_cdek_test_mode")){
                $this->config['account'] = $this->modx->getOption("shoplogistic_cdek_test_account");
                $this->config['password'] = $this->modx->getOption("shoplogistic_cdek_test_pass");
                $this->config['url'] = $this->modx->getOption("shoplogistic_cdek_test_url");
            }else{
                $this->config['account'] = $this->modx->getOption("shoplogistic_cdek_account");
                $this->config['password'] = $this->modx->getOption("shoplogistic_cdek_pass");
                $this->config['url'] = $this->modx->getOption("shoplogistic_cdek_url");
            }
        }

        /**
         * Авторизация
         *
         * @return bool
         */
        public function auth(){
            $data = array(
                "grant_type" => "client_credentials",
                "client_id" => $this->config['account'],
                "client_secret" => $this->config['password']
            );
            $response = $this->request("oauth/token?parameters", $data);
            if($response["access_token"] && $response["expires_in"]){
                $this->setToken($response["access_token"], $response["expires_in"]);
                return true;
            }else{
                // уведомление в телегу
                $json = json_encode($response, JSON_UNESCAPED_UNICODE);
                $this->sl->darttelegram->sendMessage("PROGGER", "СДЭК ошибка: <pre language=\"json\">".$json."</pre>");
                return false;
            }
            return $response;
        }

        /**
         * Подготовка параметров товара для расчета
         * $products - массив с ID товаров
         *
         * @param $products
         * @return array
         */
        public function prepareProducts($products){
            $packages = array();
            $this->modx->log(1, print_r($products, 1));
            foreach($products as $product){
                $product_data = $this->sl->cart->getProductParams($product)[0];
                if($product_data){
                    $package = array(
                        "weight" => intval($product_data['product']['weight_brutto'] * 1000),
                        "length" => intval($product_data['product']['length']),
                        "width" => intval($product_data['product']['width']),
                        "height" => intval($product_data['product']['height']),
                    );
                    $packages[] = $package;
                }
            }
            return $packages;
        }

        /**
         * Калькулятор стоимости доставки
         * $products - список ID товара и кол-во
         *
         * @param $from
         * @param $to
         * @param $products
         * @return array
         */
        public function getCalcPrice($from, $to, $products){
            if(!$this->checkToken()){
                $this->auth();
            }
            $codes = array();
            $products_data = $this->prepareProducts($products);
            $data = array(
                'type' => 1,
                'from_location' => array(
                    'postal_code' => $from
                ),
                'to_location' => array(
                    'postal_code' => $to
                ),
                'packages' => $products_data
            );
            $this->log($data);
            $response = $this->request("calculator/tarifflist", $data, 'POST', 'json');
            $this->log($response);
            foreach($response['tariff_codes'] as $code){
                // Приоритет Дверь - Склад и Дверь - Дверь https://api-docs.cdek.ru/63345519.html -> Приложение 2
                $available_codes = array(139, 137);
                if(in_array($code['tariff_code'], $available_codes)){
                    if($code['tariff_code'] == 139){
                        $codes['terminal'] = array(
                            "price" => $code['delivery_sum'],
                            "time" => $code["calendar_max"]
                        );
                    }
                    if($code['tariff_code'] == 137) {
                        $codes['door'] = array(
                            "price" => $code['delivery_sum'],
                            "time" => $code["calendar_max"]
                        );
                    }
                }
                $trmnls = array();
                $terminals = $this->getOffices($to);
                foreach($terminals as $terminal){
                    $phs = array();
                    if(count($terminal["phones"])){
                        foreach($terminal["phones"] as $phone){
                            $phs[] = $phone["number"];
                        }
                    }
                    $tmp = array(
                        "code" => $terminal["code"],
                        "lat" => $terminal["location"]["latitude"],
                        "lon" => $terminal["location"]["longitude"],
                        "address" => $terminal["location"]["address_full"],
                        "image" => "/assets/files/img/cdek.svg",
                        "phones" => implode(", ", $phs),
                        "workTime" => $terminal["location"]["work_time"]
                    );
                    $trmnls[] = $tmp;
                }
                $codes['terminals'] = $trmnls;
            }
            return $codes;
        }

        /**
         * Берем список офисов по индексу города
         *
         * @param $postal_code
         * @return array
         */
        public function getOffices($postal_code){
            if(!$this->checkToken()){
                $this->auth();
            }
            $data = array(
                "postal_code" => $postal_code,
                "type" => "PVZ"
            );
            $response = $this->request("deliverypoints", $data, "GET");
            return $response;
        }

        /**
         * Запрос к API СДЭК
         *
         * @param $action
         * @param $data
         * @param $type
         * @param $data_type
         * @return array
         */
        protected function request($action, $data, $type = "POST", $data_type = 'form_data'){
            $out = array();
            $url = $this->config['url'].$action;
            if($type == "GET"){
                $url = $url.'?'.http_build_query($data, '', '&');
            }
            if( $curl = curl_init() ) {
                if($data_type == 'json'){
                    $headers = array('Content-Type:application/json');
                }else{
                    $headers = array('Content-Type: application/x-www-form-urlencoded');
                }
                curl_setopt($curl, CURLOPT_URL, $url);
                curl_setopt($curl, CURLOPT_RETURNTRANSFER,true);

                curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($curl, CURLOPT_HEADER, false);
                if($type == "POST"){
                    curl_setopt($curl, CURLOPT_POST, true);
                    if($data_type == 'json'){
                        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
                    }else{
                        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($data, '', '&'));
                    }
                }
                if($this->checkToken() || $action != 'oauth/token?parameters'){
                    $headers[] = "Authorization: Bearer ".$this->modx->getOption("shoplogistic_cdek_token");
                }else{
                    // $this->auth();
                }
                curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
                $out = curl_exec($curl);
                curl_close($curl);
            }
            $response_data = json_decode($out, 1);
            return $response_data;
        }

        /**
         * Проверка актуальности токена
         *
         * @return bool
         */
        public function checkToken(){
            $expired = $this->modx->getOption("shoplogistic_cdek_token_expired_in");
            $token = $this->modx->getOption("shoplogistic_cdek_token");
            if($token){
                if(intval($expired) < time()){
                    return false;
                }else{
                    return true;
                }
            }else{
                return false;
            }
        }

        /**
         * Установка токена
         *
         * @param $access_token
         * @param $expires_in
         * @return void
         */
        protected function setToken($access_token, $expires_in){
            $this->updateSetting("shoplogistic_cdek_token", $access_token);
            $time = time() + intval($expires_in);
            $this->updateSetting("shoplogistic_cdek_token_expired_in", $time);
            //Чистим кеш
            $this->modx->cacheManager->refresh(array('system_settings' => array()));
        }

        /**
         * General method to update settings
         *
         * @param $key
         * @param $value
         */
        protected function updateSetting($key, $value)
        {
            $setting = $this->modx->getObject('modSystemSetting', ['key' => $key]);
            if (!$setting) {
                $setting = $this->modx->newObject('modSystemSetting');
                $setting->set('key', $key);
            }
            $setting->set('value', $value);
            $setting->save();
        }

        public function log($data, $file = 'delivery_cdek'){
            $this->modx->log(xPDO::LOG_LEVEL_ERROR, print_r($data, 1), array(
                'target' => 'FILE',
                'options' => array(
                    'filename' => $file.'.log'
                )
            ));
        }
    }