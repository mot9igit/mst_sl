<?php
$xpdo_meta_map['slReturn']= array (
  'package' => 'shoplogistic',
  'version' => '1.1',
  'table' => 'sl_return',
  'extends' => 'xPDOSimpleObject',
  'tableMeta' => 
  array (
    'engine' => 'InnoDB',
  ),
  'fields' => 
  array (
    'order_id' => 0,
    'num' => '',
    'date' => NULL,
    'status' => 0,
    'decision' => '',
    'comments' => '',
    'timeframe' => NULL,
    'full_name' => '',
    'bank_name' => '',
    'bank_bik' => '',
    'corr_account' => '',
    'pay_account' => '',
    'createdon' => NULL,
    'createdby' => 0,
    'updatedon' => NULL,
    'updatedby' => 0,
  ),
  'fieldMeta' => 
  array (
    'order_id' => 
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
      'precision' => '255',
      'phptype' => 'string',
      'null' => true,
      'default' => '',
    ),
    'date' => 
    array (
      'dbtype' => 'timestamp',
      'phptype' => 'datetime',
      'null' => true,
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
    'decision' => 
    array (
      'dbtype' => 'varchar',
      'precision' => '255',
      'phptype' => 'string',
      'null' => true,
      'default' => '',
    ),
    'comments' => 
    array (
      'dbtype' => 'varchar',
      'precision' => '255',
      'phptype' => 'string',
      'null' => true,
      'default' => '',
    ),
    'timeframe' => 
    array (
      'dbtype' => 'timestamp',
      'phptype' => 'datetime',
      'null' => true,
    ),
    'full_name' => 
    array (
      'dbtype' => 'varchar',
      'precision' => '255',
      'phptype' => 'string',
      'null' => true,
      'default' => '',
    ),
    'bank_name' => 
    array (
      'dbtype' => 'varchar',
      'precision' => '255',
      'phptype' => 'string',
      'null' => true,
      'default' => '',
    ),
    'bank_bik' => 
    array (
      'dbtype' => 'varchar',
      'precision' => '255',
      'phptype' => 'string',
      'null' => true,
      'default' => '',
    ),
    'corr_account' => 
    array (
      'dbtype' => 'varchar',
      'precision' => '255',
      'phptype' => 'string',
      'null' => true,
      'default' => '',
    ),
    'pay_account' => 
    array (
      'dbtype' => 'varchar',
      'precision' => '255',
      'phptype' => 'string',
      'null' => true,
      'default' => '',
    ),
    'createdon' => 
    array (
      'dbtype' => 'datetime',
      'phptype' => 'datetime',
      'null' => true,
    ),
    'createdby' => 
    array (
      'dbtype' => 'int',
      'precision' => '10',
      'attributes' => 'unsigned',
      'phptype' => 'integer',
      'null' => true,
      'default' => 0,
    ),
    'updatedon' => 
    array (
      'dbtype' => 'datetime',
      'phptype' => 'datetime',
      'null' => true,
    ),
    'updatedby' => 
    array (
      'dbtype' => 'int',
      'precision' => '10',
      'attributes' => 'unsigned',
      'phptype' => 'integer',
      'null' => true,
      'default' => 0,
    ),
  ),
  'indexes' => 
  array (
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
    'num' => 
    array (
      'alias' => 'num',
      'primary' => false,
      'unique' => false,
      'type' => 'BTREE',
      'columns' => 
      array (
        'num' => 
        array (
          'length' => '',
          'collation' => 'A',
          'null' => false,
        ),
      ),
    ),
    'date' => 
    array (
      'alias' => 'date',
      'primary' => false,
      'unique' => false,
      'type' => 'BTREE',
      'columns' => 
      array (
        'date' => 
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
    'Orders' => 
    array (
      'class' => 'slOrder',
      'local' => 'order_id',
      'foreign' => 'id',
      'cardinality' => 'one',
      'owner' => 'foreign',
    ),
    'Status' => 
    array (
      'class' => 'slReturnStatus',
      'local' => 'status',
      'foreign' => 'id',
      'cardinality' => 'one',
      'owner' => 'foreign',
    ),
    'CreatedUser' => 
    array (
      'class' => 'modUser',
      'local' => 'createdby',
      'foreign' => 'id',
      'cardinality' => 'many',
      'owner' => 'foreign',
    ),
    'CreatedUserProfile' => 
    array (
      'class' => 'modUserProfile',
      'local' => 'createdby',
      'foreign' => 'id',
      'cardinality' => 'many',
      'owner' => 'foreign',
    ),
    'UpdatedUser' => 
    array (
      'class' => 'modUser',
      'local' => 'updatedby',
      'foreign' => 'id',
      'cardinality' => 'many',
      'owner' => 'foreign',
    ),
    'UpdatedUserProfile' => 
    array (
      'class' => 'modUserProfile',
      'local' => 'updatedby',
      'foreign' => 'id',
      'cardinality' => 'many',
      'owner' => 'foreign',
    ),
  ),
);
