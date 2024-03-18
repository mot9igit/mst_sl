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

            // Статусы файлов выгрузки
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
                ],
                [
                    'name' => 'Импортирован',
                    'color' => '00FF00',
                    'active' => 1,
                    'id' => 4
                ],
                [
                    'name' => 'Ошибка',
                    'color' => 'FF0000',
                    'active' => 1,
                    'id' => 5
                ],
                [
                    'name' => 'В процессе',
                    'color' => 'С0С0С0',
                    'active' => 1,
                    'id' => 6
                ],
                [
                    'name' => 'Черновик',
                    'color' => 'FFFFFF',
                    'active' => 1,
                    'id' => 7
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

            // Статусы заданий парсера
            $statuses = [
                [
                    'name' => 'В очереди на обработку',
                    'color' => '000000',
                    'active' => 1,
                    'id' => 1
                ],
                [
                    'name' => 'Завершен',
                    'color' => '00FF00',
                    'active' => 1,
                    'id' => 2
                ],
                [
                    'name' => 'Ошибка',
                    'color' => 'FF0000',
                    'active' => 1,
                    'id' => 3
                ],
                [
                    'name' => 'В процессе',
                    'color' => 'С0С0С0',
                    'active' => 1,
                    'id' => 4
                ],
                [
                    'name' => 'Черновик',
                    'color' => 'FFFFFF',
                    'active' => 1,
                    'id' => 5
                ],
            ];

            foreach ($statuses as $properties) {
                $id = $properties['id'];
                unset($properties['id']);

                $level = $modx->getObject('slParserTasksStatus', [
                    'id' => $id,
                    'OR:name:=' => $properties['name']
                ]);
                if (!$level) {
                    $level = $modx->newObject('slParserTasksStatus', $properties);
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

            // Статусы подключений к программам
            $statuses = [
                [
                    'name' => 'Заявка на рассмотрении',
                    'color' => '000000',
                    'anchor' => '',
                    'anchor_description' => '',
                    'active' => 1,
                    'id' => 1
                ],[
                    'name' => 'Заявка одобрена',
                    'color' => '003366',
                    'anchor' => '',
                    'anchor_description' => '',
                    'active' => 1,
                    'id' => 2
                ],
                [
                    'name' => 'Отказ от программы',
                    'color' => '008000',
                    'anchor' => '',
                    'anchor_description' => '',
                    'active' => 1,
                    'id' => 3
                ]
            ];

            foreach ($statuses as $properties) {
                $id = $properties['id'];
                unset($properties['id']);

                $level = $modx->getObject('slBonusesConnectionStatus', [
                    'id' => $id,
                    'OR:name:=' => $properties['name']
                ]);
                if (!$level) {
                    $level = $modx->newObject('slBonusesConnectionStatus', $properties);
                }
                $level->save();

                $status_id = $level->get('id');
                $status_name = $properties['name'];
            }

            // Статусы отгрузок
            $statuses = [
                [
                    'name' => 'Ожидается',
                    'color' => '000000',
                    'anchor' => '',
                    'anchor_description' => '',
                    'active' => 1,
                    'id' => 1
                ],[
                    'name' => 'В пути',
                    'color' => '003366',
                    'anchor' => '',
                    'anchor_description' => '',
                    'active' => 1,
                    'id' => 2
                ],
                [
                    'name' => 'Выполнен',
                    'color' => '993300',
                    'anchor' => '',
                    'anchor_description' => '',
                    'active' => 1,
                    'id' => 3
                ],
                [
                    'name' => 'Выполнен частично',
                    'color' => '333300',
                    'anchor' => '',
                    'anchor_description' => '',
                    'active' => 4,

                ],
                [
                    'name' => 'Просрочен',
                    'color' => '003300',
                    'anchor' => '',
                    'anchor_description' => '',
                    'active' => 1,
                    'id' => 5
                ]
            ];

            foreach ($statuses as $properties) {
                $id = $properties['id'];
                unset($properties['id']);

                $level = $modx->getObject('slWarehouseShipmentStatus', [
                    'id' => $id,
                    'OR:name:=' => $properties['name']
                ]);
                if (!$level) {
                    $level = $modx->newObject('slWarehouseShipmentStatus', $properties);
                }
                $level->save();

                $status_id = $level->get('id');
                $status_name = $properties['name'];
            }

            // Статусы баланса
            $statuses = [
                [
                    'name' => 'На рассмотрении',
                    'color' => '000000',
                    'active' => 1,
                    'id' => 1
                ],[
                    'name' => 'Выполнена',
                    'color' => '003366',
                    'active' => 1,
                    'id' => 2
                ],
                [
                    'name' => 'Отклонена',
                    'color' => '003300',
                    'active' => 1,
                    'id' => 3
                ]
            ];

            foreach ($statuses as $properties) {
                $id = $properties['id'];
                unset($properties['id']);

                $level = $modx->getObject('slStoreBalancePayRequestStatus', [
                    'id' => $id,
                    'OR:name:=' => $properties['name']
                ]);
                if (!$level) {
                    $level = $modx->newObject('slStoreBalancePayRequestStatus', $properties);
                }
                $level->save();

                $status_id = $level->get('id');
                $status_name = $properties['name'];
            }

            // Статусы карточек товара
            $statuses = [
                [
                    'name' => 'Укажите бренд',
                    'color' => '000000',
                    'active' => 1,
                    'id' => 1
                ],[
                    'name' => 'Укажите артикул',
                    'color' => 'C0C0C0',
                    'active' => 1,
                    'id' => 2
                ],[
                    'name' => 'Сопоставлен',
                    'color' => '00FF00',
                    'active' => 1,
                    'id' => 4
                ],[
                    'name' => 'Нет карточки товара',
                    'color' => 'FF0000',
                    'active' => 1,
                    'id' => 5
                ],[
                    'name' => 'Укажите цену',
                    'color' => '969696',
                    'active' => 1,
                    'id' => 6
                ]
            ];

            foreach ($statuses as $properties) {
                $id = $properties['id'];
                unset($properties['id']);

                $level = $modx->getObject('slStoresRemainsStatus', [
                    'id' => $id,
                    'OR:name:=' => $properties['name']
                ]);
                if (!$level) {
                    $level = $modx->newObject('slStoresRemainsStatus', $properties);
                }
                $level->save();

                $status_id = $level->get('id');
                $status_name = $properties['name'];
            }

            // Статусы parserdata
            $statuses = [
                [
                    'name' => 'Новый',
                    'status_key' => 'NEW',
                    'color' => '33CCCC',
                    'active' => 1,
                    'id' => 1
                ],
                [
                    'name' => 'В очереди на обработку',
                    'status_key' => 'WAITING_DATA_PROCESSING',
                    'color' => '000000',
                    'active' => 1,
                    'id' => 2
                ],
                [
                    'name' => 'Завершен',
                    'status_key' => 'OK',
                    'color' => '00FF00',
                    'active' => 1,
                    'id' => 3
                ],
                [
                    'name' => 'Ошибка',
                    'status_key' => 'ERROR',
                    'color' => 'FF0000',
                    'active' => 1,
                    'id' => 4
                ],
                [
                    'name' => 'Обработка данных',
                    'status_key' => 'DATA_PROCESSING',
                    'color' => 'С0С0С0',
                    'active' => 1,
                    'id' => 5
                ],
                [
                    'name' => 'Ожидание парсинга',
                    'status_key' => 'WAITING_PARSING',
                    'color' => 'FFFFFF',
                    'active' => 1,
                    'id' => 6
                ],
                [
                    'name' => 'Парсинга',
                    'status_key' => 'PARSING',
                    'color' => 'FFFFFF',
                    'active' => 1,
                    'id' => 7
                ]
            ];

            foreach ($statuses as $properties) {
                $id = $properties['id'];
                unset($properties['id']);

                $level = $modx->getObject('slParserDataTasksStatus', [
                    'id' => $id,
                    'OR:name:=' => $properties['name']
                ]);
                if (!$level) {
                    $level = $modx->newObject('slParserDataTasksStatus', $properties);
                }
                $level->save();

                $status_id = $level->get('id');
                $status_name = $properties['name'];
            }

            // Сервисы parserdata
            $services = [
                [
                    'name' => 'Яндекс.Маркет',
                    'service_key' => 'market.yandex',
                    'url' => 'https://market.yandex.ru/',
                    'active' => 1,
                    'id' => 1
                ],
                [
                    'name' => 'Wildberries',
                    'service_key' => 'wildberries',
                    'url' => 'https://www.wildberries.ru/',
                    'active' => 1,
                    'id' => 2
                ],
                [
                    'name' => 'Все инструменты',
                    'service_key' => 'vseinstrumenti',
                    'url' => 'https://www.vseinstrumenti.ru/',
                    'active' => 1,
                    'id' => 3
                ]
            ];

            foreach ($services as $properties) {
                $id = $properties['id'];
                unset($properties['id']);

                $level = $modx->getObject('slParserDataService', [
                    'id' => $id,
                    'OR:name:=' => $properties['name']
                ]);
                if (!$level) {
                    $level = $modx->newObject('slParserDataService', $properties);
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
            $modx->removeCollection('slBonusesConnectionStatus', []);
            $modx->removeCollection('slWarehouseShipmentStatus', []);
            $modx->removeCollection('slStoreBalancePayRequestStatus', []);
            $modx->removeCollection('slStoresRemainsStatus', []);
            $modx->removeCollection('slParserTasksStatus', []);
            $modx->removeCollection('slParserDataTasksStatus', []);
            $modx->removeCollection('slParserDataService', []);
            break;
    }
}
return true;