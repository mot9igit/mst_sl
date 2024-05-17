<?php
$xpdo_meta_map['slParserDataCatsOptions']= array (
  'package' => 'shoplogistic',
  'version' => '1.1',
  'table' => 'sl_parserdata_cats_options',
  'extends' => 'xPDOSimpleObject',
  'tableMeta' => 
  array (
    'engine' => 'InnoDB',
  ),
  'fields' => 
  array (
    'name' => '',
    'folder' => '',
    'filter' => '',
    'examples' => '',
    'to_field' => '',
    'option_id' => 0,
    'cat_id' => 0,
    'ignore' => 0,
    'createdon' => NULL,
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
    'folder' => 
    array (
      'dbtype' => 'varchar',
      'precision' => '255',
      'phptype' => 'string',
      'null' => true,
      'default' => '',
    ),
    'filter' => 
    array (
      'dbtype' => 'varchar',
      'precision' => '255',
      'phptype' => 'string',
      'null' => true,
      'default' => '',
    ),
    'examples' => 
    array (
      'dbtype' => 'text',
      'phptype' => 'string',
      'null' => true,
      'default' => '',
    ),
    'to_field' => 
    array (
      'dbtype' => 'varchar',
      'precision' => '255',
      'phptype' => 'string',
      'null' => true,
      'default' => '',
    ),
    'option_id' => 
    array (
      'dbtype' => 'int',
      'precision' => '10',
      'attributes' => 'unsigned',
      'phptype' => 'integer',
      'null' => true,
      'default' => 0,
    ),
    'cat_id' => 
    array (
      'dbtype' => 'int',
      'precision' => '10',
      'attributes' => 'unsigned',
      'phptype' => 'integer',
      'null' => true,
      'default' => 0,
    ),
    'ignore' => 
    array (
      'dbtype' => 'tinyint',
      'precision' => '1',
      'phptype' => 'boolean',
      'null' => true,
      'default' => 0,
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
    'option_id' => 
    array (
      'alias' => 'option_id',
      'primary' => false,
      'unique' => false,
      'type' => 'BTREE',
      'columns' => 
      array (
        'option_id' => 
        array (
          'length' => '',
          'collation' => 'A',
          'null' => false,
        ),
      ),
    ),
    'cat_id' => 
    array (
      'alias' => 'cat_id',
      'primary' => false,
      'unique' => false,
      'type' => 'BTREE',
      'columns' => 
      array (
        'cat_id' => 
        array (
          'length' => '',
          'collation' => 'A',
          'null' => false,
        ),
      ),
    ),
    'ignore' => 
    array (
      'alias' => 'ignore',
      'primary' => false,
      'unique' => false,
      'type' => 'BTREE',
      'columns' => 
      array (
        'ignore' => 
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
    'Option' => 
    array (
      'class' => 'msOption',
      'local' => 'option_id',
      'foreign' => 'id',
      'cardinality' => 'one',
      'owner' => 'foreign',
    ),
    'Cat' => 
    array (
      'class' => 'slParserDataCats',
      'local' => 'cat_id',
      'foreign' => 'id',
      'cardinality' => 'one',
      'owner' => 'foreign',
    ),
  ),
);
