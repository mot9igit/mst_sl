<?php
/** @var xPDOTransport $transport */
/** @var array $options */
/** @var modX $modx */
if ($transport->xpdo) {
    $modx =& $transport->xpdo;

    $dev = MODX_BASE_PATH . 'Extras/shopLogistic/';
    /** @var xPDOCacheManager $cache */
    $cache = $modx->getCacheManager();
    if (file_exists($dev) && $cache) {
        if (!is_link($dev . 'assets/components/shoplogistic')) {
            $cache->deleteTree(
                $dev . 'assets/components/shoplogistic/',
                ['deleteTop' => true, 'skipDirs' => false, 'extensions' => []]
            );
            symlink(MODX_ASSETS_PATH . 'components/shoplogistic/', $dev . 'assets/components/shoplogistic');
        }
        if (!is_link($dev . 'core/components/shoplogistic')) {
            $cache->deleteTree(
                $dev . 'core/components/shoplogistic/',
                ['deleteTop' => true, 'skipDirs' => false, 'extensions' => []]
            );
            symlink(MODX_CORE_PATH . 'components/shoplogistic/', $dev . 'core/components/shoplogistic');
        }
    }
}

return true;