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
                    'name' => 'В очереди',
                    'color' => '000000',
                    'active' => 1,
                    'id' => 1
                ],
                [
                    'name' => 'В процессе',
                    'color' => '008000',
                    'active' => 1,
                    'id' => 2
                ],
                [
                    'name' => 'Выполнен',
                    'color' => '003366',
                    'active' => 1,
                    'id' => 3
                ]
            ];

            foreach ($statuses as $properties) {
                $id = $properties['id'];
                unset($properties['id']);

                $level = $modx->getObject('slReportsStatus', [
                    'id' => $id,
                    'OR:name:=' => $properties['name']
                ]);
                if (!$level) {
                    $level = $modx->newObject('slReportsStatus', $properties);
                }
                $level->save();

                $status_id = $level->get('id');
                $status_name = $properties['name'];
            }
            break;

        case xPDOTransport::ACTION_UNINSTALL:
            $modx->removeCollection('slReportsStatus', []);
            break;
    }
}
return true;