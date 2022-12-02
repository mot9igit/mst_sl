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
			'OnHandleRequest' => [],
			'OnLoadWebDocument' => [],
			'OnMODXInit' => [],
			'OnPageNotFound' => [],
        ],
    ],
];