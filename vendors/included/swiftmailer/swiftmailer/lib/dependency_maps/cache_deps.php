<?php
if (!defined('ABSPATH')) {
    exit;
}
WappoSwift_DependencyContainer::getInstance()
    ->register('cache')
    ->asAliasOf('cache.array')

    ->register('tempdir')
    ->asValue('/tmp')

    ->register('cache.null')
    ->asSharedInstanceOf('WappoSwift_KeyCache_NullKeyCache')

    ->register('cache.array')
    ->asSharedInstanceOf('WappoSwift_KeyCache_ArrayKeyCache')
    ->withDependencies(['cache.inputstream'])

    ->register('cache.disk')
    ->asSharedInstanceOf('WappoSwift_KeyCache_DiskKeyCache')
    ->withDependencies(['cache.inputstream', 'tempdir'])

    ->register('cache.inputstream')
    ->asNewInstanceOf('WappoSwift_KeyCache_SimpleKeyCacheInputStream');
