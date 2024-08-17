<?php

class slAPIRequestHistoryGetListProcessor extends modObjectGetListProcessor
{
    public $objectType = 'slAPIRequestHistory';
    public $classKey = 'slAPIRequestHistory';
    public $defaultSortField = 'id';
    public $defaultSortDirection = 'DESC';
    //public $permission = 'list';


    /**
     * We do a special check of permissions
     * because our objects is not an instances of modAccessibleObject
     *
     * @return boolean|string
     */
    public function beforeQuery()
    {
        if (!$this->checkPermissions()) {
            return $this->modx->lexicon('access_denied');
        }

        return true;
    }


    /**
     * @param xPDOQuery $c
     *
     * @return xPDOQuery
     */
    public function prepareQueryBeforeCount(xPDOQuery $c)
    {
        $query = trim($this->getProperty('query'));
        $store_id = trim($this->getProperty('store_id'));

        $c->leftJoin('slAPIRequestStatus', 'slAPIRequestStatus', '`slAPIRequestStatus`.`id` = `slAPIRequestHistory`.`status`');
        $c->leftJoin('slStores', 'slStores', '`slStores`.`id` = `slAPIRequestHistory`.`store_id`');

        if ($query) {
            $c->where([
                'slAPIRequestHistory.method:LIKE' => "%{$query}%",
                'OR:slAPIRequestHistory.description:LIKE' => "%{$query}%"
            ]);
        }

        if($store_id){
            $c->where([
                'store_id:=' => $store_id,
            ]);
        }

        $c->select(
            $this->modx->getSelectColumns('slAPIRequestHistory', 'slAPIRequestHistory', '', array(), true) . ',
            slAPIRequestStatus.name as status_name, slAPIRequestStatus.color as color, COALESCE(slStores.name, "Нет") as store_name'
        );

        return $c;
    }


    /**
     * @param xPDOObject $object
     *
     * @return array
     */
    public function prepareRow(xPDOObject $object)
    {
        $array = $object->toArray();
        $array['actions'] = [];

        $array["request_content"] = '';
        if($array['request']){
            $contents = '';
            $path = $this->modx->getOption("base_path").$array['request'];
            $zip = new ZipArchive();
            if(file_exists($path)){
                if($zip->open($path)){
                    $file = basename($path, ".zip");
                    $fp = $zip->getStream($file.'.json');
                    if($fp) {
                        while (!feof($fp)) {
                            $contents .= fread($fp, 2);
                        }
                        $array["request_content"] = json_encode(json_decode($contents), JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE);
                    }
                }
            }
        }

        $array["response_content"] = '';
        if($array['response']){
            $contents = '';
            $path = $this->modx->getOption("base_path").$array['response'];
            $zip = new ZipArchive();
            if(file_exists($path)){
                if($zip->open($path)){
                    $file = basename($path, ".zip");
                    $fp = $zip->getStream($file.'.json');
                    if($fp) {
                        while (!feof($fp)) {
                            $contents .= fread($fp, 2);
                        }
                        $array["response_content"] = json_encode(json_decode($contents, 1), JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE);
                    }
                }
            }
        }


        if($array['file']){
            // Edit
            $array['actions'][] = [
                'cls' => '',
                'icon' => 'icon icon-download',
                'title' => $this->modx->lexicon('shoplogistic_download'),
                'action' => 'downloadAPIRequest',
                'button' => true,
                'menu' => true,
            ];
        }

        // Edit
        $array['actions'][] = [
            'cls' => '',
            'icon' => 'icon icon-edit',
            'title' => $this->modx->lexicon('shoplogistic_apirequest_update'),
            //'multiple' => $this->modx->lexicon('shoplogistic_items_update'),
            'action' => 'updateAPIRequest',
            'button' => true,
            'menu' => true,
        ];

        // Remove
        $array['actions'][] = [
            'cls' => '',
            'icon' => 'icon icon-trash-o action-red',
            'title' => $this->modx->lexicon('shoplogistic_apirequest_remove'),
            'multiple' => $this->modx->lexicon('shoplogistic_apirequests_remove'),
            'action' => 'removeAPIRequest',
            'button' => true,
            'menu' => true,
        ];

        return $array;
    }

}

return 'slAPIRequestHistoryGetListProcessor';