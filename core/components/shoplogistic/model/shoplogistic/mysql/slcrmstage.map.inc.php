<?php
$xpdo_meta_map['slCRMStage']= array (
  'package' => 'shoplogistic',
  'version' => '1.1',
  'table' => 'sl_crm_deal_stages',
  'extends' => 'xPDOSimpleObject',
  'tableMeta' => 
  array (
    'engine' => 'InnoDB',
  ),
  'fields' => 
  array (
    'crm_id' => '',
    'name' => '',
    'color' => '000000',
    'transition_to' => 0,
    'transition_fail' => 0,
    'transition_anchor' => '',
    'user_description' => '',
    'category_id' => 0,
    'sort' => 0,
    'stores_available' => 0,
    'warehouses_available' => 0,
    'active' => 1,
    'check_code' => 0,
    'pay' => 0,
    'to_tk' => 0,
    'check_deal' => 0,
    'description' => '',
    'properties' => NULL,
  ),
  'fieldMeta' => 
  array (
    'crm_id' => 
    array (
      'dbtype' => 'varchar',
      'precision' => '100',
      'phptype' => 'string',
      'null' => false,
      'default' => '',
    ),
    'name' => 
    array (
      'dbtype' => 'varchar',
      'precision' => '100',
      'phptype' => 'string',
      'null' => false,
      'default' => '',
    ),
    'color' => 
    array (
      'dbtype' => 'char',
      'precision' => '6',
      'phptype' => 'string',
      'null' => true,
      'default' => '000000',
    ),
    'transition_to' => 
    array (
      'dbtype' => 'int',
      'precision' => '11',
      'phptype' => 'integer',
      'null' => false,
      'default' => 0,
    ),
    'transition_fail' => 
    array (
      'dbtype' => 'int',
      'precision' => '11',
      'phptype' => 'integer',
      'null' => false,
      'default' => 0,
    ),
    'transition_anchor' => 
    array (
      'dbtype' => 'varchar',
      'precision' => '100',
      'phptype' => 'string',
      'null' => false,
      'default' => '',
    ),
    'user_description' => 
    array (
      'dbtype' => 'varchar',
      'precision' => '255',
      'phptype' => 'string',
      'null' => false,
      'default' => '',
    ),
    'category_id' => 
    array (
      'dbtype' => 'int',
      'precision' => '11',
      'phptype' => 'integer',
      'null' => false,
      'default' => 0,
    ),
    'sort' => 
    array (
      'dbtype' => 'int',
      'precision' => '11',
      'phptype' => 'integer',
      'null' => false,
      'default' => 0,
    ),
    'stores_available' => 
    array (
      'dbtype' => 'tinyint',
      'precision' => '1',
      'phptype' => 'integer',
      'null' => true,
      'default' => 0,
    ),
    'warehouses_available' => 
    array (
      'dbtype' => 'tinyint',
      'precision' => '1',
      'phptype' => 'integer',
      'null' => true,
      'default' => 0,
    ),
    'active' => 
    array (
      'dbtype' => 'tinyint',
      'precision' => '1',
      'attributes' => 'unsigned',
      'phptype' => 'integer',
      'null' => true,
      'default' => 1,
    ),
    'check_code' => 
    array (
      'dbtype' => 'tinyint',
      'precision' => '1',
      'attributes' => 'unsigned',
      'phptype' => 'integer',
      'null' => true,
      'default' => 0,
    ),
    'pay' => 
    array (
      'dbtype' => 'tinyint',
      'precision' => '1',
      'attributes' => 'unsigned',
      'phptype' => 'integer',
      'null' => true,
      'default' => 0,
    ),
    'to_tk' => 
    array (
      'dbtype' => 'tinyint',
      'precision' => '1',
      'attributes' => 'unsigned',
      'phptype' => 'integer',
      'null' => true,
      'default' => 0,
    ),
    'check_deal' => 
    array (
      'dbtype' => 'tinyint',
      'precision' => '1',
      'attributes' => 'unsigned',
      'phptype' => 'integer',
      'null' => true,
      'default' => 0,
    ),
    'description' => 
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
      'phptype' => 'string',
      'null' => true,
    ),
  ),
  'indexes' => 
  array (
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
    'name' => 
    array (
      'alias' => 'name',
      'primary' => false,
      'unique' => false,
      'type' => 'BTREE',
      'columns' => 
      array (
        'name' => 
        array (
          'length' => '',
          'collation' => 'A',
          'null' => false,
        ),
      ),
    ),
    'active' => 
    array (
      'alias' => 'active',
      'primary' => false,
      'unique' => false,
      'type' => 'BTREE',
      'columns' => 
      array (
        'active' => 
        array (
          'length' => '',
          'collation' => 'A',
          'null' => false,
        ),
      ),
    ),
    'pay' => 
    array (
      'alias' => 'pay',
      'primary' => false,
      'unique' => false,
      'type' => 'BTREE',
      'columns' => 
      array (
        'pay' => 
        array (
          'length' => '',
          'collation' => 'A',
          'null' => false,
        ),
      ),
    ),
    'check_code' => 
    array (
      'alias' => 'check_code',
      'primary' => false,
      'unique' => false,
      'type' => 'BTREE',
      'columns' => 
      array (
        'check_code' => 
        array (
          'length' => '',
          'collation' => 'A',
          'null' => false,
        ),
      ),
    ),
    'to_tk' => 
    array (
      'alias' => 'to_tk',
      'primary' => false,
      'unique' => false,
      'type' => 'BTREE',
      'columns' => 
      array (
        'to_tk' => 
        array (
          'length' => '',
          'collation' => 'A',
          'null' => false,
        ),
      ),
    ),
    'check_deal' => 
    array (
      'alias' => 'check_deal',
      'primary' => false,
      'unique' => false,
      'type' => 'BTREE',
      'columns' => 
      array (
        'check_deal' => 
        array (
          'length' => '',
          'collation' => 'A',
          'null' => false,
        ),
      ),
    ),
    'sort' => 
    array (
      'alias' => 'sort',
      'primary' => false,
      'unique' => false,
      'type' => 'BTREE',
      'columns' => 
      array (
        'sort' => 
        array (
          'length' => '',
          'collation' => 'A',
          'null' => false,
        ),
      ),
    ),
    'category_id' => 
    array (
      'alias' => 'category_id',
      'primary' => false,
      'unique' => false,
      'type' => 'BTREE',
      'columns' => 
      array (
        'category_id' => 
        array (
          'length' => '',
          'collation' => 'A',
          'null' => false,
        ),
      ),
    ),
    'transition_to' => 
    array (
      'alias' => 'transition_to',
      'primary' => false,
      'unique' => false,
      'type' => 'BTREE',
      'columns' => 
      array (
        'transition_to' => 
        array (
          'length' => '',
          'collation' => 'A',
          'null' => false,
        ),
      ),
    ),
    'transition_fail' => 
    array (
      'alias' => 'transition_fail',
      'primary' => false,
      'unique' => false,
      'type' => 'BTREE',
      'columns' => 
      array (
        'transition_fail' => 
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
    'Category' => 
    array (
      'class' => 'slCRMCategory',
      'local' => 'category_id',
      'foreign' => 'id',
      'owner' => 'foreign',
      'cardinality' => 'one',
    ),
    'Transition' => 
    array (
      'class' => 'slCRMStage',
      'local' => 'transition_to',
      'foreign' => 'id',
      'owner' => 'foreign',
      'cardinality' => 'one',
    ),
    'TransitionFail' => 
    array (
      'class' => 'slCRMStage',
      'local' => 'transition_fail',
      'foreign' => 'id',
      'owner' => 'foreign',
      'cardinality' => 'one',
    ),
  ),
);
