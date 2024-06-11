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
            'message' => $this->modx->lexicon($message, $placeholders),
            'data' => $data,
        );

        return $this->sl->config['json_response']
            ? json_encode($response)
            : $response;
    }
}