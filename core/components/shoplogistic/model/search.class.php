<?php

class slSearch
{

    function __construct(shopLogistic &$sl, modX &$modx, array $config = array())
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
            'searchFields' => 'resource:pagetitle,ms:article,ms:vendor_article',

            'connectorUrl' => $assetsUrl . 'connector.php',
            'assetsUrl' => $assetsUrl,
            'assetsPath' => $assetsPath,
            'cssUrl' => $assetsUrl . 'css/',
            'jsUrl' => $assetsUrl . 'js/'
        ], $config);
    }

    public function requestPrepare($request){
        return trim(mb_strtolower($request, 'UTF-8'));
    }

    public function getSearchCriteria($request){
        $where = array();
        $fields = explode(",", $this->config['searchFields']);
        foreach($fields as $field){
            $field_data = explode(":", $field);
            if($field_data[0] == 'resource'){
                $table = 'modResource';
            }
            if($field_data[0] == 'ms'){
                $table = 'Data';
            }
            if($table && isset($field_data[1])){
                $where["`{$table}`.`{$field_data[1]}`:LIKE"] = '%'.$request.'%';
            }
        }
        return $where;
    }


    public function getSearchObjects($request, $limit = 1){
        $where = array();
        if(is_array($request)) {
            foreach ($request as $o) {
                $where = array_merge($where, $this->getSearchCriteria($o));
            }
        }else{
            $where = $this->getSearchCriteria($request);
        }
        $query = $this->modx->newQuery("modResource");
        $query->leftJoin("msProductData", "Data");
        if(count($where)){
            $query->where($where, xPDOQuery::SQL_OR);
        }
        $query->select(array(
            "`modResource`.*",
            "`Data`.*"
        ));
        if($limit){
            $query->limit(1);
        }
        if ($query->prepare() && $query->stmt->execute()) {
            echo $query->toSQL();
            $rows = $query->stmt->fetchAll(PDO::FETCH_ASSOC);
            return $rows;
        }
        return false;
    }

    public function getObject($type, $id = 0, $where = array()){
        $output = array();
        $where_str = '';
        if($id){
            $where_str = '`id` = ' . $id;
        }else{
            if(count($where)){
                foreach($where as $key => $item){
                    // пока без модификаторов
                    if($where_str == ''){
                        $where_str = '`' . $key . '` = "' . $item . '"';
                    }else{
                        $where_str .= 'AND `' . $key . '` = "' . $item . '"';
                    }
                }
            }
        }
        $sql = "SELECT * FROM {$this->modx->getTableName($type)} WHERE {$where_str} LIMIT 1";
        $q = $this->modx->prepare($sql);
        $q->execute();
        $str = $q->fetch(PDO::FETCH_ASSOC);
        if($str) {
            $output = $str;
        }
        return $output;
    }

    public function addRequest($request){
        $request = $this->requestPrepare($request);
        if($request){
            $criteria = array(
                'request' => $request
            );
            $object = $this->getObject('slSearchHistory', 0, $criteria);
            if($object){
                $obj = $this->modx->getObject('slSearchHistory', $object['id']);
                if($obj){
                    $count = $object['num']++;
                    $obj->set("num", $count);
                    $obj->save();
                }
            }else{
                $obj = $this->modx->newObject('slSearchHistory');
                $obj->set("request", $request);
                $obj->set("num", 1);
                $obj->save();
            }
        }
    }

    public function getTranslitRu($request){
        $converter = array(
            'а' => 'a',    'б' => 'b',    'в' => 'v',    'г' => 'g',    'д' => 'd',
            'е' => 'e',    'ё' => 'e',    'ж' => 'zh',   'з' => 'z',    'и' => 'i',
            'й' => 'y',    'к' => 'k',    'л' => 'l',    'м' => 'm',    'н' => 'n',
            'о' => 'o',    'п' => 'p',    'р' => 'r',    'с' => 's',    'т' => 't',
            'у' => 'u',    'ф' => 'f',    'х' => 'h',    'ц' => 'c',    'ч' => 'ch',
            'ш' => 'sh',   'щ' => 'sch',  'ь' => '',     'ы' => 'y',    'ъ' => '',
            'э' => 'e',    'ю' => 'yu',   'я' => 'ya',

            'А' => 'A',    'Б' => 'B',    'В' => 'V',    'Г' => 'G',    'Д' => 'D',
            'Е' => 'E',    'Ё' => 'E',    'Ж' => 'Zh',   'З' => 'Z',    'И' => 'I',
            'Й' => 'Y',    'К' => 'K',    'Л' => 'L',    'М' => 'M',    'Н' => 'N',
            'О' => 'O',    'П' => 'P',    'Р' => 'R',    'С' => 'S',    'Т' => 'T',
            'У' => 'U',    'Ф' => 'F',    'Х' => 'H',    'Ц' => 'C',    'Ч' => 'Ch',
            'Ш' => 'Sh',   'Щ' => 'Sch',  'Ь' => '',     'Ы' => 'Y',    'Ъ' => '',
            'Э' => 'E',    'Ю' => 'Yu',   'Я' => 'Ya',
        );

        $value = strtr($request, $converter);
        return $value;
    }

    public function getTranslitEn($request){
        $converter = array(
            'a' => 'а',     'b' => 'б',     'v' => 'в',     'g' => 'г',     'd' => 'д',
            'e' => 'е',     'e' => 'ё',     'zh' => 'ж',    'z' => 'з',     'i' => 'и',
            'y' => 'й',     'k' => 'к',     'l' => 'л',     'm' => 'м',     'n' => 'н',
            'o' => 'о',     'p' => 'п',     'r' => 'р',     's' => 'с',     't' => 'т',
            'u' => 'у',     'f' => 'ф',     'h' => 'х',     'c' => 'ц',     'ch' => 'ч',
            'sh' => 'ш',    'sch' => 'щ',   'y' => 'ы',     'e' => 'э',     'yu' => 'ю',
            'ya' => 'я',

            'A' => 'А',     'B' => 'Б',     'V' => 'В',     'G' => 'Г',     'D' => 'Д',
            'E' => 'Е',     'E' => 'Ё',     'Zh' => 'Ж',    'Z' => 'З',     'I' => 'И',
            'Y' => 'Й',     'K' => 'К',     'L' => 'Л',     'M' => 'М',     'N' => 'Н',
            'O' => 'О',     'P' => 'П',     'R' => 'Р',     'S' => 'С',     'T' => 'Т',
            'U' => 'У',     'F' => 'Ф',     'H' => 'Х',     'C' => 'Ц',     'Ch' => 'Ч',
            'Sh' => 'Ш',    'Sch' => 'Щ',   'Y' => 'Ы',     'E' => 'Э',     'Yu' => 'Ю',
            'Ya' => 'Я'
        );

        $value = strtr($request, $converter);
        return $value;
    }

    public function getLoyoutRu($request){
        $converter = array(
            'f' => 'а',	',' => 'б',	'd' => 'в',	'u' => 'г',	'l' => 'д',	't' => 'е',	'`' => 'ё',
            ';' => 'ж',	'p' => 'з',	'b' => 'и',	'q' => 'й',	'r' => 'к',	'k' => 'л',	'v' => 'м',
            'y' => 'н',	'j' => 'о',	'g' => 'п',	'h' => 'р',	'c' => 'с',	'n' => 'т',	'e' => 'у',
            'a' => 'ф',	'[' => 'х',	'w' => 'ц',	'x' => 'ч',	'i' => 'ш',	'o' => 'щ',	'm' => 'ь',
            's' => 'ы',	']' => 'ъ',	"'" => "э",	'.' => 'ю',	'z' => 'я',

            'F' => 'А',	'<' => 'Б',	'D' => 'В',	'U' => 'Г',	'L' => 'Д',	'T' => 'Е',	'~' => 'Ё',
            ':' => 'Ж',	'P' => 'З',	'B' => 'И',	'Q' => 'Й',	'R' => 'К',	'K' => 'Л',	'V' => 'М',
            'Y' => 'Н',	'J' => 'О',	'G' => 'П',	'H' => 'Р',	'C' => 'С',	'N' => 'Т',	'E' => 'У',
            'A' => 'Ф',	'{' => 'Х',	'W' => 'Ц',	'X' => 'Ч',	'I' => 'Ш',	'O' => 'Щ',	'M' => 'Ь',
            'S' => 'Ы',	'}' => 'Ъ',	'"' => 'Э',	'>' => 'Ю',	'Z' => 'Я',

            '@' => '"',	'#' => '№',	'$' => ';',	'^' => ':',	'&' => '?',	'/' => '.',	'?' => ',',
        );

        $value = strtr($request, $converter);
        return $value;
    }

    public function getLoyoutEn($request){
        $converter = array(
            'а' => 'f',	'б' => ',',	'в' => 'd',	'г' => 'u',	'д' => 'l',	'е' => 't',	'ё' => '`',
            'ж' => ';',	'з' => 'p',	'и' => 'b',	'й' => 'q',	'к' => 'r',	'л' => 'k',	'м' => 'v',
            'н' => 'y',	'о' => 'j',	'п' => 'g',	'р' => 'h',	'с' => 'c',	'т' => 'n',	'у' => 'e',
            'ф' => 'a',	'х' => '[',	'ц' => 'w',	'ч' => 'x',	'ш' => 'i',	'щ' => 'o',	'ь' => 'm',
            'ы' => 's',	'ъ' => ']',	'э' => "'",	'ю' => '.',	'я' => 'z',

            'А' => 'F',	'Б' => '<',	'В' => 'D',	'Г' => 'U',	'Д' => 'L',	'Е' => 'T',	'Ё' => '~',
            'Ж' => ':',	'З' => 'P',	'И' => 'B',	'Й' => 'Q',	'К' => 'R',	'Л' => 'K',	'М' => 'V',
            'Н' => 'Y',	'О' => 'J',	'П' => 'G',	'Р' => 'H',	'С' => 'C',	'Т' => 'N',	'У' => 'E',
            'Ф' => 'A',	'Х' => '{',	'Ц' => 'W',	'Ч' => 'X',	'Ш' => 'I',	'Щ' => 'O',	'Ь' => 'M',
            'Ы' => 'S',	'Ъ' => '}',	'Э' => '"',	'Ю' => '>',	'Я' => 'Z',

            '"' => '@',	'№' => '#',	';' => '$',	':' => '^',	'?' => '&',	'.' => '/',	',' => '?',
        );

        $value = strtr($request, $converter);
        return $value;
    }

    public function search($request){
        // ищем по артикулу и наименованию товара + транслит
        $req = array(
            'default' => $request,
            'loyout_ru' => $this->getLoyoutRu($request),
            'loyout_en' => $this->getLoyoutEn($request),
            'translit_ru' => $this->getTranslitRu($request),
            'translit_en' => $this->getTranslitEn($request)
        );
        // Уникальный массив получившихся запросов
        $result = array_unique($req);
        foreach($result as $key => $res){
            $data = $this->getSearchObjects($res, 1);
            print_r($data);
        }
        return $result;
    }


}