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
	'express_delivery' => [
		'xtype' => 'textfield',
		'value' => 1,
		'area' => 'shoplogistic_eshoplogistic',
	],
    'pickup_delivery' => [
        'xtype' => 'textfield',
        'value' => 1,
        'area' => 'shoplogistic_eshoplogistic',
    ],
    'blank_image' => [
        'xtype' => 'textfield',
        'value' => 'assets/components/shoplogistic/img/nopic.png',
        'area' => 'shoplogistic_main',
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
    'store_colors' => [
        'xtype' => 'textfield',
        'value' => '',
        'area' => 'shoplogistic_main',
    ],
	'open_fields_warehouse' => [
		'xtype' => 'textfield',
		'value' => 'contact,email,phone',
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
		'value' => '',
		'area' => 'shoplogistic_requizites',
	],
	'inn' => [
		'xtype' => 'textfield',
		'value' => '',
		'area' => 'shoplogistic_requizites',
	],
	'kpp' => [
		'xtype' => 'textfield',
		'value' => '',
		'area' => 'shoplogistic_requizites',
	],
    // CRM settings
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
    'assigned_by_id' => [
        'xtype' => 'numberfield',
        'value' => 0,
        'area' => 'shoplogistic_crm',
    ],
    'type_id' => [
        'xtype' => 'textfield',
        'value' => '',
        'area' => 'shoplogistic_crm',
    ],
	'debug_log' => [
		'xtype' => 'combo-boolean',
		'value' => 0,
		'area' => 'shoplogistic_main',
	],
    'check_percent' => [
        'xtype' => 'textfield',
        'value' => 15,
        'area' => 'shoplogistic_products',
    ],
    // Delivery Settings
    'cdek_test_url' => [
        'xtype' => 'textfield',
        'value' => 'https://api.edu.cdek.ru/v2/',
        'area' => 'shoplogistic_cdek',
    ],
    'cdek_test_account' => [
        'xtype' => 'textfield',
        'value' => 'EMscd6r9JnFiQ3bLoyjJY6eM78JrJceI',
        'area' => 'shoplogistic_cdek',
    ],
    'cdek_test_pass' => [
        'xtype' => 'textfield',
        'value' => 'PjLZkKBHEiLK3YsjtNrt3TGNG0ahs3kG',
        'area' => 'shoplogistic_cdek',
    ],
    'cdek_url' => [
        'xtype' => 'textfield',
        'value' => 'https://api.cdek.ru/v2/',
        'area' => 'shoplogistic_cdek',
    ],
    'cdek_account' => [
        'xtype' => 'textfield',
        'value' => '',
        'area' => 'shoplogistic_cdek',
    ],
    'cdek_pass' => [
        'xtype' => 'textfield',
        'value' => '',
        'area' => 'shoplogistic_cdek',
    ],
    'cdek_test_mode' => [
        'xtype' => 'combo-boolean',
        'value' => 0,
        'area' => 'shoplogistic_cdek',
    ],
    'cdek_token' => [
        'xtype' => 'textfield',
        'value' => '',
        'area' => 'shoplogistic_cdek',
    ],
    'yandex_oauth_token' => [
        'xtype' => 'textfield',
        'value' => '',
        'area' => 'shoplogistic_yandex',
    ],
    'yandex_express_url' => [
        'xtype' => 'textfield',
        'value' => 'https://b2b.taxi.yandex.net/',
        'area' => 'shoplogistic_yandex',
    ],
    'yandex_express_url_test' => [
        'xtype' => 'textfield',
        'value' => 'https://b2b.taxi.yandex.net/',
        'area' => 'shoplogistic_yandex',
    ],
    'yandex_delivery_url' => [
        'xtype' => 'textfield',
        'value' => 'https://b2b-authproxy.taxi.yandex.net/',
        'area' => 'shoplogistic_yandex',
    ],
    'yandex_delivery_url_test' => [
        'xtype' => 'textfield',
        'value' => 'https://b2b.taxi.tst.yandex.net/',
        'area' => 'shoplogistic_yandex',
    ],
    'yandex_delivery_platform_id_test' => [
        'xtype' => 'textfield',
        'value' => 'fbed3aa1-2cc6-4370-ab4d-59c5cc9bb924',
        'area' => 'shoplogistic_yandex',
    ],
    'postrf_token' => [
        'xtype' => 'textfield',
        'value' => '',
        'area' => 'shoplogistic_postrf',
    ],
    'postrf_key' => [
        'xtype' => 'textfield',
        'value' => '',
        'area' => 'shoplogistic_postrf',
    ],
    'postrf_url' => [
        'xtype' => 'textfield',
        'value' => 'https://otpravka-api.pochta.ru/',
        'area' => 'shoplogistic_postrf',
    ],
    // PARSER DATA
    'parserdata_token' => [
        'xtype' => 'textfield',
        'value' => '',
        'area' => 'shoplogistic_parserdata',
    ],
    'parserdata_url' => [
        'xtype' => 'textfield',
        'value' => 'https://apimarket.parserdata.ru/api/v2/',
        'area' => 'shoplogistic_parserdata',
    ],
    // ORDER SETTINGS
    'tax_percent' => [
        'xtype' => 'numberfield',
        'decimalPrecision' => 2,
        'value' => 5,
        'area' => 'shoplogistic_order',
    ],
    'regenerate_code' => [
        'xtype' => 'combo-boolean',
        'value' => 1,
        'area' => 'shoplogistic_order',
    ],
    'code_live' => [
        'xtype' => 'numberfield',
        'value' => 0,
        'area' => 'shoplogistic_order',
    ]
];