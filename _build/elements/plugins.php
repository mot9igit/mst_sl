<?php

return [
    'shopLogistic' => [
        'file' => 'shoplogistic',
        'description' => 'Base functional plugin',
        'events' => [
        	'msOnGetProductFields' => [],
			'msOnChangeOrderStatus' => [],
			'msOnCreateOrder' => [],
			'msOnManagerCustomCssJs' => [],
			'OnDocFormRender' => [],
			'OnLoadWebDocument' => [],
			'OnMODXInit' => [],
        ],
    ],
];