<?php

/**
 * Класс утилит и полезных функций для облегчения кода
 */
class Tools
{

    function __construct(shopLogistic &$sl, modX &$modx, array $config = array())
    {
        $this->sl =& $sl;
        $this->modx =& $modx;
        $this->modx->lexicon->load('shoplogistic:default');
    }

    public function requestPrepare($request){
        return trim(mb_strtolower($request, 'UTF-8'));
    }
}