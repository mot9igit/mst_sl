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
		'xtype' => 'numberfield',
		'value' => '',
		'area' => 'shoplogistic_main',
	],
	'cart_mode' => [
		'xtype' => 'numberfield',
		'value' => 2,
		'area' => 'shoplogistic_main',
	],
	'cart_to_warehouse' => [
		'xtype' => 'combo-boolean',
		'value' => 1,
		'area' => 'shoplogistic_main',
	],
	'mode' => [
		'xtype' => 'numberfield',
		'value' => 1,
		'area' => 'shoplogistic_main',
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
		'xtype' => 'textfield',
		'value' => '',
		'area' => 'shoplogistic_requizites',
	],
	'crm_webhook' => [
		'xtype' => 'textfield',
		'value' => 'https://mstmp.bitrix24.ru/rest/17/pbrmt2haeyl6feh0/',
		'area' => 'shoplogistic_crm',
	],
	'crm_product_key_field' => [
		'xtype' => 'textfield',
		'value' => 'product.article',
		'area' => 'shoplogistic_crm',
	],
	'crm_link_products' => [
		'xtype' => 'combo-boolean',
		'value' => 1,
		'area' => 'shoplogistic_crm',
	],
	'check_percent' => [
		'xtype' => 'textfield',
		'value' => 15,
		'area' => 'shoplogistic_products',
	],
	'default_stage' => [
		'xtype' => 'numberfield',
		'value' => 0,
		'area' => 'shoplogistic_crm',
	],
	'payment_stage' => [
		'xtype' => 'numberfield',
		'value' => 0,
		'area' => 'shoplogistic_crm',
	],
	'debug_log' => [
		'xtype' => 'combo-boolean',
		'value' => 0,
		'area' => 'shoplogistic_main',
	],
];