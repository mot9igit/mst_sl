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

}