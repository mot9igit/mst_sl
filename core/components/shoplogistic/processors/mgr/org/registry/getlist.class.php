<?php

class slStoreRegistryGetListProcessor extends modObjectGetListProcessor
{
    public $objectType = 'slStoreRegistry';
    public $classKey = 'slStoreRegistry';
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
		$store_id = trim($this->getProperty('org_id'));

		if($store_id){
			$c->where([
				'org_id:=' => $store_id,
			]);
		}

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

        // Download
        $array['actions'][] = [
            'cls' => '',
            'icon' => 'icon icon-download',
            'title' => $this->modx->lexicon('shoplogistic_storeregistry_download'),
            'action' => 'downloadRegistry',
            'button' => true,
            'menu' => true,
        ];

        // Remove
        $array['actions'][] = [
            'cls' => '',
            'icon' => 'icon icon-trash-o action-red',
            'title' => $this->modx->lexicon('shoplogistic_storeregistry_remove'),
            'action' => 'removeRegistry',
            'button' => true,
            'menu' => true,
        ];

        return $array;
    }

}

return 'slStoreRegistryGetListProcessor';