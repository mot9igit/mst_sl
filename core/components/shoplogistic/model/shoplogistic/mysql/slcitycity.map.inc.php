<?php
$xpdo_meta_map['slCityCity']= array (
  'package' => 'shoplogistic',
  'version' => '1.1',
  'table' => 'sl_city',
  'extends' => 'xPDOSimpleObject',
  'tableMeta' => 
  array (
    'engine' => 'InnoDB',
  ),
  'fields' => 
  array (
    'key' => '',
    'fias_id' => '',
    'city' => '',
    'city_r' => '',
    'phone' => '',
    'email' => '',
    'address' => '',
    'address_full' => '',
    'address_coordinats' => '',
    'lat' => 0.0,
    'lng' => 0.0,
    'default' => 0,
    'properties' => NULL,
  ),
  'fieldMeta' => 
  array (
    'key' => 
    array (
      'dbtype' => 'varchar',
      'precision' => '255',
      'phptype' => 'string',
      'null' => false,
      'default' => '',
    ),
    'fias_id' => 
    array (
      'dbtype' => 'varchar',
      'precision' => '255',
      'phptype' => 'string',
      'null' => false,
      'default' => '',
    ),
    'city' => 
    array (
      'dbtype' => 'varchar',
      'precision' => '255',
      'phptype' => 'string',
      'null' => false,
      'default' => '',
    ),
    'city_r' => 
    array (
      'dbtype' => 'varchar',
      'precision' => '255',
      'phptype' => 'string',
      'null' => false,
      'default' => '',
    ),
    'phone' => 
    array (
      'dbtype' => 'varchar',
      'precision' => '255',
      'phptype' => 'string',
      'null' => false,
      'default' => '',
    ),
    'email' => 
    array (
      'dbtype' => 'varchar',
      'precision' => '255',
      'phptype' => 'string',
      'null' => false,
      'default' => '',
    ),
    'address' => 
    array (
      'dbtype' => 'varchar',
      'precision' => '255',
      'phptype' => 'string',
      'null' => false,
      'default' => '',
    ),
    'address_full' => 
    array (
      'dbtype' => 'varchar',
      'precision' => '255',
      'phptype' => 'string',
      'null' => false,
      'default' => '',
    ),
    'address_coordinats' => 
    array (
      'dbtype' => 'varchar',
      'precision' => '255',
      'phptype' => 'string',
      'null' => false,
      'default' => '',
    ),
    'lat' => 
    array (
      'dbtype' => 'float',
      'precision' => '10,6',
      'phptype' => 'float',
      'null' => false,
      'default' => 0.0,
    ),
    'lng' => 
    array (
      'dbtype' => 'float',
      'precision' => '10,6',
      'phptype' => 'float',
      'null' => false,
      'default' => 0.0,
    ),
    'default' => 
    array (
      'dbtype' => 'tinyint',
      'precision' => '1',
      'phptype' => 'boolean',
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
    'default' => 
    array (
      'alias' => 'default',
      'primary' => false,
      'unique' => false,
      'type' => 'BTREE',
      'columns' => 
      array (
        'default' => 
        array (
          'length' => '',
          'collation' => 'A',
          'null' => false,
        ),
      ),
    ),
    'key' => 
    array (
      'alias' => 'key',
      'primary' => false,
      'unique' => true,
      'type' => 'BTREE',
      'columns' => 
      array (
        'key' => 
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
    'fields' => 
    array (
      'class' => 'cityFolderFields',
      'local' => 'id',
      'foreign' => 'city',
      'cardinality' => 'many',
      'owner' => 'local',
    ),
    'resources' => 
    array (
      'class' => 'cityFolderResource',
      'local' => 'id',
      'foreign' => 'city',
      'cardinality' => 'many',
      'owner' => 'local',
    ),
  ),
);
