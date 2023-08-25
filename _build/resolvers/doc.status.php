<?php

/** @var xPDOTransport $transport */
/** @var array $options */
/** @var modX $modx */
if ($transport->xpdo) {
    $modx = $transport->xpdo;
    switch ($options[xPDOTransport::PACKAGE_ACTION]) {
        case xPDOTransport::ACTION_INSTALL:
        case xPDOTransport::ACTION_UPGRADE:
            $modx->addPackage('shoplogistic', MODX_CORE_PATH . 'components/shoplogistic/model/');
            $lang = $modx->getOption('manager_language') === 'en' ? 1 : 0;

            $statuses = [
                [
                    'name' => 'Новый',
                    'color' => '000000',
                    'active' => 1,
                    'id' => 1
                ],[
                    'name' => 'Не требует подписи',
                    'color' => '000000',
                    'active' => 1,
                    'id' => 2
                ],
                [
                    'name' => 'Требует подписи',
                    'color' => '008000',
                    'active' => 1,
                    'id' => 3
                ],
                [
                    'name' => 'Подписан',
                    'color' => '003366',
                    'active' => 1,
                    'id' => 4
                ]
            ];

            foreach ($statuses as $properties) {
                $id = $properties['id'];
                unset($properties['id']);

                $level = $modx->getObject('slDocsStatus', [
                    'id' => $id,
                    'OR:name:=' => $properties['name']
                ]);
                if (!$level) {
                    $level = $modx->newObject('slDocsStatus', $properties);
                }
                $level->save();

                $status_id = $level->get('id');
                $status_name = $properties['name'];
            }

            // Статусы файлов вынрузки
            $statuses = [
                [
                    'name' => 'В очереди на обработку',
                    'color' => '000000',
                    'active' => 1,
                    'id' => 1
                ],[
                    'name' => 'Обработан ждет модерацию',
                    'color' => '003366',
                    'active' => 1,
                    'id' => 2
                ],
                [
                    'name' => 'Прошел модерацию',
                    'color' => '008000',
                    'active' => 1,
                    'id' => 3
                ]
            ];

            foreach ($statuses as $properties) {
                $id = $properties['id'];
                unset($properties['id']);

                $level = $modx->getObject('slExportFileStatus', [
                    'id' => $id,
                    'OR:name:=' => $properties['name']
                ]);
                if (!$level) {
                    $level = $modx->newObject('slExportFileStatus', $properties);
                }
                $level->save();

                $status_id = $level->get('id');
                $status_name = $properties['name'];
            }

        // Статусы заявок
        $statuses = [
            [
                'name' => 'Новая',
                'color' => '000000',
                'active' => 1,
                'id' => 1
            ],[
                'name' => 'В обработке',
                'color' => '003366',
                'active' => 1,
                'id' => 2
            ],
            [
                'name' => 'Обработана',
                'color' => '008000',
                'active' => 1,
                'id' => 3
            ]
        ];

        foreach ($statuses as $properties) {
            $id = $properties['id'];
            unset($properties['id']);

            $level = $modx->getObject('slCardRequestStatus', [
                'id' => $id,
                'OR:name:=' => $properties['name']
            ]);
            if (!$level) {
                $level = $modx->newObject('slCardRequestStatus', $properties);
            }
            $level->save();

            $status_id = $level->get('id');
            $status_name = $properties['name'];
        }
            break;

        case xPDOTransport::ACTION_UNINSTALL:
            $modx->removeCollection('slDocsStatus', []);
            $modx->removeCollection('slExportFileStatus', []);
            $modx->removeCollection('slCardRequestStatus', []);
            break;
    }
}
return true;