<?php
$xpdo_meta_map['slActions']= array (
  'package' => 'shoplogistic',
  'version' => '1.1',
  'table' => 'sl_actions',
  'extends' => 'xPDOSimpleObject',
  'tableMeta' => 
  array (
    'engine' => 'InnoDB',
  ),
  'fields' => 
  array (
    'name' => '',
    'image' => '',
    'image_inner' => '',
    'store_id' => 0,
    'description' => '',
    'resource' => 0,
    'regions' => '',
    'cities' => '',
    'content' => NULL,
    'date_from' => NULL,
    'date_to' => NULL,
    'global' => 1,
    'active' => 1,
    'createdon' => NULL,
    'createdby' => 0,
    'updatedon' => NULL,
    'updatedby' => 0,
    'properties' => NULL,
  ),
  'fieldMeta' => 
  array (
    'name' => 
    array (
      'dbtype' => 'varchar',
      'precision' => '255',
      'phptype' => 'string',
      'null' => true,
      'default' => '',
    ),
    'image' => 
    array (
      'dbtype' => 'varchar',
      'precision' => '255',
      'phptype' => 'string',
      'null' => true,
      'default' => '',
    ),
    'image_inner' => 
    array (
      'dbtype' => 'varchar',
      'precision' => '255',
      'phptype' => 'string',
      'null' => true,
      'default' => '',
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
    'description' => 
    array (
      'dbtype' => 'varchar',
      'precision' => '255',
      'phptype' => 'string',
      'null' => true,
      'default' => '',
    ),
    'resource' => 
    array (
      'dbtype' => 'int',
      'precision' => '10',
      'attributes' => 'unsigned',
      'phptype' => 'integer',
      'null' => true,
      'default' => 0,
    ),
    'regions' => 
    array (
      'dbtype' => 'text',
      'precision' => '1024',
      'phptype' => 'json',
      'null' => true,
      'default' => '',
    ),
    'cities' => 
    array (
      'dbtype' => 'text',
      'precision' => '1024',
      'phptype' => 'json',
      'null' => true,
      'default' => '',
    ),
    'content' => 
    array (
      'dbtype' => 'text',
      'phptype' => 'text',
      'null' => true,
    ),
    'date_from' => 
    array (
      'dbtype' => 'datetime',
      'phptype' => 'datetime',
      'null' => true,
    ),
    'date_to' => 
    array (
      'dbtype' => 'datetime',
      'phptype' => 'datetime',
      'null' => true,
    ),
    'global' => 
    array (
      'dbtype' => 'tinyint',
      'precision' => '1',
      'phptype' => 'boolean',
      'null' => true,
      'default' => 1,
    ),
    'active' => 
    array (
      'dbtype' => 'tinyint',
      'precision' => '1',
      'phptype' => 'boolean',
      'null' => true,
      'default' => 1,
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
    'properties' => 
    array (
      'dbtype' => 'text',
      'phptype' => 'json',
      'null' => true,
    ),
  ),
  'indexes' => 
  array (
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
    'resource' => 
    array (
      'alias' => 'resource',
      'primary' => false,
      'unique' => false,
      'type' => 'BTREE',
      'columns' => 
      array (
        'resource' => 
        array (
          'length' => '',
          'collation' => 'A',
          'null' => false,
        ),
      ),
    ),
    'regions' => 
    array (
      'alias' => 'regions',
      'primary' => false,
      'unique' => false,
      'type' => 'BTREE',
      'columns' => 
      array (
        'regions' => 
        array (
          'length' => '512',
          'collation' => 'A',
          'null' => false,
        ),
      ),
    ),
    'cities' => 
    array (
      'alias' => 'cities',
      'primary' => false,
      'unique' => false,
      'type' => 'BTREE',
      'columns' => 
      array (
        'cities' => 
        array (
          'length' => '512',
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
    'global' => 
    array (
      'alias' => 'global',
      'primary' => false,
      'unique' => false,
      'type' => 'BTREE',
      'columns' => 
      array (
        'global' => 
        array (
          'length' => '',
          'collation' => 'A',
          'null' => false,
        ),
      ),
    ),
    'date_from' => 
    array (
      'alias' => 'date_from',
      'primary' => false,
      'unique' => false,
      'type' => 'BTREE',
      'columns' => 
      array (
        'date_from' => 
        array (
          'length' => '',
          'collation' => 'A',
          'null' => false,
        ),
      ),
    ),
    'date_to' => 
    array (
      'alias' => 'date_to',
      'primary' => false,
      'unique' => false,
      'type' => 'BTREE',
      'columns' => 
      array (
        'date_to' => 
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
    'Stores' => 
    array (
      'class' => 'slActionsStores',
      'local' => 'id',
      'foreign' => 'action_id',
      'cardinality' => 'many',
      'owner' => 'local',
    ),
    'Products' => 
    array (
      'class' => 'slActionsProducts',
      'local' => 'id',
      'foreign' => 'action_id',
      'cardinality' => 'many',
      'owner' => 'local',
    ),
  ),
  'aggregates' => 
  array (
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
    'modResource' => 
    array (
      'class' => 'modResource',
      'local' => 'resource',
      'foreign' => 'id',
      'cardinality' => 'one',
      'owner' => 'foreign',
    ),
    'slStores' => 
    array (
      'class' => 'slStores',
      'local' => 'store_id',
      'foreign' => 'id',
      'cardinality' => 'one',
      'owner' => 'foreign',
    ),
  ),
);
