<?php
$xpdo_meta_map['slOrderOptProduct']= array (
  'package' => 'shoplogistic',
  'version' => '1.1',
  'table' => 'sl_order_opt_products',
  'extends' => 'xPDOSimpleObject',
  'tableMeta' => 
  array (
    'engine' => 'InnoDB',
  ),
  'fields' => 
  array (
    'remain_id' => NULL,
    'order_id' => NULL,
    'name' => NULL,
    'count' => 1,
    'price' => 0.0,
    'weight' => 0.0,
    'cost' => 0.0,
    'options' => NULL,
    'actions' => NULL,
  ),
  'fieldMeta' => 
  array (
    'remain_id' => 
    array (
      'dbtype' => 'int',
      'precision' => '10',
      'phptype' => 'integer',
      'attributes' => 'unsigned',
      'null' => false,
    ),
    'order_id' => 
    array (
      'dbtype' => 'int',
      'precision' => '10',
      'phptype' => 'integer',
      'attributes' => 'unsigned',
      'null' => false,
    ),
    'name' => 
    array (
      'dbtype' => 'varchar',
      'precision' => '255',
      'phptype' => 'string',
      'null' => true,
    ),
    'count' => 
    array (
      'dbtype' => 'int',
      'precision' => '10',
      'phptype' => 'integer',
      'attributes' => 'unsigned',
      'null' => true,
      'default' => 1,
    ),
    'price' => 
    array (
      'dbtype' => 'decimal',
      'precision' => '12,2',
      'phptype' => 'float',
      'null' => true,
      'default' => 0.0,
    ),
    'weight' => 
    array (
      'dbtype' => 'decimal',
      'precision' => '13,3',
      'phptype' => 'float',
      'null' => true,
      'default' => 0.0,
    ),
    'cost' => 
    array (
      'dbtype' => 'decimal',
      'precision' => '12,2',
      'phptype' => 'float',
      'null' => true,
      'default' => 0.0,
    ),
    'options' => 
    array (
      'dbtype' => 'text',
      'phptype' => 'json',
      'null' => true,
    ),
    'actions' => 
    array (
      'dbtype' => 'text',
      'phptype' => 'json',
      'null' => true,
    ),
  ),
  'indexes' => 
  array (
    'remain_id' => 
    array (
      'alias' => 'remain_id',
      'primary' => false,
      'unique' => false,
      'type' => 'BTREE',
      'columns' => 
      array (
        'remain_id' => 
        array (
          'length' => '',
          'collation' => 'A',
          'null' => false,
        ),
      ),
    ),
    'order_id' => 
    array (
      'alias' => 'order_id',
      'primary' => false,
      'unique' => false,
      'type' => 'BTREE',
      'columns' => 
      array (
        'order_id' => 
        array (
          'length' => '',
          'collation' => 'A',
          'null' => false,
        ),
      ),
    ),
  ),
  'aggregates' => 
  array (
    'Remain' => 
    array (
      'class' => 'slStoresRemains',
      'local' => 'remain_id',
      'foreign' => 'id',
      'owner' => 'foreign',
      'cardinality' => 'one',
    ),
    'Order' => 
    array (
      'class' => 'slOrder',
      'local' => 'order_id',
      'foreign' => 'id',
      'owner' => 'foreign',
      'cardinality' => 'one',
    ),
  ),
);
