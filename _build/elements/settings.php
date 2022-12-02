<?php

return [
	'frontend_css' => [
		'xtype' => 'textfield',
		'value' => '[[+cssUrl]]web/shoplogistic.css',
		'area' => 'shoplogistic_main',
	],
	'frontend_js' => [
		'xtype' => 'textfield',
		'value' => '[[+jsUrl]]web/shoplogistic.js',
		'area' => 'shoplogistic_main',
	],
    'api_key' => [
        'xtype' => 'textfield',
        'value' => '',
        'area' => 'shoplogistic_eshoplogistic',
    ],
	'api_key_dadata' => [
		'xtype' => 'textfield',
		'value' => '',
		'area' => 'shoplogistic_eshoplogistic',
	],
	'secret_key_dadata' => [
		'xtype' => 'textfield',
		'value' => '',
		'area' => 'shoplogistic_eshoplogistic',
	],
	'default_delivery' => [
		'xtype' => 'textfield',
		'value' => 1,
		'area' => 'shoplogistic_eshoplogistic',
	],
	'curier_delivery' => [
		'xtype' => 'textfield',
		'value' => 1,
		'area' => 'shoplogistic_eshoplogistic',
	],
	'punkt_delivery' => [
		'xtype' => 'textfield',
		'value' => 1,
		'area' => 'shoplogistic_eshoplogistic',
	],
	'post_delivery' => [
		'xtype' => 'textfield',
		'value' => 1,
		'area' => 'shoplogistic_eshoplogistic',
	],
	'regexp_gen_code' => [
		'xtype' => 'textfield',
		'value' => 'sl-/([a-zA-Z0-9]{4-10})/',
		'area' => 'shoplogistic_main',
	],
	'open_fields_store' => [
		'xtype' => 'textfield',
		'value' => 'contact,email,phone',
		'area' => 'shoplogistic_main',
	],
	'open_fields_warehouse' => [
		'xtype' => 'textfield',
		'value' => 'contact,email,phone',
		'area' => 'shoplogistic_main',
	],
	'tax_percent' => [
		'xtype' => 'numberfield',
		'decimalPrecision' => 2,
		'value' => 2,
		'area' => 'shoplogistic_main',
	],
	'default_store' => [
		'xtype' => 'shoplogistic-combo-store',
		'value' => 5,
		'area' => 'shoplogistic_main',
	],
	'mode' => [
		'xtype' => 'numberfield',
		'value' => 1,
		'area' => 'shoplogistic_main',
	],
	'phx_prefix' => [
		'xtype' => 'textfield',
		'value' => 'cf.',
		'area' => 'shoplogistic_city',
	],
	'city_fields' => [
		'xtype' => 'textfield',
		'value' => 'key,city,phone,email',
		'area' => 'shoplogistic_city',
	],
	'catalogs' => [
		'xtype' => 'textfield',
		'value' => '',
		'area' => 'shoplogistic_city',
	],
	'km' => [
		'xtype' => 'numberfield',
		'value' => '80',
		'area' => 'shoplogistic_city',
	],
	'ur_name' => [
		'xtype' => 'textfield',
		'value' => 'Берсенев Андрей Юрьевич, ИП',
		'area' => 'shoplogistic_requizites',
	],
	'inn' => [
		'xtype' => 'textfield',
		'value' => '741500616394',
		'area' => 'shoplogistic_requizites',
	],
	'kpp' => [
		'xtype' => 'shoplogistic_requizites',
		'value' => '',
		'area' => 'shoplogistic_requizites',
	]
];