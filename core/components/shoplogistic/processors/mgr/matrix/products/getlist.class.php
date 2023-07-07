<?php

class slStoresMatrixProductsGetListProcessor extends modObjectGetListProcessor
{
    public $objectType = 'slStoresMatrixProducts';
    public $classKey = 'slStoresMatrixProducts';
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
		$c->leftJoin('slStoresMatrix', 'Matrix');
        $c->leftJoin('msProduct', 'Product');
        $c->leftJoin('modResource', 'Content', 'Product.id = Content.id');
    	$matrix_id = trim($this->getProperty('matrix_id'));
        $query = trim($this->getProperty('query'));
        if ($query) {
            $c->where([
                'description:LIKE' => "%{$query}%",
				'OR:Content.pagetitle:LIKE' => "%{$query}%",
                'OR:Product.article:LIKE' => "%{$query}%"
            ]);
        }

        if($matrix_id){
			$c->where([
				'matrix_id:=' => $matrix_id,
			]);
		}

		$c->select(
			$this->modx->getSelectColumns('slStoresMatrixProducts', 'slStoresMatrixProducts', '') . ',
            Matrix.name as matrix,
            Content.pagetitle as product'
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
            'title' => $this->modx->lexicon('shoplogistic_matrix_product_update'),
            //'multiple' => $this->modx->lexicon('shoplogistic_items_update'),
            'action' => 'updateMatrixProduct',
            'button' => true,
            'menu' => true,
        ];

        // Remove
        $array['actions'][] = [
            'cls' => '',
            'icon' => 'icon icon-trash-o action-red',
            'title' => $this->modx->lexicon('shoplogistic_matrix_product_remove'),
            'multiple' => $this->modx->lexicon('shoplogistic_matrix_product_remove'),
            'action' => 'removeMatrixProduct',
            'button' => true,
            'menu' => true,
        ];

        return $array;
    }

}

return 'slStoresMatrixProductsGetListProcessor';