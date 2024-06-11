<?php
$xpdo_meta_map['slActionsComplects']= array (
  'package' => 'shoplogistic',
  'version' => '1.1',
  'table' => 'sl_actions_complects',
  'extends' => 'xPDOSimpleObject',
  'tableMeta' => 
  array (
    'engine' => 'InnoDB',
  ),
  'fields' => 
  array (
    'action_id' => 0,
  ),
  'fieldMeta' => 
  array (
    'action_id' => 
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
    'action_id' => 
    array (
      'alias' => 'action_id',
      'primary' => false,
      'unique' => false,
      'type' => 'BTREE',
      'columns' => 
      array (
        'action_id' => 
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
    'slActions' => 
    array (
      'class' => 'slActions',
      'local' => 'action_id',
      'foreign' => 'id',
      'cardinality' => 'one',
      'owner' => 'foreign',
    ),
  ),
);
