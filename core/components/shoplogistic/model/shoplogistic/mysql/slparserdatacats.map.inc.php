<?php
$xpdo_meta_map['slParserDataCats']= array (
  'package' => 'shoplogistic',
  'version' => '1.1',
  'table' => 'sl_parserdata_cats',
  'extends' => 'xPDOSimpleObject',
  'tableMeta' => 
  array (
    'engine' => 'InnoDB',
  ),
  'fields' => 
  array (
    'name' => '',
    'description' => '',
    'export_name' => '',
    'export_parents' => '',
    'cat_id' => 0,
    'service_id' => 0,
    'check' => 0,
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
    'description' => 
    array (
      'dbtype' => 'varchar',
      'precision' => '255',
      'phptype' => 'string',
      'null' => true,
      'default' => '',
    ),
    'export_name' => 
    array (
      'dbtype' => 'varchar',
      'precision' => '255',
      'phptype' => 'string',
      'null' => true,
      'default' => '',
    ),
    'export_parents' => 
    array (
      'dbtype' => 'varchar',
      'precision' => '255',
      'phptype' => 'string',
      'null' => true,
      'default' => '',
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
    'service_id' => 
    array (
      'dbtype' => 'int',
      'precision' => '10',
      'attributes' => 'unsigned',
      'phptype' => 'integer',
      'null' => true,
      'default' => 0,
    ),
    'check' => 
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
    'service_id' => 
    array (
      'alias' => 'service_id',
      'primary' => false,
      'unique' => false,
      'type' => 'BTREE',
      'columns' => 
      array (
        'service_id' => 
        array (
          'length' => '',
          'collation' => 'A',
          'null' => false,
        ),
      ),
    ),
    'export_name' => 
    array (
      'alias' => 'export_name',
      'primary' => false,
      'unique' => false,
      'type' => 'BTREE',
      'columns' => 
      array (
        'export_name' => 
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
    'check' => 
    array (
      'alias' => 'check',
      'primary' => false,
      'unique' => false,
      'type' => 'BTREE',
      'columns' => 
      array (
        'check' => 
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
  ),
  'composites' => 
  array (
    'Options' => 
    array (
      'class' => 'slParserDataCatsOptions',
      'local' => 'id',
      'foreign' => 'cat_id',
      'cardinality' => 'many',
      'owner' => 'local',
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
    'Service' => 
    array (
      'class' => 'slParserDataService',
      'local' => 'service_id',
      'foreign' => 'id',
      'cardinality' => 'one',
      'owner' => 'foreign',
    ),
    'Resource' => 
    array (
      'class' => 'modResource',
      'local' => 'cat_id',
      'foreign' => 'id',
      'cardinality' => 'one',
      'owner' => 'foreign',
    ),
  ),
);
