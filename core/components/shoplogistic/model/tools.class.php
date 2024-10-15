<?php

/**
 * Класс инструментов
 */

class slTools
{
    public $modx;
    public $sl;

    public $config;

    public function __construct(shopLogistic &$sl, modX &$modx)
    {
        $this->sl =& $sl;
        $this->modx =& $modx;
        $this->modx->lexicon->load('shoplogistic:default');
    }

    /**
     * разница между датами в днях
     *
     * @param $date_from
     * @param $date_to
     * @return false|int
     * @throws Exception
     */
    public function getDiffDates($date_from, $date_to){
        $date1 = new DateTime($date_from);
        $date2 = new DateTime($date_to);
        $interval = $date1->diff($date2);
        return $interval->days;
    }

    /**
     * Все города и регионы организации
     *
     * @param $id
     * @return array
     */

    public function getOrgCityAndRegions($id){
        $query = $this->modx->newQuery("slOrgStores");
        $query->leftJoin("slStores", "slStores", "slStores.id = slOrgStores.store_id");
        $query->leftJoin("dartLocationCity", "dartLocationCity", "dartLocationCity.id = slStores.city");
        $query->leftJoin("dartLocationRegion", "dartLocationRegion", "dartLocationRegion.id = dartLocationCity.region");
        $query->where(array(
            "slOrgStores.org_id:=" => $id
        ));
        $query->select(array(
            "slOrgStores.*",
            "dartLocationCity.id as city_id",
            "dartLocationRegion.id as region_id",
        ));
        if($query->prepare() && $query->stmt->execute()){
            $store_data = $query->stmt->fetchAll(PDO::FETCH_ASSOC);

            $result = array();

            foreach($store_data as $store){
                if($result['city_id']){
                    if($result['city'] == ""){
                        $result['city'] = $result['city'] . $store['city_id'];
                    }else{
                        $result['city'] = $result['city'] . ',' . $store['city_id'];
                    }
                }
                if($store['region_id']){
                    if($result['region'] == ""){
                        $result['region'] = $result['region'] . $store['region_id'];
                    }else{
                        $result['region'] = $result['region'] . ',' . $store['region_id'];
                    }
                }
            }

            return $result;
        }
        return array();
    }

    /**
     * Берем город. Параметр массив из DaData
     *
     * @param $address
     * @return int
     */
    public function getCity($address){
        $criteria = array(
            "fias_id:=" => $address["data"]["city_fias_id"] ? $address["data"]["city_fias_id"] : $address["data"]["settlement_fias_id"]
        );
        $city = $this->modx->getObject("dartLocationCity", $criteria);
        if($city){
            return $city->get("id");
        }else{
            $criteria = array(
                "fias_id:=" => $address["data"]["region_fias_id"]
            );
            $region = $this->modx->getObject("dartLocationRegion", $criteria);
            if($region){
                $resource = $this->modx->newObject("modResource");
                $city = $this->modx->newObject("dartLocationCity");
                $city->set("city", $address["data"]["city"] ? $address["data"]["city"] : $address["data"]["settlement"]);
                $city->set("key", $resource->cleanAlias($address["data"]["city"] ? $address["data"]["city"] : $address["data"]["settlement"]));
                $city->set("postal_code", $address["postal_code"]);
                $city->set("region", $region->get("id"));
                if($city->save()){
                    return $city->get("id");
                }
            }
        }
        return 0;
    }


    public function getStoreInfo($store_id){
        $query = $this->modx->newQuery("slStores");
        $query->leftJoin("dartLocationCity", "dartLocationCity", "dartLocationCity.id = slStores.city");
        $query->leftJoin("dartLocationRegion", "dartLocationRegion", "dartLocationRegion.id = dartLocationCity.region");
        $query->where(array(
            "slStores.id:=" => $store_id
        ));
        $query->select(array(
            "slStores.*",
            "dartLocationCity.id as city_id",
            "dartLocationRegion.id as region_id",
        ));
        if($query->prepare() && $query->stmt->execute()){
            $store_data = $query->stmt->fetch(PDO::FETCH_ASSOC);
            return $store_data;
        }
        return array();
    }

    /**
     * Форматирование цены
     *
     * @param $number
     * @param $decimals
     * @return string
     */
    public function numberFormat($number, $decimals = 0){
        return number_format($number, $decimals, ',', ' ');
    }

    /**
     * Формат номера телефона
     *
     * @param $number
     * @param $decimals
     * @return string
     */

