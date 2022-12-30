<?php

return [
    'shopLogistic' => [
        'file' => 'shoplogistic',
        'description' => 'Base functional plugin',
        'events' => [
			'msOnChangeOrderStatus' => [],
			'msOnCreateOrder' => [],
			'msOnManagerCustomCssJs' => [],
			'OnDocFormRender' => [],
			'OnLoadWebDocument' => [],
			'OnMODXInit' => [],
        ],
    ],
];