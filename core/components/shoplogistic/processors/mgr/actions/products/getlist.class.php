<?php

class slActionsProductsGetListProcessor extends modObjectGetListProcessor
{
    public $objectType = 'slActionsProducts';
    public $classKey = 'slActionsProducts';
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
        $c->leftJoin('msProductData', 'msProductData', "msProductData.id = slActionsProducts.product_id");
        $c->leftJoin('modResource', 'modResource', "modResource.id = slActionsProducts.product_id");
        $c->leftJoin('slActions', 'slActions');

        $query = trim($this->getProperty('query'));
        if ($query) {
            $c->where([
                'modResource.pagetitle:LIKE' => "%{$query}%",
                'OR:msProductData.vendor_article:LIKE' => "%{$query}%",
                'OR:description:LIKE' => "%{$query}%"
            ]);
        }

        $action_id = (int)trim($this->getProperty('action_id'));
        if($action_id){
            $c->where([
                'action_id:=' => $action_id,
            ]);
        }

        $c->select(
            $this->modx->getSelectColumns('slActionsProducts', 'slActionsProducts', '') . ',
            modResource.pagetitle as product_name,
            msProductData.vendor_article as product_vendor_article,
            slActions.name as action_name'
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

        // Edit
        $array['actions'][] = [
            'cls' => '',
            'icon' => 'icon icon-edit',
            'title' => $this->modx->lexicon('shoplogistic_action_product_update'),
            //'multiple' => $this->modx->lexicon('shoplogistic_actions_update'),
            'action' => 'updateActionProduct',
            'button' => true,
            'menu' => true,
        ];

        if (!$array['active']) {
            $array['actions'][] = [
                'cls' => '',
                'icon' => 'icon icon-power-off action-green',
                'title' => $this->modx->lexicon('shoplogistic_action_product_enable'),
                'multiple' => $this->modx->lexicon('shoplogistic_action_products_enable'),
                'action' => 'enableActionProduct',
                'button' => true,
                'menu' => true,
            ];
        } else {
            $array['actions'][] = [
                'cls' => '',
                'icon' => 'icon icon-power-off action-gray',
                'title' => $this->modx->lexicon('shoplogistic_action_product_disable'),
                'multiple' => $this->modx->lexicon('shoplogistic_action_products_disable'),
                'action' => 'disableActionProduct',
                'button' => true,
                'menu' => true,
            ];
        }

        // Remove
        $array['actions'][] = [
            'cls' => '',
            'icon' => 'icon icon-trash-o action-red',
            'title' => $this->modx->lexicon('shoplogistic_action_product_remove'),
            'multiple' => $this->modx->lexicon('shoplogistic_action_products_remove'),
            'action' => 'removeActionProduct',
            'button' => true,
            'menu' => true,
        ];

        return $array;
    }

}

return 'slActionsProductsGetListProcessor';