    function phoneFormat($phone)
    {
        $phone = trim($phone);

        $res = preg_replace(
            array(
                '/[\+]?([7|8])[-|\s]?\([-|\s]?(\d{3})[-|\s]?\)[-|\s]?(\d{3})[-|\s]?(\d{2})[-|\s]?(\d{2})/',
                '/[\+]?([7|8])[-|\s]?(\d{3})[-|\s]?(\d{3})[-|\s]?(\d{2})[-|\s]?(\d{2})/',
                '/[\+]?([7|8])[-|\s]?\([-|\s]?(\d{4})[-|\s]?\)[-|\s]?(\d{2})[-|\s]?(\d{2})[-|\s]?(\d{2})/',
                '/[\+]?([7|8])[-|\s]?(\d{4})[-|\s]?(\d{2})[-|\s]?(\d{2})[-|\s]?(\d{2})/',
                '/[\+]?([7|8])[-|\s]?\([-|\s]?(\d{4})[-|\s]?\)[-|\s]?(\d{3})[-|\s]?(\d{3})/',
                '/[\+]?([7|8])[-|\s]?(\d{4})[-|\s]?(\d{3})[-|\s]?(\d{3})/',
            ),
            array(
                '+7$2$3$4$5',
                '+7$2$3$4$5',
                '+7$2$3$4$5',
                '+7$2$3$4$5',
                '+7$2$3$4',
                '+7$2$3$4',
            ),
            $phone
        );

        return $res;
    }

    /**
     * Генерация кода выдачи
     *
     */
    public function generate_code($length = 6){
        $arr = array(
            '1', '2', '3', '4', '5', '6', '7', '8', '9', '0'
        );
        $res = '';
        for ($i = 0; $i < $length; $i++) {
            $res .= $arr[random_int(0, count($arr) - 1)];
        }

        $code_live = $this->modx->getOption("shoplogistic_code_live");
        if($code_live){
            $date = new DateTime();
            $live = time() + $code_live;
            $newDate = $date->setTimestamp($live);
            // $interval = 'P1D';
            // $newDate->add(new DateInterval($interval));
            $date = $newDate->format('Y-m-d H:i:s');
        }else{
            $date = 0;
        }
        return array(
            "code" => $res,
            "date_until" => $date
        );
    }

    /**
     * Наименования с зависимости от числительного
     *
     * @param $amount
     * @param $variants
     * @param $number
     * @param $delimiter
     * @return mixed|string
     */
    public function decl($amount, $variants, $number = false, $delimiter = '|') {
        $variants = explode($delimiter, $variants);
        if (count($variants) < 2) {
            $variants = array_fill(0, 3, $variants[0]);
        } elseif (count($variants) < 3) {
            $variants[2] = $variants[1];
        }
        $modulusOneHundred = $amount % 100;
        switch ($amount % 10) {
            case 1:
                $text = $modulusOneHundred == 11
                    ? $variants[2]
                    : $variants[0];
                break;
            case 2:
            case 3:
            case 4:
                $text = ($modulusOneHundred > 10) && ($modulusOneHundred < 20)
                    ? $variants[2]
                    : $variants[1];
                break;
            default:
                $text = $variants[2];
        }

        return $number
            ? $amount . ' ' . $text
            : $text;
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

    /**
     * Берем таймзон
     *
     * @param $offset
     * @return false|string
     */
    public function getTimezone($offset){
        $string_offset = explode("+", $offset);
        $timezoneName = timezone_name_from_abbr("", $string_offset[1]*3600, false);
        return $timezoneName;
    }

    /**
     * Логгирование
     *
     * @param $data
     * @param $file
     * @return void
     */
    public function log($data, $file = 'sl_log'){
        $this->modx->log(xPDO::LOG_LEVEL_ERROR, print_r($data, 1), array(
            'target' => 'FILE',
            'options' => array(
                'filename' => $file.'.log'
            )
        ));
    }

    /**
     * Backtrace
     *
     * @return string
     */
    function backtrace(){
        $backtrace = debug_backtrace();

        $output = array();
        foreach ($backtrace as $bt) {
            $args = '';
            if(isset($bt['args'])){
                foreach ($bt['args'] as $a) {
                    if (!empty($args)) {
                        $args .= ', ';
                    }
                    switch (gettype($a)) {
                        case 'integer':
                        case 'double':
                            $args .= $a;
                            break;
                        case 'string':
                            //$a = htmlspecialchars(substr(, 0, 64)).((strlen($a) > 64) ? '...' : '');
                            $args .= "\"$a\"";
                            break;
                        case 'array':
                            $args .= 'Array('.count($a).')';
                            break;
                        case 'object':
                            $args .= 'Object('.get_class($a).')';
                            break;
                        case 'resource':
                            $args .= 'Resource('.strstr($a, '#').')';
                            break;
                        case 'boolean':
                            $args .= $a ? 'TRUE' : 'FALSE';
                            break;
                        case 'NULL':
                            $args .= 'Null';
                            break;
                        default:
                            $args .= 'Unknown';
                    }
                }
                $tmp["file"] = @$bt['file'].' - line '.@$bt['line'];
                $tmp["call"] = @$bt['class'].@$bt['type'].@$bt['function'].'('.$args.')';
                $output[] = $tmp;
            }
        }
        return $output;
    }

    /**
     * Архивируем файл или папку
     *
     * @param $file
     * @param $filename
     * @param $path
     * @return string
     */
    public function toZip($file, $filename, $path){
        $zip = new ZipArchive;
        if($zip->open($path.$filename, ZipArchive::CREATE ) === TRUE) {
            if(is_dir($file)){
                $dir = opendir($file);
                while($tmp_file = readdir($dir)) {
                    if(is_file($file.$tmp_file)) {
                        $zip->addFile($file.$tmp_file, $tmp_file);
                    }
                }
            }else{
                if(is_file($file)) {
                    $zip->addFile($file, basename($file));
                }
            }
            $zip->close();
        }
        return $path.$filename;
    }

    /**
     * Обработка изображения
     *
     * @param $url
     * @param $options
     * @return array
     */
    public function prepareImage($url, $options = "", $prefix = 1){
        $out = array();
        if($prefix) {
            $pos = strpos($url, 'assets/content/');
            if ($pos === false) {
                $url = 'assets/content/' . $url;
            }
        }
        $image = $this->modx->getOption("base_path") . $url;
        $out['image'] = $this->modx->getOption("site_url") . $url;
        if($options){
            $big_file = $this->modx->runSnippet("phpThumbOn", array(
                "input" => $image,
                "options" => $options
            ));
            $out['thumb_big'] = $this->modx->getOption("site_url") . $big_file;
            $out['files'][] = array(
                "thumb_big" => str_replace("//a", "/a", $this->modx->getOption("site_url") . $big_file),
                "url" => str_replace("//a", "/a", $this->modx->getOption("site_url") . $url)
            );
        }
        return $out;
    }

    /**
     * Берем IP посетителя
     *
     * @return mixed|string
     */
    public function get_ip(){
        $value = '';
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $value = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $value = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } elseif (!empty($_SERVER['REMOTE_ADDR'])) {
            $value = $_SERVER['REMOTE_ADDR'];
        }

        return $value;
    }

