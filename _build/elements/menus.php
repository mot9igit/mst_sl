<?php

return [

	'shoplogistic' => array(
		'description' => 'shoplogistic_menu_desc',
		'icon' => '<i class="icon-shopping-cart icon icon-large"></i>',
		'action' => 'home',
	),
    'shoplogistic_actions' => array(
        'description' => 'shoplogistic_actions_desc',
        'parent' => 'shoplogistic',
        'menuindex' => 1,
        'action' => 'mgr/actions',
    ),
    'shoplogistic_adv' => array(
        'description' => 'shoplogistic_adv_desc',
        'parent' => 'shoplogistic',
        'menuindex' => 1,
        'action' => 'mgr/adv',
    ),
    'shoplogistic_filedocs' => array(
        'description' => 'shoplogistic_filedocs_desc',
        'parent' => 'shoplogistic',
        'menuindex' => 2,
        'action' => 'mgr/docs',
    ),
    'shoplogistic_parser' => array(
        'description' => 'shoplogistic_parser_desc',
        'parent' => 'shoplogistic',
        'menuindex' => 3,
        'action' => 'mgr/parser',
    ),
    'shoplogistic_parserdata' => array(
        'description' => 'shoplogistic_parserdata_desc',
        'parent' => 'shoplogistic',
        'menuindex' => 3,
        'action' => 'mgr/parserdata',
    ),
    'shoplogistic_queue' => array(
        'description' => 'shoplogistic_queue_desc',
        'parent' => 'shoplogistic',
        'menuindex' => 4,
        'action' => 'mgr/queue',
    ),
    'shoplogistic_export_files' => array(
        'description' => 'shoplogistic_export_files_desc',
        'parent' => 'shoplogistic',
        'menuindex' => 5,
        'action' => 'mgr/export_files',
    ),
    'shoplogistic_card_request' => array(
        'description' => 'shoplogistic_card_request_desc',
        'parent' => 'shoplogistic',
        'menuindex' => 6,
        'action' => 'mgr/cardrequest',
    ),
    'shoplogistic_balance_pay_request' => array(
        'description' => 'shoplogistic_balance_pay_request_desc',
        'parent' => 'shoplogistic',
        'menuindex' => 7,
        'action' => 'mgr/balancepayrequest',
    ),
	'shoplogistic_orders' => array(
		'description' => 'shoplogistic_orders_desc',
		'parent' => 'shoplogistic',
		'menuindex' => 8,
		'action' => 'mgr/orders',
	),
	'shoplogistic_settings' => array(
		'description' => 'shoplogistic_settings_desc',
		'parent' => 'shoplogistic',
		'menuindex' => 9,
		'action' => 'mgr/settings',
	),
	'shoplogistic_crm' => array(
		'description' => 'shoplogistic_crm_desc',
		'parent' => 'shoplogistic',
		'menuindex' => 10,
		'action' => 'mgr/crm',
	)
];