<?php

class slClassGetListProcessor extends modProcessor
{
    /**
     * @return string
     */
    public function process()
    {
        $type = $this->getProperty('type');
        $interface = 'ms' . ucfirst($type) . 'Interface';
        $handler = 'ms' . ucfirst($type) . 'Handler';

        $declared = get_declared_classes();
        /** @var shopLogistic $shopLogistic */
        $shopLogistic = $this->modx->getService('shopLogistic');
        $shopLogistic->loadCustomClasses($type);

        $declared = array_diff(get_declared_classes(), $declared);
        $available = [];
        foreach ($declared as $class) {
            if ($class == $handler || strpos($class, 'Exception') !== false) {
                continue;
            }
            try {
                $object = in_array($type, ['delivery'])
                    ? new $class($this->modx->newObject('msProduct'))
                    : new $class($shopLogistic);

                if (!empty($object) && is_a($object, $interface)) {
                    $available[] = [
                        'type' => $type,
                        'class' => $class,
                    ];
                }
            } catch (Error $e) {
                // nothing
            }
        }

        return $this->outputArray($available);
    }
}

return 'slClassGetListProcessor';