    /**
     * Отправить письмо
     *
     * @param $chunk
     * @param $data
     * @param $mails (array or string)
     * @return void
     */
    public function sendMail($chunk, $data, $mails, $subject){
        $pdo = $this->modx->getParser()->pdoTools;
        $message = $pdo->getChunk($chunk, $data);

        $this->modx->getService('mail', 'mail.modPHPMailer');
        $this->modx->mail->set(modMail::MAIL_BODY, $message);
        $this->modx->mail->set(modMail::MAIL_FROM, $this->modx->getOption("emailsender"));
        $this->modx->mail->set(modMail::MAIL_FROM_NAME, $this->modx->getOption("site_name"));
        $this->modx->mail->set(modMail::MAIL_SUBJECT, $subject);
        if(is_array($mails)){
            foreach($mails as $mail){
                $this->modx->mail->address('to',$mail);
            }
        }else{
            $this->modx->mail->address('to', $mails);
        }
        $this->modx->mail->address('reply-to', 'client.ms@yandex.ru');
        $this->modx->mail->setHTML(true);
        if (!$this->modx->mail->send()) {
            $this->modx->log(modX::LOG_LEVEL_ERROR,'An error occurred while trying to send the email: '.$this->modx->mail->mailer->ErrorInfo);
        }
        $this->modx->mail->reset();
    }

    /**
     * Сохраняем лог
     *
     * @param $data
     * @return mixed
     */
    public function saveLog($data){
        $log = $this->modx->newObject('slLog', $data);
        $log->save();
        return $log->toArray();
    }

    /**
     * This method returns an error of the order
     *
     * @param string $message A lexicon key for error message
     * @param array $data .Additional data, for example cart status
     * @param array $placeholders Array with placeholders for lexicon entry
     *
     * @return array|string $response
     */
    public function error($message = '', $data = array(), $placeholders = array())
    {
        $response = array(
            'success' => false,
            //'message' => $this->modx->lexicon($message, $placeholders),
            'message' => $message,
            'data' => $data,
        );

        return $this->sl->config['json_response']
            ? json_encode($response)
            : $response;
    }


    /**
     * This method returns an success of the order
     *
     * @param string $message A lexicon key for success message
     * @param array $data .Additional data, for example cart status
     * @param array $placeholders Array with placeholders for lexicon entry
     *
     * @return array|string $response
     */
    public function success($message = '', $data = array(), $placeholders = array())
    {
        $response = array(
            'success' => true,
            'message' => $message,
            'data' => $data,
        );

        return $this->sl->config['json_response']
            ? json_encode($response)
            : $response;
    }
}