<?php

return [
	'shoplogistic' => array(
		'description' => 'shoplogistic_menu_desc',
		'icon' => '<i class="icon-shopping-cart icon icon-large"></i>',
		'action' => 'home',
	),
    'shoplogistic_filedocs' => array(
        'description' => 'shoplogistic_filedocs_desc',
        'parent' => 'shoplogistic',
        'menuindex' => 1,
        'action' => 'mgr/docs',
    ),
    'shoplogistic_export_files' => array(
        'description' => 'shoplogistic_export_files_desc',
        'parent' => 'shoplogistic',
        'menuindex' => 1,
        'action' => 'mgr/export_files',
    ),
    'shoplogistic_card_request' => array(
        'description' => 'shoplogistic_card_request_desc',
        'parent' => 'shoplogistic',
        'menuindex' => 1,
        'action' => 'mgr/cardrequest',
    ),
    'shoplogistic_balance_pay_request' => array(
        'description' => 'shoplogistic_balance_pay_request_desc',
        'parent' => 'shoplogistic',
        'menuindex' => 1,
        'action' => 'mgr/balancepayrequest',
    ),
	'shoplogistic_orders' => array(
		'description' => 'shoplogistic_orders_desc',
		'parent' => 'shoplogistic',
		'menuindex' => 0,
		'action' => 'mgr/orders',
	),
	'shoplogistic_settings' => array(
		'description' => 'shoplogistic_settings_desc',
		'parent' => 'shoplogistic',
		'menuindex' => 1,
		'action' => 'mgr/settings',
	),
	'shoplogistic_crm' => array(
		'description' => 'shoplogistic_crm_desc',
		'parent' => 'shoplogistic',
		'menuindex' => 1,
		'action' => 'mgr/crm',
	)
];