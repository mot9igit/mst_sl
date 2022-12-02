<?php

return [
	'shoplogistic' => array(
		'description' => 'shoplogistic_menu_desc',
		'icon' => '<i class="icon-shopping-cart icon icon-large"></i>',
		'action' => 'home',
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
	)
];