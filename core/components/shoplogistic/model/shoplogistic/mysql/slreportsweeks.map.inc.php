<?php
$xpdo_meta_map['slReportsWeeks']= array (
  'package' => 'shoplogistic',
  'version' => '1.1',
  'table' => 'sl_reports_weeks',
  'extends' => 'xPDOSimpleObject',
  'tableMeta' => 
  array (
    'engine' => 'InnoDB',
  ),
  'fields' => 
  array (
    'report_id' => 0,
    'date_from' => NULL,
    'date_to' => NULL,
    'properties' => NULL,
  ),
  'fieldMeta' => 
  array (
    'report_id' => 
    array (
      'dbtype' => 'int',
      'precision' => '10',
      'attributes' => 'unsigned',
      'phptype' => 'integer',
      'null' => true,
      'default' => 0,
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
    'properties' => 
    array (
      'dbtype' => 'text',
      'phptype' => 'json',
      'null' => true,
    ),
  ),
  'indexes' => 
  array (
    'report_id' => 
    array (
      'alias' => 'report_id',
      'primary' => false,
      'unique' => false,
      'type' => 'BTREE',
      'columns' => 
      array (
        'report_id' => 
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
    'Sales' => 
    array (
      'class' => 'slReportsWeekSales',
      'local' => 'id',
      'foreign' => 'week_id',
      'cardinality' => 'many',
      'owner' => 'local',
    ),
  ),
  'aggregates' => 
  array (
    'Report' => 
    array (
      'class' => 'slReports',
      'local' => 'report_id',
      'foreign' => 'id',
      'cardinality' => 'one',
      'owner' => 'foreign',
    ),
  ),
);
