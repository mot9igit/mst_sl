<?php
$xpdo_meta_map['slOrderOpt']= array (
  'package' => 'shoplogistic',
  'version' => '1.1',
  'table' => 'sl_orders_opt',
  'extends' => 'xPDOSimpleObject',
  'tableMeta' => 
  array (
    'engine' => 'InnoDB',
  ),
  'fields' => 
  array (
    'warehouse_id' => 0,
    'store_id' => 0,
    'crm_id' => 0,
    'ship_id' => 0,
    'status' => 0,
    'num' => '',
    'cost' => 0.0,
    'cart_cost' => 0.0,
    'delivery_cost' => 0.0,
    'delivery_date' => NULL,
    'date' => NULL,
    'weight' => 0.0,
    'volume' => 0.0,
    'createdon' => NULL,
    'updatedon' => NULL,
    'active' => 1,
    'description' => '',
    'code' => '',
    'code_until' => NULL,
    'comment' => NULL,
    'tk' => '',
    'tk_id' => '',
    'properties' => NULL,
  ),
  'fieldMeta' => 
  array (
    'warehouse_id' => 
    array (
      'dbtype' => 'int',
      'precision' => '10',
      'attributes' => 'unsigned',
      'phptype' => 'integer',
      'null' => true,
      'default' => 0,
    ),
    'store_id' => 
    array (
      'dbtype' => 'int',
      'precision' => '10',
      'attributes' => 'unsigned',
      'phptype' => 'integer',
      'null' => true,
      'default' => 0,
    ),
    'crm_id' => 
    array (
      'dbtype' => 'int',
      'precision' => '10',
      'attributes' => 'unsigned',
      'phptype' => 'integer',
      'null' => true,
      'default' => 0,
    ),
    'ship_id' => 
    array (
      'dbtype' => 'int',
      'precision' => '10',
      'attributes' => 'unsigned',
      'phptype' => 'integer',
      'null' => true,
      'default' => 0,
    ),
    'status' => 
    array (
      'dbtype' => 'int',
      'precision' => '10',
      'attributes' => 'unsigned',
      'phptype' => 'integer',
      'null' => true,
      'default' => 0,
    ),
    'num' => 
    array (
      'dbtype' => 'varchar',
      'precision' => '20',
      'phptype' => 'string',
      'null' => true,
      'default' => '',
    ),
    'cost' => 
    array (
      'dbtype' => 'decimal',
      'precision' => '12,2',
      'phptype' => 'float',
      'null' => true,
      'default' => 0.0,
    ),
    'cart_cost' => 
    array (
      'dbtype' => 'decimal',
      'precision' => '12,2',
      'phptype' => 'float',
      'null' => true,
      'default' => 0.0,
    ),
    'delivery_cost' => 
    array (
      'dbtype' => 'decimal',
      'precision' => '12,2',
      'phptype' => 'float',
      'null' => true,
      'default' => 0.0,
    ),
    'delivery_date' => 
    array (
      'dbtype' => 'datetime',
      'phptype' => 'datetime',
      'null' => true,
    ),
    'date' => 
    array (
      'dbtype' => 'datetime',
      'phptype' => 'datetime',
      'null' => true,
    ),
    'weight' => 
    array (
      'dbtype' => 'decimal',
      'precision' => '13,3',
      'phptype' => 'float',
      'null' => true,
      'default' => 0.0,
    ),
    'volume' => 
    array (
      'dbtype' => 'decimal',
      'precision' => '13,3',
      'phptype' => 'float',
      'null' => true,
      'default' => 0.0,
    ),
    'createdon' => 
    array (
      'dbtype' => 'datetime',
      'phptype' => 'datetime',
      'null' => true,
    ),
    'updatedon' => 
    array (
      'dbtype' => 'datetime',
      'phptype' => 'datetime',
      'null' => true,
    ),
    'active' => 
    array (
      'dbtype' => 'tinyint',
      'precision' => '1',
      'phptype' => 'boolean',
      'null' => true,
      'default' => 1,
    ),
    'description' => 
    array (
      'dbtype' => 'varchar',
      'precision' => '255',
      'phptype' => 'string',
      'null' => true,
      'default' => '',
    ),
    'code' => 
    array (
      'dbtype' => 'varchar',
      'precision' => '255',
      'phptype' => 'string',
      'null' => true,
      'default' => '',
    ),
    'code_until' => 
    array (
      'dbtype' => 'datetime',
      'phptype' => 'datetime',
      'null' => true,
    ),
    'comment' => 
    array (
      'dbtype' => 'text',
      'phptype' => 'string',
      'null' => true,
    ),
    'tk' => 
    array (
      'dbtype' => 'varchar',
      'precision' => '255',
      'phptype' => 'string',
      'null' => true,
      'default' => '',
    ),
    'tk_id' => 
    array (
      'dbtype' => 'varchar',
      'precision' => '255',
      'phptype' => 'string',
      'null' => true,
      'default' => '',
    ),
    'properties' => 
    array (
      'dbtype' => 'text',
      'phptype' => 'json',
      'null' => true,
    ),
  ),
  'indexes' => 
  array (
    'warehouse_id' => 
    array (
      'alias' => 'warehouse_id',
      'primary' => false,
      'unique' => false,
      'type' => 'BTREE',
      'columns' => 
      array (
        'warehouse_id' => 
        array (
          'length' => '',
          'collation' => 'A',
          'null' => false,
        ),
      ),
    ),
    'store_id' => 
    array (
      'alias' => 'store_id',
      'primary' => false,
      'unique' => false,
      'type' => 'BTREE',
      'columns' => 
      array (
        'store_id' => 
        array (
          'length' => '',
          'collation' => 'A',
          'null' => false,
        ),
      ),
    ),
    'status' => 
    array (
      'alias' => 'status',
      'primary' => false,
      'unique' => false,
      'type' => 'BTREE',
      'columns' => 
      array (
        'status' => 
        array (
          'length' => '',
          'collation' => 'A',
          'null' => false,
        ),
      ),
    ),
    'crm_id' => 
    array (
      'alias' => 'crm_id',
      'primary' => false,
      'unique' => false,
      'type' => 'BTREE',
      'columns' => 
      array (
        'crm_id' => 
        array (
          'length' => '',
          'collation' => 'A',
          'null' => false,
        ),
      ),
    ),
    'ship_id' => 
    array (
      'alias' => 'ship_id',
      'primary' => false,
      'unique' => false,
      'type' => 'BTREE',
      'columns' => 
      array (
        'ship_id' => 
        array (
          'length' => '',
          'collation' => 'A',
          'null' => false,
        ),
      ),
    ),
    'tk_id' => 
    array (
      'alias' => 'tk_id',
      'primary' => false,
      'unique' => false,
      'type' => 'BTREE',
      'columns' => 
      array (
        'tk_id' => 
        array (
          'length' => '',
          'collation' => 'A',
          'null' => false,
        ),
      ),
    ),
  ),
  'composites' => 
  array (
    'Products' => 
    array (
      'class' => 'slOrderProduct',
      'local' => 'id',
      'foreign' => 'order_id',
      'cardinality' => 'many',
      'owner' => 'local',
    ),
  ),
  'aggregates' => 
  array (
    'Warehouse' => 
    array (
      'class' => 'slWarehouse',
      'local' => 'warehouse_id',
      'foreign' => 'id',
      'cardinality' => 'one',
      'owner' => 'foreign',
    ),
    'Store' => 
    array (
      'class' => 'slStores',
      'local' => 'store_id',
      'foreign' => 'id',
      'cardinality' => 'one',
      'owner' => 'foreign',
    ),
    'Status' => 
    array (
      'class' => 'slCRMStage',
      'local' => 'status',
      'foreign' => 'id',
      'cardinality' => 'one',
      'owner' => 'foreign',
    ),
    'slWarehouseShipment' => 
    array (
      'class' => 'slWarehouseShipment',
      'local' => 'ship_id',
      'foreign' => 'id',
      'cardinality' => 'one',
      'owner' => 'foreign',
    ),
  ),
);
