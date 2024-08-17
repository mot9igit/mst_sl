<?php
class bonusAnalyticsHandler
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
    public function handlePages($action, $properties = array())
    {
        switch ($action) {
            case 'get/sales/bonus':
                $response = $this->getSalesBonus($properties);
                break;
            case 'get/sales/targets':
                $response = $this->getSalesBonusTargets($properties);
                break;
        }
        return $response;
    }

    /**
     * Берём продажи в магазине за периуд с бонусами и без
     */
    public function getSalesBonus($properties){
        if($properties['id']){
            $result = [];


            $query = $this->modx->newQuery("slStoreDocs");
            $query->where(array(
                "slStoreDocs.store_id:=" => $properties['id']
            ));
            if($properties['period']){
                if($properties['period'] == "month"){
                    $query->where(array(
                        "slStoreDocs.date:>=" => date('Y-m-01')
                    ));

                    $result = $this->date_range(date('Y-m-01'), date('Y-m-d'));

                } elseif ($properties['period'] == "week") {
                    $query->where(array(
                        "slStoreDocs.date:>=" => date("Y-m-d", strtotime('monday this week', strtotime(date('Y-m-d',time()))))
                    ));

                    $result = $this->date_range(date("Y-m-d", strtotime('monday this week', strtotime(date('Y-m-d',time())))), date('Y-m-d'));
                } elseif ($properties['period'] == "3month") {
                    $Date = new DateTime(date('Y-m-d',time()));
                    $shift = -3;

                    //  сохраним день
                    $day = $Date->format('d');
                    // первый день целевого месяца
                    $Date->modify('first day of this month')->modify(($shift > 0 ? '+':'') . $shift . ' months');
                    // если наш день больше числа дней в месяце, возьмем последний
                    $day = $day > $Date->format('t') ? $Date->format('t') : $day;
                    $data_from = $Date->modify('+' . $day-1 . ' days')->format('c');

                    $query->where(array(
                        "slStoreDocs.date:>=" => $data_from
                    ));

                    $result = $this->date_range($data_from, date('Y-m-d'));

                } elseif ($properties['period'] == "day") {
                    $query->where(array(
                        "slStoreDocs.date:>=" => date('Y-m-d')
                    ));

                    $result = $this->date_range(date('Y-m-d'), date('Y-m-d'));
                }
            }
            $query->select(array("slStoreDocs.*"));

            $data['test'] = $result;
            $data['info']['sales'] = $this->modx->getCount("slStoreDocs", $query);

            if($query->prepare() && $query->stmt->execute()){
                $docs = $query->stmt->fetchAll(PDO::FETCH_ASSOC);

                foreach ($docs as $k => $doc){
                    $timestamp = strtotime($doc['date']);

                    if(!$doc['phone']){
                        $result[date('Y-m-d', $timestamp)]['sales'] = $result[date('Y-m-d', $timestamp)]['sales'] + 1;
                    } else{
                        $result[date('Y-m-d', $timestamp)]['sales_bonus'] = $result[date('Y-m-d', $timestamp)]['sales_bonus'] + 1;
                    }
                }

                $data["result"] = $result;
                foreach ($result as $k => $res){
                    $date = new DateTime($k);
                    $data["chart"]['dates'][] = $date->Format('d.m.y');
                    $data["chart"]['sales'][] = $res['sales'];
                    $data["chart"]['sales_bonus'][] = $res['sales_bonus'];
                }

                $data["docs"] = $docs;
            }


            $query->where(array(
                "slStoreDocs.phone:!=" => "",
                "AND:slStoreDocs.phone:!=" => null,
            ));

            $data['info']['sales_bonus'] = $this->modx->getCount("slStoreDocs", $query);

            if($data['info']['sales'] && $data['info']['sales_bonus']){
                $data['info']['percent'] = round(100 - ($data['info']['sales'] - $data['info']['sales_bonus']) / ($data['info']['sales'] / 100), 2);
            } else{
                $data['info']['percent'] = 0;
            }
        }

        return $data;
    }

    /**
     * Возращает массив дат с частотой 1 день.
     * Принимает дату начала и дату окончания
     */
    function date_range($first, $last, $step = '+1 day', $output_format = 'Y-m-d' ) {

        $dates = [];
        $current = strtotime($first);
        $last = strtotime($last);

        while($current <= $last) {
            $dates[date($output_format, $current)] = array(
                "sales" => 0,
                "sales_bonus" => 0
            );
            $current = strtotime($step, $current);
        }

        return $dates;
    }

    /**
     * Берём цели по регистрации покупателей в бонусной системе
     */
    public function getSalesBonusTargets($properties) {
        if($properties['id']){
            $query = $this->modx->newQuery("slBonusMotivation");
            $query->where(array(
                "slBonusMotivation.global:=" => 1,
                "FIND_IN_SET('".$properties['id']."', REPLACE(REPLACE(REPLACE(slBonusMotivation.stores, '\"', ''), '[', ''), ']','')) > 0",
            ),xPDOQuery::SQL_OR);
            $query->select(array("slBonusMotivation.*"));


            if($query->prepare() && $query->stmt->execute()) {
                $motivations = $query->stmt->fetchAll(PDO::FETCH_ASSOC);
                $urlMain = $this->modx->getOption("site_url");

                foreach ($motivations as $key => $motivation){
                    //Берём подарок
                    $q = $this->modx->newQuery("slBonusMotivationGift");
                    $ids = str_replace('"', "", $motivation['gift_ids']);
                    $ids = str_replace('[', "", $ids);
                    $ids = str_replace(']', "", $ids);
                    $ids = explode(',', $ids);

                    $q->where(array(
                        "`slBonusMotivationGift`.`id`:IN" => $ids
                    ));
                    $q->select(array("slBonusMotivationGift.*"));

                    if($q->prepare() && $q->stmt->execute()) {
                        $motivations[$key]['gift'] = $q->stmt->fetchAll(PDO::FETCH_ASSOC);

                        foreach ($motivations[$key]['gift'] as $k => $gift){
                            $motivations[$key]['gift'][$k]['image'] = $urlMain . "assets/content/" . $gift['image'];
                        }
                    }

                    //Берём все продажи магазина в этот периуд
                    $que = $this->modx->newQuery("slStoreDocs");
                    $que->where(array(
                        "slStoreDocs.store_id:=" => $properties['id']
                    ));
                    $que->where(array(
                        "slStoreDocs.date:>=" => $motivation['date_from'],
                        "AND:slStoreDocs.date:<=" => $motivation['date_to']
                    ));
                    $que->select(array("slStoreDocs.*"));

                    $motivations[$key]['info']['sales'] = $this->modx->getCount("slStoreDocs", $que);

                    $que->where(array(
                        "slStoreDocs.phone:!=" => "",
                        "AND:slStoreDocs.phone:!=" => null,
                    ));

                    $motivations[$key]['info']['sales_bonus'] = $this->modx->getCount("slStoreDocs", $que);

                    if($motivations[$key]['info']['sales'] && $motivations[$key]['info']['sales_bonus']){
                        $motivations[$key]['info']['percent'] = round(100 - ($motivations[$key]['info']['sales'] - $motivations[$key]['info']['sales_bonus']) / ($motivations[$key]['info']['sales'] / 100), 2);
                    } else{
                        $motivations[$key]['info']['percent'] = 0;
                    }


                    if($motivation['date_to'] < date('Y-m-d')) {
                        //цель завершена
                        if($motivation['percent'] <= $motivations[$key]['info']['percent']){
                            //цель выполнена
                            $motivations[$key]['status'] = "done";
                        }else{
                            $motivations[$key]['status'] = "not_completed";
                        }
                    }else{
                        $motivations[$key]['status'] = "expectation";
                    }

                }

            }

            return $motivations;
        }
    }
}